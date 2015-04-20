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

$tpl = new pTemplate();
$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/ftp_accounts.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');
$tpl->define_dynamic('mail_message', 'page');
$tpl->define_dynamic('mail_item', 'page');
$tpl->define_dynamic('mail_auto_respond', 'mail_item');
$tpl->define_dynamic('mails_total', 'page');
$tpl->define_dynamic('catchall_message', 'page');
$tpl->define_dynamic('catchall_item', 'page');
$tpl->define_dynamic('ftp_message', 'page');
$tpl->define_dynamic('ftp_item', 'page');
$tpl->define_dynamic('no_mails', 'page');

// page functions.

function gen_page_ftp_list(&$tpl, &$sql, $admin_id, $admin_name) {
	$query = '
		SELECT
			`gid`,
			`members`
		FROM
			`ftp_group`
		WHERE
			`groupname` = ?
	';

	$rs = exec_query($sql, $query, array($admin_name));

	if ($rs->RecordCount() == 0) {
		$tpl->assign(array(
			'FTP_MSG'		=> tr('FTP list is empty!'),
			'FTP_ITEM'		=> '',
			'FTPS_TOTAL'	=> ''
		));
		$tpl->parse('FTP_MESSAGE', 'ftp_message');
	} else {
		$tpl->assign('FTP_MESSAGE', '');
		$ftp_accs = explode(',', $rs->fields['members']);
		sort($ftp_accs);
		reset($ftp_accs);
		for ($i = 0; $i < count($ftp_accs); $i++) {
			$ftp_accs_encode[$i] = decode_idna($ftp_accs[$i]);
			$tpl->assign(array(
					'FTP_ACCOUNT'	=> $ftp_accs_encode[$i],
					'UID'			=> urlencode ($ftp_accs[$i])
			));
			$tpl->parse('FTP_ITEM', '.ftp_item');
		}
		$tpl->assign('TOTAL_FTP_ACCOUNTS', count($ftp_accs));
	}
}

// common page data.

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(array(
	'TR_PAGE_TITLE'	=> tr('Selity - Client/Manage Users'),
	'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
	'THEME_CHARSET'		=> tr('encoding'),
	'ISP_LOGO'			=> get_logo($_SESSION['user_id'])
)	);

// dynamic page data.

if (isset($_SESSION['email_support']) && $_SESSION['email_support'] == "no") {
	$tpl->assign('NO_MAILS', '');
}

gen_page_ftp_list($tpl, $sql, $_SESSION['user_id'], $_SESSION['user_logged']);

// static page messages.

gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_ftp_accounts.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_ftp_accounts.tpl');

gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(array(
	'TR_MANAGE_USERS'		=> tr('Manage users'),
	'TR_TYPE'				=> tr('Type'),
	'TR_STATUS'				=> tr('Status'),
	'TR_ACTION'				=> tr('Action'),
	'TR_TOTAL_FTP_ACCOUNTS'	=> tr('FTPs total'),
	'TR_DOMAIN'				=> tr('Domain'),
	'TR_FTP_USERS'			=> tr('FTP users'),
	'TR_FTP_ACCOUNT'		=> tr('FTP account'),
	'TR_FTP_ACTION'			=> tr('Action'),
	'TR_EDIT'				=> tr('Edit'),
	'TR_DELETE'				=> tr('Delete'),
	'TR_MESSAGE_DELETE'		=> tr('Are you sure you want to delete %s?', true, '%s')
));

gen_page_message($tpl);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

