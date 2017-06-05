<?php
/**
 * Base Model
 *
 * @date        : 2016/07/27
 * @author      : Tiger <DropFan@Gmail.com>
 *
 * @last-modified : 2016/11/26
 * @author        : Tiger <DropFan@Gmail.com>
 */
require_once 'inc/class/SDB.php';

abstract class BaseModel
{
    protected $sdb;

    public function __construct($data = null)
    {
        // debug('__construct :'. $data);
        // var_dump($data);
        $log = getLogInstance(get_class() . '::__construct()');
        $log->debug(json_encode($data));
        if (is_integer($data)) {
            $log->debug('`data` is integer');
            $id = $data;
        } elseif (is_array($data) && !empty($empty)) {
            $log->debug('`data` is array');
            $this->fillWith($data);
            $id = isset($data['id']) ? intval($data['id']) : 0;
        } else {
            debug('fucking data!');
            $log->debug('Bad Data');

            return null;
        }

        $this->sdb = static::getDB();
        $log->debug('sdb:' . json_encode($this->sdb));
        if ($id > 0) {
            $this->fetchById($id);
            debug('fetch by id:' . $id);
            $log->debug('fetch by id:' . $id);
        } elseif ($id === 0 && !empty($data)) {
            debug('fuck?');
            $this->fillWith($data);
            debug(' new :' . $this->id);
            $log->debug(' new :' . $this->id);
        }
    }


    /**
     * Gets sdb instance
     *
     * @return     SDB  SDB instance
     */
    public function db()
    {
        return static::getDB();
    }


    /**
     * Gets mysqli instance from SDB->service
     *
     * @return     mysqli  mysqli instance
     */
    public function mysqli()
    {
        return static::getDB()->service;
    }

    /**
     * Gets the table name.
     *
     * @return     string  The table name string.
     */
    public function getTableName()
    {
        if (method_exists($this, 'getTable')) {
            return $this->getTable();
        }
        return static::$tableName;
    }

    public function fetchById($id)
    {
        $log = getLogInstance(get_class() . '::fetchById()');
        $table  = static::$tableName;
        $fields = array_keys(static::$tableFields);

        $selection = "`id` ='$id'";

        $log->debug(json_encode($id));
        $log->debug(json_encode($table));
        $log->debug(json_encode($fields));
        $log->debug(json_encode($selection));
        $log->debug(json_encode('`id` DESC'));

        $rows = $this->sdb->select($table, $fields, $selection, [], [], '`id` DESC', 0, 1);

        foreach ($rows as $key => $value) {
            $row = $value;
        }

        if ($row) {
            foreach ($row as $key => $value) {
                $this->$key = $value;
            }
        }
        $log->debug(json_encode($row));
        return $row;
    }

    public static function create(array $data)
    {
        $log = getLogInstance(get_class() . '::create()');
        $log->debug("param:" . json_encode($data));

        $instance = new static(0);
        $instance->fillwith($data);
        // var_dump($data);die;
        $instance->add();
        $log->debug("return:" . json_encode($instance));
        return $instance;
    }

    protected function getFillable()
    {
        if (isset(static::$fillable) && !empty(static::$fillable)) {
            $fillable = static::$fillable;
        } elseif (isset(static::$notfill) && !empty(static::$notfill)) {
            $notfill  = static::$notfill;
            $fillable = array_diff_key(static::$tableFields, $notfill);
        } else {
            $fillable = static::$tableFields;
        }

        return $fillable;
    }

    public function fillWith(array $data)
    {
        $table = static::$tableName;
        // $fields = array_keys(static::$tableFields);
        $fillable = array_keys($this->getFillable());

        foreach ($data as $k => $v) {
            if (in_array($k, $fillable)) {
                switch (static::$tableFields[$k]) {
                    case 'i':
                    case 'int':
                    case 'integer':
                    case 'ts':
                    case 'timestamp':
                        $this->$k = intval($v);
                        break;
                    case 'f':
                    case 'd':
                    case 'float':
                    case 'double':
                        $this->$k = floatval($v);
                        break;
                    case 's':
                    case 'str':
                    case 'string':
                    case 'email':
                    case 'uri':
                    case 'url':
                    case 'phone':
                    case 'mobile':
                    case 'text':
                    case 'json':
                        $this->$k = (string) $v;
                        break;
                    case 'bool':
                    case 'boolean':
                        $falseList = ['false', 'null', 'undefined', 'nan', 'n/a', '[]', '{}', '--', '-'];

                        if (is_string($v) && in_array(strtolower($v), $falseList)) {
                            $v = false;
                        }

                        $this->$k = boolval($v);
                        break;
                    case 'dt':
                    case 'date':
                    case 'datetime':
                    case 'time':
                        $old_timezone = date_default_timezone_get();
                        date_default_timezone_set('UTC');
                        $this->$k = gmdate('Y-m-d H:i:s', strtotime($v));
                        date_default_timezone_set($old_timezone);
                        break;
                    case 'bin':
                    case 'origin':
                    case 'o':
                        $this->$k = $v;
                        break;
                    default:
                        debug('error type:' . $k);
                        throw new Exception("Invalid type of field definition ($table -> $k)");
                        break;
                }
            }
        }

        debug('fill with data finished.');

        return $this;
    }

    public function add()
    {
        $this->id = $this->insert();

        return $this;
    }

    public static function getOneById($id)
    {
        $sdb    = static::getDB();
        $table  = static::$tableName;
        $fields = array_keys(static::$tableFields);

        $selection = "`id` = '$id'";

        $rows = $sdb->select($table, $fields, $selection, [], [], '`id` DESC', 0, 1);

        foreach ($rows as $key => $value) {
            $record = $value;
        }

        return $record;
    }

    public static function parseWhere($where)
    {
        $log = getLogInstance(get_class() . '::parseWhere()');
        $log->debug('param:' . json_encode($where));
        $fields = array_keys(static::$tableFields);
        $result = ' 1 ';

        if (empty($where)) {
            $result = '';
        } elseif (is_array($where)) {
            foreach ($where as $field => $condition) {
                if (is_integer($field) && is_string($condition)) {
                    $result .= $condition;
                } elseif (in_array($field, $fields) && !empty($condition)) {
                    if (is_string($condition)) {
                        $result .= " AND `{$field}` = '{$condition}'";
                    } elseif (is_array($condition)) {
                        foreach ($condition as $k => $v) {
                            if (is_integer($k) && is_string($v)) {
                                $result .= " AND `{$field}` = '{$v}'";
                                continue;
                            }
                            if (in_array(strtoupper($k), ['<', '>', '=', '<=', '>=', '<>', '!=', 'LIKE', 'NOT LIKE'])) {
                                $result .= " AND `{$field}` {$k} '{$v}'";
                            } elseif (in_array(strtoupper($k), ['IN', 'NOT IN']) && is_array($v)) {
                                $val_str = implode(',', $v);
                                $result .= " AND `{$field}` {$k} ({$val_str})";
                            }
                        } // end foreach $condition
                    }
                }
            } //end foreach $where
        } elseif (is_string($where)) {
            $result .= ' AND ' . $where;
        }
        $log->debug('return:' . json_encode($result));
        return $result;
    }

    public static function parseOrder($sortby)
    {
        $log = getLogInstance(get_class() . '::parseOrder()');
        $log->debug('param:' . json_encode($sortby));
        $fields = array_keys(static::$tableFields);
        $result = '';
        $order  = [];
        if (empty($sortby)) {
            $result = '`id` DESC';
        } elseif (is_array($sortby)) {

            foreach ($sortby as $field => $value) {

                if (in_array($field, $fields)
                    && in_array(strtoupper($value), ['DESC', 'ASC'])) {
                    $order[] = " `{$field}` {$value}";
                } elseif (in_array($value, $fields)) {
                    $order[] = "`{$value}`";
                } elseif (is_integer($field) && is_string($value)) {
                    $result .= ", {$value}";
                }
            }
            $order = implode(' , ', $order);
            $result .= $order;

        } elseif (is_string($sortby)) {
            $result = $sortby;
        }

        $result = trim_recursive($result, ', ') . ', `id` DESC';
        $log->debug('return:' . json_encode($result));
        return $result;
    }

    public static function getAll($page = 1, $perpage = 100, $where = [], $sortby = [], $search = '')
    {
        $sdb    = static::getDB();
        $table  = static::$tableName;
        $fields = array_keys(static::$tableFields);

        $selection = ' 1 ';

        if (empty($where)) {
            $selection = '';
        } elseif (is_array($where)) {
            $selection = static::parseWhere($where);
        } elseif (is_string($where)) {
            $selection .= $where;
        }
        debug("where: {$selection}");

        // $order = [];
        if (!empty($sortby)) {
            $order = static::parseOrder($sortby);
        } else {
            $order = '`id` DESC';
        }
        debug("order: {$order}");

        $start = ($page - 1) * $perpage;
        $start < 0 && $start = 0;
        $limit = $perpage;

        $rows = $sdb->select($table, $fields, $selection, [], [], $order, $start, $limit);

        $records = [];
        foreach ($rows as $key => $value) {
            $records[] = $value;
        }

        return $records;
    }

    public static function getCount($where = [])
    {
        $sdb    = static::getDB();
        $table  = static::$tableName;
        $fields = array_keys(static::$tableFields);

        $selection = '';

        if (empty($where)) {
            $selection = '';
        } elseif (is_array($where)) {
            $selection = static::parseWhere($where);
        } elseif (is_string($where)) {
            $selection .= $where;
        }

        $count = $sdb->count($table, $selection, [], []);
        // var_dump($count);die;

        return $count;
    }

    public function update()
    {
        $log = getLogInstance(get_class() . '::update()');
        $id = $this->id;

        $table      = static::$tableName;
        $fields_all = static::$tableFields;

        $fillable = $this->getFillable();
        // var_dump($fillable);die;

        $params = [];
        $fields = [];
        $types  = [];

        foreach ($fillable as $field => $type) {
            $params[] = empty($this->$field) ? '' : $this->$field;
            $fields[] = $field;
            $types[]  = $type;
        }
        // var_dump($this);die;
        $log->debug('dump:' . json_encode($this));
        return $this->sdb->update($table, $id, $fields, $params, $types);
    }

    public function insert()
    {
        $log = getLogInstance(get_class() . '::insert()');
        $table      = static::$tableName;
        $fields_all = static::$tableFields;

        $fillable = $this->getFillable();

        $fields = [];
        $params = [];
        $types  = [];

        foreach ($fillable as $field => $type) {
            $params[] = empty($this->$field) ? '' : $this->$field;
            $fields[] = $field;
            $types[]  = $type;
        }
        $log->debug('dump:' . json_encode($this));
        return $this->sdb->insert($table, $fields, $params, $types);
    }

    public function delete()
    {
        $log = getLogInstance(get_class() . '::delete()');

        $id = $this->id;

        $table = static::$tableName;

        $log->debug("id:$id");
        $log->debug("table:$table");

        return $this->sdb->delete($table, $id);
    }

    public static function deleteAll($idList)
    {
        $log = getLogInstance(get_class() . '::deleteAll()');
        $log->debug('param:' . json_encode($idList));
        $table = static::$tableName;
        $sdb   = static::getDB();
        $count = 0;

        if (!is_array($idList)) {
            $idList = [$idList];
        }

        if (empty($idList)) {
            $log->error('param `idList` is empty');
            return false;
        }

        foreach ($idList as $id) {
            $count += $sdb->delete($table, $id);
        }
        $log->debug('return:count:' . $count);
        return $count;
    }
}
