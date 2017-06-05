<?php
function daddslashes($string, $force = 0)
{
    !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

    if (!MAGIC_QUOTES_GPC || $force) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = daddslashes($val, $force);
            }
        } else {
            $string = addslashes($string);
        }
    }

    return $string;
}

function dhtmlspecialchars($string)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = dhtmlspecialchars($val);
        }
    } else {
        $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1',
            //$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
            str_replace(['&', '"', '<', '>'], ['&amp;', '&quot;', '&lt;', '&gt;'], $string));
    }

    return $string;
}

function http_get($url, $ref_url = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.81 Safari/537.36');
    if ($ref_url) {
        curl_setopt($ch, CURLOPT_REFERER, $ref_url);
    }
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $data = curl_exec($ch);
    curl_close();

    return $data;
}

function returnHttp($data = [], $status = 'ok', $err = [])
{
    global $debugMsg;

    $res = [
        'status' => $status,
        // 'data' => $data,
    ];

    if (is_array($err) && !empty($err)) {
        $err['code'] > 0 && $res['errno']     = $err['code'];
        !empty($err['info']) && $res['error'] = $err['info'];
    }

    /*
    if (!$data) {
        unset($res['data']);
    }*/

    if (is_array($data) && $data) {
        foreach ($data as $key => $value) {
            if (defined('DEBUG') && DEBUG) {
                $res[$key] = $value;
            } else {
                $key{0} != '_' && $res[$key] = $value;
            }
        }
    } elseif ($data) {
        $res['response'] = $data;
    }

    if (defined('DEBUG') && DEBUG && !empty($debugMsg)) {
        if (is_string($debugMsg)) {
            $res['dbgmsg'] = explode("\n", $debugMsg);
        } else {
            $res['dbgmsg'] = $debugMsg;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($res, JSON_NUMERIC_CHECK);
    die;
}

function returnHttpError($info, $code = 0)
{
    $code < 1000 && $code > 100 && http_response_code($code);
    returnHttp([], $status = 'error', $err = ['info' => $info, 'code' => $code]);
}
