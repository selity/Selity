<?php
/**
 * Selity - A server control panel
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2008 by ispCP | http://isp-control.net
 * @copyright	2012-2015 by Selity
 * @link 		http://selity.org
 * @author 		ispCP Team
 *
 * @license
 *   This program is free software; you can redistribute it and/or modify it under
 *   the terms of the MPL General Public License as published by the Free Software
 *   Foundation; either version 1.1 of the License, or (at your option) any later
 *   version.
 *   You should have received a copy of the MPL Mozilla Public License along with
 *   this program; if not, write to the Open Source Initiative (OSI)
 *   http://opensource.org | osi@opensource.org
 */

require '../include/selity-lib.php';

check_login(__FILE__);

$cfg = configs::getInstance();
$tpl = template::getInstance();

$theme_color = $cfg->USER_INITIAL_THEME;

$tpl->saveVariable(
	array(
		'TR_PAGE_TITLE'		=> tr('Selity - Admin/Settings'),
		'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
		'THEME_CHARSET'		=> tr('encoding'),
		//'ISP_LOGO'		=> get_logo($_SESSION['user_id'])
	)
);

if (array_key_exists('Submit', $_POST)) {
	$lostpassword 						= $_POST['lostpassword'];
	$lostpassword_timeout 				= clean_input($_POST['lostpassword_timeout']);
	$passwd_chars 						= clean_input($_POST['passwd_chars']);
	$passwd_strong 						= $_POST['passwd_strong'];
	$bruteforce 						= $_POST['bruteforce'];
	$bruteforce_between 				= $_POST['bruteforce_between'];
	$bruteforce_max_login				= clean_input($_POST['bruteforce_max_login']);
	$bruteforce_block_time 				= clean_input($_POST['bruteforce_block_time']);
	$bruteforce_between_time 			= clean_input($_POST['bruteforce_between_time']);
	$bruteforce_max_capcha				= clean_input($_POST['bruteforce_max_capcha']);
	$create_default_email_addresses 	= $_POST['create_default_email_addresses'];
	$hard_mail_suspension 				= $_POST['hard_mail_suspension'];
	$user_initial_lang 					= $_POST['def_language'];
	$support_system 					= $_POST['support_system'];
	$hosting_plan_level					= $_POST['hosting_plan_level'];
	$domain_rows_per_page 				= clean_input($_POST['domain_rows_per_page']);
	$checkforupdate						= $_POST['checkforupdate'];
	$gui_debug							= $_POST['gui_debug'];
	// change Loglevel to constant:
	switch ($_POST['log_level']) {
		case 'E_USER_NOTICE':
			$log_level = E_USER_NOTICE;
			break;
		case 'E_USER_WARNING':
			$log_level = E_USER_WARNING;
			break;
		case 'E_USER_ERROR':
			$log_level = E_USER_ERROR;
			break;
		default:
			$log_level = E_USER_OFF;
	} // switch

	if ((!is_number($lostpassword_timeout)) OR (!is_number($passwd_chars))
			OR (!is_number($bruteforce_max_login)) OR (!is_number($bruteforce_block_time))
			OR (!is_number($bruteforce_between_time)) OR (!is_number($bruteforce_max_capcha))
			OR (!is_number($domain_rows_per_page))) {
		$tpl->addMessage(tr('ERROR: Only positive numbers are allowed!'));
	} else if ($domain_rows_per_page < 1) {
		$domain_rows_per_page = 1;
	} else {
		$cfg->LOSTPASSWORD						= $lostpassword;
		$cfg->LOSTPASSWORD_TIMEOUT				= $lostpassword_timeout;
		$cfg->PASSWD_CHARS						= $passwd_chars;
		$cfg->PASSWD_STRONG						= $passwd_strong;
		$cfg->BRUTEFORCE						= $bruteforce;
		$cfg->BRUTEFORCE_BETWEEN				= $bruteforce_between;
		$cfg->BRUTEFORCE_MAX_LOGIN				= $bruteforce_max_login;
		$cfg->BRUTEFORCE_BLOCK_TIME				= $bruteforce_block_time;
		$cfg->BRUTEFORCE_BETWEEN_TIME			= $bruteforce_between_time;
		$cfg->BRUTEFORCE_MAX_CAPTCHA			= $bruteforce_max_capcha;
		$cfg->CREATE_DEFAULT_EMAIL_ADDRESSES	= $create_default_email_addresses;
		$cfg->HARD_MAIL_SUSPENSION				= $hard_mail_suspension;
		$cfg->USER_INITIAL_LANG					= $user_initial_lang;
		$cfg->SELITY_SUPPORT_SYSTEM				= $support_system;
		$cfg->HOSTING_PLANS_LEVEL				= $hosting_plan_level;
		$cfg->DOMAIN_ROWS_PER_PAGE				= $domain_rows_per_page;
		$cfg->LOG_LEVEL							= $log_level;
		$cfg->CHECK_FOR_UPDATES					= $checkforupdate;
		$cfg->GUI_DEBUG							= $gui_debug;
		$cfg->saveAll();
		$tpl->addMessage(tr('Settings saved!'));
	}
}

$tpl->saveVariable(
	array(
		'LOSTPASSWORD_TIMEOUT_VALUE'	=> $cfg->LOSTPASSWORD_TIMEOUT,
		'PASSWD_CHARS'					=> $cfg->PASSWD_CHARS,
		'BRUTEFORCE_MAX_LOGIN_VALUE'	=> $cfg->BRUTEFORCE_MAX_LOGIN,
		'BRUTEFORCE_BLOCK_TIME_VALUE'	=> $cfg->BRUTEFORCE_BLOCK_TIME,
		'BRUTEFORCE_BETWEEN_TIME_VALUE'	=> $cfg->BRUTEFORCE_BETWEEN_TIME,
		'BRUTEFORCE_MAX_CAPTCHA'		=> $cfg->BRUTEFORCE_MAX_CAPTCHA,
		'DOMAIN_ROWS_PER_PAGE'			=> $cfg->DOMAIN_ROWS_PER_PAGE
	)
);
$default_language = $cfg->USER_INITIAL_LANG;
$lng = selity_language::getInstance();
$languageList = $lng->getDisponibleLanguages();
$old_language = $_SESSION['user_def_lang'];
$list = array();
asort($languageList, SORT_STRING);
foreach ($languageList as $lang) {
	$lng->setLanguage($lang);
	$list[] = array('LANG_VALUE' => $lang, 'LANG_NAME'=>gettext('Localised language'), 'LANG_SELECTED' => $lang == $default_language ? 'selected' : '');
}
$tpl->saveRepeats(array('LANGUAGE'=>$list));
$lng->setLanguage($old_language);

$tpl->saveVariable(array(
	'LOSTPASSWORD_SELECTED_ON'			=> $cfg->LOSTPASSWORD ? 'selected' : '',
	'LOSTPASSWORD_SELECTED_OFF'			=> $cfg->LOSTPASSWORD ? '' : 'selected',

	'PASSWD_STRONG_ON'					=> $cfg->PASSWD_STRONG ? 'selected' : '',
	'PASSWD_STRONG_OFF'					=> $cfg->PASSWD_STRONG ? '' : 'selected',

	'BRUTEFORCE_SELECTED_ON'			=> $cfg->BRUTEFORCE ? 'selected' : '',
	'BRUTEFORCE_SELECTED_OFF'			=> $cfg->BRUTEFORCE ? '' : 'selected',

	'LOSTPASSWORD_SELECTED_ON'			=> $cfg->LOSTPASSWORD ? 'selected' : '',
	'LOSTPASSWORD_SELECTED_OFF'			=> $cfg->LOSTPASSWORD ? '' : 'selected',

	'BRUTEFORCE_BETWEEN_SELECTED_ON'	=> $cfg->BRUTEFORCE_BETWEEN ? 'selected' : '',
	'BRUTEFORCE_BETWEEN_SELECTED_OFF'	=> $cfg->BRUTEFORCE_BETWEEN ? '' : 'selected',

	'SUPPORT_SYSTEM_SELECTED_ON'		=> $cfg->SELITY_SUPPORT_SYSTEM ? 'selected' : '',
	'SUPPORT_SYSTEM_SELECTED_OFF'		=> $cfg->SELITY_SUPPORT_SYSTEM ? '' : 'selected',

	'CREATE_DEFAULT_EMAIL_ADDRESSES_ON'		=> $cfg->CREATE_DEFAULT_EMAIL_ADDRESSES ? 'selected' : '',
	'CREATE_DEFAULT_EMAIL_ADDRESSES_OFF'	=> $cfg->CREATE_DEFAULT_EMAIL_ADDRESSES ? '' : 'selected',

	'HARD_MAIL_SUSPENSION_ON'			=> $cfg->HARD_MAIL_SUSPENSION ? 'selected' : '',
	'HARD_MAIL_SUSPENSION_OFF'			=> $cfg->HARD_MAIL_SUSPENSION ? '' : 'selected',

	'CHECK_FOR_UPDATES_SELECTED_ON'		=> $cfg->CHECK_FOR_UPDATES ? 'selected' : '',
	'CHECK_FOR_UPDATES_SELECTED_OFF'	=> $cfg->CHECK_FOR_UPDATES ? '' : 'selected',

	'HOSTING_PLANS_LEVEL_ADMIN'			=> $cfg->HOSTING_PLANS_LEVEL ? 'selected' : '',
	'HOSTING_PLANS_LEVEL_RESELLER'		=> $cfg->HOSTING_PLANS_LEVEL ? '' : 'selected',

	'GUI_DEBUG_ON'						=> $cfg->GUI_DEBUG ? 'selected' : '',
	'GUI_DEBUG_OFF'						=> $cfg->GUI_DEBUG ? '' : 'selected',
));


switch($cfg->LOG_LEVEL){
	case E_USER_OFF:
		$tpl->saveRepeats(array(
			'LOG_LEVEL_SELECTED_OFF'		=>'selected',
			'LOG_LEVEL_SELECTED_NOTICE'		=>'',
			'LOG_LEVEL_SELECTED_WARNING'	=>'',
			'LOG_LEVEL_SELECTED_ERROR'		=>''
		));
		break;
	case E_USER_NOTICE:
		$tpl->saveRepeats(array(
			'LOG_LEVEL_SELECTED_OFF'		=>'',
			'LOG_LEVEL_SELECTED_NOTICE'		=>'selected',
			'LOG_LEVEL_SELECTED_WARNING'	=>'',
			'LOG_LEVEL_SELECTED_ERROR'		=>'',
		));
		break;
	case E_USER_WARNING:
		$tpl->saveRepeats(array(
			'LOG_LEVEL_SELECTED_OFF'		=>'',
			'LOG_LEVEL_SELECTED_NOTICE'		=>'',
			'LOG_LEVEL_SELECTED_WARNING'	=>'selected',
			'LOG_LEVEL_SELECTED_ERROR'		=>'',
		));
		break;
	default:
		$tpl->saveRepeats(array(
			'LOG_LEVEL_SELECTED_OFF'		=>'',
			'LOG_LEVEL_SELECTED_NOTICE'		=>'',
			'LOG_LEVEL_SELECTED_WARNING'	=>'',
			'LOG_LEVEL_SELECTED_ERROR'		=>'selected',
		));
} // switch

/*
 *
 * static page messages.
 *
 */

$tpl->saveVariable(
	array(
		'TR_GENERAL_SETTINGS'				=> tr('General settings'),
		'TR_SETTINGS'						=> tr('Settings'),
		'TR_MESSAGE'						=> tr('Message'),
		'TR_LOSTPASSWORD'					=> tr('Lost password'),
		'TR_LOSTPASSWORD_TIMEOUT'			=> tr('Activation link expire time (minutes)'),
		'TR_PASSWORD_SETTINGS'				=> tr('Password settings') ,
		'TR_PASSWD_STRONG'					=> tr('Use strong Passwords') ,
		'TR_PASSWD_CHARS'					=> tr('Password length'),
		'TR_BRUTEFORCE'						=> tr('Bruteforce detection'),
		'TR_BRUTEFORCE_BETWEEN'				=> tr('Block time between logins'),
		'TR_BRUTEFORCE_MAX_LOGIN'			=> tr('Max number of login attempts'),
		'TR_BRUTEFORCE_BLOCK_TIME'			=> tr('Blocktime (minutes)'),
		'TR_BRUTEFORCE_BETWEEN_TIME'		=> tr('Block time between logins (seconds)'),
		'TR_BRUTEFORCE_MAX_CAPTCHA'			=> tr('Max number of CAPTCHA attempts'),
		'TR_OTHER_SETTINGS'					=> tr('Other settings'),
		'TR_MAIL_SETTINGS'					=> tr('E-Mail settings'),
		'TR_CREATE_DEFAULT_EMAIL_ADDRESSES'	=> tr('Create default E-Mail addresses'),
		'TR_HARD_MAIL_SUSPENSION'			=> tr('E-Mail accounts are hard suspended'),
		'TR_USER_INITIAL_LANG'				=> tr('Default language'),
		'TR_SUPPORT_SYSTEM'					=> tr('Support system'),
		'TR_ENABLED'						=> tr('Enabled'),
		'TR_DISABLED'						=> tr('Disabled'),
		'TR_APPLY_CHANGES'					=> tr('Apply changes'),
		'TR_SERVERPORTS'					=> tr('Server ports'),
		'TR_HOSTING_PLANS_LEVEL'			=> tr('Hosting plans available for'),
		'TR_ADMIN'							=> tr('Admin'),
		'TR_RESELLER'						=> tr('Reseller'),
		'TR_DOMAIN_ROWS_PER_PAGE'			=> tr('Domains per page'),
		'TR_LOG_LEVEL'						=> tr('Log Level'),
		'TR_E_USER_OFF'						=> tr('Disabled'),
		'TR_E_USER_NOTICE'					=> tr('Notices, Warnings and Errors'),
		'TR_E_USER_WARNING'					=> tr('Warnings and Errors'),
		'TR_E_USER_ERROR'					=> tr('Errors'),
		'TR_CHECK_FOR_UPDATES'				=> tr('Check for update'),
		'TR_GUI_DEBUG'						=> tr('GUI debug')
		)
	);

genAdminMainMenu();
genAdminSettingsMenu();

$tpl->flushOutput('admin/settings');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

