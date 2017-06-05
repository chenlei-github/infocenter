<?php
/**
 * Revenue Model
 *
 * @date   : 2016/10/27
 * @author : Tiger <DropFan@Gmail.com>
 */
require_once 'inc/class/BaseModel.php';

class Revenue extends BaseModel
{
    public static $db        = 'opdata';
    public static $tableName = 'revenue';
    public static $sdbName   = 'revenue';

    public $id        = 0;
    public $platform  = '';

    public $revenue = 0;

    public $date = '1970-01-01';

    public $created_at = 'now';
    public $updated_at = 'now';

    public static $tableFields = [
        'id'          => 'integer',
        'platform'    => 'string',
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
                case 'revenue':
                case 'date':
                    if (empty($v)) {
                        $errors[] = "The $k can not be empty!";
                    }
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
