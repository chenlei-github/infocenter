<?php
require_once 'inc/class/AsyncTask.php';
require_once 'inc/class/BaseController.php';


/**
* 
*/
class AsoKeywordsPage  extends BaseController
{
    protected $template_dir = 'opdata/';
    protected $config;
    protected $task;
    protected $mail_list;

    public function __construct()
    {
        parent::__construct();
        $_config = require 'config/asynctask.php';
        $config = $_config['aso_keywords'];
        debug(json_encode($config));
        $this->config = $config;
        $this->task = new AsyncTask($config['cmd'], $config['pidfile'],
             $config['outputfile'], $config['tail_recent_number']);

        $mail_list = require_once 'config/mail_list.php';
        $this->mail_list =  $mail_list['aso_keywords'];
    }

    public function index() 
    {
        $this->requireUser();
        $this->setCsrfToken();

        $this->assign('title', 'Google Play Keywords Tools.');
        $this->assign('is_running', $this->task->is_running());
        $this->assign('mail_list', $this->mail_list);
        return $this->display('gpkeywords.tpl');
    }

    public function viewTask()
    {
        $this->requireUser();
        $this->setCsrfToken();

        $task_input = '';
        if (file_exists($this->config['inputfile'])) {
            $task_input = file_get_contents($this->config['inputfile']);
        }
        echo nl2br($task_input);
        return;
    }


    public function results()
    {
        $this->requireUser();
        $this->setCsrfToken();

        $website_dir = $this->config['website_dir'];
        $website_baseurl = $this->config['website_baseurl'];

        $file_list = [];
        $scan_result = scandir($website_dir);
        
        foreach ($scan_result as $entry) {
            if ($this->endsWith($entry,'.xls')) {
                $mtime = date('Y-m-d H:i:s' ,filemtime($website_dir . '/' .$entry));
                $file_list[$mtime] = [
                    'name' => $entry,
                    'url' => "$website_baseurl/$entry",
                ];                
            }
        }

        ksort($file_list);
        $file_list = array_reverse($file_list);


        $this->assign('title', 'Google Play Keywords Results.');
        $this->assign('file_list', $file_list);
        return $this->display('aso_results.tpl');
    }

    protected function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

}