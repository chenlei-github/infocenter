<?php

require_once('inc/Models/Odd.php');

class FeaturePage extends BaseController
{
    public function index()
    {
        $this->requireUser();

        $this->assign('title', 'Message');

        $messages = Message::getMessages();
        // var_dump($messages);die;

        // $this->assign('total', )

        $this->assign('messages', $messages);
        return $this->fetch('../tpl/message/index.tpl');
    }

    public function add()
    {
        $this->requireUser();

        $this->assign('title', 'Add message');

        return $this->fetch('../tpl/message/add.tpl');
    }

    public function edit($id)
    {
        $this->requireUser();

        $this->assign('title', 'Edit message');

        return $this->fetch('../tpl/message/edit.tpl');

    }
}
