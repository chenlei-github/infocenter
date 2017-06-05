<?php
error_reporting(0);
require '/var/www/lib/translator/translator.php';
require 'inc/common.inc.php';

$translation_config = require __DIR__ . '/../config/translator.php';
$LANGUAGE  = require __DIR__.'/../config/language.php';
// echo ('LANGUAGE='.json_encode($LANGUAGE));

$APPID    = $translation_config['MESSAGE']['APPID'];
$KEY_LIST = $translation_config['MESSAGE']['KEY_LIST'];

#ERROR NUMBER
define('ERROR_NO_ERROR', 0);
define('ERRRO_BAD_REQUEST', 1);
define('ERROR_NO_MESSAGE', 2);
define('ERRRO_NO_CONFIGURE', 4);

$g_errno = ERROR_NO_ERROR;

#获取三个参数

$g_lang   = isset($lang) ? str_replace('-','_',$lang) : 'en';
$g_appver = isset($appver) ? intval($appver) : 0;

#
$first_two = strtolower(substr($g_lang,0,2));
$last_two =  strtoupper(substr($g_lang,3,2));
$g_lang = $first_two . '_' .  $last_two ;
// echo 'g_lang:' . $g_lang . "\n";

#don't check appver so far;

#check lang
$languageList          = array_keys($LANGUAGE['CODE_TO_NAME']);
$languageShortNameList = array_keys($LANGUAGE['SHORT_TO_LONG']);
if (!in_array($g_lang, $languageList)) {
    $short_name = substr($g_lang,0,2);
    if (in_array($short_name, $languageShortNameList)) {
        $g_lang = $LANGUAGE['SHORT_TO_LONG'][$short_name];
    } else {
        $g_lang = 'en_US';
    }
}

#check appid
$appid_list = array_keys($APP_LIST);
$g_appid    = null;
if (isset($appid) && in_array($appid, $appid_list)) {
    $g_appid = $appid;
} else {
    showErrorMsg(ERRRO_BAD_REQUEST, "appid don't exits");
    exit(-1);
}

#获取国家code和洲code

$ip            = getIp();
$ip            = isset($ip) ? $ip : '';
$countryCode   = getCountryCodeByIP($ip);
$countryCode   = !empty($countryCode) ? $countryCode : 'UN';
$continentCode = Country::getContinentOfCountry($countryCode);
$continentCode = !empty($continentCode) ? $continentCode : 'UN';
$messages      = null;

$messages = getMessages($db, $continentCode, $countryCode, $g_appid, $g_appver, $g_lang);

if ($messages == null || empty($messages)) {
    $messages = [];
    $g_errno |= ERROR_NO_MESSAGE;
}

$AppConfig = null;
try {
    $AppConfig = getAppConfigure($db, $g_appid);
    $AppConfig = json_decode($AppConfig);
} catch (Exception $e) {
}
if ($AppConfig == null || empty($AppConfig)) {
    $g_errno |= ERRRO_NO_CONFIGURE;
}

$resp = null;
if ($g_errno == ERROR_NO_MESSAGE + ERRRO_NO_CONFIGURE) {
    $resp = [
        'status' => 'error',
        'errno'  => $g_errno,
    ];
} else {
    $resp = [
        'status'    => 'ok',
        'errno'     => $g_errno,
        'msg'       => $messages,
        'configure' => $AppConfig,
        // 'appver'=>$g_appver
    ];
}
#return json to client
header('Content-Type: application/json');
echo json_encode($resp, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);




function getIP()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

function getCountryCodeByIp($ip)
{
    $ret;
    $gi  = geoip_open('/usr/share/GeoIP/GeoIP.dat', GEOIP_STANDARD);
    $ret = geoip_country_code_by_addr($gi, $ip);
    geoip_close($gi);

    return $ret;
}

function getMask()
{
    $mask = getMaskOfCountry();
    $ip   = getIp();
    // echo "ip=$ip\n";
    $CountryCode = getCountryCodeByIP($ip);
    // echo "CountryCode=$CountryCode\n";
    $ContinentCode = getContinentOfCountry($CountryCode);
    // echo "ContinentCode=$ContinentCode\n";

    return $mask[$ContinentCode][$CountryCode];
}

function get_rand($proArr) {
    $result = '';
    $proSum = 100;
    foreach ($proArr as $key => $proCur) {
        $randNum = mt_rand(1, $proSum);
        if ($randNum <= $proCur) {
            $result = $key;
            break;
        } else {
            $proSum -= $proCur;
        }
    }
    return $result;
}

//获取已分组的消息，并根据概率随机返回其中1条
function getGroupMsg($db, $continentCode, $mask, $appid, $appver){
    $now = gmdate('Y-m-d H:i:s');

    $map = " `status`=1 AND '{$now}' > `start` AND '{$now}' < `end` AND (`region` = 1 or (`{$continentCode}` & {$mask} = {$mask}) )  AND `appid` = '{$appid}' ";
    $map_appver = '  AND appver_min = 0  AND  appver_max = 0 ';
    if ($appver > 0) {
        $map_appver = "  AND appver_min <= '{$appver}' AND ('{$appver}' <= appver_max or appver_max = 0 ) ";
    }
    $map .= $map_appver;

    $sub_sql = "(select * from message_group where {$map} LIMIT 1) AS g";

    $field = 'g.id as gid, m.id, r.prob, m.title, m.description, m.link, m.call_to_action, m.icon, m.image, g.notification, g.popup, m.language, g.time, g.start, g.end';
    $sql = "SELECT {$field} FROM {$sub_sql}, message_group_relation r, messages m WHERE g.id=r.msg_group_id AND m.id=r.msg_id AND m.status=2 ";

    $data = $db->fetchAll($sql);
    if (empty($data)) {
        return null;
    }

    $proArr = array_column($data, 'prob', 'id');

    if (array_sum($proArr) != 100) {
        return null;
    }

    $msg_id = get_rand($proArr);
    $res_data =  array_values(array_filter($data, function($v) use ($msg_id) { return $v['id'] == $msg_id; }));

    return $res_data;
}

//获取未分组的消息
function getMsg($db, $continentCode, $mask, $appid, $appver){
    $now = gmdate('Y-m-d H:i:s');

    $select = 'SELECT `id`,`title`,`description`,`link`,`call_to_action`,`icon`,`image`,`notification`,`popup`,`language`, `time`,`start`,`end` FROM messages ';
    $where  = "WHERE `status` = 1  AND '$now'>`start` AND '$now'<`end` AND (`region` = 1 or (`$continentCode` & $mask = $mask) )  AND `appid`='$appid'";

    if ($appver > 0) {
        $where .= "  AND `appver_min` <= '$appver' AND ('$appver' <= `appver_max` or `appver_max` = 0 )";
    } else {
        $where .= '  AND `appver_min` = 0  AND  `appver_max` = 0';
    }

    $sql = $select . $where . ' LIMIT 1';

    return $db->fetchAll($sql);
}


function getMessages($db, $continentCode, $countryCode, $appid, $appver, $g_lang)
{
    $mask = Country::getMaskOfCountry($countryCode);
    $is_group_msg = true;
    $messages = getGroupMsg($db, $continentCode, $mask, $appid, $appver);
    if (empty($messages)) {
        $is_group_msg = false;
        $messages = getMsg($db, $continentCode, $mask, $appid, $appver);
    }

    if (empty($messages)) {
        return null;
    }

    $mid_list    = getAllMids($messages);

    if (empty($mid_list)) {
        return $messages;
    }

    global $APPID;
    global $KEY_LIST;
    $translation = getTranslation($APPID, $KEY_LIST, $mid_list, $g_lang);

    if (empty($translation)) {
        return $messages;

    }

    $messages = mergeTranslationToMessages($translation, $messages, $g_lang);

    if ($is_group_msg){
        $messages[0]['id'] = intval($messages[0]['gid']) * 10000;
    }
    return $messages;
}

function getTranslation($APPID, $key_list, $mid_list, $lang_list)
{
    // echo "lang_list=".json_encode($lang_list)."\n";
    // echo "mid_list=".json_encode($mid_list)."\n";
    // echo "key_list=".json_encode($key_list)."\n";
    // echo "APPID=".json_encode($APPID)."\n";
    $field_list = ['MESSAGE_TITLTE', 'MESSAGE_DESCRIPTION', 'MESSAGE_CALL_TO_ACTION'];
    if (!is_array($mid_list)) {
        $mid_list = [$mid_list];
    }
    if (!is_array($lang_list)) {
        $lang_list = [$lang_list];
    }
    $translator = new Translator($APPID, $key_list);
    if ($translator == false) {
        return false;
        // echo "translator is null\n";
    }
    $translation = $translator->getBatchString($field_list, $mid_list, $lang_list);
    // echo "translation=".json_encode($translation)."\n";
    return $translation;

}
function getAllLanguages($messages)
{
    $field_list = ['title', 'description','call_to_action'];
    $lang_list  = [];
    #获取所有的语言
    foreach ($messages as $msg) {
        $lg = $msg['language'];
        if (!empty($lg)) {
            $lg = explode(',', $lg);
            foreach ($lg as $l) {
                $lang_list[] = $l;
            }
        }
    }
}

function getAllMids($messages)
{
    $mid_list = [];
    foreach ($messages as $msg) {
        $mid_list[] = $msg['id'];
    }

    return $mid_list;
}

function mergeTranslationToMessages($translation, $messages, $lang)
{
    // $lang_list  = getAllLanguages($messages);
    $field_list       = ['title', 'description', 'call_to_action'];
    $trans_field_name = [
        'title'       => 'MESSAGE_TITLTE',
        'description' => 'MESSAGE_DESCRIPTION',
        'call_to_action' => 'MESSAGE_CALL_TO_ACTION',
    ];
    $arr = [];
    foreach ($messages as $msg) {
        $mid = $msg['id'];
        $msg_lang = $msg['language'];
        if ( strpos($msg_lang, $lang) !== false ) {
            foreach ($field_list as $field) {
                $trans_field = $trans_field_name[$field];
                if (!in_array($trans_field, array_keys($translation))) {continue;}
                if (!in_array($mid, array_keys($translation[$trans_field]))) {continue;}
                if (!in_array($lang, array_keys($translation[$trans_field][$mid]))) {continue;}
                $trans = $translation[$trans_field][$mid][$lang];
                if (!empty($trans)) {
                    $msg[$field] = $trans;
                }
            }
            $msg['language'] = $lang;
        } else {
            $msg['language'] = 'en_US';
        }
        $arr[] = $msg;
    }
    #var_dump($messages);

    return $arr;
}

function getAppConfigure($db, $appid)
{
    $sql   = 'SELECT `configure` FROM appconfigs ';
    $where = "WHERE `appid`='$appid'; ";
    $sql   = $sql . $where;
    #echo $sql;
    #echo "\n";

    $config = '';
    try {
        $rs = $db->fetchAll($sql);
        foreach ($rs as $r) {
            $conf = $r['configure'];
            $config = $conf ;
        }
    } catch (Exception $e) {
    }

    return $config;
}

function showErrorMsg($errno, $msg)
{
    $arr = [
        'status' => 'error',
        'errno'  => $errno,
        'error'  => $msg,
    ];
    header('Content-Type: application/json');
    echo json_encode($arr, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
}
