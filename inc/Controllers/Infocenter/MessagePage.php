<?php
/**
 * Message Page Controller
 *         list(index)/add/edit
 *
 * @date: 2016/07/27
 * @author: Tiger <DropFan@Gmail.com>
 */
require_once 'inc/Models/Message.php';
require_once '/var/www/infocenter/config.php';
require_once 'inc/class/Country.php';

class MessagePage extends BaseController
{
    protected $template_dir = 'message/';


    private static $table_fields = [
        ['name' => 'id', 'has_sorted' => true, 'asc' => 'ID_ASC', 'des' => 'ID_DES'],
        ['name' => 'Tag', 'has_sorted' => false, 'asc' => '', 'des' => ''],
        ['name' => 'title', 'has_sorted' => true, 'asc' => 'TITLE_ASC', 'des' => 'TITLE_DES'],
        ['name' => 'status', 'has_sorted' => true, 'asc' => 'STATUS_ASC', 'des' => 'STATUS_DES'],
        // ['name' => 'description', 'has_sorted' => true, 'asc' => 'DESCRIPTION_ASC', 'des' => 'DESCRIPTION_DES'],
        // ['name' => 'link', 'has_sorted' => true, 'asc' => 'LINK_ASC', 'des' => 'LINK_DES'],
        // ['name' => 'image', 'has_sorted' => false, 'asc' => '', 'des' => ''],
        ['name' => 'notification', 'has_sorted' => true, 'asc' => 'NOTIFICATION_ASC', 'des' => 'NOTIFICATION_DES'],
        // ['name' => 'popup', 'has_sorted' => true, 'asc' => 'POPUP_ASC', 'des' => 'POPUP_DES'],
        ['name' => 'icon', 'has_sorted' => false, 'asc' => '', 'des' => ''],
        ['name' => 'start', 'has_sorted' => true, 'asc' => 'START_ASC', 'des' => 'START_DES'],
        ['name' => 'end', 'has_sorted' => true, 'asc' => 'END_ASC', 'des' => 'END_DES'],
        ['name' => 'last_update', 'has_sorted' => true, 'asc' => 'LAST_UPDATE_ASC', 'des' => 'LAST_UPDATE_DES'],
        ['name' => 'op', 'has_sorted' => false, 'asc' => '', 'des' => ''],
    ];

    private static $table_fields_orders = [
        'ID_ASC'           => '`id`',
        'ID_DES'           => '`id` DESC',
        'STATUS_ASC'       => '`status`',
        'STATUS_DES'       => '`status` DESC',
        'TITLE_ASC'        => '`title`',
        'TITLE_DES'        => '`title` DESC',
        'DESCRIPTION_ASC'  => '`description`',
        'DESCRIPTION_DES'  => '`description` DESC',
        'LINK_ASC'         => '`link`',
        'LINK_DES'         => '`link` DESC',
        'NOTIFICATION_ASC' => '`notification`',
        'NOTIFICATION_DES' => '`notification` DESC',
        'POPUP_ASC'        => '`popup`',
        'POPUP_DES'        => '`popup` DESC',
        'START_ASC'        => '`start`',
        'START_DES'        => '`start` DESC',
        'END_ASC'          => '`end`',
        'END_DES'          => '`end` DESC',
        'LAST_UPDATE_ASC'  => '`last_update`',
        'LAST_UPDATE_DES'  => '`last_update` DESC',
    ];


    public function index()
    {
        $this->requireUser();
        $this->setCsrfToken();
        $this->assign('title', 'Message List');

        $perpage = isset($_REQUEST['perpage'])
                    ? intval($_REQUEST['perpage']) ?: 20
                    : 20;
        $page = isset($_REQUEST['page'])
                    ? intval($_REQUEST['page']) ?: 1
                    : 1;
        $order = isset($_REQUEST['order'])
                    ? (string) $_REQUEST['order'] ?: ''
                    : '';

        $search = isset($_REQUEST['search']) ? (string) $_REQUEST['search'] ?: '': '';

        $appid = isset($_REQUEST['appid']) ? intval($_REQUEST['appid']) : '';

        $flag = isset($_REQUEST['flag']) ? $_REQUEST['flag'] : '';

        $where = [];
        if (!empty($appid)) {
            $where = ['appid' => "$appid"];
        }
        if (!empty($flag)) {
            $where = ['flag' => "$flag"];
        }

        // debug(json_encode($where));

        #排序
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'ID_DES';
        // debug('sort:' . $sort);
        $order = '';
        if (in_array($sort, array_keys(MessagePage::$table_fields_orders))) {
            $order = MessagePage::$table_fields_orders[$sort];
        }

        $messages = Message::getAll($page, $perpage, $where, $order, $search);

        $total = Message::getCount($where);

        // debug($total);

        $max_page = ceil($total / $perpage);
        if ($page > $max_page ) {
            $page = $max_page;
        }

        $this->assign('messages', $messages);
        $this->assign('max_page', $max_page);
        $this->assign('current_page', $page);
        $this->assign('total_count', $total);

        $app_list = $GLOBALS['configs']['app_list'];
        $appname  = isset($app_list[$appid])
                    ? $app_list[$appid]['appname']
                    : 'All App';

        $this->assign('app_list', $app_list);
        $this->assign('appid', $appid);
        $this->assign('appname', $appname);
        $this->assign('_flag', $flag);

        $this->assign('page', $page);
        $this->assign('table_head', MessagePage::$table_fields);
        $this->assign('sort', $sort);

        return $this->display('index.tpl');
    }

    public function add()
    {
        $this->requireUser();
        $this->setCsrfToken();

        $this->assign('title', 'Add message');

        $app_list = $GLOBALS['configs']['app_list'];
        $languages = require 'config/language.php';
        $common_config = require 'config/common.php';

        $this->assign('app_list', $app_list);
        $this->assign('languages', $languages['CODE_TO_NAME']);

        $regionTree = [];

        foreach (Country::CONTINENT_LIST as $continent => $ccList) {
            $countries = [];

            foreach ($ccList as $k => $cc) {
                $regionTree[$continent]['countries'][$cc]['name'] = Country::COUNTRY_LIST[$cc]['name'];
                $regionTree[$continent]['countries'][$cc]['mask'] = Country::COUNTRY_LIST[$cc]['mask'];
                $regionTree[$continent]['countries'][$cc]['checked'] = 'false';
            }

            $regionTree[$continent]['checked'] = 'false';
            $regionTree[$continent]['liSelected'] = [];
        }

        $regionTree['AS']['name'] = 'Asia';
        $regionTree['EE']['name'] = 'Eastern Europe';
        $regionTree['EU']['name'] = 'Western Europe';
        $regionTree['NA']['name'] = 'North America';
        $regionTree['LA']['name'] = 'Latin America and the Caribbean';
        $regionTree['OC']['name'] = 'Oceania';
        $regionTree['AF']['name'] = 'African';
        $regionTree['UN']['name'] = 'Other';

        $this->assign('region_tree', $regionTree);
        $this->assign('status_enum', $common_config['status_enum']);

        $default_link = '';
        if (isset($_GET['link']) && !empty($_GET['link'])) {
            $default_link = base64_decode($_GET['link']);
            if (!$default_link) {
                $default_link = '';
            }
        }
        $this->assign('default_link', $default_link);
        $this->assign('message_status_enum', $common_config['status_enum']);

        return $this->display('add.tpl');
    }

    public function edit()
    {
        $this->requireUser();
        $this->setCsrfToken();
        $id = intval($_GET['id']) ?: 0;
        if ($id <= 0) {
            // $this->errorpage('Invalid id.');
        }
        $message  = new Message($id);

        $app_list = $GLOBALS['configs']['app_list'];
        $languages = require 'config/language.php';
        $common_config = require 'config/common.php';

        $regionTree = [];

        foreach (Country::CONTINENT_LIST as $continent => $ccList) {
            $countries = [];

            foreach ($ccList as $k => $cc) {
                $regionTree[$continent]['countries'][$cc]['name'] = Country::COUNTRY_LIST[$cc]['name'];
                if ((Country::COUNTRY_LIST[$cc]['mask'] & $message->$continent) === Country::COUNTRY_LIST[$cc]['mask']) {
                    $countries[] = $cc;
                    $checked = 'true';
                } else {
                    $checked = 'false';
                }
                $regionTree[$continent]['countries'][$cc]['checked'] = $checked;
                $regionTree[$continent]['countries'][$cc]['mask'] = Country::COUNTRY_LIST[$cc]['mask'];
            }

            $regionTree[$continent]['checked'] = $message->$continent > 0 ? 'true' : 'false';

            $regionTree[$continent]['liSelected'] = $countries;

            if (is_array($countries) && !empty($countries)) {
                $message->$continent = implode(',', $countries);
            } else {
                $message->$continent = 0;
            }
        }

        $m = Message::getDB();
        $sql = "SELECT id FROM `message_group_relation` WHERE msg_id='{$id}' LIMIT 1";
        $res = $m->query($sql);
        $res = mysqli_fetch_row($res);
        $can_change_status = empty($res[0])? true : false;
        $regionTree['AS']['name'] = 'Asia';
        $regionTree['EE']['name'] = 'Eastern Europe';
        $regionTree['EU']['name'] = 'Western Europe';
        $regionTree['NA']['name'] = 'North America';
        $regionTree['LA']['name'] = 'Latin America and the Caribbean';
        $regionTree['OC']['name'] = 'Oceania';
        $regionTree['AF']['name'] = 'African';
        $regionTree['UN']['name'] = 'Other';

        $this->assign('title', 'Edit message');
        $this->assign('app_list', $app_list);
        $this->assign('languages', $languages['CODE_TO_NAME']);
        $this->assign('message', $message);
        $this->assign('region_tree', $regionTree);
        $this->assign('can_change_status', $can_change_status);
        $this->assign('message_status_enum', $common_config['status_enum']);
        $this->assign('message_status_str', $common_config['status_enum']['' . $message->status]);

        return $this->display('edit.tpl');
    }
}
