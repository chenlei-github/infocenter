<?php

require_once 'inc/class/BaseModel.php';

class AppConfig extends BaseModel
{

    public static $db        = 'Infocenter';
    public static $tableName = 'appconfigs';
    public static $sdbName   = 'appconfig';

    public $id        = 0;
    public $appid     = '';
    public $type      = '';
    public $configure = '';

    public $created_at = '';
    public $updated_at = '';

    public static $tableFields = [
        'id'        => 'i',
        'appid'     => 's',
        'type'      => 's',
        'configure' => 's',
    ];

    public static $notfill = [
        'id'         => 'i',
        'created_at' => 's',
        'updated_at' => 's',
    ];

    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    public static function getDB()
    {
        global $configs;
        $config = [
            'host' => $configs['INFOCENTER_DB_HOST'],
            'port' => $configs['INFOCENTER_DB_PORT'],
            'user' => $configs['INFOCENTER_DB_USER'],
            'pass' => $configs['INFOCENTER_DB_PASS'],
            'name' => $configs['INFOCENTER_DB_NAME'],
        ];

        return SDB::getInstance(self::$sdbName, $config);
    }

}
