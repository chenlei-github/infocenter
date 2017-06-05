<?php
/**
 * Message Model
 *
 * @date   : 2016/07/26
 * @author : Tiger <DropFan@Gmail.com>
 */
require_once 'inc/class/BaseModel.php';

class Message extends BaseModel
{
    public static $db        = 'Infocenter';
    public static $tableName = 'messages';
    public static $sdbName   = 'message';

    public $id          = 0;
    public $status      = 0;
    public $title       = '';
    public $description = '';
    public $call_to_action = '';
    public $link        = '';
    public $icon        = '';
    public $image       = '';

    public $notification = false;
    public $popup        = false;

    public $language = '';
    public $region   = 1;

    public $AS = 0;
    public $EU = 0;
    public $EE = 0;
    public $NA = 0;
    public $LA = 0;
    public $OC = 0;
    public $AF = 0;
    public $UN = 0;

    public $flag       = '';
    public $appid      = '';
    public $appver_min = 0;
    public $appver_max = 0;

    public $time  = '00:00';
    public $start = 'now';
    public $end   = '1 year';

    public $created_at = 'now';
    public $updated_at = 'now';

    public static $tableFields = [
        'id'           => 'integer',
        'flag'         => 'string',
        'status'       => 'integer',
        'title'        => 'string',
        'description'  => 'string',
        'call_to_action' => 'string',
        'link'         => 'string',
        'icon'         => 'string',
        'image'        => 'string',
        'notification' => 'boolean',
        'popup'        => 'boolean',
        'language'     => 'string',
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
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public static $fillable = [
        'status'       => 'integer',
        'flag'         => 'string',
        'title'        => 'string',
        'description'  => 'string',
        'link'         => 'string',
        'icon'         => 'string',
        'image'        => 'string',
        'call_to_action' => 'string',
        'notification' => 'boolean',
        'popup'        => 'boolean',
        'language'     => 'string',
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

        $this->created_at = date('Y-m-d H:i:s', strtotime($this->created_at) + 28800);
        $this->updated_at = date('Y-m-d H:i:s', strtotime($this->created_at) + 28800);
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

    public static function validation(array $data)
    {
        $fields = array_keys(self::$tableFields);
        $errors = [];
        $status = $data['status'];
        foreach ($data as $k => $v) {
            switch ($k) {
                case 'id':
                    break;
                case 'status':
                    break;
                case 'flag':
                    break;
                case 'title':
                    if (empty($v)) {
                        $errors[] = "The $k can not be empty!";
                    }
                    if (strlen($v) > 255) {
                        $errors[] = "The $k is too long!";
                    }
                    break;
                case 'description':
                    if (empty($v)) {
                        $errors[] = "The $k can not be empty!";
                    }
                    break;
                case 'link':
                    break;
                case 'call_to_action':
                    break;
                case 'icon':
                    break;
                case 'image':
                    break;
                case 'notification':
                    break;
                case 'popup':
                    break;
                case 'language':
                    break;
                case 'region':
                    break;
                case 'AS':
                    break;
                case 'EU':
                    break;
                case 'EE':
                    break;
                case 'NA':
                    break;
                case 'LA':
                    break;
                case 'OC':
                    break;
                case 'AF':
                    break;
                case 'UN':
                    break;
                case 'appid':
                    if ($status!='2' && !isset($GLOBALS['configs']['app_list'][$v])) {
                        $errors[] = 'This appid is error!';
                    }
                    break;
                case 'appver_min':
                    break;
                case 'appver_max':
                    break;
                case 'time':
                    break;
                case 'start':
                    if ($status!='2' && empty($v)) {
                        $errors[] = 'start date can not be empty!';
                    }
                    break;
                case 'end':
                    if ($status!='2' && empty($v)) {
                        $errors[] = 'end date can not be empty!';
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
