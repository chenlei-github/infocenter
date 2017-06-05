<?php

require_once '/var/www/lib/translator/translator.php';

class ArticleTranslation
{
    private static $INNER_FIELD_TITLE = 'ARTICLE_TITLTE';
    private static $INNER_FIELD_CONTENT = 'ARTICLE_CONTENT';
    private static $INNER_FIELD_AUTHOR = 'ARTICLE_AUTHOR';
    private static $INNER_FIELD_EDITOR = 'ARTICLE_EDITOR';

    private static $INNER_FIELD_LIST = [
        'ARTICLE_TITLTE',
        'ARTICLE_CONTENT',
        'ARTICLE_AUTHOR',
        'ARTICLE_EDITOR',
    ];

    private static $FILED_MAP = [
        'ARTICLE_TITLTE' => 'title',
        'ARTICLE_CONTENT' => 'content',
        'ARTICLE_AUTHOR' => 'author',
        'ARTICLE_EDITOR' => 'editor',
    ];

    public static $FIELD_LIST = [
        'title',
        'content',
        'author',
        'editor',
    ];

    public $id = 0;
    public $title = '';
    public $content = '';
    public $author = '';
    public $editor = '';
    public $language = 'en';

    private static $translator = null;

    public function __construct($id, $language = 'en_US')
    {
        if (empty($id) || !is_int($id)) {
            debug("MessageTranslation.__construct($id,$language):Bad ID.");
            return null;
        }

        $lang_list = self::getSupportLanguageList();
        if (!in_array($language, $lang_list)) {
            debug("MessageTranslation.__construct($id,$language):Bad language:set en_US.");
            self::$language == 'en_US';
        }

        $data = fetchById($id, $language);
        if (empty($data)) {
            return null;
        }

        $this->id = $id;
        $this->language = $language;
        $this->title = isset($data['title']) ? $data['title'] : '';
        $this->content = isset($data['content']) ? $data['content'] : '';
        $this->author = isset($data['author']) ? $data['author'] : '';
        $this->editor = isset($data['editor']) ? $data['editor'] : '';

    }

    private static function getTranslator()
    {
        if (self::$translator instanceof Translator) {
            return self::$translator;
        }

        $config = require 'config/translator.php';
        $appid = $config['ARTICLE']['APPID'];
        $key_list = $config['ARTICLE']['KEY_LIST'];

        self::$translator = new Translator($appid, $key_list);

        return self::$translator;
    }

    public static function getSupportLanguageList()
    {
        $lang = require 'config/language.php';

        return array_keys($lang['CODE_TO_NAME']);
    }

    public static function fetchById($id, $language)
    {
        $translator = self::getTranslator();
        $field_list = self::$FIELD_LIST;
        $inner_field_list = self::$INNER_FIELD_LIST;     

        if (!is_array($language)) {
            $lang_list = [$language];
        }else{
            $lang_list = $language;
        }

        $trans = $translator->getBatchString(self::$INNER_FIELD_LIST, $id, $lang_list);

        // debug('INNER_FIELD_LIST=' . json_encode(self::$INNER_FIELD_LIST));
        // debug("id=$id");
        // debug('trans=' . json_encode($trans));

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
        // debug('data=' . json_encode($data));

        return $data;
    }

    public static function validate($data)
    {
        $lang_list = self::getSupportLanguageList();
        $fields = self::$FIELD_LIST;
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
        $translator = self::getTranslator();
        if ($translator == null) {
            debug('get getTranslator error');
        }
        $lang_list = self::getSupportLanguageList();
        $inner_field_list = self::$INNER_FIELD_LIST;
        $success_count = 0;
        foreach ($lang_list as $lang) {
            if (!in_array($lang, array_keys($data))) {continue;}
            foreach ($inner_field_list as $inner_field) {
                $field = self::$FILED_MAP[$inner_field];
                if (!in_array($field, array_keys($data[$lang]))) {continue;}
                $trans = $data[$lang][$field];
                $stat = $translator->setString($inner_field, $id, $lang, $trans);
                debug("addOrUpdate()setString($inner_field, $id, $lang, $trans)");
                debug("stat:$stat");
                if ($stat) {
                    debug('addOrUpdate():success');
                    $success_count += 1;
                }
            }
        }

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
        if ($this->id <= 0) {
            return false;
        }
        $translator = static::getTranslator();
        if (!$translator){
            debug("saveData():getTranslator():failed.");
            return false;
        }
        $stat = $translator->setString($this->INNER_FIELD_TITLE, $this->id, $this->language);
        $stat |= $translator->setString($this->INNER_FIELD_CONTENT, $this->id, $this->content);
        $stat |= $translator->setString($this->INNER_FIELD_AUTHOR, $this->id, $this->author);
        $stat |= $translator->setString($this->INNER_FIELD_EDITOR, $this->id, $this->editor);
        return $stat;
    }

}
