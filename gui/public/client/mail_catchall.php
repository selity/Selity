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
$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/mail_catchall.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');
$tpl->define_dynamic('catchall_message', 'page');
$tpl->define_dynamic('catchall_item', 'page');


// page functions.

function gen_user_mail_action($mail_id, $mail_status) {
	if ($mail_status === Config::get('ITEM_OK_STATUS')) {
		return array(tr('Delete'), "mail_delete.php?id=".$mail_id, "mail_edit.php?id=".$mail_id);
	} else {
		return array(tr('N/A'), '#', '#');
	}
}

function gen_user_catchall_action($mail_id, $mail_status) {
	if ($mail_status === Config::get('ITEM_ADD_STATUS')) {
		return array(tr('N/A'), '#');//Addition in progress
	} else if ($mail_status === Config::get('ITEM_OK_STATUS')) {
		return array(tr('Delete CatchAll'), "mail_catchall_delete.php?id=".$mail_id);
	} else if ($mail_status === Config::get('ITEM_CHANGE_STATUS')) {
		return array(tr('N/A'), '#');
	} else if ($mail_status === Config::get('ITEM_DELETE_STATUS')) {
		return array(tr('N/A'), '#');
	} else {
		return null;
	}
}

function gen_catchall_item(&$tpl, $action, $dmn_id, $dmn_name, $mail_id, $mail_acc, $mail_status, $ca_type) {
	$show_dmn_name = decode_idna($dmn_name);

	if ($action === 'create') {
		$tpl->assign(
			array(
				'CATCHALL_DOMAIN'			=> $show_dmn_name,
				'CATCHALL_ACC'				=> tr('None'),
				'CATCHALL_STATUS'			=> tr('N/A'),
				'CATCHALL_ACTION'			=> tr('Create catch all'),
				'CATCHALL_ACTION_SCRIPT'	=> "mail_catchall_add.php?id=$dmn_id;$ca_type"
				)
			);
	} else {
		list($catchall_action, $catchall_action_script) = gen_user_catchall_action($mail_id, $mail_status);

		$show_dmn_name = decode_idna($dmn_name);
		$show_mail_acc = decode_idna($mail_acc);

		$tpl->assign(
			array(
				'CATCHALL_DOMAIN' => $show_dmn_name,
				'CATCHALL_ACC' => $show_mail_acc,
				'CATCHALL_STATUS' => translate_dmn_status($mail_status),
				'CATCHALL_ACTION' => $catchall_action,
				'CATCHALL_ACTION_SCRIPT' => $catchall_action_script
			)
		);
	}
}

function gen_page_catchall_list(&$tpl, &$sql, $admin_id) {
	global $counter;

	$tpl->assign('CATCHALL_MESSAGE', '');


		$query = "
			SELECT
				alias_id, alias_name
			FROM
				domain_aliasses
			WHERE
				admin_id = '$admin_id'
			  and
				alias_status = 'ok'
		";

		$rs = execute_query($sql, $query);

		while (!$rs->EOF) {


			$als_id = $rs->fields['alias_id'];

			$als_name = $rs->fields['alias_name'];

			$query = "
				SELECT
					mail_id, mail_acc, status
				FROM
					mail_users
				WHERE
					admin_id = '$admin_id'
				  and
					sub_id = '$als_id'
				  and
					mail_type = 'alias_catchall'
			";

			$rs_als = execute_query($sql, $query);

			if ($rs_als->RecordCount() == 0) {
				gen_catchall_item($tpl, 'create', $als_id, $als_name, '', '', '', 'alias');
			} else {
				gen_catchall_item(
					$tpl,
					'delete',
					$als_id,
					$als_name,
					$rs_als->fields['mail_id'],
					$rs_als->fields['mail_acc'],
					$rs_als->fields['status'], 'alias'
				);
			}

			$tpl->parse('CATCHALL_ITEM', '.catchall_item');

			$rs->MoveNext();
			$counter ++;
		}

		$query = "
			SELECT
				a.subdomain_alias_id, CONCAT(a.subdomain_alias_name,'.',b.alias_name) as subdomain_name
			FROM
				subdomain_alias as a, domain_aliasses as b
			WHERE
				b.admin_id = '$admin_id'
			and
			   	a.alias_id = b.alias_id
			and
				a.subdomain_alias_status = 'ok'
		";

		$rs = execute_query($sql, $query);

		while (!$rs->EOF) {

			$als_id = $rs->fields['subdomain_alias_id'];

			$als_name = $rs->fields['subdomain_name'];

			$query = "
				SELECT
					mail_id, mail_acc, status
				FROM
					mail_users
				WHERE
					admin_id = '$admin_id'
				  and
					sub_id = '$als_id'
				  and
					mail_type = 'alssub_catchall'
			";

			$rs_als = execute_query($sql, $query);

			if ($rs_als->RecordCount() == 0) {
				gen_catchall_item($tpl, 'create', $als_id, $als_name, '', '', '', 'alssub');
			} else {
				gen_catchall_item(
					$tpl,
					'delete',
					$als_id,
					$als_name,
					$rs_als->fields['mail_id'],
					$rs_als->fields['mail_acc'],
					$rs_als->fields['status'], 'alssub'
				);
			}

			$tpl->parse('CATCHALL_ITEM', '.catchall_item');

			$rs->MoveNext();
			$counter ++;
		}

}

// common page data.

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(
	array(
		'TR_PAGE_TITLE'	=> tr('Selity - Client/Manage Users'),
		'THEME_COLOR_PATH'					=> '../themes/'.$theme_color,
		'THEME_CHARSET'						=> tr('encoding'),
		'ISP_LOGO'							=> get_logo($_SESSION['user_id'])
	)
);

// dynamic page data.

if (isset($_SESSION['email_support']) && $_SESSION['email_support'] == 'no') {
	$tpl->assign('NO_MAILS', '');
}

gen_page_catchall_list($tpl, $sql, $_SESSION['user_id']);

// static page messages.

gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_email_accounts.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_email_accounts.tpl');

gen_logged_from($tpl);
check_permissions($tpl);

$tpl->assign(
	array(
		'TR_STATUS'					=> tr('Status'),
		'TR_ACTION'					=> tr('Action'),
		'TR_CATCHALL_MAIL_USERS'	=> tr('Catch all account'),
		'TR_DOMAIN'					=> tr('Domain'),
		'TR_CATCHALL'				=> tr('Catch all'),
		'TR_MESSAGE_DELETE'			=> tr('Are you sure you want to delete %s?', '%s')
	)
);

gen_page_message($tpl);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

