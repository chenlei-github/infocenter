<?php
require_once 'inc/class/Country.php';
require_once 'inc/class/BaseController.php';
require_once 'inc/Models/Store/Theme.php';
require_once 'inc/Models/Opdata/AndroidPublisher.php';
require_once 'inc/Models/Opdata/GPlayText.php';
require_once 'inc/class/AsyncTask.php';

/**
 * Aso ajax Controller
 *
 * @date   : 2016/10/27
 * @author : Tiger <DropFan@Gmail.com>
 */
class AsoAjax extends BaseController
{

    public $returnType = 'json';
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


    public function getGooglePlayDescriptionList()
    {
        // $this->checkCsrfToken();

        $status = 'error';
        $messages = [''];
        $errors = [''];


        $data = json_decode($_POST['data'], true);

        if (!$data) {
            $status = 'error';
            $errors = ['Invalid data.'];
        }

        $play_account = $data['play_account'];

        $credentials = $this->configs['google_play_account'][$play_account]['credentials'];

        $ap = new AndroidPublisher($credentials);
        $status = 'ok';
        $edits_lists = [];
        foreach ($data['package_names'] as $package) {
            $edits_lists[$package] = [];
            $res = $ap->getEditsListing($package);
            if (!$res) {
                $errors[] = "{$package} error:";
                $errors[] = $ap->getLastError();
                $status = 'error';
            } else {
                $messages[] = " Get {$package} list success!";
                $listings = $res->getListings();

                foreach ($listings as $listing) {
                    $lang = $listing['language'];
                    $edits_lists[$package][$lang] = [];
                    $edits_lists[$package][$lang]['language'] = $listing['language'];
                    $edits_lists[$package][$lang]['title'] = $listing['title'];
                    $edits_lists[$package][$lang]['shortDescription'] = $listing['shortDescription'];
                    $edits_lists[$package][$lang]['fullDescription'] = $listing['fullDescription'];
                    $edits_lists[$package][$lang]['video'] = $listing['video'];
                }
            }
        }
        // $errors[] = $ap->errors;

        // dump($androidpublisher);
        $resp = [
            'status' => $status,
            'messages' => $messages,
            'errors' => $errors,
            'packages' => $data['package_names'],
            'list' => $edits_lists,
        ];
        return $this->returnJson($resp);
    }

    public function saveGPlayText()
    {
        $this->checkCsrfToken();

        $type = $this->post('type');
        debug("type:$type");
        $theme_config = require 'config/theme.php';
        $theme_types  = $theme_config['types'];
        if (!array_key_exists($type, $theme_types)) {
            $resp = [
                'status' => 'error',
                'data' => [],
                'messages' => [''],
                'errors' => ['Bad param `type`'],
            ];
            return $this->returnJson($resp);
        }

        $data = [
            'type' => $this->post('type'),
            'name' => $this->post('name'),
            'data' => $this->post('data'),
        ];
        $text_id = intval($this->post('id'));
        // var_dump($data['data']);

        if ($validation = GPlayText::validation($data)) {
            if ($text_id > 0) {
                $m = new GPlayText($text_id);
                $m->name = $data['name'];
                $m->data = $data['data'];
                // $m->data = base64_encode($data['data']);
                if ($m->update()) {
                    $resp = [
                        'status' => 'ok',
                        'id' => $m->id,
                        'messages' => ['Update success!'],
                        'errors' => ['']
                    ];
                } else {
                    $resp = [
                        'status' => 'error',
                        'messages' => [''],
                        'errors' => ['Update failed!']
                    ];
                }
                // end update
            } else {
                // $data['data'] = base64_encode($data['data']);
                $m = GPlayText::create($data);
                if ($m->id > 0) {
                    $resp = [
                        'status' => 'ok',
                        'id' => $m->id,
                        'messages' => ['Add success!'],
                        'errors' => ['']
                    ];
                } else {
                    $resp = [
                        'status' => 'error',
                        'messages' => [''],
                        'errors' => ['Add data failed. Please try again.']
                    ];
                }
                // end create
            }
        } else {
            $resp = [
                'status' => 'error',
                'messages' => ['Invalid data!'],
                'errors' => $validation,
            ];
        }


        return $this->returnJson($resp);
    }

    public function deleteGPlayText()
    {
        $this->checkCsrfToken();

        $text_id = intval($this->post('id'));

        if ($text_id > 0) {
            $m = new GPlayText($text_id);

            if ($m->delete()) {
                $resp = [
                    'status' => 'ok',
                    'id' => $m->id,
                    'messages' => ['delete success!'],
                    'errors' => ['no errors']
                ];
            } else {
                $resp = [
                    'status' => 'error',
                    'messages' => ['delete failed. Please try again.'],
                    'errors' => ['']
                ];
            }
        } else {
            $resp = [
                'status' => 'error',
                'messages' => ['Invalid text id!'],
                'errors' => ['']
            ];
        }

        return $this->returnJson($resp);
    }

    public function getGPlayTextList()
    {
        $status = 'error';
        $messages = [''];
        $errors = [''];
        $data = [];

        $type = $this->post('type');
        debug("type:$type");

        $theme_config = require 'config/theme.php';
        $theme_types  = $theme_config['types'];

        if (!array_key_exists($type, $theme_types)) {
            $resp = [
                'status' => 'error',
                'data' => [],
                'messages' => [''],
                'errors' => ['Bad param `type`'],
            ];
            return $this->returnJson($resp);
        }
        $type = intval($type);
        $where = " AND `type` = $type ";
        $rows = GPlayText::getAll(
            $page = 1,
            $perpage = 100,
            $where = $where,
            $sortby = ['updated_at' => 'DESC']
        );
        // var_dump($rows);
        // die;
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                // $row['data'] = base64_decode($row['data']);

                // $row['data'] = str_replace('\\"', '"', $row['data']);
                // $row['data'] = str_replace('\\\\', '\\', $row['data']);
                $row['data'] = stripslashes($row['data']);

                // var_dump(json_decode($row['data'], 1));die;
                $row['data'] = json_decode($row['data'], 1);
                $data[] = $row;
            }
            $status = 'ok';
        } else {
            $messages = ['not found!'];
            $errors = ['not found!'];
        }

        $resp = [
            'status' => $status,
            'data' => $data,
            'messages' => $messages,
            'errors' => $errors,
        ];
        return $this->returnJson($resp);
    }

    //  start update google paly text asynctask
    public function startGPlayTask()
    {
        $this->checkCsrfToken();
        $log = getLogInstance(get_class() . '::startGPlayTask()');

        $log->debug('RAW _REQUEST:' . json_encode($_REQUEST));

        $task_type = isset($_POST['task_type']) ? $_POST['task_type'] : '';
        $data = json_decode($_POST['data'], true);

        $log->debug('task_type:' . $task_type);

        $asynctask_list = require 'config/asynctask.php';

        if (!$data || !array_key_exists($task_type, $asynctask_list)) {
            $resp = [
                'status' => 'error',
                'messages' => [],
                'errors' => ['Invalid data.'],
            ];
            $log->error('Invalid data.');
            $log->error('Exit.');
            return $this->returnJson($resp);
        }
        $asynctask_config = $asynctask_list[$task_type];

        // check type
        $package_names = $data['package_names'];
        $type = $data['type'];
        debug($type);
        $log->debug('type:' . $type);

        $theme_config = require 'config/theme.php';
        $theme_types  = $theme_config['types'];
        if (!array_key_exists($type, $theme_types)) {
            $resp = [
                'status' => 'error',
                'messages' => [],
                'errors' => ['Bad param `type`.'],
            ];
            $log->error('Bad param `type!');
            $log->error('Exit.');
            return $this->returnJson($resp);
        }
        // remove unused type;
        unset($data['type']);
        // check package
        $where = [
            'type' => $type,
        ];
        $all_package = Theme::getPackageNames($where, [], 0, 999);
        $all_package_names = [];
        foreach ($all_package as $v) {
            $all_package_names[] = $v['package_name'];
        }
        foreach ($package_names as $package) {
            if (!in_array($package, $all_package_names)) {
                $resp = [
                    'status' => 'error',
                    'messages' => [],
                    'errors' => ["$package is not the type [$type]."],
                ];
                $log->error("$package is not the type [$type].");
                $log->error('Exit.');
                return $this->returnJson($resp);
            }
        }
        debug(json_encode($data));
        $log->debug(json_encode($data));

        $task = new AsyncTask($asynctask_config['cmd'], $asynctask_config['pidfile'],
             $asynctask_config['outputfile'], $asynctask_config['tail_recent_number']);
        if ($task->is_running()) {
            $resp = [
                'status' => 'error',
                'messages' => [],
                'errors' => ['Task is running.'],
            ];
            $log->error('Task is running.');
            $log->error('Exit.');
            return $this->returnJson($resp);
        }

        $inputfile = $asynctask_config['inputfile'];
        $f = fopen($inputfile,'w');
        fwrite($f, json_encode($data,1));
        fclose($f);

        // echo 'test';
        // die;
        $task->start();

        $resp = [
            'status' => 'ok',
            'messages' => ['Start Task Success.'],
            'errors' => [],
        ];
        $log->debug('Start Task Success.');
        $log->debug('Exit.');
        return $this->returnJson($resp);
    }

    public function stopGplayTask()
    {
        $this->checkCsrfToken();

        $log = getLogInstance(get_class() . '::stopGplayTask()');
        $log->debug('RAW _REQUEST:' . json_encode($_REQUEST));

        $task_type = isset($_POST['task_type']) ? $_POST['task_type'] : '';
        debug("task_type:$task_type");
        $log->debug("task_type:$task_type");

        $asynctask_list = require 'config/asynctask.php';

        if (!array_key_exists($task_type, $asynctask_list)) {
            $log->error('Bad param `task_type`!');
            return $this->returnJson('Bad param `task_type`!');
        }

        $asynctask_config = $asynctask_list[$task_type];

        $task = new AsyncTask($asynctask_config['cmd'], $asynctask_config['pidfile'],
             $asynctask_config['outputfile'], $asynctask_config['tail_recent_number']);

        if ($task->is_running()) {
            $task->stop();
            if ($task->is_running()) {
                $log->error('Fail to stop Task!');
                $this->returnJson('Fail to stop Task!', 'error');
            } else {
                $log->debug('Success to stop Task!');
                $this->returnJson('Success to stop Task!', 'ok');
            }
        } else {
            $log->error('No job is running!');
            $this->returnJson('No job is running!', 'error');
        }
    }


    public function getGplayTaskStatus()
    {

        $this->checkCsrfToken();

        $log = getLogInstance(get_class() . '::getGplayTaskStatus()');
        $log->debug('RAW _REQUEST:' . json_encode($_REQUEST));

        $task_type = isset($_POST['task_type']) ? $_POST['task_type'] : '';
        debug("task_type:$task_type");
        $log->debug("task_type:$task_type");

        $asynctask_list = require 'config/asynctask.php';

        if (!array_key_exists($task_type, $asynctask_list)) {
            $log->error('Bad param `task_type`!');
            $log->error('Exit.');
            return $this->returnJson('Bad param `task_type`!');
        }

        $asynctask_config = $asynctask_list[$task_type];

        $task = new AsyncTask($asynctask_config['cmd'], $asynctask_config['pidfile'],
             $asynctask_config['outputfile'], $asynctask_config['tail_recent_number']);

        $res = [
            'status' => 'ok',
            'log' => $task->stat(),
            'pid' => $task->get_pid(),
            'running' => $task->is_running(),
        ];
        $log->debug(json_encode($res));
        return $this->returnJson($res, 'ok');
    }

    public function getGplayTaskResults()
    {

        $this->checkCsrfToken();

        $log = getLogInstance(get_class() . '::getGplayTaskResults()');
        $log->debug('RAW _REQUEST:' . json_encode($_REQUEST));

        $task_type = isset($_POST['task_type']) ? $_POST['task_type'] : '';
        debug("task_type:$task_type");
        $log->debug("task_type:$task_type");

        $asynctask_list = require 'config/asynctask.php';

        if (!array_key_exists($task_type, $asynctask_list)) {
            $log->error('Bad param `task_type`!');
            return $this->returnJson('Bad param `task_type`!');
        }

        $asynctask_config = $asynctask_list[$task_type];

        $task = new AsyncTask($asynctask_config['cmd'], $asynctask_config['pidfile'],
             $asynctask_config['outputfile'], $asynctask_config['tail_recent_number']);

        if ($task->is_running()) {
            $log->error('Task Is Running.!');
            return $this->returnJson('Task Is Running.', 'error');
        }

        $resultfile = $asynctask_config['resultfile'];
        if (!file_exists($resultfile)) {
            $log->error('No Results!');
            return $this->returnJson('No Results.','error');
        }

        $results = file_get_contents($resultfile);

        $res = [
            'status' => 'ok',
            'results' => json_decode($results,1),
        ];
        $log->debug(json_encode($res));
        return $this->returnJson($res, 'ok');
    }

}
