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


$play_account = $data['play_account'];
$credentials = $auth['google_play_account'][$play_account]['credentials'];
$ap = new AndroidPublisher($credentials);

$message_list = update_text($ap, $data);

$res = json_encode($message_list,1);

$f = fopen($outputfile, 'w');
fwrite($f, $res);
fclose($f);

echo 'Write Messages Done!' . PHP_EOL;
echo 'Done!' . PHP_EOL;
echo date('Y-m-d H:i:s');
echo PHP_EOL;
die;


function update_text($ap, $data)
{
    $message_list = [];
    $field_list = ['title', 'shortDescription', 'fullDescription'];

    foreach ($data['package_names'] as $package) {
        $edits_lists = get_text($ap, $package);
        if (!$edits_lists) {
            $message_list[] = [
                'package' => $package,
                'lang' => '',
                'status' => 'error',
                'message' => 'GET list FAIL!' . $ap->getLastError(),
            ];
            ez_dump(end($message_list));
            continue;
        }
        foreach ($data['res'] as $lang => $v) {
            $resource = [
                'packageName' => $package,
                'language' => $lang,
                'video' => '',
            ];
            if (!array_key_exists($lang, $edits_lists)) {
                $bad_value = false;
                $empty_field = [];
                foreach ($field_list as $key) {
                    if (empty($v[$key])) {
                        $bad_value = true;
                        $empty_field[] = $key;
                    }
                }
                if ($bad_value) {
                    $message_list[] = [
                        'package' => $package,
                        'lang' => $lang,
                        'status' => 'warning',
                        'message' => 'Please Fill ' . implode(',', $empty_field) . '!',
                    ];
                    ez_dump(end($message_list));
                    continue;
                }
            } else {
                $old_resource = $edits_lists[$lang];
                $resource['title'] = $old_resource['title'];
                $resource['shortDescription'] = $old_resource['shortDescription'];
                $resource['fullDescription'] = $old_resource['fullDescription'];
                $resource['video'] = $old_resource['video'];
            }

            foreach ($field_list as $k) {
                if (!empty($v[$k])) {
                    $resource[$k] = $v[$k];
                }
            }

            if (!$ap->updateEditsListing($resource)) {
                $message_list[] = [
                    'package' => $package,
                    'lang'    => $lang,
                    'status'  => 'error',
                    'message' => $ap->getLastError(),
                ];
                ez_dump(end($message_list));
                if ($ap->packageError) {
                    break;
                }
            } else {
                $message_list[] = [
                    'package' => $package,
                    'lang' => $lang,
                    'status' => 'success',
                    'message' => '',
                ];
                ez_dump(end($message_list));
            }
        }
    }

    return $message_list;
}



function get_text($ap, $package)
{
    $results = [];
    $res = $ap->getEditsListing($package);    
    if ($res) {
        $listings = $res->getListings();
        foreach ($listings as $listing) {
            $lang = $listing['language'];
            $results[$lang] = [];
            $results[$lang]['language'] = $listing['language'];
            $results[$lang]['title'] = $listing['title'];
            $results[$lang]['shortDescription'] = $listing['shortDescription'];
            $results[$lang]['fullDescription'] = $listing['fullDescription'];
            $results[$lang]['video'] = $listing['video'];
        }
    }
    return $results;
}


function ez_dump($msg)
{
    echo $msg['package'] . ' | ' . $msg['lang'] . ' : ' . $msg['status'] . ' :' . $msg['message'];
    echo PHP_EOL;
}


