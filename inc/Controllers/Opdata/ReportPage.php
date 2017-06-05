<?php
/**
 * Revenue Page Controller
 *
 *
 * @date: 2016/10/27
 * @author: Tiger <DropFan@Gmail.com>
 *
 * @last-modified : 2016/11/26
 * @author        : Tiger <DropFan@Gmail.com>
 */
require_once 'inc/Controllers/Opdata/ReportController.php';
require_once 'inc/class/Country.php';

class ReportPage extends ReportController
{
    protected $template_dir = 'opdata/';

    public function index()
    {
        return $this->revenue();
    }

    public function revenue()
    {
        $this->requireUser();
        $this->setCsrfToken();
        $this->assign('title', 'Revenue Report');

        $appid = $this->get('appid') ?: null;

        $startDate = $this->get('startDate') ?: date('Y-m-d', time() - 86400 * 7);
        $endDate   = $this->get('endDate') ?: date('Y-m-d');
        $platform  = $this->get('platform') ?: '';
        $country   = $this->get('country') ?: '';
        $appname   = $this->get('appname') ?: '';

        $this->assign('startDate', $startDate);
        $this->assign('endDate', $endDate);
        $this->assign('platform', $platform);

        $support_platform = $this->config['platforms'];
        $support_app = $this->config['apps'];

        $this->assign('platform', $platform);
        $this->assign('appname', $appname);

        $this->assign('support_platform', $support_platform);
        $this->assign('support_app', $support_app);

        return $this->display('report.tpl');
    }

    public function country()
    {
        $this->requireUser();
        $this->setCsrfToken();
        $this->assign('title', 'Revenue Report');

        $appid = $this->get('appid') ?: null;

        $startDate = $this->get('startDate') ?: date('Y-m-d', time() - 86400 * 7);
        $endDate   = $this->get('endDate') ?: date('Y-m-d');
        $platform  = $this->get('platform') ?: '';
        // $country = $this->get('country') ?: '';

        $this->assign('startDate', $startDate);
        $this->assign('endDate', $endDate);
        $this->assign('platform', $platform);

        $support_platform = $this->config['platforms'];
        $support_app = $this->config['apps'];

        $this->assign('support_platform', $support_platform);
        $this->assign('support_app', $support_app);

        return $this->display('revenue.tpl');
    }

    public function import()
    {
        $this->requireUser();
        $this->setCsrfToken();

        $crawler = $this->config['crawler'];

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
