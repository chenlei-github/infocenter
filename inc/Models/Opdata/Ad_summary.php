<?php
/**
 * Ad_summary Model
 *
 * @date   : 2016/10/27
 * @author : Tiger <DropFan@Gmail.com>
 */
require_once 'inc/class/BaseModel.php';

class Ad_summary extends BaseModel
{
    public static $db        = 'opdata';
    public static $tableName = 'ad_summary';
    public static $sdbName   = 'ad_summary';

    public $id        = 0;
    public $platform  = '';
    public $country   = '';
    public $app       = '';
    public $placement = '';

    public $request    = 0;
    public $filled     = 0;
    public $impression = 0;
    public $click      = 0;

    public $filled_rate = 0;
    public $ctr         = 0;
    public $ecpm        = 0;
    public $revenue     = 0;

    public $date = '1970-01-01';

    public $created_at = 'now';
    public $updated_at = 'now';

    public static $tableFields = [
        'id'          => 'integer',
        'platform'    => 'string',
        'country'     => 'string',
        'app'         => 'string',
        'placement'   => 'string',
        'request'     => 'integer',
        'filled'      => 'integer',
        'impression'  => 'integer',
        'click'       => 'integer',
        'filled_rate' => 'float',
        'ctr'         => 'float',
        'ecpm'        => 'float',
        'revenue'     => 'float',
        'date'        => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public static $fillable = [
    ];

    public static $notfill = [
        'id'         => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function __construct($data = null)
    {
        parent::__construct($data);

        $this->created_at = date('Y-m-d H:i:s', strtotime($this->created_at) + 28800);
        $this->updated_at = date('Y-m-d H:i:s', strtotime($this->created_at) + 28800);
    }

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

    public static function validation(array $data)
    {
        $fields = array_keys(self::$tableFields);
        $errors = [];
        foreach ($data as $k => $v) {
            switch ($k) {
                case 'id':
                    break;
                case 'platform':
                    if (empty($v)) {
                        $errors[] = "The $k can not be empty!";
                    }
                    break;
                case 'country':
                    break;
                case 'app':
                    break;
                case 'placement':
                    break;
                case 'request':
                    break;
                case 'filled':
                    break;
                case 'impression':
                    break;
                case 'click':
                    break;
                case 'filled_rate':
                    break;
                case 'ctr':
                    break;
                case 'ecpm':
                    break;
                case 'revenue':
                    break;
                case 'date':
                    break;
                case 'created_at':
                    break;
                case 'updated_at':
                    break;
                default:
                    $errors[] = 'Invalid Field !';
                    break;
            } // end switch
        } // end foreach

        if (empty($errors)) {
            return true;
        }

        return $errors;
    }
}
