<?php

$configs = require __DIR__ . '/../../config.php';

$debug = $configs['DEBUG'];

if ($debug) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
}

include_once 'global.func.php';
include_once 'config.inc.php';
include_once 'mysqldb.php';
include_once 'tcache.inc.php';

$db = new MysqlDb($db_host, $db_user, $db_pass, $db_name);

$use_tcache = $configs['Tcache_enable_fstore'];

$tc = new Tcache($tcache_cfg);

foreach (['_COOKIE', '_POST', '_GET'] as $_request) {
    foreach ($$_request as $_key => $_value) {
        !isset($$_key) && $_key{0} != '_' && $$_key = daddslashes($_value);
    }
}
