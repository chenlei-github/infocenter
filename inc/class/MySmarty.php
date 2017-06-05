<?php

require_once __DIR__. '/../smarty/Smarty.class.php';

class MySmarty extends Smarty
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplateDir(realpath(__DIR__.'/../tpl'));
        $this->setCacheDir(realpath(__DIR__.'/../../runtime/cache'));
        $this->setConfigDir(realpath(__DIR__.'/../smarty/configs'));
        $this->setCompileDir(realpath(__DIR__.'/../../runtime/templates_c'));
        //     $this->template_dir = '.';
        //     $this->compile_dir = '../inc/smarty/templates_c';
        //     $this->cache_dir = '../inc/smarty/cache';
        //     $this->config_dir = '../inc/smarty/configs';

        if (true) {
            $this->force_compile = true;
        } else {
            $this->compile_check = false;
        }
    }
}
