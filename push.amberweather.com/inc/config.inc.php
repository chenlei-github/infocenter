<?php
//$configs = require '../config.php';
//$configs = require ($_SERVER['DOCUMENT_ROOT'] . "/../config.php");
$configs = require __DIR__ . '/../../config.php';

$db_host = $configs['INFOCENTER_DB_HOST'];
$db_user = $configs['INFOCENTER_DB_USER'];
$db_pass = $configs['INFOCENTER_DB_PASS'];
$db_name = $configs['INFOCENTER_DB_NAME'];

$db_charset = 'utf-8';

$tcache_cfg = $configs['Tcache_cfg_fstore'];
$APP_LIST   = $configs['app_list'];

