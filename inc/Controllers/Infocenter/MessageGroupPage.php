<?php
require_once 'inc/class/BaseController.php';
require_once 'inc/Models/MessageGroupRelation.php';
require_once 'inc/Models/MessageGroup.php';
require_once 'inc/Models/Message.php';

require_once '/var/www/infocenter/config.php';
require_once 'inc/class/Country.php';

class MessageGroupPage extends BaseController
{
    protected $template_dir = 'message_group/';
    public function __construct()
    {
        parent::__construct();
        $this->requireUser();
    }


    public function index()
    {
        $page = isset($_REQUEST['page'])
                    ? intval($_REQUEST['page']) ?: 1
                    : 1;
        $order = isset($_REQUEST['order'])
                    ? (string) $_REQUEST['order'] ?: ''
                    : '';
        $perpage = isset($_REQUEST['perpage'])
                    ? intval($_REQUEST['perpage']) ?: 20
                    : 20;
        $search = isset($_REQUEST['search']) ? (string) $_REQUEST['search'] ?: '': '';
        $where = [];

        $total = MessageGroup::getCount($where);

        $data = MessageGroup::getAll($page, $perpage, $where, $order, $search);

        $max_page = ceil($total / $perpage);
        if ($page > $max_page ) {
            $page = $max_page;
        }

        $this->assign('title', '消息分组管理');
        $this->assign('_get_status', '');
        $this->assign('data', $data);

        $this->assign('max_page', $max_page);
        $this->assign('current_page', $page);
        $this->assign('total_count', $total);

        return $this->display('index.tpl');
    }

    public function msgList()
    {
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        $data = MessageGroupRelation::getMsgProb($id);
        $this->assign('data', $data);
        return $this->display('msg_list.tpl');
    }

    public function delGroup($old_id=''){
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        $id = empty($id) ? $old_id : $id;

        if(empty($id)){
            echo 'id不能为空！';
            return false;
        }

        $affect_row = MessageGroup::deleteAll($id);
        if($affect_row > 0) MessageGroupRelation::deleteByGid($id);

        $res = ['info'=>$count, 'status'=>'ok'];

        return $this->returnJson($res);
    }

    public function add()
    {
        $this->setCsrfToken();

        $search = isset($_REQUEST['search']) ? (string) $_REQUEST['search'] ?: '': '';
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] ?: '': '';
        $flag = isset($_REQUEST['flag']) ? (string) $_REQUEST['flag'] ?: '': '';

        $app_list = $GLOBALS['configs']['app_list'];
        $languages = require 'config/language.php';
        $common_config = require 'config/common.php';

        $this->assign('app_list', $app_list);
        $this->assign('languages', $languages['CODE_TO_NAME']);

        $regionTree = [];
        $messageGroup = '';
        $msg_prob = '';
        if($id){
            $messageGroup  = MessageGroup::getOneById($id);
            $msg_prob = MessageGroupRelation::getMsgProb($id);
        }

        foreach (Country::CONTINENT_LIST as $continent => $ccList) {
            $countries = [];

            foreach ($ccList as $k => $cc) {
                $regionTree[$continent]['countries'][$cc]['name'] = Country::COUNTRY_LIST[$cc]['name'];
                 $checked = 'false';

                 if($messageGroup!=''){
                    if ((Country::COUNTRY_LIST[$cc]['mask'] & $messageGroup[$continent]) == Country::COUNTRY_LIST[$cc]['mask']) {
                        $countries[] = $cc;
                        $checked = 'true';
                    }
                 }
                $regionTree[$continent]['countries'][$cc]['checked'] = $checked;
                $regionTree[$continent]['countries'][$cc]['mask'] = Country::COUNTRY_LIST[$cc]['mask'];
            }

            $regionTree[$continent]['checked'] = 'false';
            if($messageGroup){
                $regionTree[$continent]['checked'] = $messageGroup[$continent] > 0 ? 'true' : 'false';
            }

            $regionTree[$continent]['liSelected'] = $countries;

            if($messageGroup){
                if (is_array($countries) && !empty($countries)) {
                    $messageGroup[$continent] = implode(',', $countries);
                } else {
                    $messageGroup[$continent] = 0;
                }
            }
        }
        $regionTree['AS']['name'] = 'Asia';
        $regionTree['EE']['name'] = 'Eastern Europe';
        $regionTree['EU']['name'] = 'Western Europe';
        $regionTree['NA']['name'] = 'North America';
        $regionTree['LA']['name'] = 'Latin America and the Caribbean';
        $regionTree['OC']['name'] = 'Oceania';
        $regionTree['AF']['name'] = 'African';
        $regionTree['UN']['name'] = 'Other';

        $where = ['status' => '2'];
        if(!empty($flag)){
            $where['flag'] = $flag;
        }
        $message_list = Message::getAll(1, 999, $where, [], $search);

        foreach ($message_list as $key=>$row) {
            $extra = ['is_checked' => 0, 'prob' => 0];
            if(!empty($msg_prob)){
                foreach ($msg_prob as $prob) {
                    if($row['id'] == $prob['id']){
                        $extra = ['is_checked' => 1, 'prob' => $prob['prob']];
                    }
                }
            }
            $row = array_merge($row, $extra);
            $message_list[$key] = $row;
        }
        array_multisort(
            array_column($message_list, 'is_checked'),
            SORT_DESC,
            $message_list
        );

        $this->assign('group_id', $id);
        $this->assign('region_tree', $regionTree);
        $this->assign('status_enum', $common_config['status_enum']);
        $this->assign('message_status_enum', $common_config['status_enum']);
        $this->assign('title', ($id ? '编辑' : '新增') .'分组');
        $this->assign('data', $message_list);
        $this->assign('msg_prob', $msg_prob);
        $this->assign('group_data', $messageGroup);
        $this->assign('flag', $flag);
        return $this->display('add.tpl');
    }


    public function update()
    {
        $data = isset($_POST['group_data']) ? json_decode($_POST['group_data'], 1) : null;
        $msg_data = isset($_POST['msg_data']) ? $_POST['msg_data'] : '';
        $old_group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : '';

        if(empty($msg_data) && $data['status'] == '1'){
            $resp = ['status' => 'error',
                'message' => '该分组下的消息为空！',
                'errors' => ''
            ];
            return $this->returnJson($resp);
        }

        foreach (Country::CONTINENT_NAMES as $c) {

            $$c = explode(',', $data[$c]);

            if (!empty($$c) && is_array($$c)) {
                $value = 0;
                foreach ($$c as $countryCode) {
                    if (empty($countryCode)) {
                        continue;
                    }
                    $value |= Country::COUNTRY_LIST[$countryCode]['mask'];
                }
                $data[$c] = $value;
            } else {
                $data[$c] = 0;
            }
        }

        $data['notification'] = $data['notification'] ? 1 : 0;
        $data['popup'] = $data['popup'] ? 1 : 0;
        $data['appver_min'] = $data['appver_min'] ? $data['appver_min'] : 0;
        $data['appver_max'] = $data['appver_max'] ? $data['appver_max'] : 0;

        $affect_row = 0;
        if(empty($old_group_id)){
            $res = MessageGroup::create($data);
        }else{
            $MessageGroupModel = new MessageGroup($old_group_id);
            $affect_row = $MessageGroupModel->fillWith($data)->update();
        }

        $msg_group_id = empty($old_group_id) ? $res->id : $old_group_id;
        if($msg_group_id && !empty($msg_data)){
            MessageGroupRelation::deleteByGid($msg_group_id);
            $new_arr = [];
            foreach ($msg_data as $row) {
                $data = [
                    'msg_group_id' => $msg_group_id,
                    'msg_id' => $row['id'],
                    'prob' => $row['prob']
                ];
                MessageGroupRelation::create($data);
            }
        }

        $resp = ['status' => 'error',
            'message' => 'Add failed!',
            'errors' => ['Insert data failed. Please try again.']
        ];

        if ($msg_group_id) {
            $resp = ['message' => '操作成功'];
        }
        return $this->returnJson($resp);
    }


}