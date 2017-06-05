<?php
/**
 * Class SDB is a mysqli helper.
 *
 * @date  a few years ago~~ (before 2011? 2012?)
 *
 * @last-modifyed : 2016/11/26
 * @author        : Tiger <DropFan@Gmail.com>
 */
class SDB
{
    public $service;

    /**
     * An array to store several instances of SDB
     * @var array
     */
    private static $instance = [];

    private $dbname = '';
    private $host   = '127.0.0.1';
    private $user   = '';
    private $pass   = '';
    private $port   = 3306;

    public static function uuid()
    {
        return sprintf(
            '%04x%04x%04x%04x%04x%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    public static function getInstance($name, $config = [])
    {
        /*if (self::$instance == null) {
        self::$instance = new SDB();
        }*/

        $log = getLogInstance(get_class() . '::getInstance()');
        $__params__ = ['name', 'config'];
        foreach ($__params__ as $k) {
            $log->debug("param:`$k`:" . json_encode($$k));
        }

        if (!isset(self::$instance[$name]) || !self::$instance[$name]) {
            self::$instance[$name] = new SDB($config);
        }

        return self::$instance[$name];
    }

    private function __construct($config = [])
    {
        if (!$config || !is_array($config)) {
            $configs = require 'config.php';
            $config  = $configs['db_default'];
        }

        $this->host   = $config['host'] ?: '127.0.0.1';
        $this->port   = $config['port'] ?: '3306';
        $this->user   = $config['user'] ?: 'test';
        $this->pass   = $config['pass'] ?: '123456';
        $this->dbname = $config['name'] ?: 'test';

        $this->service = new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->port);
    }

    /**
     * Executes a sql query with $mysqli->query()
     *
     * @see    http://php.net/manual/zh/mysqli.query.php
     *
     * @param  string  $sql               SQL statement
     * @param  int     $resultmode        The resultmode constant:
     *                                    MYSQLI_USE_RESULT or MYSQLI_STORE_RESULT
     *
     * @return boolean|mysqli_result  return by $mysqli->query()
     */
    public function query($sql, $resultmode = MYSQLI_STORE_RESULT)
    {
        $log = getLogInstance(get_class() . '::query()');
        $log->debug('sql:' . $sql);
        return $this->service->query($sql, $resultmode);
    }

    /**
     * Gets the number of affected rows in a previous MySQL operation
     *
     * @see    http://php.net/manual/zh/mysqli.affected-rows.php
     *
     * @return int  Number of affected rows in previous MySQL operation
     */
    public function affectedRows()
    {
        $count =  $this->service->affected_rows;
        return $count;
    }

    /**
     * Gets mysqli error
     *
     * @return string last error info in mysqli
     */
    public function error()
    {
        $error = $this->service->error;
        return $error();
    }

    /**
     * Gets mysqli errno
     *
     * @return int last error number in mysqli
     */
    public function errno()
    {
        $errno = $this->service->errno;
        return $errno;
    }

    private function checkParamCount($params, $types)
    {
        return count($params) === count($types);
    }

    private function buildParams(&$stmt, $params, $types)
    {
        $log = getLogInstance(get_class() . '::getInstance()');
        $__params__ = ['stmt', 'params', 'types'];
        foreach ($__params__ as $k) {
            $log->debug("param:`$k`:" . json_encode($$k));
        }

        $count = count($params);
        if ($count == 0) {
            return;
        }
        $types = $this->buildTypes($types);

        $func_params   = [];
        $func_params[] = implode('', $types);

        for ($i = 0; $i < $count; $i++) {
            $param         = 'bind' . $i;
            $$param        = $params[$i];
            $func_params[] = &$$param;

            // debug("param[$i]: $param, $type");
            // debug("func_params[$i]: " . $func_params[$i]);
        }
        debug('params >> ' . join('+', $params));
        debug('types >> ' . join('+', $types));
        debug('func_params >> ' . join('+', $func_params));

        $log->debug('params >> ' . json_encode($params));
        $log->debug('types >> ' . json_encode($types));
        $log->debug('func_params >> ' . json_encode($func_params));

        call_user_func_array([$stmt, 'bind_param'], $func_params);

    }

    private function buildTypes($types)
    {
        $t = [];
        foreach ($types as $type) {
            switch ($type) {
                case 'i':
                case 'int':
                case 'integer':
                case 'ts':
                case 'timestamp':
                case 'bool':
                case 'boolean':
                    $t[] = 'i';
                    break;
                case 'f':
                case 'float':
                case 'd':
                case 'double':
                    $t[] = 'd';
                    break;
                case 's':
                case 'str':
                case 'string':
                case 'dt':
                case 'date':
                case 'datetime':
                case 'time':
                case 'text':
                case 'json':
                    $t[] = 's';
                    break;
                case 'b':
                case 'bin':
                case 'o':
                case 'origin':
                    $t[] = 'b';
                    break;
                default:
                    debug('buildTypes error. unknown type :' . $type);
                    $t[] = 'b';
                    break;
            }
        }

        return $t;
    }

    private function bindAssoc(&$stmt, &$out)
    {
        $data   = $stmt->result_metadata();
        $fields = [];
        $out    = [];

        while ($field = mysqli_fetch_field($data)) {
            $fields[] = &$out[$field->name];
        }

        call_user_func_array([$stmt, 'bind_result'], $fields);
        $log = getLogInstance(get_class() . '::bindAssoc()');
        $log->debug('stmt:' . json_encode($stmt));
        $log->debug('out:' . json_encode($out));
    }

    private function printParams($params, $types)
    {
        // $log = getLogInstance(get_class() . '::printParams()');
        for ($i = 0; $i < count($params); $i++) {
            $param = $params[$i];
            $type  = $types[$i];
            // $log->debug("param[$i]: $param, $type");
            // debug("param[$i]: $param, $type");
        }
    }

    private function arrayCopy($arr)
    {
        $arr2 = [];

        foreach ($arr as $key => $value) {
            $arr2[$key] = $value;
        }

        return $arr2;
    }

    public function count($table, $selection = '', $params, $types)
    {
        $log = getLogInstance(get_class() . '::count()');
        $__params__ = ['table', 'selection', 'params', 'types'];
        foreach ($__params__ as $k) {
            $log->debug("param:`$k`:" . json_encode($$k));
        }

        if (!$this->checkParamCount($params, $types)) {
            $log->warning('bad param count!');
            return null;
        }

        if (!empty($selection)) {
            $selection = "WHERE $selection";
        } else {
            $selection = '';
        }

        $sql = "SELECT COUNT(1) FROM `$table` $selection";
        debug($sql);
        $log->debug("sql:$sql");

        if ($stmt = $this->service->prepare($sql)) {
            $this->buildParams($stmt, $params, $types);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                $row = $result->fetch_row();
                debug('result :' . $row[0]);
                $log->debug('result :' . $row[0]);
                return $row[0];
            }
            debug('count err:' . $this->service->error);
            $log->debug('count err:' . $this->service->error);
            $stmt->close();
        }

        return false;
    }

    public function select($table, $projection, $selection, $params, $types, $order, $start, $perpage)
    {
        $log = getLogInstance(get_class() . '::select()');
        $__params__ = ['table', 'projection', 'selection', 'params', 'types', 'order', 'start', 'perpage'];
        foreach ($__params__ as $k) {
            $log->debug("param:`$k`:" . json_encode($$k));
        }

        if (!$this->checkParamCount($params, $types)) {
            return null;
        }

        $selection = trim($selection);
        if (!empty($selection)) {
            $selection = "WHERE $selection";
        }

        $order = trim($order);
        if (!empty($order)) {
            $order = "ORDER BY $order";
        }

        //notice that $start can be 0 !!!
        if (!empty($perpage)) {
            $limit = 'LIMIT ' . $start . ',' . $perpage;
            debug('LIMIT: ' . $limit);
        } else {
            $limit = '';
        }

        if (is_array($projection)) {
            $sql = 'SELECT `' . implode('`, `', $projection) . "` FROM `$table` $selection $order $limit";
        } else {
            $sql = "SELECT $projection FROM `$table` $selection $order $limit";
        }

        debug("select: $sql");
        $log->debug("select: $sql");

        $rows = [];

        if ($stmt = $this->service->prepare($sql)) {
            $this->buildParams($stmt, $params, $types);
            $stmt->execute();
            $row = [];

            $this->bindAssoc($stmt, $row);

            while ($stmt->fetch()) {
                $rows[] = $this->arrayCopy($row);
            }
            debug('select err:' . $this->service->error);
            $log->debug('select err:' . $this->service->error);
            $stmt->close();
        }
        $log->debug('return:' . json_encode($rows));
        return $rows;
    }

    public function get($table, $id, $projection)
    {
        $where = 'where id=? limit 1';

        if (is_array($projection)) {
            $sql = 'select `' . implode('`, `', $projection) . '` from `' . $table . '` ' . $where;
        } else {
            $sql = "select $projection from `$table` $where";
        }

        $row = null;

        if ($stmt = $this->service->prepare($sql)) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $row = [];

            $this->bindAssoc($stmt, $row);
            if (!$stmt->fetch()) {
                $row = null;
            }

            $stmt->close();
        }

        return $row;
    }

    public function update($table, $id, $projection, $params, $types)
    {
        $log = getLogInstance(get_class() . '::update()');
        $__params__ = ['table', 'id', 'projection', 'params', 'types'];
        foreach ($__params__ as $k) {
            $log->debug("param:`$k`:" . json_encode($$k));
        }

        if (!$this->checkParamCount($params, $types)) {
            return null;
        }

        $sql = "UPDATE `$table` SET `" . implode('`=?, `', $projection) . '`=? WHERE id=?';
        debug("update: $sql");
        $log->debug("update: $sql");

        $affected_rows = 0;

        if ($stmt = $this->service->prepare($sql)) {
            $params[] = $id;
            $types[]  = 'i';

            $this->printParams($params, $types);

            $this->buildParams($stmt, $params, $types);

            $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
        }
        debug('update err:' . $this->service->error);
        $log->debug('update err:' . $this->service->error);
        $log->debug('return:' . $affected_rows);

        return $affected_rows;
    }

    public function insert($table, $projection, $params, $types)
    {
        $log = getLogInstance(get_class() . '::insert()');
        $__params__ = ['table', 'projection', 'params', 'types'];
        foreach ($__params__ as $k) {
            $log->debug("param:`$k`:" . json_encode($$k));
        }

        if (!$this->checkParamCount($params, $types)) {
            return null;
        }

        $sql = 'INSERT INTO `' . $table . '` (`' . implode('`,`', $projection) . '`) VALUES (' . str_repeat('?,', count($params) - 1) . ' ?);';
        debug("insert: $sql count: " . count($params));
        $log->debug("insert: $sql count: " . count($params));

        if ($stmt = $this->service->prepare($sql)) {
            $this->printParams($params, $types);

            $this->buildParams($stmt, $params, $types);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $insert_id = $stmt->insert_id;
            }
            debug('insert err:' . $this->service->error);
            $log->debug('insert err:' . $this->service->error);

            $stmt->close();
        }
        $log->debug('return:`insert_id`' . json_encode($insert_id));
        if (isset($insert_id)) {
            return $insert_id;
        } else {
            return null;
        }
    }

    public function delete($table, $id)
    {
        $log = getLogInstance(get_class() . '::delete()');
        $__params__ = ['table', 'id'];
        foreach ($__params__ as $k) {
            $log->debug("param:`$k`:" . json_encode($$k));
        }

        $sql = "DELETE FROM `$table` WHERE id=?";
        debug("delete: $sql");
        $log->debug("delete: $sql");

        $affected_rows = 0;

        if ($stmt = $this->service->prepare($sql)) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
        }
        debug('delete err:' . $this->service->error);
        $log->debug('delete err:' . $this->service->error);
        $log->debug('return:affected_rows:' . $affected_rows);

        return $affected_rows;
    }

    public function real_escape($string)
    {
        return $this->service->real_escape_string($string);
    }
}
