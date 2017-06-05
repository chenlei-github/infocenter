<?php

require_once('inc/Models/Message.php');

class IndexPage extends BaseController
{
    protected $template_dir = '';

    public function index()
    {
        // var_dump($this);die('fuck index');
        $this->requireUser();

        $this->smarty->assign('title', 'Infocenter');
        return $this->display('home/index.tpl');
    }

    public function message()
    {
        $this->requireUser();

        $this->assign('title', 'Message');

        $messages = Message::getMessages();
        // var_dump($messages);die;
        $this->assign('messages', $messages);
        $this->display('message/index.tpl');
    }

    public function add()
    {

    }
}
