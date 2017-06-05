<?php
/**
 * Base ReportController
 *
 *
 * @date: 2016/10/27
 * @author: Tiger <DropFan@Gmail.com>
 */
require_once 'inc/Models/Opdata/Revenue.php';
require_once 'inc/Models/Opdata/Ad_summary.php';
require_once 'inc/class/Country.php';

class ReportController extends BaseController
{
    protected $template_dir = 'opdata/';
    protected $config;

    public function __construct()
    {
        parent::__construct();
        $this->config = require 'config/opdata.php';
    }

    public function index()
    {
        return false;
    }
}
