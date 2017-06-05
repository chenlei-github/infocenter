<?php

require_once 'inc/class/BaseModel.php';

class Keyword extends BaseModel
{

    public static $db        = 'opdata';
    public static $tableName = 'gp_keyword';
    public static $sdbName   = 'gp_keyword';

    public $id      = 0;
    public $search  = '';
    public $lang   = '';
    public $status   = 1;


    public static $tableFields = [
        'id'          => 'i',
        'search'      => 's',
        'country'     => 's',
        'lang'        => 's',
        'type'        => 's',
        'status'      => 'i',
        'recent_data_time' => 's',
    ];

    public function __construct($id = null)
    {
        parent::__construct($id);
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

    public static function getAllType(){
        $model = self::getDB();
        return $model->select(self::$tableName,'distinct(`type`)');
    }
    public static function getAllLang(){
        $model = self::getDB();
        return $model->select(self::$tableName,'distinct(`lang`)');
    }
    public static function getAllCountry(){
        $model = self::getDB();
        return $model->select(self::$tableName,'distinct(`country`)');
    }


}
