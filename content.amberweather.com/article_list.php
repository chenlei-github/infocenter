<?php

require 'inc/common.inc.php';
$article_config = require __DIR__ . '/../config/article.php';


# Specail CID ,999=Fact

define('CID_FACT', '999');

$g_page   = isset($page) ? intval($page) : 1;
$g_psize  = isset($psize) ? intval($psize) : 10;
$g_cid    = isset($cid) ? intval($cid) : -1;
$g_asc    = isset($asc) ? intval($asc) : 0;
$cid_list = array_keys($article_config['category']);

# 下面拼接sql语句


$sql = 'SELECT `id` ,`title`,`link`,`image`,`cid` FROM `articles`';


if (CID_FACT == $g_cid) {
    $sql = 'SELECT `id`, `title`, `author`, `author_link` as link, `image`, `cid` FROM `articles` ';
}


$where = ' WHERE `status` =  1 ';

if (in_array($g_cid, $cid_list)) {
    $where .= "  AND  `cid` = $g_cid ";
} elseif ($g_cid == -1) {
    $where .= ' AND   `cid` != ' . CID_FACT . ' ';
} else {
    showNotFoundPage();
}
if ($g_page < 1) {
    $g_page = 1;
}

$oders  = '  ORDER BY `created_at` DESC  ';

if (CID_FACT == $g_cid) {
    if ($g_asc == 1) {
        $oders = '  ORDER BY `weight` ASC   ';
    } else {
        $oders = '  ORDER BY `weight` DESC  ';
    }
}


$offset = ($g_page - 1) * $g_psize;
$limit  = " limit $offset,$g_psize  ;  ";

// echo "sql=" . $sql . $where . $oders . $limit;
$rows = null;
$rows = $db->fetchAll($sql . $where . $oders . $limit);
if (empty($rows)) {
    showNotFoundPage();
}

$list_count = count($rows);

$LINK_BASE = $article_config['url_prefix'];

if (CID_FACT != $g_cid) {
    for ($i = 0; $i < $list_count; $i++) {
        $rows[$i]['link'] = $LINK_BASE . $rows[$i]['link'];
    }
}


$resp = [
    'status'   => 'ok',
    'count'    => $list_count,
    'articles' => $rows,
];

if (isset($configs['DEBUG']) && $configs['DEBUG']) {
    $resp['sql'] = $sql . $where . $oders . $limit;
}

header('Content-Type: application/json');
echo json_encode($resp);

function showNotFoundPage()
{
    $resp = ['status' => 'none'];
    header('Content-Type: application/json');
    echo json_encode($resp, JSON_PRETTY_PRINT);
    exit(1);
}
