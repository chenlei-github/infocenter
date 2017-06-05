<?php

require_once 'inc/class/BaseModel.php';

class MessageGroup extends BaseModel
{

    public static $db        = 'Infocenter';
    public static $tableName = 'message_group';
    public static $sdbName   = 'message_group';

    public $id          = 0;
    public $name        = '';
    public $status      = 0;

    public $notification = 0;
    public $popup        = 0;

    public $region   = 1;
    public $AS = 0;
    public $EU = 0;
    public $EE = 0;
    public $NA = 0;
    public $LA = 0;
    public $OC = 0;
    public $AF = 0;
    public $UN = 0;

    public $appid      = '';
    public $appver_min = 0;
    public $appver_max = 0;


    public static $tableFields = [
        'id'           => 'integer',
        'name'         => 'string',
        'status'       => 'integer',
        'notification' => 'boolean',
        'popup'        => 'boolean',
        'region'       => 'integer',
        'AS'           => 'integer',
        'EU'           => 'integer',
        'EE'           => 'integer',
        'NA'           => 'integer',
        'LA'           => 'integer',
        'OC'           => 'integer',
        'AF'           => 'integer',
        'UN'           => 'integer',
        'appid'        => 'integer',
        'appver_min'   => 'integer',
        'appver_max'   => 'integer',
        'time'         => 'string',
        'start'        => 'datetime',
        'end'          => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];
    public static $fillable = [
        'name'         => 'string',
        'status'       => 'integer',
        'notification' => 'boolean',
        'popup'        => 'boolean',
        'region'       => 'integer',
        'AS'           => 'integer',
        'EU'           => 'integer',
        'EE'           => 'integer',
        'NA'           => 'integer',
        'LA'           => 'integer',
        'OC'           => 'integer',
        'AF'           => 'integer',
        'UN'           => 'integer',
        'appid'        => 'string',
        'appver_min'   => 'integer',
        'appver_max'   => 'integer',
        'time'         => 'string',
        'start'        => 'datetime',
        'end'          => 'datetime',
    ];

    public static $notfill = [
        'id'         => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function __construct($data = null)
    {
        parent::__construct($data);
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
