REPLACE INTO `revenue_app`(`platform`,`appname`,`date`,`revenue`)
    SELECT "pingstart","unnamed",`date`, sum(revenue) FROM `ad_pingstart_placement` WHERE `placement` != 'apply success' GROUP BY `date`;

REPLACE INTO `revenue_app`(`platform`,`appname`,`date`,`revenue`)
    SELECT "baidu","unnamed",`date`, sum(revenue) FROM `ad_baidu`  GROUP BY `date`;