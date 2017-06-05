<?php
require_once 'inc/class/BaseModel.php';

/**
 * Data Model of revenue tables
 *
 * @date          : 2016/11/23
 * @author        : Tiger <DropFan@Gmail.com>
 *
 * @last-modified : 2016/11/26
 * @author        : Tiger <DropFan@Gmail.com>
 */
class RevModel extends BaseModel
{
    public static $db        = 'opdata';
    public static $tableName = 'revenue_app';
    public static $sdbName   = 'revenue_app';


    public static $tableFields = [
        'id'         => 'integer',
        'platform'   => 'string',
        'appname'    => 'string',
        'revenue'    => 'float',
        'date'       => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static $fillable = [
    ];

    public static $notfill = [
        'id'         => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public $startDate = '1970-01-01';
    public $endDate   = '1970-01-01';

    public $platform = '';
    public $appname  = '';

    public $placement = '';
    public $country   = '';

    protected $breakdowns = ['platform', 'appname', 'placement', 'country'];

    protected $breakdown = [
        'platform'  => '',
        'appname'   => '',
        'placement' => '',
        'country'   => ''
    ];
    protected $valueSelected = ['revenue'];
    protected $valueSelectable = [
        'revenue',
        'request', 'fill', 'impression', 'click',
        'fill_rate', 'imp_rate', 'click_rate',
        'ecpm'
    ];


    /**
     * __construct method
     *
     * @param array or int $data data array or id
     */
    /*public function __construct($data = null)
    {
        parent::__construct($data);

        $this->created_at = date('Y-m-d H:i:s', strtotime($this->created_at) + 28800);
        $this->updated_at = date('Y-m-d H:i:s', strtotime($this->updated_at) + 28800);
    }*/


    /**
     * Construct method of Revmodel
     *
     * @param      string  $startDate  The start date
     * @param      string  $endDate    The end date
     * @param      string  $platform   The platform
     * @param      string  $app        The appname
     */
    public function __construct($startDate = '', $endDate = '', $platform = '', $app = '')
    {

        $this->configs = require $_SERVER['DOCUMENT_ROOT'] . '/../config/opdata.php';

        $this->platforms = $this->configs['platforms'];
        $this->apps = $this->configs['apps'];

        $this->breakdowns = ['platform', 'appname', 'placement', 'country'];

        in_array($platform, $this->platforms) && $this->setPlatform($platform);
        in_array($app, $this->apps) && $this->setAppname($app);

        $this->startDate = date('Y-m-d', strtotime($startDate));
        $this->endDate   = date('Y-m-d', strtotime($endDate));
    }


    /**
     * Select values to query
     *
     * @param      array|string      $value  The value array like this:
     *                                       ['revenue', 'request', 'click']
     *
     * @throws     Exception  The param is invalid! It should be array or string.
     * @throws     Exception  Not support this value.
     *
     * @return     RevModel     ( description_of_the_return_value )
     */
    public function selectValue($value = [])
    {
        if (is_string($value)) {
            $value = [$value];
        } elseif (!is_array($value)) {
            throw new Exception('The param is invalid! It should be array or string');
        }

        foreach ($value as $v) {
            if (in_array($v, $this->valueSelectable)) {
                $this->valueSelected[] = $v;
                $this->valueSelected = array_unique($this->valueSelected);
            } else {
                throw new Exception('Not support this value.');
            }
        }

        return $this;
    }

    /**
     * Sets the breakdown.
     *
     * @param      array      $breakdowns  The breakdowns array like this:
     *                                    [
     *                                        'platform'  => 'facebook',
     *                                        'app'       => 'amber_weather',
     *                                        'country'   => 'CN',
     *                                        'placement' => '1234567890'
     *                                    ];
     *
     * @throws     Exception  This breakdown ($k) is not supported.
     *
     * @return     RevModel     ( description_of_the_return_value )
     */
    public function setBreakdown(array $breakdowns)
    {
        foreach ($breakdowns as $k => $v) {
            if (is_string($k) && in_array($k, $this->breakdowns)) {
                call_user_func([&$this, "set{$k}"], $v);
            } else {
                throw new Exception("This breakdown ($k) is not supported.");
            }
        }
        return $this;
    }

    /**
     * Sets the platform.
     *
     * @param      string     $platform  The platform name in table
     *
     * @throws     Exception  This platform ({$platform}) is empty or not supported.
     *
     * @return     RevModel     ( description_of_the_return_value )
     */
    public function setPlatform($platform = '')
    {
        if (is_string($platform) && in_array($platform, $this->platforms)) {
            $this->platform = $platform;
            $this->breakdown['platform'] = $platform;
        } else {
            throw new Exception("This platform ({$platform}) is empty or not supported.");
        }

        return $this;
    }

    /**
     * Sets the appname.
     *
     * @param      string     $app    The appname in table
     *
     * @throws     Exception  This app ({$app}) is empty or not supported.
     *
     * @return     RevModel     ( description_of_the_return_value )
     */
    public function setAppname($app = '')
    {
        if (is_string($app) && in_array($app, $this->apps)) {
            $this->appname = $app;
            $this->breakdown['appname'] = $app;
        } else {
            throw new Exception("This appname ({$app}) is empty or not supported.");
        }

        return $this;
    }


    /**
     * Sets the country.
     *
     * @param      string     $countryCode  The country code like "CN" (ISO standard code)
     *
     * @throws     Exception  This country code ({$countryCode}) is invalid or not supported.
     *
     * @return     RevModel     ( description_of_the_return_value )
     */
    public function setCountry($countryCode = '')
    {
        if (is_string($countryCode) && strlen($countryCode) === 2) {
            $this->country = strtoupper($countryCode);
            $this->breakdown['country'] = $countryCode;
        } else {
            throw new Exception("This country code ({$countryCode}) is invalid or not supported.");
        }

        return $this;
    }

    /**
     * Sets the placement.
     *
     * @param      string     $placement  The placement id or code.
     *
     * @throws     Exception  You should set platform first!
     * @throws     Exception  This placement code ({$placement}) is invalid or not supported.
     *
     * @return     RevModel   ( description_of_the_return_value )
     */
    public function setPlacement($placement = '')
    {
        if (!$this->platform) {
            throw new Exception("You should set platform first!");
        }

        if ($placement && (is_string($placement) || is_integer($placement))) {
            $this->placement = strtoupper($placement);
            $this->breakdown['placement'] = $placement;
        } else {
            throw new Exception("This placement code ({$placement}) is invalid or not supported.");
        }

        return $this;
    }


    /**
     * Gets the db instance.
     *
     * @return SDB DB instance.
     */
    public static function getDB()
    {
        global $configs;
        $config = [
            'host' => $configs['OPDATA_DB_HOST'],
            'port' => $configs['OPDATA_DB_PORT'],
            'user' => $configs['OPDATA_DB_USER'],
            'pass' => $configs['OPDATA_DB_PASS'],
            'name' => $configs['OPDATA_DB_NAME'],
        ];

        return SDB::getInstance(self::$sdbName, $config);
    }

    /**
     * Sets the table.(unfinished !!!!!)
     *
     * @param      string  $tableName  The table name
     *
     * @return     $this
     */
    public function setTable($tableName)
    {
        $breakdowns = explode('_', $tableName);
        return $this;
    }

    /**
     * Gets the table name with $this->breakdown.
     *
     * @return     string  The table name.
     */
    public function getTable()
    {
        $table = [];
        $tableName = '';

        if ($this->valueSelected !== ['revenue']) {
            $table[0] = 'ad';
        } else {
            $table[0] = 'revenue';
        }

        if (!$this->breakdown['platform']) {
            $tableName = 'revenue_app';
        } else {
            $table[1] = 'app';
            if ($this->breakdown['appname']) {
                $table[1] = 'app';
            }
            if ($this->breakdown['country']) {
                $table[0] = 'ad';
                $table[1] = $this->breakdown['platform'];
                $table[2] = 'country';
            }
            if ($this->breakdown['placement']) {
                $table[0] = 'ad';
                $table[1] = $this->breakdown['platform'];
                $table[2] = 'placement';
            }
        }

        if($tableName === '') {
            array_map(function($str){
                return trim($str, "-_ \t\n\r\0\x0B");
            }, $table);
            $tableName = implode('_', $table);
        }
        debug("Report tableName: {$tableName}");
        return $tableName;
    }


    /**
     * Fetches all data from table.
     *
     * @param      array   $where    The where array
     * @param      array   $sortby   The sortby array
     * @param      integer  $page     The page number
     * @param      integer  $perpage  The perpage
     *
     * @return     array    ( description_of_the_return_value )
     */
    public function fetchAll($where = [], $sortby = [], $page = 1, $perpage = 999999)
    {
        $sdb    = static::getDB();
        $table  = $this->getTableName();
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

    /**
     * Queries all data from table.
     *
     * @return     $this
     */
    public function queryAll()
    {

        $where = [
            'date' => [
                '>=' => $this->startDate,
                '<=' => $this->endDate,
            ]
        ];

        // build where condition
        foreach ($this->breakdown as $k => $v) {
            $this->$k = $v;
            if ($v) {
                $where[$k] = $v;
            }
        }

        $sortby = [
            // 'date' => 'DESC', 'id'
            'date' => 'DESC',
        ];

        $rows = $this->fetchAll($where, $sortby);

        $this->rows = $rows;

        return $this;
    }


    /**
     * Generate report array
     *
     * @return     array  ( description_of_the_return_value )
     */
    public function report() {
        if (!$this->rows) {
            $this->queryAll();
        }

        $data = [
            'dates'   => [],
            // 'facebook' => [
            //     'sum' => [
            //         'revenue' => 99999,
            //     ],
            //     $this->startDate => [
            //         'revenue' => 33333
            //     ],
            //     'app1' => [
            //         'sum' => 1234567890,
            //         '2010-01-01' => [
            //             'revenue' => 0,
            //         ]
            //     ]
            // ],
            // $this->startDate => [
            //     'revenue' => 333,
            // ],
            'summary' => [
                'revenue' => 0
            ],
        ];

        $d1 = new DateTime($this->startDate, new DateTimeZone('UTC'));
        $d2 = new DateTime($this->endDate, new DateTimeZone('UTC'));

        $days = $d2->diff($d1)->days;
        debug('days:' . $days);

        // generate dates array and empty data
        for ($i = 0; $i <= $days; $i++) {
            $date = date('Y-m-d', $d1->getTimestamp() + 86400 * $i);

            $data['dates'][] = $date;

            $data['sum'][$date]['revenue'] = 0;


            if ($this->platform) {
                $data[$this->platform][$date]['revenue'] = 0;
            } else {
                foreach ($this->platforms as $platform) {
                    $data[$platform][$date]['revenue'] = 0;
                    $data[$platform]['sum']['revenue'] = 0;
                }
            }
        }

        // summarize data...
        foreach ($this->rows as $i => $v) {
            $date = $v['date'];
            $platform = $v['platform'];
            $app = $v['appname'];
            $rev = $v['revenue'];

            $pfdata = &$data[$platform];
            if (!$this->platform) {
                // sum all app's revenue.
                $data[$platform][$date]['revenue'] += $v['revenue'];
            } else {
                $data[$platform][$date]['revenue'] += $v['revenue'];
                $data[$platform][$app][$date]['revenue'] = $v['revenue'];
            }
            $data['sum'][$date]['revenue'] += $v['revenue'];
        }

        return $data;
    }

}
