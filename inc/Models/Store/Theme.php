<?php
/**
 * Theme Model
 *
 * @date   : 2016/12/06
 * @author : Tiger <DropFan@Gmail.com>
 */
require_once 'inc/class/BaseModel.php';


class Theme extends BaseModel
{
    public static $db        = 'Store';
    public static $tableName = 'theme';
    public static $sdbName   = 'theme';

    public $id = 0;

    public $package_name;
    public $project_name;
    public $name;
    public $support_language;
    public $type;
    public $icon;
    public $paid;
    public $featured;
    public $weight;
    public $is_new;
    public $add_time;
    public $update_time;
    public $promotion_image;
    public $promotion_image_url;
    public $preview_image_urls;
    public $preview_gif_image_url;
    public $description;
    public $market_link;
    public $version;
    public $downloads;
    public $rating_score;
    public $rating_count;
    public $min_api_level;
    public $max_api_level;
    public $help_content;
    public $error_content;
    public $feature;
    public $restriction;
    public $regex;
    public $regex_index;
    public $product_id;
    public $is_ourproduct;
    public $promotion_link;
    public $category;
    public $download_url;
    public $app_promotion_image;
    public $promotion_icon;
    public $partner_url;
    public $partner_icon;
    public $transparent_preview_image_4_1;
    public $transparent_preview_image_4_2_clock;
    public $transparent_preview_image_4_2_forecast;
    public $raise_public_type;
    public $raise_public_switch;
    public $raise_public_current;
    public $raise_public_target1;
    public $raise_public_target2;
    public $min_app_version;
    public $wallpaper;
    public $wallpaper_preview;
    public $tags;
    public $country_type;
    public $real_package_name;
    public $appid;

    public $created_at = 'now';
    public $updated_at = 'now';

    public static $tableFields = [
        'id' => 'integer',
        'package_name' => 's',
        'real_package_name' => 's',
        'appid' => 's',
        'project_name' => 's',
        'name' => 's',
        'support_language' => 's',
        'type' => 'i',
        'icon' => 's',
        'paid' => 'd',
        'featured' => 'i',
        'weight' => 'i',
        'is_new' => 'i',
        'promotion_image' => 's',
        'promotion_image_url' => 's',
        'preview_image_urls' => 's',
        'preview_gif_image_url' => 's',
        'description' => 's',
        'market_link' => 's',
        'version' => 's',
        'downloads' => 'i',
        'rating_score' => 'd',
        'rating_count' => 'i',
        'min_api_level' => 'i',
        'max_api_level' => 'i',
        'help_content' => 's',
        'error_content' => 's',
        'feature' => 's',
        'restriction' => 's',
        'regex' => 's',
        'regex_index' => 'i',
        'product_id' => 's',
        'is_ourproduct' => 'i',
        'status' => 'i',
        'promotion_link' => 's',
        'category' => 'i',
        'download_url' => 's',
        'app_promotion_image' => 's',
        'promotion_icon' => 's',
        'partner_url' => 's',
        'partner_icon' => 's',
        'transparent_preview_image_4_1' => 's',
        'transparent_preview_image_4_2_clock' => 's',
        'transparent_preview_image_4_2_forecast' => 's',
        'raise_public_type' => 'i',
        'raise_public_switch' => 'i',
        'raise_public_current' => 'i',
        'raise_public_target1' => 'i',
        'raise_public_target2' => 'i',
        'min_app_version' => 'i',
        'wallpaper' => 's',
        'wallpaper_preview' => 's',
        'tags' => 's',
        'country_type' => 'i',
        'update_time' => 's',
    ];

    public static $fillable = [
            'id' => 'i',
            'package_name' => 's',
            'real_package_name' => 's',
            'appid' => 's',
            'project_name' => 's',
            'name' => 's',
            'support_language' => 's',
            'type' => 'i',
            'icon' => 's',
            'paid' => 'd',
            'featured' => 'i',
            'weight' => 'i',
            'is_new' => 'i',
            'promotion_image' => 's',
            'promotion_image_url' => 's',
            'preview_image_urls' => 's',
            'preview_gif_image_url' => 's',
            'description' => 's',
            'market_link' => 's',
            'version' => 's',
            'downloads' => 'i',
            'rating_score' => 'd',
            'rating_count' => 'i',
            'min_api_level' => 'i',
            'max_api_level' => 'i',
            'help_content' => 's',
            'error_content' => 's',
            'feature' => 's',
            'restriction' => 's',
            'regex' => 's',
            'regex_index' => 'i',
            'product_id' => 's',
            'is_ourproduct' => 'i',
            'status' => 'i',
            'promotion_link' => 's',
            'category' => 'i',
            'download_url' => 's',
            'app_promotion_image' => 's',
            'promotion_icon' => 's',
            'partner_url' => 's',
            'partner_icon' => 's',
            'transparent_preview_image_4_1' => 's',
            'transparent_preview_image_4_2_clock' => 's',
            'transparent_preview_image_4_2_forecast' => 's',
            'raise_public_type' => 'i',
            'raise_public_switch' => 'i',
            'raise_public_current' => 'i',
            'raise_public_target1' => 'i',
            'raise_public_target2' => 'i',
            'min_app_version' => 'i',
            'wallpaper' => 's',
            'wallpaper_preview' => 's',
            'tags' => 's',
            'country_type' => 'i',
    ];

    public static $notfill = [
        'update_time' => 's',
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
            'host' => $configs['STORE_DB_HOST'],
            'port' => $configs['STORE_DB_PORT'],
            'user' => $configs['STORE_DB_USER'],
            'pass' => $configs['STORE_DB_PASS'],
            'name' => $configs['STORE_DB_NAME'],
        ];

        return SDB::getInstance(self::$sdbName, $config);
    }

    public static function getPackageNames($where = [], $sortby = [], $page = 0, $perpage = 999)
    {
        $db     = static::getDB();
        $table  = static::$tableName;
        $fields = ['package_name', 'name', 'type'];

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

        $rows = $db->select($table, $fields, $selection, [], [], $order, $start, $limit);

        $records = [];
        foreach ($rows as $key => $value) {
            $records[] = $value;
        }

        return $records;
    }

    public static function validation(array $data)
    {
        $fields = array_keys(self::$tableFields);
        $errors = [];
        foreach ($data as $k => $v) {
            switch ($k) {
                case 'id':
                    break;
                case 'status':
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
                    if (!empty($v) && !isset($GLOBALS['configs']['app_list'][$v])) {
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
                    if (empty($v)) {
                        $errors[] = 'start date can not be empty!';
                    }
                    break;
                case 'end':
                    if (empty($v)) {
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
