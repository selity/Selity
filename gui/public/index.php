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

require 'include/selity-lib.php';

if (isset($_GET['logout'])) {
	unset_user_login_data();
}

do_session_timeout();
init_login();

if (isset($_POST['uname']) && isset($_POST['upass']) && !empty($_POST['uname']) && !empty($_POST['upass'])) {

	$uname = encode_idna($_POST['uname']);
	check_input(trim($_POST['uname']));
	check_input(trim($_POST['upass']));
	if (register_user($uname, $_POST['upass'])) {
		redirect_to_level_page();
	}
	header('Location: index.php');
	die();
}

if (check_user_login()) {
	if (!redirect_to_level_page()) {
		unset_user_login_data();
	}
}

shall_user_wait();

$cfg = configs::getInstance();
$theme_color = isset($_SESSION['user_theme']) ? $_SESSION['user_theme'] : $cfg->USER_INITIAL_THEME;
$tpl = template::getInstance();

if ($cfg->MAINTENANCEMODE || databaseUpdate::getInstance()->checkUpdateExists()) {
	$tpl->addMessage(nl2br($cfg->MAINTENANCEMODE_MESSAGE));
}


$tpl->saveVariable(array(
	'TR_PAGE_TITLE'		=> tr('Selity'),
	'THEME_COLOR_PATH'	=> $cfg->LOGIN_TEMPLATE_PATH,
	'THEME_CHARSET'		=> tr('encoding'),
	'TR_LOGIN'			=> tr('Login'),
	'TR_USERNAME'		=> tr('Username'),
	'TR_PASSWORD'		=> tr('Password'),
	'TR_LOGIN_INFO'		=> tr('Please enter your login information'),
));


if ($cfg->LOSTPASSWORD) {
	$tpl->saveVariable(array('TR_LOSTPW' => tr('Lost password')));
	$tpl->saveSection('LOSTPW');
}

if (isset($_SESSION['user_page_message'])) {
	$tpl->addMessage($_SESSION['user_page_message']);
	unset($_SESSION['user_page_message']);
}

$tpl->flushOutput('login');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
