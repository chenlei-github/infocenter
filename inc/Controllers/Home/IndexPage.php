<?php

class IndexPage extends BaseController
{
    protected $template_dir = '';

    public function index()
    {
        // var_dump($this);die('fuck index');
        $this->requireUser();

        $this->smarty->assign('title', 'Home');
        return $this->display('home/index.tpl');
    }
}
