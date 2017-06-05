<?php
/**
 * fact API
 * @date : 2016/11/26
 * @author  : Tiger <DropFan@Gmail.com>
 *
 * @params :
 *          int page     page number
 *          int size     size of perpage
 *          int asc      order by widget asc or desc 1=asc 0=desc
 *
 */

require '/var/www/lib/translator/translator.php';
require 'inc/common.inc.php';

$translation_config = require __DIR__ . '/../config/translator.php';
$LANGUAGE  = require __DIR__.'/../config/language.php';
$APPID    = $translation_config['ARTICLE']['APPID'];
$KEY_LIST = $translation_config['ARTICLE']['KEY_LIST'];




$g_lang =  isset($lang) ? $lang : 'en_US';

#check lang
$languageList          = array_keys($LANGUAGE['CODE_TO_NAME']);
$languageShortNameList = array_keys($LANGUAGE['SHORT_TO_LONG']);
if (!in_array($g_lang, $languageList)) {
    if (in_array($g_lang, $languageShortNameList)) {
        $g_lang = $LANGUAGE['SHORT_TO_LONG'][$g_lang];
    } else {
        $g_lang = 'en_US';
    }
}



$debugMsg = [];

if ($debug) {
    defined('DEBUG') || define('DEBUG', true);
}

if (strtolower($link) !== 'list') {
    returnHttpError('Bad Request!', 400);
}

if (isset($size) && is_numeric($size)) {
    $size = intval($size);
} else {
    $size = 10;
}
$size < 1 && $size = 10;

if (isset($page) && is_numeric($page)) {
    $page = intval($page);
} else {
    $page = 1;
}
$page < 1 && $page = 1;

$offset = ($page - 1) * $size;

if (isset($asc) && !empty($asc)) {
    $sort = intval($asc) == 1 ? 'ASC' : 'DESC';
} else {
    $sort = 'DESC';
}

try {
    $db = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
} catch (Exception $e) {
    $debugMsg[] = $e;
}

$select = "SELECT `id`, `language`, `title`, `content`, `author`, `author_link` as link, `image` FROM `articles` ";
$where  = "WHERE `status` =  1 AND `cid` = '999' ";
$order  = "ORDER BY `weight` {$sort} ";
$limit  = "LIMIT {$offset},{$size} ";

$sql = $select . $where . $order . $limit;

$debugMsg[] = $sql;

$rows = [];

if ($result = $db->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $status = 'ok';
} else {
    $status = 'error';
    $debugMsg[] = $db->error;
}

$list_count = count($rows);


$id_list = [] ;
foreach ($rows as $key => $value) {
    $id_list[] =  $value['id'];
}

$translation = getTranslation($APPID, $KEY_LIST, $id_list, $g_lang);

if (!empty($translation)) {
    $rows =  mergeTranslation($translation,$rows,$g_lang);
}


$resp = [
    'status' => $status,
    'count'  => $list_count,
    'data'   => $rows,
];

returnhttp($resp);



function getTranslation($APPID, $key_list, $mid_list, $lang_list)
{
    // echo "lang_list=".json_encode($lang_list)."\n";
    // echo "mid_list=".json_encode($mid_list)."\n";
    // echo "key_list=".json_encode($key_list)."\n";
    // echo "APPID=".json_encode($APPID)."\n";
    $field_list = [
            'ARTICLE_TITLTE',
            'ARTICLE_CONTENT',
            'ARTICLE_AUTHOR',
            'ARTICLE_EDITOR',
        ];

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

function mergeTranslation($translation, $articles, $lang)
{
    $field_list       = ['title', 'content', 'author', 'editor'];
    $trans_field_name = [
        'title'       => 'ARTICLE_TITLTE',
        'content'     => 'ARTICLE_CONTENT',
        'author'      => 'ARTICLE_AUTHOR',
        'editor'      => 'ARTICLE_EDITOR',
    ];

    $res = [];
    foreach ($articles as $article) {
        $aid = $article['id'];
        $support_language =  $article['language'];
        // check lang is supported.
        if (strpos($support_language, $lang) === false) {
            $res[] = $article;
            continue;
        }
        foreach ($field_list as $field) {
            $trans_field = $trans_field_name[$field];
            if (!in_array($trans_field, array_keys($translation))) {continue;}
            if (!in_array($aid, array_keys($translation[$trans_field]))) {continue;}
            if (!in_array($lang, array_keys($translation[$trans_field][$aid]))) {continue;}
            $trans = $translation[$trans_field][$aid][$lang];
            if (!empty($trans)) {
                $article[$field] = $trans;
            }
        }
        $res[] = $article;
    }

    return $res;
}