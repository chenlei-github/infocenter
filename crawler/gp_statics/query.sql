

--- mysqldump -u daisy -p daisy record_widgets_new > record_widgets_new.sql


REPLACE into gp_statics_category(`package`,`group`)
(SELECT `package_name` as `package`,`group` from record_widgets_new);


UPDATE gp_statics_category
 set category = 'old_widget'
 where `group` is not null ;


UPDATE gp_statics_category
 set category = 'locker_skin'
WHERE `group` = 'skin1';

UPDATE gp_statics_category
 set category = 'mul_widget'
 WHERE `group` in (
    'weather-widget-new-group3' ,
    'weather-widget-new-group10',
    'weather-widget-new-group11',
    'weather-widget-new-group12',
    'weather-widget-new-group13',
    'weather-widget-new-group14',
    'weather-widget-new-group15',
    'weather-widget-new-group16',
    'weather-widget-new-group17',
    'weather-widget-new-group18'
);


UPDATE gp_statics_category
 set category = 'mul_widget'
where `package` in (
    'mobi.infolife.ezweather.widget.glass',
    'mobi.infolife.ezweather.widget.blackglass',
    'mobi.infolife.ezweather.widget.gcolour',
    'mobi.infolife.ezweather.widget.greenglass',
    'mobi.infolife.ezweather.widget.nori'
);

UPDATE gp_statics_category
 set category = 'amber_weather'
where `package` in (
    'mobi.infolife.ezweather',
    'com.amber.weather',
    'mobi.infolife.ezweatherlite'
);



-- 计算外部流量

select `date`,`c1` as `from`,sum(`views`) as pv ,sum(`installed`) as install
from `gp_statics` as A ,`gp_statics_category` as B
where A.`package` = B.`package` and `c1` in ('channel','google_play_nature','third_party','google_search','AdWords') and `c2` = ''
and `date` >= '2016-10-01' and `date` <= '2016-10-10' and `category` is not null
GROUP BY `date` ,`c1`
order by `date`


-- 计算内部量

select `date`,'mul_widget' as `from`,`category` as `to` ,sum(`views`) as pv ,sum(`installed`) as install
from `gp_statics` as A ,`gp_statics_category` as B
where A.`package` = B.`package` and `c1` = 'channel' and `c2` like '%mul_%'
and `date` >= '2016-10-01' and `date` <= '2016-10-10'
group by `date`,`category`

select `date`,'old_widget' as `from`,`category` as `to` , sum(`views`) as pv ,sum(`installed`) as install
from `gp_statics` as A ,`gp_statics_category` as B
where A.`package` = B.`package` and `c1` = 'channel' and `c2` like '%OLD_WIDGET%'
and `date` >= '2016-10-01' and `date` <= '2016-10-10'
group by `date`,`category`

select `date`,'amber_weather' as `from`,`category` as `to` , sum(`views`) as pv ,sum(`installed`) as install
from `gp_statics` as A ,`gp_statics_category` as B
where A.`package` = B.`package` and `c1` = 'channel' and `c2` like '%amber_weather%'
and `date` >= '2016-10-01' and `date` <= '2016-10-10'
group by `date`,`category`



--  计算导出量


select `date`,'mul_widget' as `from`,sum(`views`) as pv ,sum(`installed`) as install
from `gp_statics` as A ,`gp_statics_category` as B
where A.`package` = B.`package` and `c1` = 'channel' and `c2` like '%mul_%' and  `category` is not null
and `date` >= '2016-10-01' and `date` <= '2016-10-10'
group by `date`

select `date`,'old_widget' as `from`,sum(`views`) as pv ,sum(`installed`) as install
from `gp_statics` as A ,`gp_statics_category` as B
where A.`package` = B.`package` and `c1` = 'channel' and `c2` like '%OLD_WIDGET%' and `category` is not null
and `date` >= '2016-10-01' and `date` <= '2016-10-10'
group by `date`


select `date`,'amber_weather' as `from`,sum(`views`) as pv ,sum(`installed`) as install
from `gp_statics` as A ,`gp_statics_category` as B
where A.`package` = B.`package` and `c1` = 'channel' and `c2` like '%amber_weather%' and `category` is not null
and `date` >= '2016-10-01' and `date` <= '2016-10-10'
group by `date`


-- 计算导入量

select `date`,`category` as `to` , sum(`views`) as pv ,sum(`installed`) as install
from `gp_statics` as A ,`gp_statics_category` as B
where A.`package` = B.`package` and `c1` = 'channel' and `c2` != ''
and `date` >= '2016-10-01' and `date` <= '2016-10-10'
group by `date`,`category`


--

select `date` , `package` , sum(`views`)  as total_views , sum(`installed`) as total_installed
from gp_statics
where `package` = 'mobi.infolife.ezweather.widget.bible' and `c1` in ('channel','google_play_nature','third_party','google_search','AdWords') and `c2` = ''
GROUP BY `date` ,`package`
order by `date`


--

select A.`date` , A.organic_views,A.organic_installed,B.total_views,B.total_installed from
(
SELECT SUM(views) as total_views,SUM(installed) as total_installed, `date`
FROM `gp_statics`
WHERE `c1` = 'channel' AND `c2` = '' AND `package` = 'mobi.infolife.ezweather.widget.blackglass' AND `date` >= '2017-01-1'
GROUP BY `date`
) as B,
(
SELECT SUM(views) as organic_views,SUM(installed) as organic_installed, `date`
FROM `gp_statics`
WHERE `c1` = 'google_play_nature' AND `c2`='' AND `package` = 'mobi.infolife.ezweather.widget.blackglass' AND `date` >= '2017-01-1'
GROUP BY `date`
) as A
where A.`date`=B.`date`
;



-- create table


CREATE TABLE `gp_statics_country` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL COMMENT '1:common;2:organic',
  `date` date NOT NULL,
  `package` varchar(100) NOT NULL,
  `cc` char(2) NOT NULL,
  `country` varchar(100) NOT NULL,
  `views` int(10) DEFAULT NULL,
  `installed` int(10) DEFAULT NULL,
  `year` int(10) DEFAULT NULL,
  `month` int(10) DEFAULT NULL,
  `year_month` char(6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date_package_cc` (`date`,`package`,`cc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `gp_statics_conversion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `package` varchar(100) NOT NULL,
  `cc` char(2) not null,
  `country` varchar(100) not null,
  `p25` int(10) DEFAULT NULL,
  `p50` int(10) DEFAULT NULL,
  `p75` int(10) DEFAULT NULL,
  `month` int(10) DEFAULT NULL,
  `year_month` char(6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date_package_cc` (`date`,`package`,`cc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


UPDATE `gp_statics_country`
SET `year` = YEAR(`date`), `month` = Month(`date`), `year_month` = DATE_FORMAT(`date`,'%Y%m')
WHERE `year` IS NULL;