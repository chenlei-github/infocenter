<?php

require_once 'inc/Models/Message.php';
require_once 'inc/class/Country.php';
require_once '/var/www/infocenter/config.php';

class CrawlerAjax extends BaseController
{

    public function index()
    {
        // $this->requireUser();
        $this->checkCsrfToken();

        $data = 'okoooooooook';

        return $this->returnJson($data);
    }

    public function import()
    {
        // $this->requireUser();
        $this->checkCsrfToken();

        $crawler = $GLOBALS['configs']['crawler'];

        if (!isset($_REQUEST['which_one']) || !array_key_exists($_REQUEST['which_one'], $crawler)) {
            $ret = [
                'status' => 'error',
                'msg'    => 'miss which_one',
            ];

            return $this->returnJson($ret);
        }

        $which        = $_REQUEST['which_one'];
        $crawler_path = $crawler[$which]['crawler'];
        $log_file     = $crawler[$which]['log'];

        $uploaddir   = '/var/www/infocenter/uploads/crawler/';
        $uploadfile  = $uploaddir . $which;
        $msg         = '';
        $status_code = 200;
        $status      = 'ok';
        $crawler_msg = '';

        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            $msg         = "File is valid, and was successfully uploaded.\n";
            $status      = 'ok';
            $crawler_msg = $this->import_data($crawler_path, $uploadfile, $log_file);
            $crawler_msg = explode("\n", $crawler_msg);
        } else {
            $msg         = "Upload File Fail!\n";
            $status      = 'error';
            $status_code = 500;
        }

        $ret = [
            'status'      => 'ok',
            'msg'         => $msg,
            'error'       => $_FILES['file']['error'],
            'crawler_msg' => $crawler_msg,
            '_FILES'      => $_FILES,
        ];

        return $this->returnJson($ret);

    }

    private function import_data($crawler_path, $input_file, $log)
    {
        $cmd = "cd /var/www/infocenter/crawler/op_data; python $crawler_path $input_file";
        debug('cmd:' . $cmd);
        $ret = shell_exec($cmd);
        debug($ret);
        $cmd = 'tail -n7 ' . $log;
        debug('cmd:' . $cmd);
        $log_out = shell_exec('tail -n7 ' . $log);
        debug($log_out);

        return $log_out;
    }

}
