<?php

require_once 'inc/Models/AppConfig.php';

class AppConfigAjax extends BaseController
{
    public $returnType = 'json';

    public function index()
    {
        // $this->requireUser();
        $this->checkCsrfToken();

        $data = 'test';

        return $this->returnJson($data);
    }

    public function add()
    {
        // $this->requireUser();
        $this->checkCsrfToken();

        if (!$this->checkParam()) {
             return $this->returnJson('Bad Params!', 'error');
        }

        $data   = $this->getDataFromPOST();
        $newid  = AppConfig::create($data)->id;
        $status = $newid == null ? 'eror' : 'ok';
        $data   = ['newid' => $newid];

        $this->returnJson($data, $status);
    }

    public function edit()
    {
        // $this->requireUser();
        $this->checkCsrfToken();
        
        if (!$this->checkParam()) {
            return $this->returnJson('Bad Params!', 'error');
        }

        $id  = isset($_POST['appconfig_id']) ? intval($_POST['appconfig_id']) : 0;
        if (empty($id)||$id==0) {
            $status = 'error';
            return $this->returnJson('Wrong Id!', 'error');
        }

        $data   = $data   = $this->getDataFromPOST();
        $appconfig = new AppConfig($id);
        $appconfig->type  =$data['type'];
        $appconfig->appid = $data['appid'];
        $appconfig->configure = $data['configure'];
        $f = $appconfig->update();
        if (!$f) {
            return $this->returnJson('Update Fail!', 'error');
        }

        return $this->returnJson($data, 'ok');
    }

    public function delete()
    {
        // $this->requireUser();
        $this->checkCsrfToken();

        if (!isset($_POST['appconfig_id'])) {
            return $this->returnJson('Wrong  Id!', 'error');
        }
        $id = intval($_POST['appconfig_id']);
        if ($id == 0) {
            return $this->returnJson('Wrong  Id!', 'error');
        }
        $appconfig = new AppConfig($id);
        // debug("AppConfig=".json_encode($appconfig));
        $f         = $appconfig->delete();
        $status    = 'ok';
        $data      = ['id' => $id];
        if (!$f) {
            $status = 'error';
        }

        return $this->returnJson($data, $status);
    }

    public function delete_many()
    {
        // $this->requireUser();
        $this->checkCsrfToken();

        if (!isset($_POST['del_list'])) {
            return $this->returnJson('Wrong  Params!', 'error');
        }

        try {

            $b64 = $_POST['del_list'];
            // $this->debug_var_dump($b64);
            $js = base64_decode($b64);
            // debug('js='+$js);
            $ids = json_decode($js);
            debug('delete_many,ids=');
            // $this->debug_var_dump($ids);
            $count = AppConfig::deleteAll($ids);
            $r     = ['success_count' => $count];

            return $this->returnJson($r, 'ok');
        } catch (Exception $e) {
            return $this->returnJson('Wrong  Params!', 'error');
        }

    }

    private function checkParam()
    {
        $keylist = ['appconfig_appid', 'appconfig_configure'];
        foreach ($keylist as $key) {
            if (!isset($_POST[$key]) || empty($_POST[$key])) {
                return false;
            }
        }

        return true;
    }

    private function getDataFromPOST()
    {
        $attrs = array_keys(AppConfig::$tableFields);
        $data  = [];
        foreach ($attrs as $attr) {
            if ($attr == 'id') {
                continue;
            }
            if (isset($_POST['appconfig_' . $attr])) {
                $data[$attr] = $_POST['appconfig_' . $attr];
            }
        }
        $data['type'] = 'default';
        $data['configure']=json_encode(json_decode($data['configure']));
        return $data;
    }

}
