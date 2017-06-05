<?php
require_once '/var/www/infocenter/config.php';

class CrawlerPage extends BaseController
{
    protected $template_dir = 'opdata/';

    public function index()
    {
        $this->requireUser();
        $this->setCsrfToken();

        $crawler = $GLOBALS['configs']['crawler'];

        $facebook_status_text   = explode("\n", shell_exec('tail -n7 ' . $crawler['facebook']['log']));
        $admob_status_text      = explode("\n", shell_exec('tail -n7 ' . $crawler['admob']['log']));
        $googleplay_status_text = explode("\n", shell_exec('tail -n7 ' . $crawler['googleplay']['log']));
        $pingstart_status_text  = explode("\n", shell_exec('tail -n7 ' . $crawler['pingstart']['log']));
        $baidu_status_text      = explode("\n", shell_exec('tail -n7 ' . $crawler['baidu']['log']));

        $this->assign('title', 'Crawler Info');
        $this->assign('facebook_status_text', $facebook_status_text);
        $this->assign('admob_status_text', $admob_status_text);
        $this->assign('googleplay_status_text', $googleplay_status_text);
        $this->assign('pingstart_status_text', $pingstart_status_text);
        $this->assign('baidu_status_text', $baidu_status_text);

        return $this->display('crawler.tpl');
    }

}
