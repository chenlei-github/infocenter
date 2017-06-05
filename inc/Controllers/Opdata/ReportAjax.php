<?php
/**
 * Report Ajax Controller
 *
 * @date:   2016/10/27
 * @author: Tiger <DropFan@Gmail.com>
 *
 * @last-modified : 2016/12/19
 * @author        : Tiger <DropFan@Gmail.com>
 */
require_once 'inc/Controllers/Opdata/ReportController.php';
require_once 'inc/class/Country.php';
require_once 'inc/Models/Opdata/RevModel.php';

class ReportAjax extends ReportController
{
    public $returnType = 'json';

    /**
     * Default action
     *
     * @return json
     */
    public function index()
    {
        // $this->requireUser();
        $this->checkCsrfToken();

        $appid = $this->get('appid') ?: null;

        $startDate = $this->get('startDate') ?: date('Y-m-d', time() - 86400);
        $endDate   = $this->get('endDate') ?: date('Y-m-d');
        $platform  = $this->get('platform') ?: '';
        $appname   = $this->get('appname') ?: '';
        $country   = $this->get('country') ?: '';
        $placement = $this->get('placement') ?: '';

        $m = new RevModel($startDate, $endDate, $platform, $appname);

        $break = [
             // 'platform'  => 'facebook',
             // 'appname'   => 'amber_weather',
             // 'country'   => '',
             // 'placement' => '1234567890'
        ];

        !empty($platform)  && $break['platform']  = $platform;
        !empty($appname)   && $break['appname']   = $appname;
        !empty($country)   && $break['country']   = $country;
        !empty($placement) && $break['placement'] = $placement;

        $m->selectValue('revenue')->setBreakdown($break);

        // $revenues = RevenueApp::summary($startDate, $endDate, $platform, $app);
        $report = $m->queryAll()->report();
        // $revenues = $model->summarize();

        $data = [
            'status' => 'ok',
            'report' => $report,
        ];

        return $this->returnJson($data);
    }


    /**
     * Query revenue report data
     *
     * @return json  revenue report json
     */
    public function revenue()
    {
        $this->index();

        // return $this->returnJson($data);
    }

    /**
     * Query country report
     *
     * @return json  country data
     */
    public function country()
    {

        $data = [
            'report' => $report_data,
        ];

        return $this->returnJson($data);
    }

    /**
     * update some data.
     *
     *
     * @return      json
     */
    public function update()
    {
        $this->checkCsrfToken();

        if ($this->get('data') == 'revenue') {
            $appname   = $this->post('appname')   ?: 'unnamed';
            $country   = $this->post('country')   ?: '';
            $placement = $this->post('placement') ?: '';
            $platform  = $this->post('platform');

            $date = $this->post('date');
            $rev  = $this->post('revenue');
            if (in_array($platform, $this->config['platforms'])) {

                $m = new RevModel();
                $sql = "INSERT INTO `revenue_app`
                    (`platform`,`appname`,`revenue`,`date`) VALUES
                    ('{$platform}','{$appname}','{$rev}','{$date}')
                    ON DUPLICATE KEY UPDATE `revenue`='{$rev}';";
                debug($sql);
                if ($m->db()->query($sql)) {
                    $data = [
                        'status' => 'ok',
                        'message' => 'modify success.',
                    ];
                } else {
                    $data = [
                        'status' => 'error',
                        'errors' => [$m->db()->error()]
                    ];
                }
            } else {
                $data = [
                    'error' => 'error',
                    'errors' => ['Invalid params!']
                ];
            }
        } else {
            $data = [
                'status' => 'error',
                'errors' => ['What are you doing ?!']
            ];
        }

        return $this->returnJson($data);
    }

    /**
     * Import data from user
     *
     * @return json log of importing script
     */
    public function import()
    {
        $this->checkCsrfToken();

        $crawler = $this->config['crawler'];

        if (!isset($_REQUEST['platform'])
            || !array_key_exists($_REQUEST['platform'], $crawler)
        ) {
            $ret = [
                'status' => 'error',
                'msg'    => 'miss platform',
            ];

            return $this->returnJson($ret);
        }

        $platform     = $_REQUEST['platform'];
        $crawler_path = $crawler[$platform]['crawler'];
        $log_file     = $crawler[$platform]['log'];

        $uploaddir   = '/var/www/infocenter/runtime/upload/';
        $uploadfile  = $uploaddir . 'opdata_import_' . $platform;
        $msg         = '';
        $status_code = 200;
        $status      = 'ok';
        $crawler_msg = '';

        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            $msg         = "File is valid, and was successfully uploaded.\n";
            $status      = 'ok';
            $crawler_msg = $this->importData($crawler_path, $uploadfile, $log_file);
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

    /**
     * Import data from upload file.
     *
     * @param string $script     The script path
     * @param string $input_file The path to data file
     * @param string $log        The log of import script
     *
     * @return log of import script.
     */
    private function importData($script, $input_file, $log)
    {
        $cmd = "cd /var/www/infocenter/crawler/op_data;
                python $crawler_path $input_file";
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
