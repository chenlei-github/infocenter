<?php
require_once 'inc/class/AsyncTask.php';
require_once 'inc/class/BaseController.php';


/**
* 
*/
class AsoKeywordsAjax extends BaseController
{
    public $returnType = 'json';
    protected $task;
    protected $config;

    public function __construct()
    {
        parent::__construct();
        $_config = require 'config/asynctask.php';
        $config = $_config['aso_keywords'];
        debug(json_encode($config));
        $this->config = $config;
        $this->task = new AsyncTask($config['cmd'], $config['pidfile'],
             $config['outputfile'], $config['tail_recent_number']);
    }

    public function index()
    {
        $this->checkCsrfToken();
        return false;
    }

    public function submit()
    {

        $this->checkCsrfToken();

        if ($this->task->is_running()) {
            return $this->returnJson('Last task is running. You may stop it or wait ...', 'error');
        }

        $data = isset($_POST['data']) ? $_POST['data'] : '';
        if (empty($data)) {
            return $this->returnJson('Bad Param', 'error');
        }
        debug($data);
        $job = json_decode($data, 1);

        $reversed = 'False';
        if ($job['reversed']) {
            $reversed = 'True';
        }

        $do_top_downloads = 'False';
        if ($job['do_top_downloads']) {
            $do_top_downloads = 'True';
        }

        $inputfile = $this->config['inputfile'];

        $has_exits = file_exists($inputfile);

        $f = fopen($inputfile, 'a');
        if ($has_exits) {
            fwrite($f, '@--' . PHP_EOL);
        }
        fwrite($f, '@task_name '. $job['task_name'] . PHP_EOL);
        fwrite($f, '@country ' . $job['country'] . PHP_EOL);
        fwrite($f, '@lang ' .  $job['lang'] . PHP_EOL);
        fwrite($f, '@keywords_method ' .  $job['keywords_method'] . PHP_EOL);
        fwrite($f, '@do_top_downloads ' . $do_top_downloads . PHP_EOL);
        fwrite($f, '@suffix_1st_list ' . $job['suffix_1st_list'] . PHP_EOL);
        fwrite($f, '@suffix_2nd_list ' . $job['suffix_2nd_list'] . PHP_EOL);
        fwrite($f, '@suffix_3rd_list ' . $job['suffix_3rd_list'] . PHP_EOL);
        fwrite($f, '@level ' .  $job['level'] . PHP_EOL);
        fwrite($f, '@reversed '. $reversed . PHP_EOL);
        fwrite($f, '@maillist '. $job['mail_list'] . PHP_EOL);
        fwrite($f, implode(PHP_EOL,$job['word_list']));
        fwrite($f, PHP_EOL);        
        fclose($f);
        $this->returnJson('', 'ok');
    }


    public function clear()
    {
        $this->checkCsrfToken();

        if ($this->task->is_running()) {
            return $this->returnJson('Last task is running. You may stop it or wait ...', 'error');
        }

        $inputfile = $this->config['inputfile'];

        if (!file_exists($inputfile)) {
            return $this->returnJson('clear task success.', 'ok');
        }
        if (unlink($inputfile)) {
            return $this->returnJson('clear task success.', 'ok');
        } else {
            return $this->returnJson('Fail to clear task!', 'error');
        }
    }

    public function start()
    {
        $this->checkCsrfToken();

        if ($this->task->is_running()) {
            return $this->returnJson('Last task is running. You may stop it or wait ...', 'error');
        }

        if (!file_exists($this->config['inputfile'])) {
           return $this->returnJson('No Job to run.','error');
        }

        $stat = $this->task->start();
        if ($stat) {
            $res = [
                'running' => $this->task->is_running(),
                'pid' => $this->task->get_pid(),
            ];
            $this->returnJson($res,'ok');
        } else {
            $this->returnJson('fail to start job', 'error');
        }
    }

    public function stop()
    {
        $this->checkCsrfToken();

        if ($this->task->is_running()) {
            $this->task->stop();
            $this->returnJson('success to stop job.', 'ok');
        } else {
            $this->returnJson('Fail to stop job!', 'error');
        }
    }

    public function status()
    {
        $this->checkCsrfToken();

        $has_submit = file_exists($this->config['inputfile']);

        $res = [
            'status' => 'ok',
            'log' => $this->task->stat(),
            'pid' => $this->task->get_pid(),
            'running' => $this->task->is_running(),
            'files' => $this->task->download_file_url(),
            'has_submit' => $has_submit,
        ];
        return $this->returnJson($res, 'ok');
    }

}

