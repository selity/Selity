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

include '../include/selity-lib.php';

check_login(__FILE__);

$tpl = new pTemplate();
$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/webtools.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('active_awstats', 'page');
$tpl->define_dynamic('active_email', 'page');
$tpl->define_dynamic('logged_from', 'page');

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(
		array(
			'TR_PAGE_TITLE' => tr('Selity - Client/Webtools'),
			'THEME_COLOR_PATH' => '../themes/'.$theme_color,
			'THEME_CHARSET' => tr('encoding'),
			'ISP_LOGO' => get_logo($_SESSION['user_id'])
		)
	);

// Check, if E-Mail is active for this user
$props = get_user_default_props($_SESSION['user_id']);

if ($props->max_mail == -1)
	$tpl->assign('ACTIVE_EMAIL', '');

/*
 *
 * static page messages.
 *
 */
gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_webtools.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_webtools.tpl');

gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(
		array(
			'TR_WEBTOOLS' => tr('Webtools'),
			'TR_BACKUP' => tr('Backup'),
			'TR_ERROR_PAGES' => tr('Error pages'),
			'TR_ERROR_PAGES_TEXT' => tr('Customize error pages for your domain'),
			'TR_BACKUP_TEXT' => tr('Backup and restore settings'),
			'TR_WEBMAIL_TEXT' => tr('Access your mail through the web interface'),
			'TR_FILEMANAGER_TEXT' => tr('Access your files through the web interface'),
			'TR_AWSTATS_TEXT' => tr('Access your Awstats statistics'),
			'TR_HTACCESS_TEXT' => tr('Manage protected areas, users and groups')
		)
	);

gen_page_message($tpl);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

