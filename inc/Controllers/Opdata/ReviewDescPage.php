<?php
require_once 'inc/class/AsyncTask.php';
require_once 'inc/class/BaseController.php';

class ReviewDescPage extends BaseController
{
    protected $template_dir = 'opdata/';
    public $task = '';
    private $_config = '';

    public function __construct()
    {
        parent::__construct();
        $this->requireUser();
        $res = require('config/aso.php');
        $this->_config = $res['app_review_desc'];

        $res = require 'config/asynctask.php';
        $task_config = $res['app_review_desc'];
        $this->task = new AsyncTask(
            $task_config['cmd'],
            $task_config['pidfile'],
            $task_config['outputfile'],
            $task_config['tail_recent_number']
        );
    }

    private function scanAll($dir, &$res = [])
    {
        if (is_dir($dir)){
            $children = scandir($dir);
            foreach ($children as $child){
              if ($child !== '.' && $child !== '..'){
                $res[] = [
                    'fname' => $child,
                    'ftime' => filemtime("{$dir}/{$child}")
                ];
                $this->scanAll($dir.'/'.$child);
              }
            }
        }
        array_multisort(
            array_column($res, 'ftime'),
            SORT_DESC,
            $res
        );
        return $res;
    }

    public function index()
    {
        $file_list = $this->scanAll($this->_config['dir']['out_data']);
        $file_list = array_column(
            array_slice($file_list, 0, $this->_config['show_out_num']), 'fname'
        );
        $this->assign('title', 'APP 描述&评论数据抓取');
        $this->assign('file_list', $file_list);
        $this->assign('host', $_SERVER['SERVER_NAME']);
        return $this->display('review_desc.tpl');
    }

    public function add()
    {
        $app = isset($_POST['app']) ? trim($_POST['app']) : '';
        $lan = isset($_POST['lan']) ? trim($_POST['lan']) : '';
        if(empty($app) || empty($lan)){
            return $this->returnJson(['msg' => 'param error', 'error']);
        }
        $app_arr = array_filter(explode("\r\n", $app));
        $data = [];
        foreach ($app_arr as $row) {
            $data[] = ['app_name' => $row, 'hl' => $lan];
        }
        if(empty($data)){
            return $this->returnJson(
                ['msg' => 'The package list data is empty', 'error']
            );
        }
        $file = $this->_config['dir']['app_list'] . 'input.json';
        $res = file_put_contents($file, $data = json_encode($data), LOCK_EX);
        if(!$res){
            return $this->returnJson(
                ['msg' => 'Writing data to file failed：'.$data, 'error']
            );
        }

        $res = $this->task->start();
        if(!$res){
            return $this->returnJson(
                ['msg' => 'Please wait for the previous task to complete'],
                'error'
            );
        }
        return $this->returnJson(['msg'=>'Success']);

    }


    public function status()
    {
        $res = [
            'status' => 'ok',
            'log' => $this->task->stat(),
            'pid' => $this->task->get_pid(),
            'running' => $this->task->is_running(),
            'files' => $this->results(1),
        ];
        return $this->returnJson($res, 'ok');
    }

    public function results($flag = 0)
    {
        $file_list = $this->scanAll($this->_config['dir']['out_data']);
        if($flag == 1){
            $file_list = array_column(
                array_slice($file_list, 0, $this->_config['show_out_num']), 'fname'
            );
            return $file_list;
        }
        $this->assign('file_list', array_slice($file_list, 0, 100));
        return $this->display('review_desc_results.tpl');
    }

    public function downfile()
    {
        $dir  = $this->_config['dir']['out_data'];
        $all_file = $this->scanAll($dir);
        $file = isset($_GET['f']) ? $_GET['f'] : '';
        //只允许下载指定配置目录中的文件，否则抛出异常
        if(!in_array($file, array_column($all_file, 'fname'))){
            echo "Non-existent file.";
            exit;
        }
        ob_start();
        header( "Content-type:  application/octet-stream ");
        header( "Accept-Ranges:  bytes ");
        header( "Content-Disposition:  attachment;  filename= {$file}.txt");
        $size=readfile($dir.'/'.$file);
        header( "Accept-Length: " .$size);
    }












}