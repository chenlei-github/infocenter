<?php

return [
    'revenue' => [
        'platform' => [
            'facebook' => [],
            'admob' => [],
            'baidu' => [],
            'pingstart' => [],
            'mobvista' => [],
            'play' => [],
        ],
    ],
    'crawler'=>[
        'pingstart' => [
            'crawler' => '/var/www/infocenter/crawler/op_data/pingstart_daily_import.py',
            'log'     => '/var/www/infocenter/crawler/op_data/pingstart_statics.log',
        ],
        'baidu'  => [
            'crawler' => '/var/www/infocenter/crawler/op_data/baidu_daily_import.py',
            'log'     => '/var/www/infocenter/crawler/op_data/baidu_statics.log',
        ],
        'facebook'  => [
            'crawler' => '/var/www/infocenter/crawler/op_data/facebook_crawler.py',
            'log'     => '/var/www/infocenter/crawler/op_data/facebook_sum_statics.log',
        ],
        'admob'  => [
            'crawler' => '/var/www/infocenter/crawler/op_data/admob_sum_crawler.py',
            'log'     => '/var/www/infocenter/crawler/op_data/admob_sum_statics.log',
        ],
        'googleplay'  => [
            'crawler' => '/var/www/infocenter/crawler/op_data/googleplay_sum_import.py',
            'log'     => '/var/www/infocenter/crawler/op_data/googleplay_sum_statics.log',
        ],
    ],

    'apps' => [
        'amber_weather',
        'weather_widget',
        'weather_lite',
        'weather_inside',
        'uncategorized',
        'unnamed',
    ],

    'platforms'  => ['facebook', 'admob', 'pingstart', 'baidu', 'mobvista', 'googleplay'],

    'store_enum' => [
        'dimension_enum1' => [
            'store_item_type' => '商店分类',
            'store_version'   => '商店形态',
            'pkg_name'        => '包名',
        ],
        'dimension_enum2' => [
            'app_type'        => 'APP类别',
            'country'         => '国家',
            'lang'            => '语言',
            'brand'           => '手机品牌',
            'model'           => '手机型号',
            'os_version'      => '系统版本',
            'open_count'      => '进入sotre次数',
        ],
    ],

    'push_enum' => [
        'dimension_enum2' => [
            'app_type'        => 'APP类别',
            'country'         => '国家',
            'lang'            => '语言',
            'brand'           => '手机品牌',
            'model'           => '手机型号',
            'os_version'      => '系统版本',

            'push_count'      => '通知次数',
            'push_type'       => '推送方式',
            'msg_id'          => '消息id',
        ],
    ],

];
