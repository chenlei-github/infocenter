-- Adminer 4.2.5 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';

SET NAMES utf8mb4;

CREATE TABLE `ad_mobvista` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `account` varchar(50) NOT NULL,
  `platform` varchar(20) NOT NULL,
  `country` char(2) DEFAULT NULL,
  `appname` varchar(100) NOT NULL,
  `placement` varchar(255) DEFAULT NULL,
  `request` int(10) DEFAULT NULL,
  `filled` int(10) DEFAULT NULL,
  `impression` int(10) DEFAULT NULL,
  `click` int(10) DEFAULT NULL,
  `filled_rate` float DEFAULT NULL,
  `ctr` float DEFAULT NULL,
  `ecpm` float DEFAULT NULL,
  `revenue` decimal(30,10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `appname_country_placement_date_account` (`appname`,`country`,`placement`,`date`,`account`),
  KEY `platform` (`platform`),
  KEY `app` (`appname`),
  KEY `country` (`country`),
  KEY `date` (`date`),
  KEY `placement` (`placement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `ad_mobvista_country` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `account` varchar(50) NOT NULL,
  `platform` varchar(20) NOT NULL,
  `appname` varchar(100) NOT NULL,
  `country` char(2) DEFAULT NULL,
  `request` int(10) DEFAULT NULL,
  `filled` int(10) DEFAULT NULL,
  `impression` int(10) DEFAULT NULL,
  `click` int(10) DEFAULT NULL,
  `filled_rate` float DEFAULT NULL,
  `ctr` float DEFAULT NULL,
  `ecpm` float DEFAULT NULL,
  `revenue` decimal(30,8) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date_account_country` (`date`,`account`,`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `ad_mobvista_placement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `account` varchar(50) NOT NULL,
  `platform` varchar(20) NOT NULL,
  `appname` varchar(100) NOT NULL,
  `placement` varchar(255) DEFAULT NULL,
  `request` int(10) DEFAULT NULL,
  `filled` int(10) DEFAULT NULL,
  `impression` int(10) DEFAULT NULL,
  `click` int(10) DEFAULT NULL,
  `filled_rate` decimal(30,10) DEFAULT NULL,
  `ctr` decimal(30,10) DEFAULT NULL,
  `ecpm` decimal(30,10) DEFAULT NULL,
  `revenue` decimal(30,10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `appname_placement_date_account` (`appname`,`placement`,`date`,`account`),
  KEY `platform` (`platform`),
  KEY `app` (`appname`),
  KEY `date` (`date`),
  KEY `placement` (`placement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 2017-01-18 05:44:29