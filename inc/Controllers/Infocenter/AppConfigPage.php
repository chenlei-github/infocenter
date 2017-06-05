<?php

require_once 'inc/Models/AppConfig.php';

class AppConfigPage extends BaseController
{
    protected $template_dir = 'appconfig/';

    public function index()
    {
        $this->requireUser();
        $this->setCsrfToken();

        $this->assign('title', 'AppConfig');

        $appconfigs = null;

        $where = [];
        $appid = '';
        #使用 appid='all',作为特殊情况处理，正常使用的appid不应该使用该值。
        if (isset($_GET['appid']) && !empty($_GET['appid']) && $_GET['appid'] != 'all') {
            $where['appid'] = $_GET['appid'];
            $appid          = $_GET['appid'];
        }

        $next_page = 1; #下一页
        $total     = AppConfig::getCount($where);
        $perpage   = isset($_GET['pagesize']) ? intval($_GET['pagesize']) : 10;
        $max_page  = 1 + intval($total / $perpage);
        $min_page  = 1;
        #当前页
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page < 1) {$page = 1;}
        if ($page > $max_page) {$page = $max_page;}

        $pre_page = $page - 1;
        if ($pre_page < 1) {$pre_page = 1;}
        $next_page = $page + 1;
        if ($next_page > $max_page) {$next_page = $max_page;}

        $app_list        = $GLOBALS['configs']['app_list'];
        $app_list['all'] = ['appid' => 'all',
            'appname'                   => 'All'];
        $appname = isset($app_list[$appid])
        ? $app_list[$appid]['appname']
        : 'All App';
        $this->assign('app_list', $app_list);
        $this->assign('appid', $appid);
        $this->assign('appname', $appname);

        $appconfigs = AppConfig::getAll($page, $perpage, $where);

        $this->assign('appconfigs', $appconfigs);
        $this->assign('total_count', $total);
        $this->assign('perpage', $perpage);
        $this->assign('max_page', $max_page);
        $this->assign('page', $page);
        $this->assign('pre_page', $pre_page);
        $this->assign('next_page', $next_page);
        $this->assign('appid', $appid);

        return $this->display('index.tpl');
    }

    public function add()
    {
        $this->requireUser();
        $this->setCsrfToken();

        $app_list = $GLOBALS['configs']['app_list'];
        $appid    = array_keys($app_list)[0];
        $appname  = $app_list[$appid]['appname'];
        $this->assign('app_list', $app_list);
        $this->assign('appid', $appid);
        $this->assign('appname', $appname);

        $this->assign('title', 'Add AppConfig');
        // $this->assign('appconfig_type', $this->genAppConfigType());

        return $this->display('add.tpl');
    }

    public function edit()
    {
        $this->requireUser();
        $this->setCsrfToken();

        if (!isset($_GET['appconfig_id']) || empty($_GET['appconfig_id'])) {
            echo "Miss Id\n";

            return;
        }
        $id = intval($_GET['appconfig_id']);
        if ($id == 0) {
            $this->returnJson('BAD PARMA!', 'error');
            return;
        }
        $appconfig = AppConfig::getOneById($id);
        if ($appconfig == null) {
            $this->returnJson('BAD PARMA!', 'error');
            return;
        }
        $this->assign('title', 'Edit AppConfig');
        $this->assign('appconfig', $appconfig);
        $this->assign('appconfig_configs', base64_encode($appconfig['configure']));

        return $this->display('edit.tpl');
    }

}
