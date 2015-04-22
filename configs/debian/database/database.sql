-- phpMyAdmin SQL Dump
-- version 4.0.8
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Apr 22, 2015 at 11:23 PM
-- Server version: 5.5.43-0+deb8u1
-- PHP Version: 5.6.7-1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `selity`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `admin_pass` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `admin_type` enum('admin','reseller','client') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'client',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_on` int(10) unsigned NOT NULL DEFAULT '0',
  `fname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` enum('M','F','U') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'U',
  `firm` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uniqkey` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uniqkey_time` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `admin_id` (`admin_id`),
  UNIQUE KEY `admin_name` (`admin_name`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  `readonly` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `domains`
--

CREATE TABLE IF NOT EXISTS `domains` (
  `dmn_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned DEFAULT NULL,
  `dmn_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dmn_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dmn_mount` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dmn_ips` varchar(4096) COLLATE utf8_unicode_ci DEFAULT 'a:0:{}',
  `dmn_forward_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`dmn_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_tpls`
--

CREATE TABLE IF NOT EXISTS `email_tpls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ftp_group`
--

CREATE TABLE IF NOT EXISTS `ftp_group` (
  `groupname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `members` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `groupname` (`groupname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ftp_users`
--

CREATE TABLE IF NOT EXISTS `ftp_users` (
  `userid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `admin_id` int(10) NOT NULL,
  `passwd` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `shell` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `homedir` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hosting_plans`
--

CREATE TABLE IF NOT EXISTS `hosting_plans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reseller_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `max_dmn` int(11) NOT NULL DEFAULT '-1',
  `max_sub` int(11) NOT NULL DEFAULT '-1',
  `max_mail` int(11) NOT NULL DEFAULT '-1',
  `max_ftp` int(11) NOT NULL DEFAULT '-1',
  `max_mysqld` int(11) NOT NULL DEFAULT '-1',
  `max_mysqlu` int(11) NOT NULL DEFAULT '-1',
  `max_disk` bigint(20) NOT NULL DEFAULT '-1',
  `max_traff` bigint(20) NOT NULL DEFAULT '-1',
  `php` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `cgi` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `support` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `backup` enum('no','sql','full') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `description` text COLLATE utf8_unicode_ci,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `setup_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `log_message` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE IF NOT EXISTS `login` (
  `session_id` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ipaddr` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastaccess` int(10) unsigned DEFAULT NULL,
  `login_count` tinyint(1) DEFAULT '0',
  `captcha_count` tinyint(1) DEFAULT '0',
  `user_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_users`
--

CREATE TABLE IF NOT EXISTS `mail_users` (
  `mail_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned DEFAULT NULL,
  `mail_acc` text COLLATE utf8_unicode_ci,
  `mail_addr` varchar(254) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail_pass` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail_forward` text COLLATE utf8_unicode_ci,
  `mail_type` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dmn_id` int(10) unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `op_result` text COLLATE utf8_unicode_ci,
  `mail_auto_respond` tinyint(1) NOT NULL DEFAULT '0',
  `mail_auto_respond_text` text COLLATE utf8_unicode_ci,
  `quota` bigint(20) DEFAULT '0',
  PRIMARY KEY (`mail_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mysql_db`
--

CREATE TABLE IF NOT EXISTS `mysql_db` (
  `sqld_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned DEFAULT '0',
  `sqld_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'n/a',
  PRIMARY KEY (`sqld_id`),
  KEY `domain_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mysql_user`
--

CREATE TABLE IF NOT EXISTS `mysql_user` (
  `sqlu_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sqld_id` int(10) unsigned DEFAULT '0',
  `sqlu_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT 'n/a',
  `sqlu_pass` varchar(64) COLLATE utf8_unicode_ci DEFAULT 'n/a',
  PRIMARY KEY (`sqlu_id`),
  KEY `sqld_id` (`sqld_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reseller_id` int(10) unsigned NOT NULL DEFAULT '0',
  `plan_id` int(10) unsigned NOT NULL DEFAULT '0',
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` enum('M','F','U') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'U',
  `firm` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_settings`
--

CREATE TABLE IF NOT EXISTS `orders_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reseller_id` int(10) unsigned NOT NULL DEFAULT '0',
  `header` text COLLATE utf8_unicode_ci,
  `footer` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reseller_props`
--

CREATE TABLE IF NOT EXISTS `reseller_props` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reseller_id` int(10) unsigned NOT NULL,
  `server_ids` text COLLATE utf8_unicode_ci,
  `reseller_ips` text COLLATE utf8_unicode_ci,
  `max_usr` int(11) NOT NULL DEFAULT '-1',
  `max_dmn` int(11) NOT NULL DEFAULT '-1',
  `max_sub` int(11) NOT NULL DEFAULT '-1',
  `max_mail` int(11) NOT NULL DEFAULT '-1',
  `max_ftp` int(11) NOT NULL DEFAULT '-1',
  `max_mysqld` int(11) NOT NULL DEFAULT '-1',
  `max_mysqlu` int(11) NOT NULL DEFAULT '-1',
  `max_disk` bigint(20) NOT NULL DEFAULT '-1',
  `max_traff` bigint(20) NOT NULL DEFAULT '-1',
  `php` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `cgi` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `support` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `reseller_id` (`reseller_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servers`
--

CREATE TABLE IF NOT EXISTS `servers` (
  `server_id` int(11) NOT NULL AUTO_INCREMENT,
  `server_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `server_ip` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `server_ssh_port` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '22',
  `server_root_user` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'root',
  `server_root_pass` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `server_fingerprint` text COLLATE utf8_unicode_ci,
  `server_pub_key` text COLLATE utf8_unicode_ci,
  `server_priv_key` text COLLATE utf8_unicode_ci,
  `server_pass_key` text COLLATE utf8_unicode_ci,
  `server_status` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `server_status text` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`server_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `server_ips`
--

CREATE TABLE IF NOT EXISTS `server_ips` (
  `ip_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` int(10) unsigned NOT NULL,
  `ip_number` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_card` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_ssl_dmn_id` int(10) DEFAULT NULL,
  `ip_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_op_result` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`ip_id`),
  KEY `server_id` (`server_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `server_traffic`
--

CREATE TABLE IF NOT EXISTS `server_traffic` (
  `straff_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `traff_time` int(10) unsigned DEFAULT NULL,
  `bytes_in` bigint(20) unsigned DEFAULT NULL,
  `bytes_out` bigint(20) unsigned DEFAULT NULL,
  `bytes_mail_in` bigint(20) unsigned DEFAULT NULL,
  `bytes_mail_out` bigint(20) unsigned DEFAULT NULL,
  `bytes_pop_in` bigint(20) unsigned DEFAULT NULL,
  `bytes_pop_out` bigint(20) unsigned DEFAULT NULL,
  `bytes_web_in` bigint(20) unsigned DEFAULT NULL,
  `bytes_web_out` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`straff_id`),
  KEY `traff_time` (`traff_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ssl_certs`
--

CREATE TABLE IF NOT EXISTS `ssl_certs` (
  `cert_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(10) NOT NULL,
  `type` enum('dmn','sub') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'dmn',
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `key` text COLLATE utf8_unicode_ci NOT NULL,
  `cert` text COLLATE utf8_unicode_ci NOT NULL,
  `ca_cert` text COLLATE utf8_unicode_ci,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cert_id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `straff_settings`
--

CREATE TABLE IF NOT EXISTS `straff_settings` (
  `straff_max` int(10) unsigned DEFAULT NULL,
  `straff_warn` int(10) unsigned DEFAULT NULL,
  `straff_email` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subdomains`
--

CREATE TABLE IF NOT EXISTS `subdomains` (
  `sub_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alias_id` int(10) unsigned DEFAULT NULL,
  `sub_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sub_mount` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sub_forward_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sub_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`sub_id`),
  KEY `alias_id` (`alias_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE IF NOT EXISTS `tickets` (
  `ticket_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_level` int(10) DEFAULT NULL,
  `ticket_from` int(10) unsigned DEFAULT NULL,
  `ticket_to` int(10) unsigned DEFAULT NULL,
  `ticket_status` int(10) unsigned DEFAULT NULL,
  `ticket_reply` int(10) unsigned DEFAULT NULL,
  `ticket_urgency` int(10) unsigned DEFAULT NULL,
  `ticket_date` int(10) unsigned DEFAULT NULL,
  `ticket_subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ticket_message` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_gui_props`
--

CREATE TABLE IF NOT EXISTS `user_gui_props` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `lang` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en_GB',
  `layout` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `layout_color` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_system_props`
--

CREATE TABLE IF NOT EXISTS `user_system_props` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` int(10) unsigned NOT NULL,
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `max_dmn` int(11) NOT NULL DEFAULT '-1',
  `max_sub` int(11) NOT NULL DEFAULT '-1',
  `max_mail` int(11) NOT NULL DEFAULT '-1',
  `max_ftp` int(11) NOT NULL DEFAULT '-1',
  `max_mysqld` int(11) NOT NULL DEFAULT '-1',
  `max_mysqlu` int(11) NOT NULL DEFAULT '-1',
  `max_disk` bigint(20) NOT NULL DEFAULT '-1',
  `max_traff` bigint(20) NOT NULL DEFAULT '-1',
  `php` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `cgi` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `support` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `backup` enum('no','sql','full') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `ips` text COLLATE utf8_unicode_ci,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `op_result` text COLLATE utf8_unicode_ci,
  `disk_amnt` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `server_id` (`server_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
