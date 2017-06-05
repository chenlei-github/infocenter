<?php
require 'inc/common.inc.php';

$g_link = isset($link) ? $link : '';

$sql   = 'SELECT * FROM `articles`';
$where = '';

if (!empty($g_link)) {
    $where = " WHERE  `link` = '$g_link';";
} else {
    showNotFoundPage();
    exit(1);
}
$row = null;
$row = $db->fetchOne($sql . $where);
if (empty($row)) {
    showNotFoundPage();
    exit(2);
}
$g_title       = isset($row['title']) ? $row['title'] : '';
$g_content     = isset($row['content']) ? $row['content'] : '';
$g_editor      = isset($row['editor']) ? $row['editor'] : '';
$g_author      = isset($row['author']) ? $row['author'] : '';
$g_author_link = isset($row['author_link']) ? $row['author_link'] : '';
$g_image       = isset($row['image']) ? $row['image'] : '';
$g_update_time = isset($row['updated_at']) ? $row['updated_at'] : '';
if (strlen($g_update_time) >= 10) {$g_update_time = substr($g_update_time, 0, 10);}

$resp = <<<HTMLEOF
<!DOCTYPE html>
<html>
    <head>
        <title>
            $g_title
        </title>
        <meta charset="utf-8" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
        </meta>
        <style>
            .app_store_badge {
                width:200px;
                height:100px;
                display: block;
                margin: auto;
            }
            .cover_img {
                width: 100%;
                height: auto;
            }
            .author {
                font-size: 11pt;
                font-weight: normal;
                font-family: arial, helvetica, sans-serif;
            }
            img {
                max-width: 100%;
                max-height: 100%;
                height: 100%;
            }
            h1 {
                font-size: 20pt;
                font-weight: bold;
                font-family: arial, helvetica, sans-serif;
                word-wrap: break-word;
                word-break: normal;
            }
            h2 {
                font-size: 14pt;
                font-weight: bold;
                font-family: arial, helvetica, sans-serif;
                word-wrap: break-word;
                word-break: normal;
            }
            body{
                font-size: 12pt;
                font-weight: normal;
                font-family: arial, helvetica, sans-serif;
                word-wrap: break-word;
                word-break: normal;
                line-height: 140%;
            }

        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <img src="$g_image" class="cover_img"></img>
            </div>
            <div class="row">
                <h1 class="col-sm-12 text-left">$g_title</h4>
            </div>
            <div class="row author">
                <div class="col-sm-12">
                <span class="text-left text-default ">
                <a href="$g_author_link" >$g_author&nbsp;&nbsp;</a>
                $g_editor&nbsp;&nbsp;$g_update_time
                </span>
                </div>
            </div>
            <hr>
            $g_content
        </div>
    </body>
</html>
HTMLEOF;

header('Content-Type: text/html;charset=UTF-8');
echo $resp;

function showNotFoundPage()
{
    $resp = <<<NotFoundPageHTMLEOF
<!DOCTYPE html><html lang="en"><meta charset="utf-8"><meta content="initial-scale=1, minimum-scale=1, width=device-width" name="viewport"><title>Page Not Found.</title>
<body>
<center style="background:#4da6ff;display: inline-block;position: fixed;top: 0;bottom: 0;left: 0;right: 0;width: auto;height:auto;margin: auto;">
<div style="background:#4da6ff;display: inline-block;position: fixed;top: 0;bottom: 0;left: 0;right: 0;width: auto;height:10em;margin: auto;">
    <p style="font: bold 2.0em Arial,Helvetica,sans-serif;font-size:1.5em;color:white">Oops!<br> You're caught in the rain. <br>Please go back.</p>
    <p style="font: bold 2.0em Arial,Helvetica,sans-serif;font-size:1.2em;color:white">Page not found.</p>
</div>
</center>
</body></html>
NotFoundPageHTMLEOF;
    http_response_code(404);
    header('Content-Type: text/html;charset=utf-8');
    echo $resp;
}
