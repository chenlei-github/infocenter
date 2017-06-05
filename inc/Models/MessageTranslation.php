<?php

require_once '/var/www/lib/translator/translator.php';
require_once 'inc/Models/Message.php';

class MessageTranslation
{
    private static $INNER_FIELD_TITLE       = 'MESSAGE_TITLTE';
    private static $INNER_FIELD_DESCRIPTION = 'MESSAGE_DESCRIPTION';
    private static $INNER_FIELD_CALL_TO_ACTION = 'MESSAGE_CALL_TO_ACTION';
    private static $INNER_FIELD_LIST        = [
        'MESSAGE_TITLTE',
        'MESSAGE_DESCRIPTION',
        'MESSAGE_CALL_TO_ACTION',
    ];

    private static $FILED_MAP = [
        'MESSAGE_TITLTE'      => 'title',
        'MESSAGE_DESCRIPTION' => 'description',
        'MESSAGE_CALL_TO_ACTION' => 'call_to_action',
    ];

    public static $FIELD_LIST = [
        'title',
        'description',
        'call_to_action',
    ];

    public $id                 = 0;
    public $title              = '';
    public $description        = '';
    public $call_to_action     = '';
    public $language           = 'en';
    private static $translator = null;

    public function __construct()
    {
    }

    public static function fetchById($id)
    {
        $log = getLogInstance(get_class() . '::fetchById()');
        $translator       = self::getTranslator();
        $field_list       = self::$FIELD_LIST;
        $inner_field_list = self::$INNER_FIELD_LIST;

        $message =  new Message($id);
        $log->debug("message:" . json_encode($message));
        $lang_list = explode(',' , $message->language);
        $log->debug('lang_list:' . json_encode($lang_list));
        if (empty($lang_list)) {
            return null;
        }

        $trans  = $translator->getBatchString(self::$INNER_FIELD_LIST, $id, $lang_list);

        debug('INNER_FIELD_LIST=' . json_encode(self::$INNER_FIELD_LIST));
        debug("id=$id");
        debug('lang_list' . json_encode($lang_list));
        debug('trans=' . json_encode($trans));

        $log->debug('INNER_FIELD_LIST=' . json_encode(self::$INNER_FIELD_LIST));
        $log->debug("id=$id");
        $log->debug('lang_list' . json_encode($lang_list));
        $log->debug('trans=' . json_encode($trans));


        if (!is_array($trans)) {
            return null;
        }
        $data = [];
        foreach ($lang_list as $lang) {
            $data[$lang] = [];
            foreach ($inner_field_list as $inner_field) {
                if (!in_array($inner_field, array_keys($trans))) {
                    continue;
                }
                if (!in_array($id, array_keys($trans[$inner_field]))) {
                    continue;
                }
                if (!in_array($lang, array_keys($trans[$inner_field][$id]))) {
                    continue;
                }

                $field = self::$FILED_MAP[$inner_field];

                $data[$lang][$field] = $trans[$inner_field][$id][$lang];
            }
        }
        debug('data=' . json_encode($data));
        $log->debug('data=' . json_encode($data));

        return $data;
    }

    public static function validate($data)
    {
        $lang_list = self::getSupportLanguageList();
        $fields    = self::$FIELD_LIST;
        if (!is_array($data)) {
            return false;
        }
        foreach ($lang_list as $lang) {
            if (!in_array($lang, $data)) {
                continue;
            }
            if (!is_array($data[$lang])) {
                return false;
            }
            foreach ($fields as $inner_field) {
                if (!in_array($field, $data[$lang])) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function set($id, $data)
    {
        $log = getLogInstance(get_class() . '::set()');
        $translator = self::getTranslator();
        if ($translator == null) {
            debug('get getTranslator error');
            $log->debug('get getTranslator error');
        }
        $lang_list        = self::getSupportLanguageList();
        $inner_field_list = self::$INNER_FIELD_LIST;
        $success_count    = 0;
        foreach ($lang_list as $lang) {
            if (!in_array($lang, array_keys($data))) {continue;}
            foreach ($inner_field_list as $inner_field) {
                $field = self::$FILED_MAP[$inner_field];
                if (!in_array($field, array_keys($data[$lang]))) {continue;}
                $trans = $data[$lang][$field];
                $stat = null;
                if (empty($trans)) {
                    $stat  = $translator->delString($inner_field, $id, $lang);
                    debug("addOrUpdate()delString($inner_field, $id, $lang)");
                    $log->debug("addOrUpdate()delString($inner_field, $id, $lang)");
                } else {
                    $stat  = $translator->setString($inner_field, $id, $lang, $trans);
                    debug("addOrUpdate()setString($inner_field, $id, $lang, $trans)");
                    $log->debug("addOrUpdate()setString($inner_field, $id, $lang, $trans)");
                }
                debug("stat:$stat");
                $log->debug("stat:$stat");
                if ($stat) {
                    debug('addOrUpdate():success');
                    $log->debug('addOrUpdate():success');
                    $success_count += 1;
                }
            }
        }
        $log->debug("return:count:$success_count");
        return $success_count;
    }

    public function add()
    {
        $this->saveData();
    }

    public function update()
    {
        $this->saveData();
    }

    public function saveData()
    {
        $log =  getLogInstance(get_class() . '::saveData()');
        $log->debug("`id`=" . $this->id);
        if ($this->id <= 0) {
            return false;
        }
        $translator = static::getTranslator();
        $log->debug('translator:' . json_encode($translator));
        $translator->setString($this->INNER_FIELD_TITLE, $this->id, $this->language);
        $log->debug('setString():' . json_encode([$this->INNER_FIELD_TITLE, $this->id, $this->language]) );
        $translator->setString($this->INNER_FIELD_DESCRIPTION, $this->id, $this->language);
        $log->debug('setString():' . json_encode([$this->INNER_FIELD_DESCRIPTION, $this->id, $this->language]));
    }

    private static function getTranslator()
    {
        if (self::$translator instanceof Translator) {
            return self::$translator;
        }

        $config   = require 'config/translator.php';
        $appid    = $config['MESSAGE']['APPID'];
        $key_list = $config['MESSAGE']['KEY_LIST'];

        self::$translator = new Translator($appid, $key_list);

        return self::$translator;
    }

    public static function getSupportLanguageList()
    {
        $lang = require 'config/language.php';

        return array_keys($lang['CODE_TO_NAME']);
    }

}
