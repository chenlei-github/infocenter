<?php
require_once __DIR__ . '/../../inc/class/AsyncTask.php';
require_once __DIR__ . '/../../inc/Models/Opdata/AndroidPublisher.php';
$config = require __DIR__ . '/config.php';
$auth = require __DIR__ . '/../../config.php';

$inputfile = $config['inputfile'];
$outputfile = $config['outputfile'];




# debug
// $package = 'mobi.infolife.ezweather.widget.journey';
// $play_account = 'Weather Widget Theme Dev Team';
// $credentials = $auth['google_play_account'][$play_account]['credentials'];
// $ap = new AndroidPublisher($credentials);
// echo json_encode(get_text($ap,$package));
// die;

if (!file_exists($inputfile)) {
    echo "Input file $inputfile not exists!\n";
    exit(1);
}

$data = json_decode(file_get_contents($inputfile),1);

if (!$data) {
    echo "Empty input json!";
    exit(2);
}

// var_dump($data);
// die;

$param_list = ['play_account', 'package_names','languages', 'image_type', 'images', 'keep_first_image'];
foreach ($param_list as $param) {
    if (!in_array($param, array_keys($data))) {
        echo "miss `$param`.";
        exit(3);
    }
}

$play_account = $data['play_account'];
$credentials = $auth['google_play_account'][$play_account]['credentials'];
$ap = new AndroidPublisher($credentials);

$message_list = update_image($ap, $data);

$res = json_encode($message_list,1);

$f = fopen($outputfile, 'w');
fwrite($f, $res);
fclose($f);

echo 'Write Messages Done!' . PHP_EOL;
echo 'Done!' . PHP_EOL;
echo date('Y-m-d H:i:s');
echo PHP_EOL;
die;


function update_image($ap, $data)
{
    $message_list = [];

    $package_names = $data['package_names'];
    $languages = $data['languages'];
    $image_type = $data['image_type'];
    $images_raw = $data['images'];
    $keep_first_image = $data['keep_first_image'];

    if (!is_array($package_names)) {
        $package_names = [$package_names];
    }
    if (!is_array($languages)) {
        $languages = [$languages];
    }
    if (!is_array($images_raw)) {
        $images_raw = [$images_raw];
    }

    $package_count = count($package_names);
    $lang_count = count($languages);
    $progress_total = $package_count * $lang_count;

    foreach ($package_names as $i => $package) {
        foreach ($languages as $j => $lang) {
            $model = [
                'packageName' => $package,
                'language'  => $lang,
                'imageType' => $image_type,
                'images'  => $images_raw,
                'keep_first_image' => $keep_first_image,
            ];
            $stat = $ap->updateEditsImages($model);
            $msg = [
                    'package' => $package,
                    'lang' => $lang,
                    'status' => 'success',
                    'message' => '',
            ];
            if (!$stat) {
                if ($ap->packageError) {
                    $msg['status'] = 'warning';
                    $msg['message'] = 'Not Found Package!';
                } else {
                    $msg['status'] = 'error';
                    $msg['message'] = $ap->getLastError();
                }
            }
            $message_list[] = $msg;
            $progress_current = $lang_count * $i + $j + 1;
            ez_dump(end($message_list), $progress_current, $progress_total);
            echo join(',', $ap->errors);
        }
    }

    return $message_list;
}



function ez_dump($msg, $current=0, $total=0)
{
    echo "[$current / $total] " . $msg['package'] . ' | ' . $msg['lang'] . ' : '
            . $msg['status'] . ' :' . $msg['message'];
    echo PHP_EOL;
}


