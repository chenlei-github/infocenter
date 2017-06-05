<?php

function trim_recursive($str, $character_mask = " \t\n\r\0\x0B", $max_depth = 0)
{
    $trimmed = trim($str, $character_mask);
    if ($trimmed == trim($str, $character_mask)) {
        debug("trim [$str] by [$character_mask] trimmed:[$trimmed]");

        return $trimmed;
    } else {
        return trim_recursive($str, $character_mask);
    }
}

function rtrim_recursive($str, $character_mask = " \t\n\r\0\x0B")
{
    $trimmed = rtrim($str, $character_mask);
    if ($trimmed == rtrim($str, $character_mask)) {
        debug("rtrim [$str] by [$character_mask] trimmed:[$trimmed]");

        return $trimmed;
    } else {
        return rtrim_recursive($str, $character_mask);
    }
}

function ltrim_recursive($str, $character_mask = " \t\n\r\0\x0B")
{
    $trimmed = ltrim($str, $character_mask);
    if ($trimmed == ltrim($str, $character_mask)) {
        debug("ltrim [$str] by [$character_mask] trimmed:[$trimmed]");

        return $trimmed;
    } else {
        return ltrim_recursive($str, $character_mask);
    }
}

function daddslashes($string, $force = 0)
{
    !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
    // if(!defined('MAGIC_QUOTES_GPC'))
    // define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
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
