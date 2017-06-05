<?php

require_once 'inc/class/BaseModel.php';

class Article extends BaseModel
{

    public static $db        = 'Infocenter';
    public static $tableName = 'articles';
    public static $sdbName   = 'article';

    public $id      = 0;
    public $status  = 0;
    public $title   = '';
    public $content = '';
    public $link    = '';
    public $editor  = '';
    public $image   = '';

    public $author      = '';
    public $author_link = '';

    public $language = '';

    public $created_at = '';
    public $updated_at = '';

    public $category = '';
    public $cid = 0;
    public $weight = 0;

    public static $tableFields = [
        'id'          => 'i',
        'status'      => 'i',
        'type'        => 'i',
        'title'       => 's',
        'content'     => 's',
        'author'      => 's',
        'author_link' => 's',
        'editor'      => 's',
        'link'        => 's',
        'image'       => 's',
        'language'    => 's',
        'created_at'  => 's',
        'updated_at'  => 's',
        'category'    => 's',
        'cid'         => 'i',
        'weight'      => 'i',
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
