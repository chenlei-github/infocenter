<?php
require_once 'inc/class/AsyncTask.php';
require_once 'inc/class/BaseController.php';
require_once 'inc/Models/Opdata/Keyword.php';


class KeywordPage extends BaseController
{
    protected $template_dir = 'opdata/';
    public $task = '';

    public function __construct()
    {
        parent::__construct();
        $this->requireUser();
        $config = require('config/aso.php');
        $this->config = $config['keyword_config'];

        $_config = require 'config/asynctask.php';
        $_config = $_config['gdeveloper_rank'];
        $this->task = new AsyncTask(
            $_config['cmd'],
            $_config['pidfile'],
            $_config['outputfile'],
            $_config['tail_recent_number']
        );
    }

    private function is_ajax(){
        return (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest")? true: false;
    }
    private function exec_crawler($data=[]){
        $new_arr = [];
        foreach ($data as $row) {
            //$tag = $row['country'] . '_' . $row['lang'] . '_' . $row['id'];
            $tag = $row['country'] . '_' . $row['lang'] . '_'. $row['type'];
            $new_arr[$tag][] = $row;
        }
        $dir = $this->config['dir'];
        foreach ($dir as $data_dir) {
            exec('rm -rf '.$data_dir);
            mkdir($data_dir, 0777,true);
        }
        $res = ['error_num' => 0, 'success_num' => 0];
        foreach ($new_arr as $key => $arr) {
            $file = $dir['keyword_txt'] . '/'. $key .'.txt';
            @file_put_contents($file, "");
            foreach ($arr as $row) {
                $fl = fopen($file, "a");
                if(!$fl){
                    $res['error_num']++;
                    continue;
                }
                $txt = $row['search']. "\n";
                if(fwrite($fl, $txt)) $res['success_num']++;
                fclose($fl);
            }
        }
        $res = $this->task->start();
        return $res ? 1 : 0 ;

    }

    public function index(){
        $is_exec_crawler = isset($_REQUEST['is_exec_crawler'])
                    ? (string) $_REQUEST['is_exec_crawler'] ?: ''
                    : '';
        $confirm_exec = isset($_REQUEST['confirm_exec'])
                    ? (string) $_REQUEST['confirm_exec'] ?: ''
                    : '';
        $status = isset($_REQUEST['status'])
                    ? (string) $_REQUEST['status'] ?: ''
                    : '';
        $type = isset($_REQUEST['type'])
                    ? (string) $_REQUEST['type'] ?: ''
                    : '';
        $country = isset($_REQUEST['country'])
                    ? (string) $_REQUEST['country'] ?: ''
                    : '';
        $lang = isset($_REQUEST['lang'])
                    ? (string) $_REQUEST['lang'] ?: ''
                    : '';
        $search = isset($_REQUEST['search'])
                    ? (string) $_REQUEST['search'] ?: ''
                    : '';

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
        foreach (['status', 'type', 'country', 'lang', 'search'] as $field) {
            if(empty($$field)) continue;
            if($field=='search'){
                $where[$field] = ['like' => "%{$$field}%"];
            }elseif ($field == 'status') {
                $where[$field] = ['=' => $$field-1];
            }else{
                $where[$field] = $$field;
            }
        }

        $all_type = $this->config['AllType'];
        $all_lang = $this->config['AllLang'];
        $all_country = $this->config['AllCountry'];
        $total = Keyword::getCount($where);

        if($confirm_exec=='1' && $this->is_ajax()){
            return $this->returnJson(['total'=>$total]);
        }

        $data = Keyword::getAll($page,
            ($is_exec_crawler == '1' ? $total : $perpage),
            $where,
            $order,
            $search
        );

        if($is_exec_crawler=='1' && intval($total)>0 && $this->is_ajax()){
            $res = $this->exec_crawler($data);
            return $this->returnJson(['status'=>$res]);
        }

        $max_page = ceil($total / $perpage);
        if ($page > $max_page ) {
            $page = $max_page;
        }

        $this->assign('data', $data);

        $this->assign('_get_search', $search);
        $this->assign('_get_status', $status);
        $this->assign('_get_type', $type);
        $this->assign('_get_lang', $lang);
        $this->assign('_get_country', $country);

        $this->assign('all_type', $all_type);
        $this->assign('all_lang', $all_lang);
        $this->assign('all_country', $all_country);

        $this->assign('title', '关键词配置');
        $this->assign('max_page', $max_page);
        $this->assign('current_page', $page);
        $this->assign('total_count', $total);
        $this->assign('page', $page);
        return $this->display('keywords_manage.tpl');
    }

    public function add_keyword(){
        $keyword = isset($_REQUEST['keyword'])
                    ? trim($_REQUEST['keyword']) ?: ''
                    : '';
        $lang = isset($_REQUEST['lang'])
                    ? (string) $_REQUEST['lang'] ?: ''
                    : '';
        $country = isset($_REQUEST['country'])
                    ? (string) $_REQUEST['country'] ?: ''
                    : '';
        $update_now = isset($_REQUEST['update_now'])
                    ? (string) $_REQUEST['update_now'] ?: ''
                    : '';
        $type = isset($_REQUEST['type'])
                    ? (string) $_REQUEST['type'] ?: ''
                    : '';

        if(empty($keyword) || empty($lang) ){
            return $this->returnJson('param error', 'error');
        }
        $keyword_arr = explode("\r\n", $keyword);
        foreach ($keyword_arr as $row) {
            $model = new Keyword();
            $sql_str = "insert into gp_keyword (search,lang,country,type)value('%s','%s','%s','%s')";
            $sql = sprintf($sql_str, $row, $lang, $country, $type);
            $model->db()->query($sql);
        }
        return $this->returnJson('success');

    }

    public function del_keyword(){
        $id = isset($_REQUEST['id'])
                    ? intval($_REQUEST['id']) ?: ''
                    : '';
        $count = Keyword::deleteAll($id);
        $res = [
            'info'=>$count,
            'status'=>'ok'
        ];
        return $this->returnJson($res);
    }

    public function status()
    {

        $res = [
            'status' => 'ok',
            'log' => $this->task->stat(),
            'pid' => $this->task->get_pid(),
            'running' => $this->task->is_running(),
            'files' => $this->task->download_file_url(),
        ];
        return $this->returnJson($res, 'ok');
    }



}