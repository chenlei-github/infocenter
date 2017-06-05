<?php
date_default_timezone_set('UTC');

require_once __DIR__ . '/../vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;



// get to the root directory of website then set include path
$r_path = realpath(__DIR__ . '/../');
set_include_path($r_path . PATH_SEPARATOR . get_include_path());

$configs = require 'config.php';
include_once 'inc/PhpConsole/phpconsole.php';
require_once 'inc/class/BaseController.php';
require_once 'inc/common/functions.php';


$debug = $configs['DEBUG'] ? true : false;
!defined('DEBUG') && define('DEBUG', $debug);

if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
}

// PhpConsole::start();
debug('start');
debug($debug ? 'mode: debug' : 'disable debug');
$request_path = '';

if (isset($_SERVER['PATH_INFO']) && strlen($_SERVER['PATH_INFO']) > 1) {
    // compatible path_info
    $request_path = ltrim_recursive($_SERVER['PATH_INFO'], '/');
    $pathinfo     = explode('/', $request_path);

    $module     = !empty($pathinfo[0]) ? ucfirst($pathinfo[0]) : 'Home';
    $controller = !empty($pathinfo[1]) ? ucfirst($pathinfo[1]) : 'Index';
    $action     = !empty($pathinfo[2]) ? $pathinfo[2] : 'index';

} elseif (isset($_SERVER['REQUEST_URI']) && strlen($_SERVER['REQUEST_URI']) > 5) {
    // compatible request_uri
    if (strpos($_SERVER['REQUEST_URI'], '?')) {
        $request_path = strstr($_SERVER['REQUEST_URI'], '?', true);
    } else {
        $request_path = $_SERVER['REQUEST_URI'];
    }
    $request_path = ltrim_recursive($request_path, '/');
    $pathinfo     = explode('/', $request_path);

    $module     = !empty($pathinfo[0]) ? ucfirst($pathinfo[0]) : 'Home';
    $controller = !empty($pathinfo[1]) ? ucfirst($pathinfo[1]) : 'Index';
    $action     = !empty($pathinfo[2]) ? $pathinfo[2] : 'index';
} else {
    // default route
    $module     = 'Home';
    $controller = 'Index';
    $action     = 'index';
}

if ($controller === 'Ajax') {
    $class  = ucfirst($pathinfo[2]) . 'Ajax';
    $action = !empty($pathinfo[3]) ? $pathinfo[3] : 'index';
} else {
    $class = $controller . 'Page';
}

$class_file = '../inc/Controllers/' . $module . '/' . $class . '.php';

debug("request_path: [$request_path] > [$module/$class/$action]");
debug('class_file: ' . $class_file);

if (!file_exists($class_file)) {
    debug('page not found.');
    // header('Location: /index.html');
    die('Page not found.');
}

require_once $class_file;

if (!class_exists($class)) {
    debug("$class is not exists.");
    die('error');
}

try {
    $web = new $class();

    // if ($web->authenticate()) {
    //     //do something
    // } else {
    //     //no user
    //     header('Location: /');
    //     die('403');
    // }

    if (method_exists($web, $action)) {
        debug("run:$controller $action");

        $res = $web->$action();

        // echo $web->pageHeader();
        // echo $res;
        // echo $web->pageFooter();
    } else {
        debug('page not found');
        die('Page not found.');
    }

} catch (Exception $error) {
    DEBUG && print $error;
    //header('Location: /index3.html');
    die('error');
}

// dump(): just 4 test
function dump($var, $type = '')
{
    switch ($type) {
        case 2:
        case 'json':
            header('Content-Type: Application/json');
            echo json_encode($var);
            break;
        case 1:
        case 'vardump':
            echo '<pre>';
            var_dump($var);
            break;
        case 0:
        case 'raw':
        default:
            if (is_string($var)) {
                echo $var;
            } elseif (is_array($var)) {
                header('Content-Type: Application/json');
                echo json_encode($var);
            } else {
                echo '<pre>';
                var_dump($var);
            }
            break;
    }
    die;
}

function getLogInstance($name)
{
    static $logs = [];
    $log_file = $GLOBALS['configs']['log_path'];
    if (!array_key_exists($name, $logs)) {
        $log = new Logger($name);
        $log->pushHandler(new StreamHandler($log_file, Logger::DEBUG));
        $logs[$name] = $log;
    }
    return $logs[$name];
}