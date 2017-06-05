<?php
/**
 * GPlay Text Model
 *
 * @date   : 2017/03/01
 * @author : Tiger <DropFan@Gmail.com>
 */
require_once 'inc/class/BaseModel.php';

class GPlayText extends BaseModel
{
    public static $db        = 'opdata';
    public static $tableName = 'aso_gplay_text';
    public static $sdbName   = 'aso_gplay';

    public $id = 0;

    public $type = -1;
    public $name = 'new';
    public $data = '';

    public $created_at = 'now';
    public $updated_at = 'now';

    public static $tableFields = [
        'id'         => 'integer',
        'type'       => 'integer',
        'name'       => 'string',
        'data'       => 'text',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static $fillable = [
        'type' => 'integer',
        'name' => 'string',
        'data' => 'text',
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
                case 'type':
                    if (!is_numeric($v)) {
                        $errors[] = 'The $k must be integer!';
                    }
                    break;
                case 'name':
                    if (empty($v)) {
                        $errors[] = "The $k can not be empty!";
                    }
                    if (strlen($v) > 255) {
                        $errors[] = "The $k is too long!";
                    }
                    break;
                case 'data':
                    if (empty($v)) {
                        $errors[] = "The $k can not be empty!";
                    }
                    if (json_decode($v) === null) {
                        $errors[] = "Invalid data!";
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
