<?php

require_once 'inc/class/BaseModel.php';

class DimensionEnum extends BaseModel
{

    public static $db        = 'push_store_statics';
    public static $tableName = 'dimension_enum';
    public static $sdbName   = 'dimension_enum';

    public $id               = 0;
    public $category         = '';
    public $dimension_name   = '';
    public $dimension_value  = '';

    public static $tableFields = [
        'id'                    => 'i',
        'category'              => 's',
        'dimension_name'        => 's',
        'dimension_value'       => 's',
    ];

    public static $notfill = [
        'id'         => 'i',
    ];

    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    public static function getDB()
    {
        global $configs;
        $config = [
            'host' => $configs['PUSH_STORE_STATICS_DB_HOST'],
            'port' => $configs['PUSH_STORE_STATICS_DB_PORT'],
            'user' => $configs['PUSH_STORE_STATICS_DB_USER'],
            'pass' => $configs['PUSH_STORE_STATICS_DB_PASS'],
            'name' => $configs['PUSH_STORE_STATICS_DB_NAME']
        ];

        return SDB::getInstance(self::$sdbName, $config);
    }
}
