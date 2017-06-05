<?php

require_once 'inc/Models/Article.php';
require_once 'inc/Models/ArticleTranslation.php';

class ArticlePage extends BaseController
{
    protected $template_dir = 'article/';

    private static $table_fields = [
        ['name' => 'id', 'has_sorted' => true, 'asc' => 'ID_ASC', 'des' => 'ID_DES'],
        ['name' => 'status', 'has_sorted' => true, 'asc' => 'STATUS_ASC', 'des' => 'STATUS_DES'],
        ['name' => 'title', 'has_sorted' => true, 'asc' => 'TITLE_ASC', 'des' => 'TITLE_DES'],
        ['name' => 'link', 'has_sorted' => true, 'asc' => 'LINK_ASC', 'des' => 'LINK_DES'],
        ['name' => 'category', 'has_sorted' => true, 'asc' => 'CATEGORY_ASC', 'des' => 'CATEGORY_DES'],
        ['name' => 'image', 'has_sorted' => false, 'asc' => '', 'des' => ''],
        // ['name' => 'language', 'has_sorted' => true, 'asc' => 'LANGUAGE_ASC', 'des' => 'LANGUAGE_DES'],
        ['name' => 'weight', 'has_sorted' => true, 'asc' => 'WEIGHT_ASC', 'des' => 'WEIGHT_DES'],
        ['name' => 'created_at', 'has_sorted' => true, 'asc' => 'CREATED_AT_ASC', 'des' => 'CREATED_AT_DES'],
        ['name' => 'updated_at', 'has_sorted' => true, 'asc' => 'UPDATED_AT_ASC', 'des' => 'UPDATED_AT_DES'],
    ];

    private static $table_fields_orders = [
        'ID_ASC'         => '`id`',
        'ID_DES'         => '`id` DESC',
        'STATUS_ASC'      => '`status`',
        'STATUS_DES'      => '`status` DESC',
        'TITLE_ASC'      => '`title`',
        'TITLE_DES'      => '`title` DESC',
        'LINK_ASC'       => '`link`',
        'LINK_DES'       => '`link` DESC',
        'CATEGORY_ASC'   => '`category`',
        'CATEGORY_DES'   => '`category` DESC',
        // 'LANGUAGE_ASC'   => '`language`',
        // 'LANGUAGE_DES'   => '`language` DESC',
        'WEIGHT_ASC'     =>  '`weight`',
        'WEIGHT_DES'     =>  '`weight` DESC',
        'CREATED_AT_ASC' => '`created_at`',
        'CREATED_AT_DES' => '`created_at` DESC',
        'UPDATED_AT_ASC' => '`created_at`',
        'UPDATED_AT_DES' => '`created_at` DESC',
    ];

    public function index()
    {
        $article_config = require 'config/article.php';
        $this->requireUser();
        $this->setCsrfToken();

        $this->assign('title', 'Article');

        $articles = null;

        $next_page = 1; #下一页
        $total     = Article::getCount();
        $perpage   = isset($_GET['pagesize']) ? intval($_GET['pagesize']) : 10;
        $max_page  = 1 + intval($total / $perpage);
        $min_page  = 1;
        #当前页
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page < 1) {$page = 1;}
        if ($page > $max_page) {$page = $max_page;}

        $pre_page = $page - 1;
        if ($pre_page < 1) {$pre_page = 1;}
        $next_page = $page + 1;
        if ($next_page > $max_page) {$next_page = $max_page;}

        #排序
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'ID_DES';
        // debug('sort:' . $sort);
        $order = '';
        if (in_array($sort, array_keys(ArticlePage::$table_fields_orders))) {
            $order = ArticlePage::$table_fields_orders[$sort];
        }

        // debug('order:' . $order);

        $articles = Article::getAll($page, $perpage,[],$order);

        debug( json_encode( $articles , 1));

        $this->assign('articles', $articles);
        $this->assign('total_count', $total);
        $this->assign('perpage', $perpage);
        $this->assign('max_page', $max_page);
        $this->assign('page', $page);
        $this->assign('pre_page', $pre_page);
        $this->assign('next_page', $next_page);
        $this->assign('table_head', ArticlePage::$table_fields);
        $this->assign('sort', $sort);

        return $this->display('index.tpl');
    }

    public function add()
    {
        $article_config = require 'config/article.php';

        $this->requireUser();
        $this->setCsrfToken();

        $this->assign('title', 'Add article');
        $this->assign('article_link', $this->genArticleLink());

        $this->assign('article_cid_enum', $article_config['category']);
        $this->assign('article_status_enum', $article_config['status_enum']);

        return $this->display('add.tpl');
    }

    public function edit()
    {
        $article_config = require 'config/article.php';
        $supported_languages = require 'config/language.php';

        $this->requireUser();
        $this->setCsrfToken();

        if (!isset($_GET['article_id']) || empty($_GET['article_id'])) {
            echo "Miss Id\n";

            return;
        }
        $id      = intval($_GET['article_id']);
        $article = Article::getOneById($id);
        if ($article == null) {
            $this->returnJson('BAD PARMA!', 'error');

            return;
        }

        // debug("article:". json_encode($article));
        $language =  $article['language'];
        $languge_list = explode(',', $language);
        $multilanguageData = ArticleTranslation::fetchById($id,$languge_list);
        if ($multilanguageData == null) {
            $multilanguageData = [];
        }
        $json = json_encode($multilanguageData);
        $multilanguage = base64_encode($json);
        // debug("multilanguage:$multilanguage\n");

        $this->assign('title', 'Edit article');
        $this->assign('article', $article);
        $this->assign('article_cid_enum', $article_config['category']);
        $this->assign('article_cid_str', $article_config['category']['' . $article['cid']]);
        $this->assign('article_status_enum', $article_config['status_enum']);
        $this->assign('article_status_str', $article_config['status_enum']['' . $article['status']]);
        $this->assign('multilanguage', $multilanguage);
        $this->assign('supported_languages',$supported_languages['CODE_TO_NAME']);
        return $this->display('edit.tpl');
    }

    private function genArticleLink()
    {
        $t = strftime('%Y-%m-%d %H:%M:%S');

        return sha1($t);
    }

}
