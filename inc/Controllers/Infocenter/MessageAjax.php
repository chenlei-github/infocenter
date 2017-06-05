<?php
/**
 * Message Ajax Controller
 *         add/update/delete
 *
 * @date: 2016/07/27
 * @author: Tiger <DropFan@Gmail.com>
 */
require_once 'inc/Models/Message.php';
require_once 'inc/class/Country.php';
require_once 'inc/Models/MessageTranslation.php';

class MessageAjax extends BaseController
{
    public $returnType = 'json';

    public function index()
    {
        $this->checkCsrfToken();

        $data = 'okoooooooook';

        return $this->returnJson($data);
    }

    public function add()
    {
        $this->checkCsrfToken();

        $log = getLogInstance(get_class() . '::add()');
        $log->debug('RAW _REQUEST:' . json_encode($_REQUEST));
        $log->debug('_POST MESSAGE:' . json_encode( $_POST['message'] ));

        $data = isset($_POST['message'])
                    ? json_decode($_POST['message'], 1)
                    : null;
        $log->debug('RAW DATA:' . json_encode($data));
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

        $log->debug('after Country::CONTINENT_NAMES:' . json_encode($data));
        $group_field = [
            'notification' => 0,
            'popup' => 0,
            'region' => 0,
            'AS' => 0,
            'EU' => 0,
            'EE' => 0,
            'NA' => 0,
            'LA' => 0,
            'OC' => 0,
            'AF' => 0,
            'UN' => 0,
            'appid' => 0,
            'appver_min' => 0,
            'appver_max' => 0,
            'time'  => '00:00',
            'start' => '1970-01-01 00:00:01',
            'end' => '1970-01-01 00:00:01'
        ];

        if($data['status'] == '2'){
            foreach ($data as $key => $row) {
                if(in_array($key, array_keys($group_field))) $data[$key] = $group_field[$key];
            }
        }

        $validator = Message::validation($data);
        if ($validator === true) {
            $message = Message::create($data);
            $log->debug("create:message:" . json_encode($message));
            $log->debug("message->id:{$message->id}");
            if ($message->id > 0) {
                $resp = [
                    'id'      => $message->id,
                    'message' => 'Add success!',
                ];
                $log->debug('Insert Success:' . json_encode($resp));
            } else {
                $resp = [
                    'status'  => 'error',
                    'message' => 'Add failed!',
                    'errors' => ['Insert data failed. Please try again.']
                ];
                $log->debug('Insert Failed:' . json_encode($resp));
            }
        } else {
            $resp = [
                'status' => 'error',
                'message' => 'Bad Input!',
                'errors' => $validator
            ];
            $log->debug('Bad Input:' . json_encode($resp));
        }


        return $this->returnJson($resp);
    }

    public function update()
    {
        $this->checkCsrfToken();

        $log = getLogInstance(get_class() . '::update()');

        $log->debug('RAW _REQUEST:' . json_encode($_REQUEST));

        $id = isset($_REQUEST['id'])
                ? intval($_REQUEST['id']) ?: null
                : null;
        $data = isset($_POST['message'])
                ? json_decode($_POST['message'], 1)
                : null;
        $log->debug("RAW DATA:" . json_encode($data));
        $log->debug("`id`=" . json_encode($id));
        $log->debug("`data['id']`=" . json_encode($data['id']));

        if (!$id || !isset($data['id']) || $data['id'] != $id) {
            $status = 'error';
            $resp   = 'Invalid message id!';
            $log->error($resp);
            return $this->returnJson($resp, $status);
        }

        foreach (Country::CONTINENT_NAMES as $c) {
            $$c = explode(',', $data[$c]);
            if (is_array($$c) && !empty($$c)) {
                if (empty($countryCode)) {
                        continue;
                }
                $value = 0;
                foreach ($$c as $countryCode) {
                    $value |= Country::COUNTRY_LIST[$countryCode]['mask'];
                }
                $data[$c] = $value;
            } else {
                $data[$c] = 0;
            }
        }

        $log->debug('after Country::CONTINENT_NAMES :' . json_encode($data));
        $group_field = [
            'notification' => 0,
            'popup' => 0,
            'region' => 0,
            'AS' => 0,
            'EU' => 0,
            'EE' => 0,
            'NA' => 0,
            'LA' => 0,
            'OC' => 0,
            'AF' => 0,
            'UN' => 0,
            'appid' => 0,
            'appver_min' => 0,
            'appver_max' => 0,
            'time'  => '',
            'start' => '1970-01-01 00:00:01',
            'end' => '1970-01-01 00:00:01'
        ];

        if($data['status'] == '2'){
            foreach ($data as $key => $row) {
                if(in_array($key, array_keys($group_field))) $data[$key] = $group_field[$key];
            }
        }
        $validator = Message::validation($data);
        if ($validator === true) {
            $message = new Message($id);
            $log->debug("new message:" . json_encode($message));
            $log->debug("message->id:{$message->id}");
            if ($message->fillWith($data)->update()) {
                $resp = 'update success.';
                $log->info($resp);
            } else {
                $resp = [
                    'status'  => 'error',
                    'message' => 'Update failed!',
                    'errors'  => ['Update Failed, please try again !'],
                ];
                $log->error(json_encode($resp));
            }
            $log->debug("update message:" . json_encode($message));
        } else {
            $resp = [
                'status' => 'error',
                'errors' => $validator
            ];
            $log->error('Bad Input:' . json_encode($resp));
        }


        return $this->returnJson($resp);
    }

    public function delete()
    {
        $this->checkCsrfToken();

        $log = getLogInstance(get_class() . '::delete()');
        $log->debug('RAW REQUEST:' . json_encode($_REQUEST));

        $id = isset($_REQUEST['id']) ? $_REQUEST['id']: null;

        $log->debug("`id`=" . json_encode($id));

        $id_list = [];

        if (is_numeric($id)) {
            $log->debug("`id`:'$id' is numeric");
            $id_list[] = intval($id);
        } elseif (is_array($id)) {
            $log->debug("`id`: is array:" . json_encode($id));
            foreach ($id as $k => $i) {
                is_numeric($id) && $id_list[] = intval($i);
            }
            $log->debug("`id_list`=" . json_encode($id_list));
        } else {
            $resp = [
                'status' => 'error',
                'errors' => 'Invalid id.'
            ];
            $log->debug('Bad Type `id`:' . json_encode($id));
            return $this->returnJson($resp);
        }
        $count = Message::deleteAll($id_list);
        $log->debug("deleteAll:count:$count");
        $resp = [
            'status' => $count > 0 ? 'ok' : 'error',
            'message' => $count > 1
                            ? "$count messages deleted successfully!"
                            : ($count = 1 ?'A message deleted successfully!':'Delete failed.')
        ];
        $log->debug(json_encode($resp));
        return $this->returnJson($resp);
    }

    public function getTranslation()
    {
        $this->checkCsrfToken();

        $resp = [
            'status'      => 'error',
            'translation' => '',
            'info'        => 'Get failed!',
        ];

        if (isset($_POST['message_id']) && is_numeric($_POST['message_id'])) {
            $mid  = intval($_POST['message_id']);
            $data = MessageTranslation::fetchById($mid);
            foreach ($data as $lang => $val) {
                if (empty($val) || $lang == 'en_US' || $lang == 'en') {
                    unset($data[$lang]);
                }
            }
            debug('data:' . json_encode($data));
            if (is_array($data) && !empty($data)) {
                $resp['status']      = 'ok';
                $resp['translation'] = json_encode($data);
            } else {
                $resp['status'] = 'ok';
                $resp['info']   = 'Get None!';
            }
        } else {
            $resp['info'] = 'Miss Id!';
        }

        return $this->returnJson($resp);
    }

    public function setTranslation()
    {
        $this->checkCsrfToken();

        $log = getLogInstance(get_class() . '::setTranslation()');
        $log->debug("RAW _REQUEST:" . json_encode($_REQUEST));

        $resp = [
            'status' => 'error',
            'info'   => '',
        ];
        if (isset($_POST['message_id']) && is_numeric($_POST['message_id'])) {
            $mid         = intval($_POST['message_id']);
            $translation = isset($_POST['translation']) ? json_decode($_POST['translation'], 1) : null;
            if ($translation != null && MessageTranslation::validate($translation)) {
                if(in_array('en_US',array_keys($translation))){
                    unset($translation['en_US']);
                }
                $count = MessageTranslation::set($mid, $translation);
                debug("set():count:$count");
                $log->info("MessageTranslation::set():count:$count");
                if ($count > 0) {
                    $resp['status'] = 'ok';
                    $resp['count']  = $count;
                } else {
                    $resp['errors'] = 'Cannot Save Translation,Please Try Later!';
                }
            } else {
                $resp['errors'] = 'Miss Translation!';
            }
        } else {
            $resp['errors'] = 'Miss Id!';
        }
        $log->debug(json_encode($resp));
        return $this->returnJson($resp);
    }

}
