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
$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/mail_accounts.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');
$tpl->define_dynamic('mail_message', 'page');
$tpl->define_dynamic('mail_item', 'page');
$tpl->define_dynamic('mail_auto_respond', 'mail_item');
$tpl->define_dynamic('mails_total', 'page');
$tpl->define_dynamic('no_mails', 'page');

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(
	array('TR_PAGE_TITLE'	=> tr('Selity - Client/Manage Users'),
		'THEME_COLOR_PATH' 						=> '../themes/'.$theme_color,
		'THEME_CHARSET'							=> tr('encoding'),
		'ISP_LOGO'								=> get_logo($_SESSION['user_id'])
	)
);

// page functions.

function gen_user_mail_action($mail_id, $mail_status) {
	if ($mail_status === Config::get('ITEM_OK_STATUS')) {
		return array(tr('Delete'), 'mail_delete.php?id='.$mail_id, tr('Edit'), 'mail_edit.php?id='.$mail_id);
	} else {
		return array(tr('N/A'), '#', tr('N/A'), '#');
	}
}

function gen_user_mail_auto_respond(&$tpl, $mail_id, $mail_type, $mail_status, $mail_auto_respond) {
		if ($mail_status === Config::get('ITEM_OK_STATUS')) {
			$tpl->assign(array(
				'AUTO_RESPOND_DISABLE'			=> $mail_auto_respond == false ? tr('Enable') : tr('Disable'),
				'AUTO_RESPOND_DISABLE_SCRIPT'	=> $mail_auto_respond == false ? 'mail_autoresponder_enable.php?id='.$mail_id : 'mail_autoresponder_disable.php?id='.$mail_id,
				'AUTO_RESPOND_EDIT'				=> $mail_auto_respond == false ? '' : tr('Edit'),
				'AUTO_RESPOND_EDIT_SCRIPT'		=> $mail_auto_respond == false ? '' : 'mail_autoresponder_edit.php?id='.$mail_id,
				'AUTO_RESPOND_VIS'				=> 'inline',
			));

		} else {
			$tpl->assign(
				array(
					'AUTO_RESPOND_DISABLE'			=> tr('Please wait for update'),
					'AUTO_RESPOND_DISABLE_SCRIPT'	=> '',
					'AUTO_RESPOND_EDIT'				=> '',
					'AUTO_RESPOND_EDIT_SCRIPT'		=> '',
					'AUTO_RESPOND_VIS'				=> 'inline'
				)
			);
		}
}

function gen_page_als_sub_mail_list(&$tpl, &$sql, $admin_id) {
	$sub_query = '
		SELECT
			t1.`mail_id`,
			t1.`mail_acc`,
			t1.`mail_type`,
			t1.`status`,
			t1.`mail_auto_respond`,
			CONCAT( LEFT(t1.`mail_forward`, 50), IF( LENGTH(t1.`mail_forward`) > 50, \'...\', \'\') ) AS \'mail_forward\',
			CONCAT(t2.`subdomain_alias_name`,\'.\',t3.`alias_name`) AS \'alssub_name\'
		FROM
			`mail_users` AS t1
		LEFT JOIN (`subdomain_alias` as t2) ON (t1.`sub_id`=t2.`subdomain_alias_id`)
		LEFT JOIN (`domain_aliasses` as t3) ON (t2.`alias_id`=t3.`alias_id`)
		WHERE
			t1.`admin_id` = ?
		AND
			(t1.`mail_type` LIKE \'%'.MT_ALSSUB_MAIL.'%\' OR t1.`mail_type` LIKE \'%'.MT_ALSSUB_FORWARD.'%\')
		ORDER BY
			t1.`mail_acc` ASC,
			t1.`mail_type` DESC
	';

	$rs = exec_query($sql, $sub_query, array($admin_id));

	if ($rs->RecordCount() == 0) {
		return 0;
	} else {
		global $counter;
		while (!$rs->EOF) {

			list($mail_delete, $mail_delete_script, $mail_edit, $mail_edit_script) = gen_user_mail_action($rs->fields['mail_id'], $rs->fields['status']);

			$mail_acc = decode_idna($rs->fields['mail_acc']);

			$show_alssub_name = decode_idna($rs->fields['alssub_name']);

			$mail_types = explode(',', $rs->fields['mail_type']);
			$mail_type = '';

			foreach ($mail_types as $type) {
				$mail_type .= user_trans_mail_type($type);
				if (strpos($type, '_forward') !== false) $mail_type .= ': ' . str_replace(array("\r\n", "\n", "\r"), ", ", $rs->fields['mail_forward']);
				$mail_type .= '<br />';
			}


			$tpl->assign(
				array(
					'MAIL_ACC'			=> $mail_acc . "@" . $show_alssub_name,
					'MAIL_TYPE'			=> $mail_type,
					'MAIL_STATUS'		=> translate_dmn_status($rs->fields['status']),
					'MAIL_DELETE'		=> $mail_delete,
					'MAIL_DELETE_SCRIPT'=> $mail_delete_script,
					'MAIL_EDIT'			=> $mail_edit,
					'MAIL_EDIT_SCRIPT'	=> $mail_edit_script
				)
			);

			gen_user_mail_auto_respond($tpl,
				$rs->fields['mail_id'],
				$rs->fields['mail_type'],
				$rs->fields['status'],
				$rs->fields['mail_auto_respond']);

			$tpl->parse('MAIL_ITEM', '.mail_item');

			$rs->MoveNext();
			$counter ++;
		}

		return $rs->RecordCount();
	}
}

function gen_page_als_mail_list(&$tpl, &$sql, $admin_id) {
	$als_query = "
		SELECT
			t1.`alias_id` AS als_id,
			t1.`alias_name` AS als_name,
			t2.`mail_id`,
			t2.`mail_acc`,
			t2.`mail_type`,
			t2.`status`,
			t2.`mail_auto_respond`,
			CONCAT( LEFT(t2.`mail_forward`, 50), IF( LENGTH(t2.`mail_forward`) > 50, '...', '') ) as 'mail_forward'
		FROM
			`domain_aliasses` AS t1,
			`mail_users` AS t2
		WHERE
			t1.`admin_id` = ?
		AND
			t2.`admin_id` = ?
		AND
			t1.`alias_id` = t2.`sub_id`
		AND
			(t2.`mail_type` LIKE '%".MT_ALIAS_MAIL."%' OR t2.`mail_type` LIKE '%".MT_ALIAS_FORWARD."%')
		ORDER BY
			t2.`mail_acc` ASC,
			t2.`mail_type` DESC
	";

	$rs = exec_query($sql, $als_query, array($admin_id, $admin_id));

	if ($rs->RecordCount() == 0) {
		return 0;
	} else {
		global $counter;
		while (!$rs->EOF) {

			list($mail_delete, $mail_delete_script, $mail_edit, $mail_edit_script) = gen_user_mail_action($rs->fields['mail_id'], $rs->fields['status']);

			$mail_acc = decode_idna($rs->fields['mail_acc']);

			$show_als_name = decode_idna($rs->fields['als_name']);

 			$mail_types = explode(',', $rs->fields['mail_type']);
			$mail_type = '';

 			foreach ($mail_types as $type) {
				$mail_type .= user_trans_mail_type($type);
				if (strpos($type, '_forward') !== false) $mail_type .= ': ' . str_replace(array("\r\n", "\n", "\r"), ", ", $rs->fields['mail_forward']);
				$mail_type .= '<br />';
			}

			$tpl->assign(
				array(
					'MAIL_ACC'			=> $mail_acc . "@" . $show_als_name,
					'MAIL_TYPE'			=> $mail_type,
					'MAIL_STATUS'		=> translate_dmn_status($rs->fields['status']),
					'MAIL_DELETE'		=> $mail_delete,
					'MAIL_DELETE_SCRIPT'=> $mail_delete_script,
					'MAIL_EDIT'			=> $mail_edit,
					'MAIL_EDIT_SCRIPT'	=> $mail_edit_script
					)
				);

			gen_user_mail_auto_respond($tpl,
				$rs->fields['mail_id'],
				$rs->fields['mail_type'],
				$rs->fields['status'],
				$rs->fields['mail_auto_respond']);

			$tpl->parse('MAIL_ITEM', '.mail_item');

			$rs->MoveNext();
			$counter ++;
		}

		return $rs->RecordCount();
	}
}

function gen_page_lists(&$tpl, &$sql, $user_id) {

	$props = get_user_default_props($user_id);

	$alssub_mails = gen_page_als_sub_mail_list($tpl, $sql, $user_id);
	$als_mails = gen_page_als_mail_list($tpl, $sql, $user_id);
	$cnt = $als_mails+$alssub_mails;


	if ($cnt > 0) {
		$tpl->assign(array(
			'MAIL_MESSAGE'			=> '',
			//'DMN_TOTAL'				=> $dmn_mails,
			//'SUB_TOTAL'				=> $sub_mails,
			'ALSSUB_TOTAL'			=> $alssub_mails,
			'ALS_TOTAL'				=> $als_mails,
			'TOTAL_MAIL_ACCOUNTS'	=> $cnt,
			'ALLOWED_MAIL_ACCOUNTS'	=> (($props->max_mail != 0) ? $props->max_mail : tr('unlimited'))
		));
	} else {
		$tpl->assign(array(
			'MAIL_MSG'		=> tr('Mail accounts list is empty!'),
			'MAIL_ITEM'		=> '',
			'MAILS_TOTAL'	=> ''
		));

		$tpl->parse('MAIL_MESSAGE', 'mail_message');
	}

	return $cnt['cnt'];
}

// dynamic page data.

if (isset($_SESSION['email_support']) && $_SESSION['email_support'] == "no") {
	$tpl->assign('NO_MAILS', '');
}

gen_page_lists($tpl, $sql, $_SESSION['user_id']);

// static page messages.

gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_email_accounts.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_email_accounts.tpl');

gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(
	array(
		'TR_MANAGE_USERS'			=> tr('Manage users'),
		'TR_MAIL_USERS'				=> tr('Mail users'),
		'TR_MAIL'					=> tr('Mail'),
		'TR_TYPE'					=> tr('Type'),
		'TR_STATUS'					=> tr('Status'),
		'TR_ACTION'					=> tr('Action'),
		'TR_AUTORESPOND'			=> tr('Auto respond'),
		'TR_DMN_MAILS'				=> tr('Domain mails'),
		'TR_SUB_MAILS'				=> tr('Subdomain mails'),
		'TR_ALS_MAILS'				=> tr('Alias mails'),
		'TR_TOTAL_MAIL_ACCOUNTS'	=> tr('Mails total'),
		'TR_DELETE'					=> tr('Delete'),
		'TR_MESSAGE_DELETE'			=> tr('Are you sure you want to delete %s?', true, '%s')
	)
);

gen_page_message($tpl);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

