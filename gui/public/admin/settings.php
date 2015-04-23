<?php

/**
 * Selity - A server control panel
 *
 * @copyright	2009-2015 by Selity
 * @link 		http://selity.org
 * @author 		Daniel Andreca (sci2tech@gmail.com)
 *
 * @license
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require '../include/selity-lib.php';

check_login(__FILE__);

$cfg = configs::getInstance();
$tpl = template::getInstance();

$theme_color = $cfg->USER_INITIAL_THEME;

if (array_key_exists('submit', $_POST)) {

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
	}

	$cfg->LOSTPASSWORD				= (int) clean_input($_POST['lostpassword']);
	$cfg->LOSTPASSWORD_TIMEOUT		= (int) clean_input($_POST['lostpassword_timeout']);
	$cfg->PASSWD_CHARS				= (int) clean_input($_POST['passwd_chars']);
	$cfg->PASSWD_STRONG				= (int) clean_input($_POST['passwd_strong']);
	$cfg->BRUTEFORCE				= (int) clean_input($_POST['bruteforce']);
	$cfg->BRUTEFORCE_BETWEEN		= (int) clean_input($_POST['bruteforce_between']);
	$cfg->BRUTEFORCE_MAX_LOGIN		= (int) clean_input($_POST['bruteforce_max_login']);
	$cfg->BRUTEFORCE_BLOCK_TIME		= (int) clean_input($_POST['bruteforce_block_time']);
	$cfg->BRUTEFORCE_BETWEEN_TIME	= (int) clean_input($_POST['bruteforce_between_time']);
	$cfg->BRUTEFORCE_MAX_CAPTCHA	= (int) clean_input($_POST['bruteforce_max_capcha']);
	$cfg->CREATE_DEFAULT_EMAILS		= (int) clean_input($_POST['create_default_emails']);
	$cfg->HARD_MAIL_SUSPENSION		= (int) clean_input($_POST['hard_mail_suspension']);
	$cfg->USER_INITIAL_LANG			= clean_input($_POST['def_language']);
	$cfg->SUPPORT_SYSTEM			= (int) clean_input($_POST['support_system']);
	$cfg->HOSTING_PLANS_LEVEL		= clean_input($_POST['hosting_plan_level']);
	$cfg->DOMAIN_ROWS_PER_PAGE		= (int) clean_input($_POST['users_rows_per_page']);
	$cfg->LOG_LEVEL					= (int) $log_level;
	$cfg->CHECK_FOR_UPDATES			= (int) clean_input($_POST['checkforupdate']);
	$cfg->GUI_DEBUG					= (int) clean_input($_POST['gui_debug']);

	$cfg->saveAll();
	$tpl->addMessage(tr('Settings saved!'));
}

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
	'TR_PAGE_TITLE'					=> tr('Selity - Admin/Settings'),
	'THEME_COLOR_PATH'				=> '../themes/'.$theme_color,
	//'THEME_CHARSET'					=> tr('encoding'),

	'TR_SETTINGS'					=> tr('Settings'),
	'TR_MESSAGE'					=> tr('Message'),
	'TR_LOSTPASSWORD'				=> tr('Lost password'),
	'TR_LOSTPASSWORD_TIMEOUT'		=> tr('Activation link expire time (minutes)'),
	'TR_PASSWORD_SETTINGS'			=> tr('Password settings') ,
	'TR_PASSWD_STRONG'				=> tr('Use strong Passwords') ,
	'TR_PASSWD_CHARS'				=> tr('Password length'),
	'TR_BRUTEFORCE'					=> tr('Bruteforce detection'),
	'TR_BRUTEFORCE_BETWEEN'			=> tr('Block time between logins'),
	'TR_BRUTEFORCE_MAX_LOGIN'		=> tr('Max number of login attempts'),
	'TR_BRUTEFORCE_BLOCK_TIME'		=> tr('Blocktime (minutes)'),
	'TR_BRUTEFORCE_BETWEEN_TIME'	=> tr('Block time between logins (seconds)'),
	'TR_BRUTEFORCE_MAX_CAPTCHA'		=> tr('Max number of CAPTCHA attempts'),
	'TR_OTHER_SETTINGS'				=> tr('Other settings'),
	'TR_MAIL_SETTINGS'				=> tr('E-Mail settings'),
	'TR_CREATE_DEFAULT_EMAILS'		=> tr('Create default E-Mail addresses'),
	'TR_HARD_MAIL_SUSPENSION'		=> tr('E-Mail accounts are hard suspended'),
	'TR_USER_INITIAL_LANG'			=> tr('Default language'),
	'TR_SUPPORT_SYSTEM'				=> tr('Support system'),
	'TR_ENABLED'					=> tr('Enabled'),
	'TR_DISABLED'					=> tr('Disabled'),
	'TR_APPLY_CHANGES'				=> tr('Apply changes'),
	'TR_SERVERPORTS'				=> tr('Server ports'),
	'TR_HOSTING_PLANS_LEVEL'		=> tr('Hosting plans available for'),
	'TR_ADMIN'						=> tr('Admin'),
	'TR_RESELLER'					=> tr('Reseller'),
	'TR_USER_ROWS_PER_PAGE'			=> tr('Users per page'),
	'TR_LOG_LEVEL'					=> tr('Log Level'),
	'TR_E_USER_OFF'					=> tr('Disabled'),
	'TR_E_USER_NOTICE'				=> tr('Notices, Warnings and Errors'),
	'TR_E_USER_WARNING'				=> tr('Warnings and Errors'),
	'TR_E_USER_ERROR'				=> tr('Errors'),
	'TR_CHECK_FOR_UPDATES'			=> tr('Check for update'),
	'TR_GUI_DEBUG'					=> tr('GUI debug'),

	'LOSTPASSWORD_TIMEOUT_VALUE'		=> $cfg->LOSTPASSWORD_TIMEOUT,
	'PASSWD_CHARS'						=> $cfg->PASSWD_CHARS,
	'BRUTEFORCE_MAX_LOGIN_VALUE'		=> $cfg->BRUTEFORCE_MAX_LOGIN,
	'BRUTEFORCE_BLOCK_TIME_VALUE'		=> $cfg->BRUTEFORCE_BLOCK_TIME,
	'BRUTEFORCE_BETWEEN_TIME_VALUE'		=> $cfg->BRUTEFORCE_BETWEEN_TIME,
	'BRUTEFORCE_MAX_CAPTCHA'			=> $cfg->BRUTEFORCE_MAX_CAPTCHA,
	'USER_ROWS_PER_PAGE'				=> $cfg->USER_ROWS_PER_PAGE,

	'LOSTPASSWORD_SELECTED_ON'			=> $cfg->LOSTPASSWORD ? 'selected' : '',
	'LOSTPASSWORD_SELECTED_OFF'			=> $cfg->LOSTPASSWORD ? '' : 'selected',

	'PASSWD_STRONG_ON'					=> $cfg->PASSWD_STRONG ? 'selected' : '',
	'PASSWD_STRONG_OFF'					=> $cfg->PASSWD_STRONG ? '' : 'selected',

	'BRUTEFORCE_SELECTED_ON'			=> $cfg->BRUTEFORCE ? 'selected' : '',
	'BRUTEFORCE_SELECTED_OFF'			=> $cfg->BRUTEFORCE ? '' : 'selected',

	'BRUTEFORCE_BETWEEN_SELECTED_ON'	=> $cfg->BRUTEFORCE_BETWEEN ? 'selected' : '',
	'BRUTEFORCE_BETWEEN_SELECTED_OFF'	=> $cfg->BRUTEFORCE_BETWEEN ? '' : 'selected',

	'SUPPORT_SYSTEM_SELECTED_ON'		=> $cfg->SUPPORT_SYSTEM ? 'selected' : '',
	'SUPPORT_SYSTEM_SELECTED_OFF'		=> $cfg->SUPPORT_SYSTEM ? '' : 'selected',

	'CREATE_DEFAULT_EMAILS_ON'			=> $cfg->CREATE_DEFAULT_EMAILS ? 'selected' : '',
	'CREATE_DEFAULT_EMAILS_OFF'			=> $cfg->CREATE_DEFAULT_EMAILS ? '' : 'selected',

	'HARD_MAIL_SUSPENSION_ON'			=> $cfg->HARD_MAIL_SUSPENSION ? 'selected' : '',
	'HARD_MAIL_SUSPENSION_OFF'			=> $cfg->HARD_MAIL_SUSPENSION ? '' : 'selected',

	'CHECK_FOR_UPDATES_SELECTED_ON'		=> $cfg->CHECK_FOR_UPDATES ? 'selected' : '',
	'CHECK_FOR_UPDATES_SELECTED_OFF'	=> $cfg->CHECK_FOR_UPDATES ? '' : 'selected',

	'HOSTING_PLANS_LEVEL_ADMIN'			=> $cfg->HOSTING_PLANS_LEVEL == 'admin' ? 'selected' : '',
	'HOSTING_PLANS_LEVEL_RESELLER'		=> $cfg->HOSTING_PLANS_LEVEL == 'reseller' ? 'selected' : '',

	'GUI_DEBUG_ON'						=> $cfg->GUI_DEBUG ? 'selected' : '',
	'GUI_DEBUG_OFF'						=> $cfg->GUI_DEBUG ? '' : 'selected',

	'LOG_LEVEL_SELECTED_OFF'			=>'',
	'LOG_LEVEL_SELECTED_NOTICE'			=>'',
	'LOG_LEVEL_SELECTED_WARNING'		=>'',
	'LOG_LEVEL_SELECTED_ERROR'			=>''
));

switch($cfg->LOG_LEVEL){
	case E_USER_OFF:
		$tpl->saveVariable(array(
			'LOG_LEVEL_SELECTED_OFF' =>'selected'
		));
		break;
	case E_USER_NOTICE:
		$tpl->saveVariable(array(
			'LOG_LEVEL_SELECTED_NOTICE' =>'selected'
		));
		break;
	case E_USER_WARNING:
		$tpl->saveVariable(array(
			'LOG_LEVEL_SELECTED_WARNING' =>'selected'
		));
		break;
	default:
		$tpl->saveVariable(array(
			'LOG_LEVEL_SELECTED_ERROR' =>'selected'
		));
}


genAdminMainMenu();
genAdminSettingsMenu();

$tpl->flushOutput('admin/settings');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
