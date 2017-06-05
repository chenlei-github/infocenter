<?php
/**
 * Base controller
 *
 * @date   : 2016/07/27
 * @author : Tiger <DropFan@Gmail.com>
 */
require_once 'inc/class/MySmarty.php';

abstract class BaseController
{

    protected $tokenKey = 'csrf_token';
    protected $configs;

    protected $smarty;
    protected $template_dir = '/';

    public $returnType = 'webpage';

    public $queryParams = [];

    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $this->configs = require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';

        if ($this->returnType == 'webpage') {
            $this->smarty = new MySmarty();
            $this->smarty->default_modifiers = ['escape:"html"'];
            $this->smarty->debugging = false;
        }

        $this->handleQueryParams();
    }

    protected function handleQueryParams()
    {
        foreach ($_GET as $k => $v) {
            !isset($this->getParams[$k]) && $this->getParams[$k]     = daddslashes($v);
            !isset($this->queryParams[$k]) && $this->queryParams[$k] = daddslashes($v);
        }

        foreach ($_POST as $k => $v) {
            !isset($this->postParams[$k]) && $this->postParams[$k]   = daddslashes($v);
            !isset($this->queryParams[$k]) && $this->queryParams[$k] = daddslashes($v);
        }
    }

    protected function get($var)
    {
        if (isset($this->getParams[$var])) {
            return $this->getParams[$var];
        }
    }

    protected function post($var)
    {
        if (isset($this->postParams[$var])) {
            return $this->postParams[$var];
        }
    }

    protected function assign($var, $value = null, $nocache = false)
    {
        if (!$var || $value === null) {
            return false;
        }

        if (is_object($value)) {
            $value = get_object_vars($value);
            // var_dump($value);die;
        }

        return $this->smarty->assign($var, $value, $nocache);
    }

    protected function fetch($tpl)
    {
        $tpl = $this->template_dir . $tpl;
        $this->assign('token', $this->getCsrfToken());

        return $this->smarty->fetch($tpl);
    }

    protected function display($tpl)
    {
        $tpl = $this->template_dir . $tpl;

        $this->assign('token', $this->getCsrfToken());

        $this->smarty->display($tpl);
    }

    protected function returnJson($data, $status = 'ok', $err = [])
    {

        $res = [
            'status' => $status,
        ];

        if (is_array($err) && !empty($err)) {
            $err['code'] > 0 && $res['errno']     = $err['code'];
            !empty($err['info']) && $res['error'] = $err['info'];
        }

        if ($data) {
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    if (!DEBUG) {
                        $key{0} != '_' && $res[$key] = $value;
                    } else {
                        $res[$key] = $value;
                    }
                }
            } else {
                $res['message'] = $data;
            }
        }

        header('Content-Type: application/json');
        echo json_encode($res, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        die;
    }

    public function authenticate()
    {
        if ($this->requireUser()) {
            $uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : null;
            // $this->user = new User($uid);

            if ($this->user->id != null) {
                $this->assign('user', $this->user);

                return true;
            }

            return false;
        }

        return true;
    }

    public function redirect($url)
    {
        $this->assign('url', $url);
        $this->smarty->display('common/redirect.tpl');
        die;
    }

    protected function generateRandomString($length = 10)
    {
        $characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    protected function getCsrfToken()
    {
        if (!isset($_SESSION[$this->tokenKey])) {
            $_SESSION[$this->tokenKey] = sha1($this->generateRandomString(16));
        }

        return $_SESSION[$this->tokenKey];
    }

    protected function setCsrfToken()
    {
        $this->assign($this->tokenKey, $this->getCsrfToken());
        debug($this->tokenKey . '=' . $this->getCsrfToken());
    }

    protected function checkCsrfToken($token = null)
    {
        if (!$token) {
            $token = isset($_REQUEST[$this->tokenKey])
            ? $_REQUEST[$this->tokenKey]
            : (isset($_REQUEST['token']) ? $_REQUEST['token'] : null);
        }
        if ($token && isset($_SESSION[$this->tokenKey]) && $token === $_SESSION[$this->tokenKey]) {
            return true;
        } elseif ($this->returnType === 'json') {
            return $this->returnJson(['errors' => 'Invalid token or session timeout, please refresh the page!'], 'error');
        } else {
            return false;
        }
    }

    abstract public function index();

    protected function requireUser()
    {
        if (isset($_SESSION['google_data']) && isset($_SESSION['user_email'])) {
            $user = json_decode(json_encode($_SESSION['google_data']));

            $user_email = $_SESSION['user_email'];
            $this->user = $user;
            // var_dump($this->user);die;
            $this->assign('user', $user);

            return true;
        } else {
            if ($this->returnType == 'webpage') {
                $googleLogin = "http://{$_SERVER['SERVER_NAME']}/google-auth/login.php";
                $this->redirect($googleLogin);
            } elseif ($this->returnType == 'json') {
                $this->returnJson('Invalid user - Authorization Required.', 'error');
            }
        }

        return false;
    }
}
