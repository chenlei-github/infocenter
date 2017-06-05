<?php

require_once 'inc/Models/Article.php';
require_once 'inc/Models/ArticleTranslation.php';

class ArticleAjax extends BaseController
{
    public $returnType = 'json';

    public function index()
    {
        // $this->requireUser();
        $this->checkCsrfToken();

        $data = 'test';

        return $this->returnJson($data);
    }

    public function add()
    {
        $article_config = require 'config/article.php';
        // $this->requireUser();

        $this->checkCsrfToken();

        if (!$this->checkKeyParam()) {
            $this->returnJson('BAD PARMA!', 'error');
            return;
        }

        $data = $this->genDataFromPOST();
        $data['language'] =  $supported_languge;
        // debug("data:=".json_encode($data));
        $data['category'] = $article_config['category'][$data['cid']];
        $newid = Article::create($data)->id;
        $status = $newid == null ? 'eror' : 'ok';
        $data = ['newid' => $newid];
        $this->returnJson($data, $status);
    }

    public function edit()
    {
        $article_config = require 'config/article.php';

        $this->checkCsrfToken();

        if (!$this->checkKeyParam()) {
            return $this->returnJson('BAD PARMA!', 'error');
        }

        $data = [];
        $status = 'ok';
        $id = intval($_POST['article_id']);

        if (empty($id)) {
            return $this->returnJson('Wrong Id!', 'error');
        }

        $supported_languge = '';
        $multilanguageData = [];
        $multilanguage =  $_POST['multilanguage'];
        if (!empty($multilanguage)) {
            $json = base64_decode($multilanguage);
            $multilanguageData = json_decode($json, 1);
            $languge_list = array_keys($multilanguageData);
            $supported_languge = join(',', $languge_list);
        }

        // debug('article->id' . $id);
        $article = new Article($id);
        // debug('article->id' . $article->id);
        $this->updateArticleFromPOST($article);
        $article->language = $supported_languge;
        $stat1 = ArticleTranslation::set($id, $multilanguageData);
        $article->category = $article_config['category'][$article->cid];
        // debug(json_encode($article));
        $stat2  = $article->update();
        if ($stat1 || $stat2) {
            $status = 'ok';
        }

        return $this->returnJson($data, $status);
    }

    public function delete()
    {
        $this->checkCsrfToken();

        if (!isset($_POST['article_id'])) {
            return $this->returnJson('Wrong  Id!', 'error');
        }
        $id = intval($_POST['article_id']);
        if ($id == 0) {
            return $this->returnJson('Wrong  Id!', 'error');
        }
        $article = new Article($id);
        $f = $article->delete();
        $status = 'ok';
        $data = ['id' => $id];
        if (!$f) {
            $status = 'error';
        }

        return $this->returnJson($data, $status);
    }

    public function delete_many()
    {
        $this->checkCsrfToken();

        if (!isset($_POST['del_list'])) {
            return $this->returnJson('Wrong  Params!', 'error');
        }

        try {

            $b64 = $_POST['del_list'];
            // $this->debug_var_dump($b64);
            $js = base64_decode($b64);
            // debug('js='+$js);
            $ids = json_decode($js);
            // debug('delete_many,ids=');
            // $this->debug_var_dump($ids);
            $count = Article::deleteAll($ids);
            $r = ['success_count' => $count];

            return $this->returnJson($r, 'ok');
        } catch (Exception $e) {
            return $this->returnJson('Wrong  Params!', 'error');
        }

    }

    private function checkKeyParam()
    {
        $keylist = ['article_title', 'article_content', 'article_link'];
        foreach ($keylist as $key) {
            if (!isset($_POST[$key]) || empty($_POST[$key])) {
                return false;
            }
        }

        return true;
    }

    private function genDataFromPOST()
    {
        $attrs = array_keys(Article::$tableFields);
        $data = [];

        foreach ($attrs as $attr) {
            if ($attr == 'id') {
                continue;
            }
            if (isset($_POST['article_' . $attr])) {
                $data[$attr] = $_POST['article_' . $attr];
            }
        }
        return $data;
    }

    private function updateArticleFromPOST($article)
    {
        $attrs = array_keys(Article::$tableFields);

        foreach ($attrs as $attr) {
            if ($attr == 'id') {
                continue;
            }
            if (isset($_POST['article_' . $attr])) {
                $article->$attr = $_POST['article_' . $attr];
            }
        }

        return $article;
    }

    private function debug_var_dump($v)
    {
        ob_start();
        var_dump($v);
        $result = ob_get_clean();
        debug($result);
    }
}
