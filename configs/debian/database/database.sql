--
-- Table structure for table `admin`
--
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `admin_pass` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `admin_type` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `domain_created` int(10) unsigned NOT NULL DEFAULT '0',
  `customer_id` varchar(200) COLLATE utf8_unicode_ci DEFAULT '0',
  `created_by` int(10) unsigned DEFAULT '0',
  `fname` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lname` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firm` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street1` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street2` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uniqkey` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uniqkey_time` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `admin_id` (`admin_id`),
  UNIQUE KEY `admin_name` (`admin_name`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_name`, `admin_pass`, `admin_type`, `domain_created`, `customer_id`, `created_by`, `fname`, `lname`, `gender`, `firm`, `zip`, `city`, `state`, `country`, `email`, `phone`, `fax`, `street1`, `street2`, `uniqkey`, `uniqkey_time`) VALUES
(1, 'daniel', '$1$%z''q#\\9U$VhMo5yqDOQ8CAXz3No24b/', 'admin', 0, '0', 0, '', '', 'M', '', '', '', 'State', '', 'daniel@daniel.eu.bogus', '', '', '', '', NULL, NULL),
(2, 'vu2002', 'e5ef8cfea252ef572105ea129637a973', 'reseller', 1428683484, '0', 1, 'First name', '', 'M', '', '', '', NULL, '', 'aa@aa.aa', '', '', '', '', NULL, NULL),
(3, 'reseller2', '9f106cd54c42343deae7fc0a6baf18f0', 'reseller', 1428683522, '0', 1, 'First name', '', 'U', '', '', '', NULL, '', 'daniel@daniel.eu.bogus', '', '', '', '', NULL, NULL),
(4, 'tst.eu.bogus', '3fd93947c0eee0e10cda93f7aaada57f', 'user', 1428683696, '', 2, 'First name', 'Last name', 'M', '', '', 'City', '', '', 'daniel@daniel.eu.bogus', '', '', '', '', NULL, NULL),
(5, 'test.eu.bogus', '9eeb6e11c312627d71c503c390d4d646', 'user', 1428683762, '', 2, '', '', 'M', '', '', '', NULL, '', 'daniel@daniel.eu.bogus', '', '', '', '', NULL, NULL),
(19, 'admin1', 'e00cf25ad42683b3df678c61f42c6bda', 'admin', 0, '0', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'daniel@daniel.eu.bogus', NULL, NULL, NULL, NULL, NULL, NULL),
(28, 'vu2028', 'reseller3', 'reseller', 1429213718, '0', 1, 'First name', 'Last name', 'U', 'Company', 'Zip/Postal', 'City', 'State', 'Country', 'reseller3@daniel.eu.bogus', 'Phone', 'Fax', 'Street 1', 'Street 2', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `autoreplies_log`
--

CREATE TABLE IF NOT EXISTS `autoreplies_log` (
  `time` datetime NOT NULL COMMENT 'Date and time of the sent autoreply',
  `from` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'autoreply message sender',
  `to` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'autoreply message recipient',
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Sent autoreplies log table';

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`name`, `value`) VALUES
('BRUTEFORCE', '1'),
('BRUTEFORCE_BETWEEN', '1'),
('BRUTEFORCE_BETWEEN_TIME', '30'),
('BRUTEFORCE_BLOCK_TIME', '30'),
('BRUTEFORCE_MAX_CAPTCHA', '5'),
('BRUTEFORCE_MAX_LOGIN', '3'),
('CHECK_FOR_UPDATES', '1'),
('CREATE_DEFAULT_EMAIL_ADDRESSES', '1'),
('DATABASE_REVISION', '1'),
('DOMAIN_ROWS_PER_PAGE', '20'),
('GUI_DEBUG', '1'),
('HARD_MAIL_SUSPENSION', '0'),
('HOSTING_PLANS_LEVEL', 'reseller'),
('ITEM_ADD_STATUS', 'toadd'),
('ITEM_CHANGE_STATUS', 'change'),
('ITEM_DELETE_STATUS', 'delete'),
('ITEM_DISABLED_STATUS', 'disabled'),
('ITEM_OK_STATUS', 'ok'),
('ITEM_ORDERED_STATUS', 'ordered'),
('ITEM_RESTORE_STATUS', 'restore'),
('ITEM_TODISABLED_STATUS', 'todisable'),
('ITEM_TOENABLE_STATUS', 'toenable'),
('LOG_LEVEL', '0'),
('LOGIN_TEMPLATE_PATH', '/themes/default'),
('LOSTPASSWORD', '1'),
('LOSTPASSWORD_TIMEOUT', '30'),
('MAINTENANCEMODE', '0'),
('MAINTENANCEMODE_MESSAGE', '"We are sorry, but the system is currently under maintenance.\nOnly administrators can login."'),
('PASSWD_CHARS', '6'),
('PASSWD_STRONG', '1'),
('PHPINI_ALLOW_URL_FOPEN', 'Off'),
('PHPINI_DISABLE_FUNCTIONS', 'show_source,system,shell_exec,passthru,exec,phpinfo,shell,symlink'),
('PHPINI_DISPLAY_ERRORS', 'Off'),
('PHPINI_ERROR_REPORTING', 'E_ALL & ~E_NOTICE & ~E_WARNING'),
('PHPINI_MAX_EXECUTION_TIME', '30'),
('PHPINI_MAX_INPUT_TIME', '60'),
('PHPINI_MEMORY_LIMIT', '128'),
('PHPINI_OPEN_BASEDIR', ''),
('PHPINI_POST_MAX_SIZE', '10'),
('PHPINI_REGISTER_GLOBALS', 'Off'),
('PHPINI_UPLOAD_MAX_FILESIZE', '10'),
('PORT_AMAVIS', '10024;tcp;AMaVis;0;1;localhost'),
('PORT_DNS', '53;tcp;DNS;1;0;'),
('PORT_FTP', '21;tcp;FTP;1;0;'),
('PORT_HTTP', '80;tcp;HTTP;1;0;'),
('PORT_HTTPS', '443;tcp;HTTPS;0;0;'),
('PORT_IMAP', '143;tcp;IMAP;1;0;'),
('PORT_IMAP-SSL', '993;tcp;IMAP-SSL;0;0;'),
('PORT_POLICYD-WEIGHT', '12525;tcp;POLICYD-WEIGHT;1;1;localhost'),
('PORT_POP3', '110;tcp;POP3;1;0;'),
('PORT_POP3-SSL', '995;tcp;POP3-SSL;0;0;'),
('PORT_POSTGREY', '10023;tcp;POSTGREY;1;1;localhost'),
('PORT_SELITY_DAEMON', '9876;tcp;Selity-Daemon;1;0;127.0.0.1'),
('PORT_SMTP', '25;tcp;SMTP;1;0;'),
('PORT_SMTP-SSL', '465;tcp;SMTP-SSL;0;0;'),
('PORT_SPAMASSASSIN', '783;tcp;SPAMASSASSIN;0;1;localhost'),
('PORT_SSH', '22;tcp;SSH;1;1;'),
('PORT_TELNET', '23;tcp;TELNET;1;0;'),
('PREVENT_EXTERNAL_LOGIN_ADMIN', '1'),
('PREVENT_EXTERNAL_LOGIN_CLIENT', '1'),
('PREVENT_EXTERNAL_LOGIN_RESELLER', '1'),
('ROOT_TEMPLATE_PATH', '/themes/'),
('SELITY_SUPPORT_SYSTEM', '1'),
('SHOW_COMPRESSION_SIZE', '1'),
('TR_GUI_DEBUG', '0'),
('USER_INITIAL_LANG', 'en_GB'),
('USER_INITIAL_THEME', 'omega_original');

-- --------------------------------------------------------

--
-- Table structure for table `custom_menus`
--

CREATE TABLE IF NOT EXISTS `custom_menus` (
  `menu_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menu_level` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `menu_order` int(10) unsigned DEFAULT NULL,
  `menu_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `menu_link` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `menu_target` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `domain_aliasses`
--

CREATE TABLE IF NOT EXISTS `domain_aliasses` (
  `alias_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned DEFAULT NULL,
  `alias_name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alias_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alias_mount` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alias_ip_id` int(10) unsigned DEFAULT NULL,
  `url_forward` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`alias_id`),
  KEY `domain_id` (`admin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `domain_aliasses`
--

INSERT INTO `domain_aliasses` (`alias_id`, `admin_id`, `alias_name`, `alias_status`, `alias_mount`, `alias_ip_id`, `url_forward`) VALUES
(1, 5, 'als.eu.bogus', 'ok', '/als_eu.bogus', 1, 'no'),
(3, 5, 'als2.eu.bogus', 'ok', '/als2.eu.bogus', 1, 'no'),
(7, 5, 'als3.eu.bogus', 'ok', '/als3.eu.bogus', 1, 'http://aa.aa/'),
(8, 5, 'als4.eu.bogus', 'ok', '/als4.eu.bogus', 1, 'no');

-- --------------------------------------------------------

--
-- Table structure for table `domain_dns`
--

CREATE TABLE IF NOT EXISTS `domain_dns` (
  `domain_dns_id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `alias_id` int(11) NOT NULL,
  `domain_dns` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `domain_class` enum('IN','CH','HS') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'IN',
  `domain_type` enum('A','AAAA','CERT','CNAME','DNAME','GPOS','KEY','KX','MX','NAPTR','NSAP','NS','NXT','PTR','PX','SIG','SRV','TXT') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'A',
  `domain_text` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `protected` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`domain_dns_id`),
  UNIQUE KEY `domain_id` (`domain_id`,`alias_id`,`domain_dns`,`domain_class`,`domain_type`,`domain_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `domain_traffic`
--

CREATE TABLE IF NOT EXISTS `domain_traffic` (
  `dtraff_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned DEFAULT NULL,
  `dtraff_time` bigint(20) unsigned DEFAULT NULL,
  `dtraff_web` bigint(20) unsigned DEFAULT NULL,
  `dtraff_ftp` bigint(20) unsigned DEFAULT NULL,
  `dtraff_mail` bigint(20) unsigned DEFAULT NULL,
  `dtraff_pop` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`dtraff_id`),
  KEY `i_domain_id` (`admin_id`),
  KEY `i_dtraff_time` (`dtraff_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `domain_traffic`
--

INSERT INTO `domain_traffic` (`dtraff_id`, `admin_id`, `dtraff_time`, `dtraff_web`, `dtraff_ftp`, `dtraff_mail`, `dtraff_pop`) VALUES
(1, 5, 1428692400, 1182, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `email_tpls`
--

CREATE TABLE IF NOT EXISTS `email_tpls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `email_tpls`
--

INSERT INTO `email_tpls` (`id`, `owner_id`, `name`, `subject`, `message`) VALUES
(1, 2, 'after-order-msg', 'Confirmation for domain order {DOMAIN}!', '\r\nDear {NAME},\r\nThis is an automatic confirmation for the order of the domain:\r\n\r\n{DOMAIN}\r\n\r\nThank you for using Selity services.\r\nThe Selity Team\r\n\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `error_pages`
--

CREATE TABLE IF NOT EXISTS `error_pages` (
  `ep_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `error_401` text COLLATE utf8_unicode_ci NOT NULL,
  `error_403` text COLLATE utf8_unicode_ci NOT NULL,
  `error_404` text COLLATE utf8_unicode_ci NOT NULL,
  `error_500` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ep_id`)
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

--
-- Dumping data for table `ftp_group`
--

INSERT INTO `ftp_group` (`groupname`, `gid`, `members`) VALUES
('test.eu.bogus', 1004, 'udmn@test.eu.bogus,uals@als.eu.bogus');

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

--
-- Dumping data for table `ftp_users`
--

INSERT INTO `ftp_users` (`userid`, `admin_id`, `passwd`, `uid`, `gid`, `shell`, `homedir`) VALUES
('udmn@test.eu.bogus', 5, '$2mTXjg3CcMMc', 1004, 1004, '/bin/sh', '/var/www/virtual/test.eu.bogus'),
('uals@als.eu.bogus', 5, '$6$jtEC9hP4yHijIRck$KBO5IG8e9NhxqFPti5dO2bfXMFQHkLjikoEMa7wkG/cYKQYdGJCiCLAsUZysUvMv3uzalFpnIcgl09noBJFRq/', 1004, 1004, '/bin/sh', '/var/www/virtual/test.eu.bogus/logs');

-- --------------------------------------------------------

--
-- Table structure for table `hosting_plans`
--

CREATE TABLE IF NOT EXISTS `hosting_plans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reseller_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `limits` text COLLATE utf8_unicode_ci,
  `description` text COLLATE utf8_unicode_ci,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `setup_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(10) unsigned NOT NULL DEFAULT '0',
  `tos` blob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `hosting_plans`
--

INSERT INTO `hosting_plans` (`id`, `reseller_id`, `name`, `limits`, `description`, `price`, `setup_fee`, `value`, `payment`, `status`, `tos`) VALUES
(1, 1, 'HP1 admin', 'a:11:{s:6:"hp_dmn";i:-1;s:6:"hp_als";s:2:"11";s:6:"hp_sub";s:2:"10";s:7:"hp_mail";s:2:"12";s:6:"hp_ftp";s:2:"13";s:9:"hp_sql_db";s:2:"14";s:11:"hp_sql_user";s:2:"15";s:8:"hp_traff";s:2:"16";s:7:"hp_disk";s:2:"17";s:6:"hp_php";s:3:"yes";s:6:"hp_cgi";s:2:"no";}', 'HP1 admin desc', '20.00', '21.00', 'Eur', 'Monthly', 1, ''),
(2, 2, 'HP1 reseller', 'a:11:{s:6:"hp_dmn";i:-1;s:6:"hp_als";s:2:"11";s:6:"hp_sub";s:2:"10";s:7:"hp_mail";s:2:"12";s:6:"hp_ftp";s:2:"13";s:9:"hp_sql_db";s:2:"14";s:11:"hp_sql_user";s:2:"15";s:8:"hp_traff";s:2:"16";s:7:"hp_disk";s:2:"17";s:6:"hp_php";s:2:"no";s:6:"hp_cgi";s:3:"yes";}', 'HP1 reseller desc', '0.00', '0.00', 'Eur', 'Monthly', 1, ''),
(3, 1, 'HP2 admin', 'a:11:{s:6:"hp_dmn";i:-1;s:6:"hp_als";s:2:"11";s:6:"hp_sub";s:2:"10";s:7:"hp_mail";s:2:"12";s:6:"hp_ftp";s:2:"13";s:9:"hp_sql_db";s:2:"14";s:11:"hp_sql_user";s:2:"15";s:8:"hp_traff";s:2:"16";s:7:"hp_disk";s:2:"17";s:6:"hp_php";s:2:"no";s:6:"hp_cgi";s:3:"yes";}', 'aaaa', '0.00', '0.00', 'Eur', 'Monthly', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `htaccess`
--

CREATE TABLE IF NOT EXISTS `htaccess` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `group_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `auth_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `auth_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `htaccess`
--

INSERT INTO `htaccess` (`id`, `admin_id`, `user_id`, `group_id`, `auth_type`, `auth_name`, `path`, `status`) VALUES
(1, 5, '2', '0', 'Basic', 'aaa', '/subdmn', 'ok'),
(3, 5, '2,4,5', '0', 'Basic', 'bbb', '/phptmp', 'ok');

-- --------------------------------------------------------

--
-- Table structure for table `htaccess_groups`
--

CREATE TABLE IF NOT EXISTS `htaccess_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ugroup` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `members` text COLLATE utf8_unicode_ci,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `htaccess_groups`
--

INSERT INTO `htaccess_groups` (`id`, `admin_id`, `ugroup`, `members`, `status`) VALUES
(1, 4, 'statistics', '1', 'ok'),
(2, 5, 'statistics', '2,5', 'ok'),
(4, 5, 'group2', '4,5', 'ok'),
(5, 5, 'group3', '4,5,6', 'ok');

-- --------------------------------------------------------

--
-- Table structure for table `htaccess_users`
--

CREATE TABLE IF NOT EXISTS `htaccess_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `uname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `upass` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `htaccess_users`
--

INSERT INTO `htaccess_users` (`id`, `admin_id`, `uname`, `upass`, `status`) VALUES
(1, 4, 'tst.eu.bogus', '$2wN.yMvcks52', 'ok'),
(2, 5, 'test.eu.bogus', '$2cUPHUddgzQY', 'ok'),
(4, 5, 'user1', '$6$S=.RngmrD1IFPAeu$zaA7cPAQ51uMcF4DB9fhtl04oMBPSNVEnA/810TsG9m.Ense1UN1hDcv3lQsBtlagUg/KledB4zIM3XnbsVcJ/', 'ok'),
(5, 5, 'user2', '$6$sE7S^tN9Oe.EWXX[$A0fds4J40Wlk/MAzOB54iMRpnU0FM9M24bJ738xMCFwoPw3o1482sfYfd6hd5q5aiHognWA11T9qkVOkbW19Z/', 'ok'),
(6, 5, 'user3', '$6$iar?zJWft]HYNo={$7yS9JArJuRbZTlyA/C9j.cKgYQiPRAnlSdwJ2TFpoxxI.HfzGEvI.IPu0CPfHGs6c3E0Pf8u39D4KFIq4RAQ10', 'ok');

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
  `mail_acc` text COLLATE utf8_unicode_ci,
  `mail_pass` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail_forward` text COLLATE utf8_unicode_ci,
  `admin_id` int(10) unsigned DEFAULT NULL,
  `mail_type` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sub_id` int(10) unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail_auto_respond` tinyint(1) NOT NULL DEFAULT '0',
  `mail_auto_respond_text` text COLLATE utf8_unicode_ci,
  `quota` int(10) DEFAULT '104857600',
  `mail_addr` varchar(254) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`mail_id`),
  KEY `domain_id` (`admin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `mail_users`
--

INSERT INTO `mail_users` (`mail_id`, `mail_acc`, `mail_pass`, `mail_forward`, `admin_id`, `mail_type`, `sub_id`, `status`, `mail_auto_respond`, `mail_auto_respond_text`, `quota`, `mail_addr`) VALUES
(8, 'nals', 'test12', '_no_', 5, 'alias_mail', 1, 'ok', 0, '', 104857600, 'nals@als.eu.bogus'),
(11, 'nsals', 'test12', '_no_', 5, 'alssub_mail', 1, 'ok', 0, '', 104857600, 'nsals@alssub.als.eu.bogus'),
(13, 'nfals', 'test12', 'b@aa.aa', 5, 'alias_mail,alias_forward', 1, 'ok', 0, '', 104857600, 'nfals@als.eu.bogus'),
(15, 'nfsals', 'test12', 'd@aa.aa', 5, 'alssub_mail,alssub_forward', 1, 'ok', 0, '', 104857600, 'nfsals@alssub.als.eu.bogus'),
(17, 'fals', '_no_', 'b@aa.aa', 5, 'alias_forward', 1, 'ok', 0, 'hfg hhf gf hf', 104857600, 'fals@als.eu.bogus'),
(19, 'fsals', '_no_', 'd@aa.aa', 5, 'alssub_forward', 1, 'ok', 0, '', 104857600, 'nsals@alssub.als.eu.bogus');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `plan_id` int(10) unsigned NOT NULL DEFAULT '0',
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  `domain_name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_id` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fname` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lname` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firm` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street1` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street2` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_settings`
--

CREATE TABLE IF NOT EXISTS `orders_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `header` text COLLATE utf8_unicode_ci,
  `footer` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `orders_settings`
--

INSERT INTO `orders_settings` (`id`, `user_id`, `header`, `footer`) VALUES
(1, 2, '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n"http://www.w3.org/TR/html4/loose.dtd">\r\n<html>\r\n <head>\r\n  <meta http-equiv="Content-Type" content="text/html; charset=encoding">\r\n  <link href="../themes/omega_original/css/selity_orderpanel.css" rel="stylesheet" type="text/css">\r\n  <title>Selity - Order Panel</title>\r\n </head>\r\n <body>\r\n  <div align="center">\r\n   <table width="100%" height="95%">\r\n	<tr align="center">\r\n	 <td align="center">\r\n			', '	 </td>\r\n	</tr>\r\n   </table>\r\n  </div>\r\n </body>\r\n</html>\r\n			');

-- --------------------------------------------------------

--
-- Table structure for table `php_ini`
--

CREATE TABLE IF NOT EXISTS `php_ini` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(10) NOT NULL,
  `status` varchar(55) COLLATE utf8_unicode_ci NOT NULL,
  `disable_functions` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'show_source, system, shell_exec, passthru, exec, phpinfo, shell, symlink, popen, proc_open',
  `allow_url_fopen` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Off',
  `register_globals` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Off',
  `display_errors` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Off',
  `error_reporting` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'E_ALL & ~E_DEPRECATED',
  `post_max_size` int(11) NOT NULL DEFAULT '10',
  `upload_max_filesize` int(11) NOT NULL DEFAULT '10',
  `max_execution_time` int(11) NOT NULL DEFAULT '30',
  `max_input_time` int(11) NOT NULL DEFAULT '60',
  `memory_limit` int(11) NOT NULL DEFAULT '128',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `plugin`
--

CREATE TABLE IF NOT EXISTS `plugin` (
  `plugin_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `plugin_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `plugin_info` text COLLATE utf8_unicode_ci NOT NULL,
  `plugin_config` text COLLATE utf8_unicode_ci,
  `plugin_status` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'disabled',
  PRIMARY KEY (`plugin_id`),
  UNIQUE KEY `name` (`plugin_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotalimits`
--

CREATE TABLE IF NOT EXISTS `quotalimits` (
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `quota_type` enum('user','group','class','all') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user',
  `per_session` enum('false','true') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'false',
  `limit_type` enum('soft','hard') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'soft',
  `bytes_in_avail` float NOT NULL DEFAULT '0',
  `bytes_out_avail` float NOT NULL DEFAULT '0',
  `bytes_xfer_avail` float NOT NULL DEFAULT '0',
  `files_in_avail` int(10) unsigned NOT NULL DEFAULT '0',
  `files_out_avail` int(10) unsigned NOT NULL DEFAULT '0',
  `files_xfer_avail` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `quotalimits`
--

INSERT INTO `quotalimits` (`name`, `quota_type`, `per_session`, `limit_type`, `bytes_in_avail`, `bytes_out_avail`, `bytes_xfer_avail`, `files_in_avail`, `files_out_avail`, `files_xfer_avail`) VALUES
('test.eu.bogus', 'group', 'false', 'hard', 18874400, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `quotatallies`
--

CREATE TABLE IF NOT EXISTS `quotatallies` (
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `quota_type` enum('user','group','class','all') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user',
  `bytes_in_used` float NOT NULL DEFAULT '0',
  `bytes_out_used` float NOT NULL DEFAULT '0',
  `bytes_xfer_used` float NOT NULL DEFAULT '0',
  `files_in_used` int(10) unsigned NOT NULL DEFAULT '0',
  `files_out_used` int(10) unsigned NOT NULL DEFAULT '0',
  `files_xfer_used` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `quotatallies`
--

INSERT INTO `quotatallies` (`name`, `quota_type`, `bytes_in_used`, `bytes_out_used`, `bytes_xfer_used`, `files_in_used`, `files_out_used`, `files_xfer_used`) VALUES
('test.eu.bogus', 'group', 4, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `quota_dovecot`
--

CREATE TABLE IF NOT EXISTS `quota_dovecot` (
  `username` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `bytes` bigint(20) NOT NULL DEFAULT '0',
  `messages` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reseller_props`
--

CREATE TABLE IF NOT EXISTS `reseller_props` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reseller_id` int(10) unsigned NOT NULL,
  `current_usr_cnt` int(11) DEFAULT '0',
  `max_usr_cnt` int(11) NOT NULL DEFAULT '-1',
  `current_sub_cnt` int(11) NOT NULL DEFAULT '0',
  `max_sub_cnt` int(11) NOT NULL DEFAULT '-1',
  `current_als_cnt` int(11) NOT NULL DEFAULT '0',
  `max_als_cnt` int(11) NOT NULL DEFAULT '-1',
  `current_mail_cnt` int(11) NOT NULL DEFAULT '0',
  `max_mail_cnt` int(11) NOT NULL DEFAULT '-1',
  `current_ftp_cnt` int(11) NOT NULL DEFAULT '0',
  `max_ftp_cnt` int(11) NOT NULL DEFAULT '-1',
  `current_sql_db_cnt` int(11) NOT NULL DEFAULT '0',
  `max_sql_db_cnt` int(11) NOT NULL DEFAULT '-1',
  `current_sql_user_cnt` int(11) NOT NULL DEFAULT '0',
  `max_sql_user_cnt` int(11) NOT NULL DEFAULT '-1',
  `current_disk_amnt` int(11) NOT NULL DEFAULT '0',
  `max_disk_amnt` int(11) NOT NULL DEFAULT '-1',
  `current_traff_amnt` int(11) NOT NULL DEFAULT '0',
  `max_traff_amnt` int(11) NOT NULL DEFAULT '-1',
  `php` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `cgi` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `support_system` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `customer_id` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reseller_ips` text COLLATE utf8_unicode_ci,
  `software_allowed` varchar(15) CHARACTER SET utf8 NOT NULL DEFAULT 'no',
  `softwaredepot_allowed` varchar(15) CHARACTER SET utf8 NOT NULL DEFAULT 'no',
  `websoftwaredepot_allowed` varchar(15) CHARACTER SET utf8 NOT NULL DEFAULT 'no',
  `php_ini_system` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `php_ini_al_disable_functions` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `php_ini_al_allow_url_fopen` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `php_ini_al_register_globals` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `php_ini_al_display_errors` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `php_ini_max_post_max_size` int(11) NOT NULL DEFAULT '0',
  `php_ini_max_upload_max_filesize` int(11) NOT NULL DEFAULT '0',
  `php_ini_max_max_execution_time` int(11) NOT NULL DEFAULT '0',
  `php_ini_max_max_input_time` int(11) NOT NULL DEFAULT '0',
  `php_ini_max_memory_limit` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `reseller_id` (`reseller_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `reseller_props`
--

INSERT INTO `reseller_props` (`id`, `reseller_id`, `current_usr_cnt`, `max_usr_cnt`, `current_sub_cnt`, `max_sub_cnt`, `current_als_cnt`, `max_als_cnt`, `current_mail_cnt`, `max_mail_cnt`, `current_ftp_cnt`, `max_ftp_cnt`, `current_sql_db_cnt`, `max_sql_db_cnt`, `current_sql_user_cnt`, `max_sql_user_cnt`, `current_disk_amnt`, `max_disk_amnt`, `current_traff_amnt`, `max_traff_amnt`, `php`, `cgi`, `support_system`, `customer_id`, `reseller_ips`, `software_allowed`, `softwaredepot_allowed`, `websoftwaredepot_allowed`, `php_ini_system`, `php_ini_al_disable_functions`, `php_ini_al_allow_url_fopen`, `php_ini_al_register_globals`, `php_ini_al_display_errors`, `php_ini_max_post_max_size`, `php_ini_max_upload_max_filesize`, `php_ini_max_max_execution_time`, `php_ini_max_max_input_time`, `php_ini_max_memory_limit`) VALUES
(1, 2, 2, 0, 11, 0, 12, 0, 13, 0, 14, 0, 15, 0, 16, 0, 18, 0, 17, 0, 'no', 'no', 'yes', '', 'a:1:{i:0;i:1;}', 'no', 'yes', 'yes', 'no', 'no', 'no', 'no', 'no', 0, 0, 0, 0, 0),
(2, 3, 0, 10, 0, 11, 0, 12, 0, 13, 0, 14, 0, 15, 0, 16, 0, 18, 0, 17, 'no', 'no', 'yes', '', '1;', 'no', 'yes', 'yes', 'no', 'no', 'no', 'no', 'no', 0, 0, 0, 0, 0),
(11, 28, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'yes', 'yes', 'yes', NULL, 'a:2:{i:0;s:1:"2";i:1;s:1:"1";}', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `server_ips`
--

CREATE TABLE IF NOT EXISTS `server_ips` (
  `ip_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip_number` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_domain` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_alias` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_card` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_ssl_domain_id` int(10) DEFAULT NULL,
  `ip_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ip_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `server_ips`
--

INSERT INTO `server_ips` (`ip_id`, `ip_number`, `ip_domain`, `ip_alias`, `ip_card`, `ip_ssl_domain_id`, `ip_status`) VALUES
(1, '192.168.0.2', 'daniel.eu.bogus', 'daniel', NULL, NULL, 'ok'),
(2, '192.168.0.103', NULL, NULL, NULL, NULL, 'ok');

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

--
-- Dumping data for table `server_traffic`
--

INSERT INTO `server_traffic` (`straff_id`, `traff_time`, `bytes_in`, `bytes_out`, `bytes_mail_in`, `bytes_mail_out`, `bytes_pop_in`, `bytes_pop_out`, `bytes_web_in`, `bytes_web_out`) VALUES
(1, 1428683400, 3699915, 3669743, 0, 0, 0, 0, 244510, 3387145),
(2, 1428685200, 2112403, 1984453, 0, 0, 0, 0, 573491, 1116001),
(3, 1428687000, 1357258, 1302442, 0, 0, 0, 0, 363053, 756738),
(4, 1428688800, 4245377, 4209377, 0, 0, 0, 0, 363524, 3674159),
(5, 1428690600, 1270836, 819527, 0, 0, 0, 0, 216996, 270552),
(6, 1428692400, 3176896, 3039288, 0, 0, 0, 0, 352054, 2454249),
(7, 1428694200, 4438112, 4404592, 0, 0, 0, 0, 182090, 4183940),
(8, 1428696000, 458954, 449805, 0, 0, 0, 0, 40357, 389831),
(9, 1428697800, 3241026, 3209639, 0, 0, 0, 0, 108965, 3075709),
(10, 1428699600, 1598033, 1391132, 0, 0, 0, 0, 310588, 1015866),
(11, 1428701400, 2590258, 2583899, 0, 0, 0, 0, 300339, 2258135),
(12, 1428703200, 2096713, 2088275, 0, 0, 0, 0, 202124, 1856691),
(13, 1428705000, 1869585, 1857884, 0, 0, 0, 0, 675804, 1009033),
(14, 1428706800, 507336, 495358, 0, 0, 0, 0, 202711, 271189),
(15, 1428733800, 39904, 37952, 0, 0, 0, 0, 10525, 21149),
(16, 1428735600, 3366007, 3307139, 0, 0, 0, 0, 180436, 3086070),
(17, 1428737400, 4294656, 3887106, 0, 0, 0, 0, 243822, 3545787),
(18, 1428768000, 3296145, 2946010, 0, 0, 0, 0, 278215, 2515681),
(19, 1428769800, 1953744, 1930370, 0, 0, 0, 0, 378832, 1479890),
(20, 1428771600, 650733, 634085, 0, 0, 0, 0, 132632, 456104),
(21, 1428773400, 394481, 321804, 0, 0, 0, 0, 127708, 154841),
(22, 1428775200, 2476856, 2420652, 0, 0, 0, 0, 179597, 2197027),
(23, 1428777000, 494188, 482471, 0, 0, 0, 0, 209079, 240362),
(24, 1428778800, 3773186, 3691362, 0, 0, 0, 0, 326700, 3287800),
(25, 1428780600, 522215, 472583, 0, 0, 0, 0, 210178, 215371),
(26, 1428782400, 271929, 265510, 0, 0, 0, 0, 129284, 103731),
(27, 1428784200, 2430867, 2423946, 0, 0, 0, 0, 175048, 2212669),
(28, 1428786000, 3316295, 3309479, 0, 0, 0, 0, 571158, 2692206),
(29, 1428787800, 2259415, 2235757, 0, 0, 0, 0, 522920, 1662036),
(30, 1428789600, 1876144, 1163479, 0, 0, 0, 0, 310837, 592669),
(31, 1428850800, 391798, 323501, 0, 0, 0, 0, 96717, 153260),
(32, 1428852600, 3761044, 3711969, 0, 0, 0, 0, 237341, 3309527),
(33, 1428865200, 3939663, 3753991, 0, 0, 0, 0, 245998, 3236626),
(34, 1428867000, 532541, 472535, 0, 0, 0, 0, 145081, 144035),
(35, 1428868800, 407700, 372179, 0, 0, 0, 0, 93560, 95530),
(36, 1428874200, 115135, 112927, 0, 0, 0, 0, 32976, 26941),
(37, 1428876000, 2981143, 2965010, 0, 0, 0, 0, 305978, 2447769),
(38, 1428877800, 3873576, 1342968, 0, 0, 0, 0, 51099, 85081),
(39, 1428913800, 2856030, 1342511, 0, 0, 0, 0, 3233, 4980),
(40, 1428915600, 1472204, 383724, 0, 0, 0, 0, 3035, 4876),
(41, 1428944400, 1402949, 585508, 0, 0, 0, 0, 0, 0),
(42, 1428948000, 75618741, 3422607, 0, 0, 0, 0, 2922, 23412),
(43, 1428949800, 3316040, 3285553, 0, 0, 0, 0, 67847, 3194290),
(44, 1428951600, 891665, 770377, 0, 0, 0, 0, 70633, 638759),
(45, 1428953400, 11486563, 2431587, 0, 0, 0, 0, 0, 0),
(46, 1428955200, 14618707, 4110222, 0, 0, 0, 0, 63037, 2133268),
(47, 1428957000, 1847085, 1782113, 0, 0, 0, 0, 103887, 1508630),
(48, 1428958800, 1771799, 1702558, 0, 0, 0, 0, 178500, 1361798),
(49, 1428960600, 1102983, 1087295, 0, 0, 0, 0, 485582, 437650),
(50, 1428998400, 12397543, 3059839, 0, 0, 0, 0, 210747, 1378557),
(51, 1429000200, 20339, 16979, 0, 0, 0, 0, 0, 0),
(52, 1429002000, 23036, 16073, 0, 0, 0, 0, 0, 0),
(53, 1429003800, 398308, 120776, 0, 0, 0, 0, 0, 0),
(54, 1429005600, 18128, 19666, 0, 0, 0, 0, 0, 0),
(55, 1429007400, 2686658, 2680249, 0, 0, 0, 0, 281148, 2376464),
(56, 1429014600, 545280, 470943, 0, 0, 0, 0, 213561, 183822),
(57, 1429016400, 999410, 454707, 0, 0, 0, 0, 123635, 214003),
(58, 1429018200, 69003, 24373, 0, 0, 0, 0, 0, 0),
(59, 1429020000, 76286, 60570, 0, 0, 0, 0, 10722, 24830),
(60, 1429021800, 145101, 104588, 0, 0, 0, 0, 21356, 56310),
(61, 1429023600, 133210, 126509, 0, 0, 0, 0, 25028, 69804),
(62, 1429025400, 257500, 246329, 0, 0, 0, 0, 93453, 132294),
(63, 1429027200, 2711939, 2703947, 0, 0, 0, 0, 177587, 2494298),
(64, 1429029000, 616259, 606927, 0, 0, 0, 0, 157403, 424957),
(65, 1429030800, 2084066, 2084862, 0, 0, 0, 0, 35790, 2030344),
(66, 1429032600, 7898994, 4811544, 0, 0, 0, 0, 150314, 3594937),
(67, 1429034400, 395683, 390394, 0, 0, 0, 0, 103902, 261581),
(68, 1429036200, 3876838, 3151233, 0, 0, 0, 0, 221362, 2661971),
(69, 1429038000, 899728, 823317, 0, 0, 0, 0, 337182, 439902),
(70, 1429039800, 503074, 503524, 0, 0, 0, 0, 170118, 303824),
(71, 1429041600, 2386602, 2225862, 0, 0, 0, 0, 78269, 2101800),
(72, 1429043400, 964502, 942834, 0, 0, 0, 0, 196079, 720401),
(73, 1429045200, 1164820, 494769, 0, 0, 0, 0, 45114, 214158),
(74, 1429047000, 119443, 99889, 0, 0, 0, 0, 21334, 56873),
(75, 1429048800, 145028, 140024, 0, 0, 0, 0, 42494, 78565),
(76, 1429050600, 2987015, 2977632, 0, 0, 0, 0, 257926, 2699834),
(77, 1429088400, 5622639, 799079, 0, 0, 0, 0, 0, 0),
(78, 1429090200, 9694508, 2034610, 0, 0, 0, 0, 4381, 7177),
(79, 1429092000, 3640498, 2747085, 0, 0, 0, 0, 89292, 2372189),
(80, 1429093800, 179802, 151843, 0, 0, 0, 0, 15606, 109538),
(81, 1429104600, 1313, 1313, 0, 0, 0, 0, 0, 0),
(82, 1429126200, 122960833, 7202806, 0, 0, 0, 0, 491004, 2612104),
(83, 1429128000, 93928, 80463, 0, 0, 0, 0, 30624, 34139),
(84, 1429129800, 2663940, 2628816, 0, 0, 0, 0, 199445, 2374987),
(85, 1429131600, 1235409, 1063001, 0, 0, 0, 0, 121751, 779183),
(86, 1429133400, 1249796, 964143, 0, 0, 0, 0, 273698, 543584),
(87, 1429135200, 2415955, 2400239, 0, 0, 0, 0, 170567, 2207885),
(88, 1429137000, 325541, 317374, 0, 0, 0, 0, 141381, 156885),
(89, 1429138800, 3010615, 1341485, 0, 0, 0, 0, 38761, 1055603),
(90, 1429169400, 10347893, 926843, 0, 0, 0, 0, 0, 0),
(91, 1429171200, 162155965, 5042639, 0, 0, 0, 0, 0, 0),
(92, 1429173000, 2465061, 2382637, 0, 0, 0, 0, 84888, 2262233),
(93, 1429174800, 293581, 225573, 0, 0, 0, 0, 6283, 186478),
(94, 1429176600, 451398, 441291, 0, 0, 0, 0, 25849, 396709),
(95, 1429178400, 30809160, 3144012, 0, 0, 0, 0, 54985, 2322072),
(96, 1429180200, 3863886, 3748018, 0, 0, 0, 0, 111794, 3529242),
(97, 1429182000, 23953, 16049, 0, 0, 0, 0, 0, 0),
(98, 1429183800, 2976056, 2769497, 0, 0, 0, 0, 88408, 2589035),
(99, 1429185600, 1143380, 1120379, 0, 0, 0, 0, 105418, 996891),
(100, 1429187400, 307894, 294822, 0, 0, 0, 0, 43888, 231308),
(101, 1429189200, 1562872, 688114, 0, 0, 0, 0, 213314, 267878),
(102, 1429191000, 1038894, 535952, 0, 0, 0, 0, 59755, 197487),
(103, 1429192800, 73591, 66412, 0, 0, 0, 0, 11393, 39291),
(104, 1429194600, 235683, 222895, 0, 0, 0, 0, 47699, 153288),
(105, 1429201800, 167413, 165630, 0, 0, 0, 0, 50618, 105495),
(106, 1429203600, 301349, 271865, 0, 0, 0, 0, 72555, 182556),
(107, 1429205400, 2271910, 2273174, 0, 0, 0, 0, 74785, 2181071),
(108, 1429207200, 68852, 61221, 0, 0, 0, 0, 11452, 31619),
(109, 1429209000, 2800778, 2712679, 0, 0, 0, 0, 111649, 2558478),
(110, 1429210800, 655713, 271708, 0, 0, 0, 0, 36550, 96039),
(111, 1429212600, 3532076, 3335460, 0, 0, 0, 0, 132420, 3119293),
(112, 1429214400, 3879369, 3769127, 0, 0, 0, 0, 484369, 3256518),
(113, 1429216200, 564074, 554975, 0, 0, 0, 0, 38162, 500306);

-- --------------------------------------------------------

--
-- Table structure for table `sql_database`
--

CREATE TABLE IF NOT EXISTS `sql_database` (
  `sqld_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned DEFAULT '0',
  `sqld_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'n/a',
  PRIMARY KEY (`sqld_id`),
  KEY `domain_id` (`admin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `sql_database`
--

INSERT INTO `sql_database` (`sqld_id`, `admin_id`, `sqld_name`) VALUES
(1, 5, 'sqldb1'),
(2, 5, 'sqldb2'),
(3, 5, 'sqldb3'),
(6, 5, 'sqldb4'),
(7, 5, 'sqldb5');

-- --------------------------------------------------------

--
-- Table structure for table `sql_user`
--

CREATE TABLE IF NOT EXISTS `sql_user` (
  `sqlu_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sqld_id` int(10) unsigned DEFAULT '0',
  `sqlu_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT 'n/a',
  `sqlu_pass` varchar(64) COLLATE utf8_unicode_ci DEFAULT 'n/a',
  PRIMARY KEY (`sqlu_id`),
  KEY `sqld_id` (`sqld_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `sql_user`
--

INSERT INTO `sql_user` (`sqlu_id`, `sqld_id`, `sqlu_name`, `sqlu_pass`) VALUES
(1, 1, 'sqlusr1', 'sqlusr1'),
(2, 2, 'sqlusr2', 'sqlusr22'),
(3, 3, 'sqlusr2', 'sqlusr22'),
(4, 3, 'sqlusr1', 'sqlusr1'),
(7, 7, 'sqlusr1', 'sqlusr1'),
(8, 7, 'sqlusr2', 'sqlusr22'),
(9, 7, 'sqlusr3', 'sqlusr3');

-- --------------------------------------------------------

--
-- Table structure for table `ssl_certs`
--

CREATE TABLE IF NOT EXISTS `ssl_certs` (
  `cert_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(10) NOT NULL,
  `type` enum('dmn','als','sub','alssub') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'dmn',
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

--
-- Dumping data for table `straff_settings`
--

INSERT INTO `straff_settings` (`straff_max`, `straff_warn`, `straff_email`) VALUES
(0, 0, 0),
(0, 0, 0),
(0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `subdomain_alias`
--

CREATE TABLE IF NOT EXISTS `subdomain_alias` (
  `subdomain_alias_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alias_id` int(10) unsigned DEFAULT NULL,
  `subdomain_alias_name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subdomain_alias_mount` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subdomain_alias_url_forward` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subdomain_alias_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`subdomain_alias_id`),
  KEY `alias_id` (`alias_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `subdomain_alias`
--

INSERT INTO `subdomain_alias` (`subdomain_alias_id`, `alias_id`, `subdomain_alias_name`, `subdomain_alias_mount`, `subdomain_alias_url_forward`, `subdomain_alias_status`) VALUES
(1, 1, 'alssub', '/als_eu.bogus/alssub', NULL, 'ok'),
(2, 1, 'alssub2', '/als_eu.bogus/alssub2', 'no', 'ok'),
(5, 3, 'alssub3', '/als2.eu.bogus/alssub3', 'no', 'ok');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`ticket_id`, `ticket_level`, `ticket_from`, `ticket_to`, `ticket_status`, `ticket_reply`, `ticket_urgency`, `ticket_date`, `ticket_subject`, `ticket_message`) VALUES
(1, 1, 5, 2, 0, 0, 1, 1428691990, 'fgdf gdfg', ' gdf gdfgdfgdfg '),
(2, NULL, 5, 2, 1, 1, 1, 1428692010, 'fgdf gdfg', 'hfg fhg hf ghfgh fhg'),
(3, 1, 5, 2, 1, 0, 1, 1428692052, ' fhg fgh', 'fghfgh'),
(4, 1, 5, 2, 1, 0, 1, 1428699431, 'nbvn', 'bvncvbn'),
(5, 2, 2, 1, 2, 0, 1, 1428957093, 'gdfg df', 'd fgdfgdfg ');

-- --------------------------------------------------------

--
-- Table structure for table `user_gui_props`
--

CREATE TABLE IF NOT EXISTS `user_gui_props` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `lang` varchar(5) COLLATE utf8_unicode_ci DEFAULT '',
  `layout` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `layout_color` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `show_main_menu_labels` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_gui_props`
--

INSERT INTO `user_gui_props` (`id`, `user_id`, `lang`, `layout`, `layout_color`, `logo`, `show_main_menu_labels`) VALUES
(1, 2, 'ro_RO', 'omega_original', NULL, 'reseller1.png', 1),
(2, 3, 'lang_', 'omega_original', NULL, '0', 1),
(3, 4, 'ro_RO', 'omega_original', NULL, '', 1),
(4, 5, 'lang_', 'omega_original', NULL, '', 1),
(5, 1, 'en_GB', 'omega_original', NULL, '', 1),
(14, 28, 'en_GB', 'default', NULL, '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_system_props`
--

CREATE TABLE IF NOT EXISTS `user_system_props` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_gid` int(10) unsigned NOT NULL DEFAULT '0',
  `user_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `user_admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_created_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_created` int(10) unsigned NOT NULL DEFAULT '0',
  `user_expires` int(10) unsigned NOT NULL DEFAULT '0',
  `user_last_modified` int(10) unsigned NOT NULL DEFAULT '0',
  `user_mailacc_limit` int(11) DEFAULT NULL,
  `user_ftpacc_limit` int(11) DEFAULT NULL,
  `user_traffic_limit` bigint(20) DEFAULT NULL,
  `user_sqld_limit` int(11) DEFAULT NULL,
  `user_sqlu_limit` int(11) DEFAULT NULL,
  `user_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_alias_limit` int(11) DEFAULT NULL,
  `user_subd_limit` int(11) DEFAULT NULL,
  `user_ip_id` int(10) unsigned DEFAULT NULL,
  `user_disk_limit` bigint(20) unsigned DEFAULT NULL,
  `user_disk_usage` bigint(20) unsigned DEFAULT NULL,
  `user_php` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_cgi` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `allowbackup` varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'full',
  `user_dns` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `user_software_allowed` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `phpini_perm_system` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `phpini_perm_register_globals` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `phpini_perm_allow_url_fopen` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `phpini_perm_display_errors` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `phpini_perm_disable_functions` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `i_user_admin_id` (`user_admin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_system_props`
--

INSERT INTO `user_system_props` (`id`, `user_gid`, `user_uid`, `user_admin_id`, `user_created_id`, `user_created`, `user_expires`, `user_last_modified`, `user_mailacc_limit`, `user_ftpacc_limit`, `user_traffic_limit`, `user_sqld_limit`, `user_sqlu_limit`, `user_status`, `user_alias_limit`, `user_subd_limit`, `user_ip_id`, `user_disk_limit`, `user_disk_usage`, `user_php`, `user_cgi`, `allowbackup`, `user_dns`, `user_software_allowed`, `phpini_perm_system`, `phpini_perm_register_globals`, `phpini_perm_allow_url_fopen`, `phpini_perm_display_errors`, `phpini_perm_disable_functions`) VALUES
(1, 1003, 1003, 4, 2, 1428683696, 0, 1429132539, 0, 0, 0, 0, 0, 'ok', 0, 0, 1, 0, 0, 'yes', 'no', 'full', 'no', 'no', 'no', 'no', 'no', 'no', 'no'),
(2, 1004, 1004, 5, 2, 1428683762, 0, 0, 13, 14, 17, 15, 16, 'ok', 12, 11, 1, 18, 0, 'yes', 'yes', 'full', 'no', 'no', 'no', 'no', 'no', 'no', 'no');

