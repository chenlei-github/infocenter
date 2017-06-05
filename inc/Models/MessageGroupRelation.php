<?php

require_once 'inc/class/BaseModel.php';

class MessageGroupRelation extends BaseModel
{

    public static $db        = 'Infocenter';
    public static $tableName = 'message_group_relation';
    public static $sdbName   = 'message_group_relation';

    public $id           = 0;
    public $msg_group_id = 0;
    public $msg_id       = 0;
    public $prob         = 0;

    public static $tableFields = [
        'id'           => 'integer',
        'msg_group_id' => 'integer',
        'msg_id'       => 'integer',
        'prob'         => 'integer',

        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];
    public static $fillable = [
        'msg_group_id' => 'integer',
        'msg_id'       => 'integer',
        'prob'         => 'integer',
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

    public static function deleteByGid($gid){
        $model = self::getDB();
        $gid = intval($gid);
        $sql = "delete from message_group_relation where msg_group_id='{$gid}'";
        return $model->query($sql);
    }

    public static function getMsgProb($gid){
        $model = self::getDB();
        $gid = intval($gid);
        $sql = 'select m.id,m.flag,m.status,m.title,m.icon,m.image,r.prob,g.name';
        $sql .= ' from messages m, message_group_relation r, message_group g';
        $sql .= ' where g.id=r.msg_group_id and m.id=r.msg_id and r.msg_group_id="'. $gid . '"';
        $res = $model->query($sql);
        $data = [];
        while($row = mysqli_fetch_array($res)){
            $data[] = $row;
        }
        return $data;
    }

}
