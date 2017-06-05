<?php
require_once 'inc/class/Country.php';
require_once 'inc/class/BaseController.php';
require_once 'inc/Models/Store/Theme.php';
require_once 'inc/Models/Opdata/AndroidPublisher.php';

/**
 * Aso Page Controller
 *
 * @date   : 2016/10/27
 * @author : Tiger <DropFan@Gmail.com>
 */
class AsoPage extends BaseController
{
    protected $template_dir = 'opdata/';
    protected $config;

    public function __construct()
    {
        parent::__construct();
        $this->config = require 'config/opdata.php';
    }

    public function index()
    {
        return false;
    }

    /**
     * update Google Play Description page
     */
    public function updateGooglePlay()
    {
        $this->requireUser();
        $this->setCsrfToken();

        $this->assign('title', 'Update Google Play Description');

        $languages = require 'config/language.php';
        $languages = ($languages['PLAY_LANG']);
        asort($languages);
        $this->assign('languages', $languages);

        $theme_config = require 'config/theme.php';
        $theme_types  = $theme_config['types'];
        $this->assign('theme_types', $theme_types);

        $type = isset($_GET['type']) ? $_GET['type'] : '';

        if (array_key_exists($type, $theme_types)) {
            $this->assign('has_type', true);
            $this->assign('theme_type_id', $type);
            $this->assign('theme_type_name', $theme_types[$type]);
        } else {
            $this->assign('has_type', false);
            return $this->display('gplay.tpl');
        }
        $type = intval($type);
        $where = " AND `type` = $type ";
        debug(json_encode($where));
        $package_names = Theme::getPackageNames($where, [], 0, 999);
        // dump($package_names);die;

        $this->assign('packages', $package_names);

        $google_play_accounts = $this->configs['google_play_account'];
        // dump($this->configs);
        $this->assign('play_account_list', $google_play_accounts);

        return $this->display('gplay.tpl');
    }

    /**
     * get Google Play Description list
     */
    public function getGooglePlayDescriptionList()
    {
        $this->requireUser();
        $this->setCsrfToken();

        $this->assign('title', 'Get Google Play Description');

        $languages = require 'config/language.php';
        $languages = ($languages['PLAY_LANG']);
        asort($languages);
        $this->assign('languages', $languages);

        $where = [
            'type' => [
                'IN' => ['0', '1', '2', '3', '4', '5', '6', '7']
            ]
        ];
        $package_names = Theme::getPackageNames($where, [], 0, 999);
        // dump($package_names);die;

        $this->assign('packages', $package_names);

        $google_play_accounts = $this->configs['google_play_account'];
        // dump($this->configs);
        $this->assign('play_account_list', $google_play_accounts);

        return $this->display('gplay_list.tpl');
    }

    public function updateGooglePlayImage()
    {
        $this->requireUser();
        $this->setCsrfToken();

        $this->assign('title', 'Get Google Play Image');

        $languages = require 'config/language.php';
        $languages = ($languages['PLAY_LANG']);
        asort($languages);
        $this->assign('languages', $languages);

        $theme_config = require 'config/theme.php';
        $theme_types  = $theme_config['types'];
        $this->assign('theme_types', $theme_types);

        $type = isset($_GET['type']) ? $_GET['type'] : '';

        if (array_key_exists($type, $theme_types)) {
            $this->assign('has_type', true);
            $this->assign('theme_type_id', $type);
            $this->assign('theme_type_name', $theme_types[$type]);
        } else {
            $this->assign('has_type', false);
            return $this->display('gplay_image.tpl');
        }
        $type = intval($type);
        $where = " AND `type` = $type ";
        debug(json_encode($where));

        $package_names = Theme::getPackageNames($where, [], 0, 999);
        // dump($package_names);die;

        $this->assign('packages', $package_names);

        $google_play_accounts = $this->configs['google_play_account'];
        // dump($this->configs);
        $this->assign('play_account_list', $google_play_accounts);
        $this->assign('image_types', AndroidPublisher::$imageTypeList);

        return $this->display('gplay_image.tpl');
    }

}
