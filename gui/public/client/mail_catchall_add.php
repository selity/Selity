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
$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/mail_catchall_add.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');
$tpl->define_dynamic('mail_list', 'page');

if (isset($_GET['id'])) {
	$item_id =  $_GET['id'];
} else if (isset($_POST['id'])) {
	$item_id =  $_POST['id'];
} else {
	set_page_message(tr('No id!'));
	user_goto('mail_catchall.php');
}

// page functions.

function gen_dynamic_page_data(&$tpl, &$sql, $id) {


	$props = get_user_default_props($_SESSION['user_id']);
	$cnt = get_user_running_mail_acc_cnt($_SESSION['user_id']);

	if ($props->max_mail != 0 && $cnt['cnt'] >= $props->max_mail) {
		set_page_message(tr('Mail accounts limit reached!'));
		header('Location: mail_catchall.php');
		die();
	}

	$ok_status = Config::get('ITEM_OK_STATUS');
	$match = array();
	if (preg_match('/(\d+);(alias|alssub)/', $id, $match) == 1) {
		$item_id = $match[1];
		$item_type = $match[2];

		if ($item_type === 'alias') {
			$query = '
				SELECT
					t1.mail_id, t1.mail_type, t2.alias_name, t1.mail_acc
				FROM
					mail_users AS t1,
					domain_aliasses AS t2
				WHERE
					t1.sub_id = t2.alias_id
				AND
					t1.status = ?
				AND
					t1.mail_type LIKE ?
				AND
					t2.alias_id = ?
				ORDER BY
					t1.mail_type DESC, t1.mail_acc
			';

			$rs = exec_query($sql, $query, array($ok_status, 'alias_%', $item_id));

			if ($rs->RecordCount() == 0) {
				$tpl->assign(array('FORWARD_MAIL' => 'checked', 'MAIL_LIST' => '', 'DEFAULT' => 'forward'));
			} else {
				$tpl->assign(array('NORMAL_MAIL' => 'checked', 'FORWARD_MAIL' => '', 'DEFAULT' => 'normal'));

				while (!$rs->EOF) {
					$show_mail_acc = decode_idna($rs->fields['mail_acc']);
					$show_alias_name = decode_idna($rs->fields['alias_name']);
					$mail_acc = $rs->fields['mail_acc'];
					$alias_name = $rs->fields['alias_name'];
					$tpl->assign(
						array(
							'MAIL_ID'				=> $rs->fields['mail_id'],
							'MAIL_ACCOUNT'			=> $show_mail_acc . '@' . $show_alias_name, // this will be show in the templates
							'MAIL_ACCOUNT_PUNNY'	=> $mail_acc . '@' . $alias_name // this will be updated wenn we crate cach all
						)
					);

					$tpl->parse('MAIL_LIST', '.mail_list');
					$rs->MoveNext();
				}
			}
		} else if ($item_type === 'alssub') {
			$query = '
				SELECT
					t1.mail_id, t1.mail_type, CONCAT( t2.subdomain_alias_name, ".", t3.alias_name ) AS subdomain_name, t1.mail_acc
				FROM
					mail_users AS t1,
					subdomain_alias AS t2,
					domain_aliasses AS t3
				WHERE
					t1.sub_id = t2.subdomain_alias_id
				AND
					t2.alias_id = t3.alias_id
				AND
					t1.status = ?
				AND
					t1.mail_type LIKE ?
				AND
					t2.subdomain_alias_id = ?
				ORDER BY
					t1.mail_type DESC, t1.mail_acc
			';

			$rs = exec_query($sql, $query, array($ok_status, 'alssub_%', $item_id));

			if ($rs->RecordCount() == 0) {
				$tpl->assign(array('FORWARD_MAIL' => 'checked', 'MAIL_LIST' => '', 'DEFAULT' => 'forward'));
			} else {
				$tpl->assign(array('NORMAL_MAIL' => 'checked', 'FORWARD_MAIL' => '', 'DEFAULT' => 'normal'));

				while (!$rs->EOF) {
					$show_mail_acc = decode_idna($rs->fields['mail_acc']);
					$show_alias_name = decode_idna($rs->fields['subdomain_name']);
					$mail_acc = $rs->fields['mail_acc'];
					$alias_name = $rs->fields['subdomain_name'];
					$tpl->assign(
						array(
							'MAIL_ID'				=> $rs->fields['mail_id'],
							'MAIL_ACCOUNT'			=> $show_mail_acc . '@' . $show_alias_name, // this will be show in the templates
							'MAIL_ACCOUNT_PUNNY'	=> $mail_acc . '@' . $alias_name // this will be updated wenn we create catch all
						)
					);
					$tpl->parse('MAIL_LIST', '.mail_list');
					$rs->MoveNext();
				}
			}
		}
	} else {
		set_page_message(tr('Invalid type!'));
		user_goto('mail_catchall.php');
	}
}

function create_catchall_mail_account(&$sql, $id) {
	list($realId, $type) = explode(';', $id);
	// Check if user is owner of the domain
	if (!preg_match('(alias|alssub)', $type) || who_owns_this($realId, $type) != $_SESSION['user_id']) {
		set_page_message(tr('User does not exist or you do not have permission to access this interface!'));
		user_goto('mail_catchall.php');
	}

	$match = array();
	if (isset($_POST['uaction']) && $_POST['uaction'] === 'create_catchall' && $_POST['mail_type'] === 'normal') {
		if (preg_match("/(\d+);(alias|alssub)/", $id, $match) == 1) {
			$item_id = $match[1];
			$item_type = $match[2];
			$post_mail_id = $_POST['mail_id'];

			if (preg_match("/(\d+);([^;]+);/", $post_mail_id, $match) == 1) {
				$mail_id = $match[1];
				$mail_acc = $match[2];

				if ($item_type === 'alias') {
					$mail_type = 'alias_catchall';
				} elseif ($item_type === 'alssub') {
					$mail_type = 'alssub_catchall';
				}

				$query = "
					SELECT
						admin_id, sub_id
					FROM
						mail_users
					WHERE
						mail_id = ?
				";

				$rs = exec_query($sql, $query, array($mail_id));
				$admin_id = $rs->fields['admin_id'];
				$sub_id = $rs->fields['sub_id'];
				$status = Config::get('ITEM_ADD_STATUS');

				// find the mail_addr (catchall -> "@(sub/alias)domain.tld", should be domain part of mail_acc
				$match = explode('@', $mail_acc);
				$mail_addr = '@' . $match[1];


				$query = "
					INSERT INTO `mail_users`
						(mail_acc,
						mail_pass,
						mail_forward,
						admin_id,
						mail_type,
						sub_id,
						status,
						mail_auto_respond,
						quota,
						mail_addr)
					VALUES
						(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
				";

				$rs = exec_query($sql, $query, array($mail_acc, '_no_', '_no_', $admin_id, $mail_type, $sub_id, $status, '_no_', NULL, $mail_addr));

				send_request();
				write_log($_SESSION['user_logged'] . ": adds new email catch all");
				set_page_message(tr('Catch all account scheduled for creation!'));
				user_goto('mail_catchall.php');
			} else {
				set_page_message(tr('No id!'));
				user_goto('mail_catchall.php');
			}
		}
	} else if (isset($_POST['uaction']) && $_POST['uaction'] === 'create_catchall' && $_POST['mail_type'] === 'forward' && isset($_POST['forward_list'])) {
		if (preg_match("/(\d+);(alias|alssub)/", $id, $match) == 1) {
			$item_id = $match[1];
			$item_type = $match[2];

			if ($item_type === 'alias') {
				$mail_type = 'alias_catchall';
				$sub_id = $item_id;
				$query = "SELECT `domain_aliasses`.`admin_id`, `alias_name` FROM `domain_aliasses` WHERE `alias_id` = ?";
				$rs = exec_query($sql, $query, $item_id);
				$admin_id = $rs->fields['admin_id'];
				$mail_addr = '@' . $rs->fields['alias_name'];

			} elseif ($item_type === 'alssub') {
				$mail_type = 'alssub_catchall';
				$sub_id = $item_id;
				$query = "
					SELECT
						t1.`subdomain_alias_name`,
						t2.`alias_name`,
						t2.`admin_id`
					FROM
						`subdomain_alias` as t1,
						`domain_aliasses` as t2
					WHERE
						t1.`subdomain_alias_id` = ?
					AND
						t1.`alias_id` = t2.`alias_id`
					";
				$rs = exec_query($sql, $query, $item_id);
				$admin_id = $rs->fields['admin_id'];
				$mail_addr = '@' . $rs->fields['subdomain_alias_name'] . '.' . $rs->fields['alias_name'];
			}
			$mail_forward = clean_input($_POST['forward_list']);
			$mail_acc = array();
			$faray = preg_split ("/[\n,]+/", $mail_forward);

			foreach ($faray as $value) {
				$value = trim($value);
				if (!chk_email($value) && $value !== '') {
					/* ERR .. strange :) not email in this line - warning */
					set_page_message(tr("Mail forward list error!"));
					return;
				} else if ($value === '') {
					set_page_message(tr("Mail forward list error!"));
					return;
				}
				$mail_acc[] = $value;
			}

			$status = Config::get('ITEM_ADD_STATUS');

			$query = "
				INSERT INTO `mail_users`
					(mail_acc,
					mail_pass,
					mail_forward,
					admin_id,
					mail_type,
					sub_id,
					status,
					mail_auto_respond,
					quota,
					mail_addr)
				VALUES
					(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			";

			$rs = exec_query($sql, $query, array(implode(',', $mail_acc), '_no_', '_no_', $admin_id, $mail_type, $sub_id, $status, '_no_', NULL, $mail_addr));

			send_request();
			write_log($_SESSION['user_logged'] . ": adds new email catch all ");
			set_page_message(tr('Catch all account scheduled for creation!'));
			user_goto('mail_catchall.php');
		} else {
			set_page_message(tr('No type provided!'));
			user_goto('mail_catchall.php');
		}
	}
}

// common page data.

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(
	array(
		'TR_PAGE_TITLE'	=> tr('Selity - Client/Create CatchAll Mail Account'),
		'THEME_COLOR_PATH'						=> "../themes/".$theme_color,
		'THEME_CHARSET'							=> tr('encoding'),
		'ISP_LOGO'								=> get_logo($_SESSION['user_id'])
	)
);

// dynamic page data.

gen_dynamic_page_data($tpl, $sql, $item_id);
create_catchall_mail_account($sql, $item_id);
$tpl->assign('ID', $item_id);

// static page messages.

gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_email_accounts.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_email_accounts.tpl');

gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(
	array(
		'TR_CREATE_CATCHALL_MAIL_ACCOUNT'	=> tr('Create catch all mail account'),
		'TR_MAIL_LIST'						=> tr('Mail accounts list'),
		'TR_CREATE_CATCHALL'				=> tr('Create catch all'),
		'TR_FORWARD_MAIL'					=> tr('Forward mail'),
		'TR_FORWARD_TO'						=> tr('Forward to')
	)
);

gen_page_message($tpl);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

