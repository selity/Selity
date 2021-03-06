<?php
/**
 * Selity - A server control panel
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2008 by ispCP | http://isp-control.net
 * @copyright	2012-2014 by Selity
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

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl = new pTemplate();
$tpl->define_dynamic('page', Config::get('ADMIN_TEMPLATE_PATH') . '/selity_updates.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('hosting_plans', 'page');
$tpl->define_dynamic('update_message', 'page');
$tpl->define_dynamic('update_infos', 'page');

$tpl->assign(
	array(
		'TR_ADMIN_SELITY_UPDATES_PAGE_TITLE' => tr('Selity - Virtual Hosting Control System'),
		'THEME_COLOR_PATH' => "../themes/$theme_color",
		'THEME_CHARSET' => tr('encoding'),
		'ISP_LOGO' => get_logo($_SESSION['user_id'])
		)
	);

/* BEGIN common functions */
function get_update_infos(&$tpl) {

	if (!Config::get('CHECK_FOR_UPDATES')) {
		$tpl->assign(
				array(
					'UPDATE_MESSAGE'	=> '',
					'UPDATE'		=> tr('Update checking is disabled!'),
					'INFOS' 		=> tr('Enable update at') . " <a href=\"settings.php\">" . tr('Settings') . "</a>"
					)
				);
		$tpl->parse('UPDATE_INFOS', 'update_infos');
		return false;
	}

	if (versionUpdate::getInstance()->checkUpdateExists()) {
		$tpl->assign(
			array(
				'UPDATE_MESSAGE' => '',
				'UPDATE' => tr('New selity update is now available'),
				'INFOS' => tr('Get it at') . " <a href=\"http://www.selity.org/download.html\" class=\"link\" target=\"_blank\">http://www.selity.org/download.html</a>"
				)
			);

		$tpl->parse('UPDATE_INFOS', 'update_infos');
	} else {
		if( versionUpdate::getInstance()->getErrorMessage() != "" ) {
			$tpl->assign(array('TR_MESSAGE' => versionUpdate::getInstance()->getErrorMessage()));
		}
		$tpl->assign('UPDATE_INFOS', '');
	}
}
/* END system functions */

/*
 *
 * static page messages.
 *
 */
gen_admin_mainmenu($tpl, Config::get('ADMIN_TEMPLATE_PATH') . '/main_menu_system_tools.tpl');
gen_admin_menu($tpl, Config::get('ADMIN_TEMPLATE_PATH') . '/menu_system_tools.tpl');

$tpl->assign(
	array(
		'TR_UPDATES_TITLE' => tr('Selity updates'),
		'TR_AVAILABLE_UPDATES' => tr('Available Selity updates'),
		'TR_MESSAGE' => tr('No new Selity updates available'),
		'TR_UPDATE' => tr('Update'),
		'TR_INFOS' => tr('Update details')
		)
	);

gen_page_message($tpl);

get_update_infos($tpl);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (Config::get('DUMP_GUI_DEBUG')) dump_gui_debug();

unset_messages();


