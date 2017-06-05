<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

#
#
# Key=APPID_PREIFX_ID_LANG
# Value=String
#
#
#
#

class Translator
{
    private $APPID;
    private $key_list;
    private $db;
    private $tableName = 'Translator2';
    private $FS        = '';

    public function __construct($APPID, $key_list)
    {
        // echo "__construct;key_list=" . $key_list."\n";
        $this->key_list = $key_list;
        $this->APPID    = $APPID;
        $this->dbOpen();
        // echo 'this->key_list=' . $this->key_list[0] . "\n";
    }

    public function delString($key_name, $id, $lang)
    {
        if (!is_string($key_name) || empty($key_name)) {
            // echo "$key_name  is not string\n";

            return false;
        }
        if (empty($id)) {
            // echo "$id  is not number\n";

            return false;
        }
        if (!is_string($lang) || empty($lang)) {
            // echo "$lang  is not empty\n";

            return false;
        }
        $inner_keys = $this->makeKey($key_name, $id, $lang);

        return $this->dbDelKey($inner_keys[0]);
    }

    public function getString($key_name, $id, $lang)
    {
        if (!is_string($key_name) || empty($key_name)) {
            // echo "$key_name  is not string\n";

            return false;
        }
        if (empty($id)) {
            // echo "$id  is not number\n";

            return false;
        }
        if (!is_string($lang) || empty($lang)) {
            // echo "$lang  is not empty\n";

            return false;
        }

        $inner_keys = $this->makeKey($key_name, $id, $lang);

        return $this->dbGetKey($inner_keys[0]);
    }

    public function getBatchString($key_name, $id, $lang)
    {
        $inner_keys = $this->makeKey($key_name, $id, $lang);
        if ($inner_keys == false) {
            return null;
        }

        $tmp_result = $this->dbGetBatchKey($inner_keys);
        // echo "\ntmp_result=".json_encode($tmp_result);

        return $this->makeResult($key_name, $id, $lang, $tmp_result);
    }

    public function setString($key_name, $id = 0, $lang = 'en', $translation)
    {

        // echo 'setString:this->key_list=' . $this->key_list;
        // echo "\n";
        if (!$this->checkKeyName($key_name)) {
            return false;
        }
        $inner_key = $this->makeKey($key_name, $id, $lang)[0];
        $stat=$this->dbPutKey($inner_key, $translation);
        return  $stat;
    }

    private function makeResult($key_name, $id, $lang, $result_list)
    {

        $key_list = $key_name;
        if (!is_array($key_list)) {
            $key_list = [$key_list];
        }
        $id_list = $id;
        if (!is_array($id_list)) {
            $id_list = [$id_list];
        }
        $lang_list = $lang;
        if (!is_array($lang_list)) {
            $lang_list = [$lang_list];
        }
        $result = [];
        $result_keys=array_keys($result_list);
        foreach ($key_list as $key) {
            $result[$key] = [];
            foreach ($id_list as $id) {
                $result[$key][$id] = [];
                foreach ($lang_list as $lang) {
                    $k                      = $this->APPID . '_' . $key . '_' . $id . '_' . $lang;
                    if(in_array($k,$result_keys)){
                        $result[$key][$id][$lang] = $result_list[$k];
                    }
                }
            }
        }
        // var_dump($result);
        // echo json_encode($result);
        return $result;


    }

    private function makeKey($key_name, $id, $lang)
    {
        if (!$this->checkKeyName($key_name)) {
            return false;
        }

        $key_list = $key_name;
        if (!is_array($key_list)) {
            $key_list = [$key_list];
        }
        $id_list = $id;
        if (!is_array($id_list)) {
            $id_list = [$id_list];
        }
        $lang_list = $lang;
        if (!is_array($lang_list)) {
            $lang_list = [$lang_list];
        }
        $key_list_ret = [];
        foreach ($key_list as $key) {
            foreach ($id_list as $id) {
                foreach ($lang_list as $lang) {
                    $key_list_ret[] = $this->APPID . '_' . $key . '_' . $id . '_' . $lang;
                }
            }
        }

        return $key_list_ret;
    }

    private function checkKeyName($key_name_list)
    {
        if (is_array($key_name_list)) {
            foreach ($key_name_list as $key => $key_name) {
                if (!in_array($key_name, $this->key_list)) {
                    return false;
                }
            }

            return true;
        }
        // echo "key_name_list=$key_name_list\n";
        // echo 'this->key_list=' . $this->key_list;
        // echo "\n";
# else
        if (in_array($key_name_list, $this->key_list)) {
            return true;
        } else {
            return false;
        }
    }

    private function dbOpen()
    {
        global $aws_config;
        $is_success = false;
        $sdk        = new Aws\Sdk($aws_config);
        try {
            $this->db   = $sdk->createDynamoDb();
            $is_success = true;
        } catch (Exception $e) {
            // echo "Translator Open Database connection Error\n";
        }

        return $is_success;
    }

    private function dbPutKey($key, $val)
    {
        // echo 'dbPutKey:key=' . $key . "\n";
        $success=false;
        try {
            $response = $this->db->putItem([
                'TableName' => $this->tableName,
                'Item'      => [
                    'KeyID' => ['S' => $key],
                    'Value' => ['S' => $val],
                ],
            ]);
            $success=true;
        } catch (Exception $e) {
            // echo "\nTranslator Put Item Error\n";
            $success=false;
        }
        return $success;
        // var_dump($response);
        // echo 'putKey:result:=' . json_encode($response);
    }

    private function dbGetKey($key)
    {
        $response = '';
        try {
            $response = $this->db->getItem([
                'TableName' => $this->tableName,
                'Key'       => [
                    'KeyID' => ['S' => $key],
                ],
            ]);
        } catch (Exception $e) {
            // echo "\nTranslator Put Item Error\n";
        }
        // echo 'dbGetKey:' . json_encode($response['Item']['Value']['S']) . "\n";
        // var_dump($response);

        return $response['Item']['Value']['S'];
    }

    private function dbDelKey($key)
    {
        $result = false;
        try{
            $response = $this->db->deleteItem([
                'TableName' => $this->tableName,
                'Key'       => [
                    'KeyID' => ['S' => $key],
                ],
            ]);
            $result =  $response['@metadata']['statusCode'] == 200;
        } catch (Exception $e) {
            $result = false;
        }
        // var_dump($response);
        // echo 'dbDelKey:' . json_encode($response) . "\n";
        return $result;
    }


    private function dbGetBatchKey($key_list)
    {
        $is_success = false;
        $is_error   = false;
        $keys       = [];
        foreach ($key_list as $key) {
            $keys[] = ['KeyID' => ['S' => $key]];
        }
        $sql = [
            'RequestItems' => [
                $this->tableName => [
                    'Keys' => $keys,
                ],
            ],
        ];
        // var_dump($sql);
        // echo 'sql=' . json_encode($sql);
        // echo "\n";
        try {
            $response = $this->db->batchGetItem($sql);
        } catch (Exception $e) {
            $is_error = true;
            echo "\nTranslator Get Batch Item Error\n";
            echo "\n" . $e->getMessage() . "\n";
        }
        if ($is_error) {
            return false;
        }
        $result = [];
        foreach ($response['Responses']['Translator2'] as $Item) {
            $key          = $Item['KeyID']['S'];
            $val          = $Item['Value']['S'];
            $result[$key] = $val;
        }

        // echo 'dbGetBatchKey:json:' . json_encode($response['Responses']['Translator2']) . "\n";
        // echo 'result:' . json_encode($result) . "\n";

        return $result;
    }
}

function fordebug(){
    $APPID='push.amberweather.com';
    $KEY_LIST=[
            'MESSAGE_TITLTE',
            'MESSAGE_DESCRIPTION',
    ];
    $t=new Translator($APPID, $KEY_LIST);
    $status=$t->setString('MESSAGE_TITLTE', 164, 'en_US', '这是测试中文');
    if($status){
        echo "success";
    }else{
        echo "failed";
    }
    $val=$t->getString('MESSAGE_TITLTE', 164, 'en_US');
    echo "\ngetString:$val";
    $bt=$t->getBatchString(['MESSAGE_TITLTE','MESSAGE_DESCRIPTION'],164,'en_US');
    echo "\nbatch:".json_encode($bt);
}
// fordebug();
