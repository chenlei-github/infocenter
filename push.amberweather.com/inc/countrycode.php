<?php
/**
 *
 * @date        : 2015/07/23
 * @description : fetch country code.
 * @author      : Tiger (DropFan@Gmail.com)
 */

include_once 'geoip.php';
include_once 'common.inc.php';

/*ip*/
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

/*sim*/
if (isset($sim) && !empty($sim)) {
    $opcode = $sim;
} else {
    $opcode = null;
    $sim    = null;
}

function getCountryCode($db, $lang, $sim, $ip)
{
    global $use_tcache;
    global $tc;
    global $msg;

    if (isset($sim) && !empty($sim)) {
        if ($use_tcache) {
            $result = $tc->get('sim_' . $sim);
            if ($result) {
                $msg .= "sim cache hit code : $result \n";

                return $result;
            }
        }
        //get country from simcode
        $sql    = "select `countrycode` from `operators` where `netcode`=$sim";
        $result = $db->fetch_first($sql);
        if ($use_tcache) {
            $tc->set('sim_' . $sim, $result['countrycode']);
            $msg .= " set sim_$sim :{$result['countrycode']} \n";
        }
        $msg .= " {$result['countrycode']} \n";

        return $result['countrycode'];
    } else {
        //get countrycode from ip?
        $c = getCountryCodeByIP($ip);
        if (isset($c) && !empty($c)) {
            $msg .= "Country Code By IP : $c \n";

            return $c;
        }

        //get country code from lang.
        if (strpos($lang, '_') !== false) {
            //right lang string.
            list($l, $c) = explode('_', $lang);
            if (isset($c) && !empty($c)) {
                $msg .= "country code By lang : $c \n";

                return $c;
            }
        }

        return false;
    } //end else
}

function getCountryIDbyCode($db, $code)
{
    global $use_tcache;
    global $tc;
    global $msg;

    if ($use_tcache) {
        $result = $tc->get('countrycode_' . $code);
        if ($result) {
            $msg .= "getCountryIDbyCode cache hit countrycode_$code : $result\n";

            return $result;
        }
    }
    $sql    = "select `id` from `countries` where `countrycode`='$code'";
    $result = $db->fetch_first($sql);
    if ($use_tcache) {
        $tc->set('countrycode_' . $code, $result['id']);
        $msg .= " set countrycode_$code :{$result['id']} \n";
    }
    $msg .= "getCountryIDbyCode : countrycode_$code : {$result['id']}\n";

    return $result['id'];
}

function getCountryCodeByIP($ip)
{
    $ret;
    $gi  = geoip_open('/usr/share/GeoIP/GeoIP.dat', GEOIP_STANDARD);
    $ret = geoip_country_code_by_addr($gi, $ip);
    geoip_close($gi);

    return $ret;
}
