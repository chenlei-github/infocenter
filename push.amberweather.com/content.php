<?php
require 'inc/common.inc.php';

#hash
#type

$g_hash = isset($hash) ? $hash : '';
$g_type = isset($type) ? $type : '';

$sql   = 'select title,content,update_time from content ';
$where = '';

if (!empty($g_hash)) {
    $where = "where   `hash`=$g_hash  ;";
} else {
    showNotFoundPage();
    exit(1);
}
$row = null;
$row = $db->fetchOne($sql);
if (empty($row)) {
    showNotFoundPage();
    exit(2);
}

$g_title       = $row['title'];
$g_content     = $row['content'];
$g_update_time = $row['update_time'];

$resp = <<<HTMLEOF
<!DOCTYPE html>
<html>
    <head>
        <title>
            $g_title
        </title>
        <meta charset="utf-8" content="width=device-width, initial-scale=1.0" name="viewport">
            <link href="https://cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" rel="stylesheet">
            </link>
            <link href="https://cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
            </link>
        </meta>
    </head>
    <body>
        <div class="container">
            <h1 class="text-center text-primary">
                $g_title
            </h1>
            <p class="text-center">
                <span class="text-info" id="update_time">
                    $g_update_time
                </span>
                <span  class="text-info">
                    By Amber Weather
                </span>
            </p>
            <div class="container">
                $g_content
            </div>
        </div>
    </body>
</html>
HTMLEOF;

header('Content-Type: text/html;charset=UTF-8');
echo $resp;

function showNotFoundPage()
{
    $resp = <<<NotFoundPageHTMLEOF
<!DOCTYPE html><html lang="en"><meta charset="utf-8"><meta content="initial-scale=1, minimum-scale=1, width=device-width" name="viewport"><title>Error 404 (Not Found)!!</title></meta><center style="background:#4da6ff;display: inline-block;position: fixed;top: 0;bottom: 0;left: 0;right: 0;width: auto;height:auto;margin: auto;"><div style="background:#4da6ff;display: inline-block;position: fixed;top: 0;bottom: 0;left: 0;right: 0;width: auto;height:10em;margin: auto;"><em style="font: bold 3.0em Bitter,Georgia,Geneva,serif;font-size: 3.0em;color:white">404</em><br/><h1 style="font: bold 2.0em Arial,Helvetica,sans-serif;font-size:1.5em;color:white">Page Not Found</h1><p><a href="http://www.amberweather.com" style="font: bold 1.2em Arial,Helvetica,sans-serif;;font-size: 1.0em;color:#b3ffb3">You could head straight to our Home Page</a></p></div></center></meta></html>
NotFoundPageHTMLEOF;
    http_response_code(404);
    header('Content-Type: text/html;charset=utf-8');
    echo $resp;
}
