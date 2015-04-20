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
$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/mail_autoresponder_enable.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');

// page functions.

function check_email_user($mail_id) {

	if (who_owns_this($mail_id, 'mail_id') != $_SESSION['user_id']) {
		set_page_message(tr('User does not exist or you do not have permission to access this interface!'));
		header('Location: mail_accounts.php');
		die();
	}
}

function gen_page_dynamic_data(&$tpl, &$sql, $mail_id) {
	if (isset($_POST['uaction']) && $_POST['uaction'] === 'enable_arsp') {
		if (empty($_POST['arsp_message'])) {
			$tpl->assign('ARSP_MESSAGE', '');
			set_page_message(tr('Please type your mail autorespond message!'));
			return;
		}

		$arsp_message = clean_input($_POST['arsp_message'], false);
		$item_change_status = Config::get('ITEM_CHANGE_STATUS');

		$query = '
			update
				`mail_users`
			set
				`status` = ?,
				`mail_auto_respond` = 1,
				`mail_auto_respond_text` = ?
			where
				`mail_id` = ?
		';

		$rs = exec_query($sql, $query, array($item_change_status, $arsp_message, $mail_id));

		send_request();
		$query = '
			SELECT
				`mail_addr`
			FROM
				`mail_users`
			WHERE
				`mail_id` = ?
		';

		$rs = exec_query($sql, $query, array($mail_id));
		$mail_name = $rs->fields['mail_addr'];
		write_log($_SESSION['user_logged'] . ': add mail autoresponder: ' . $mail_name);
		set_page_message(tr('Mail account scheduler for modification!'));
		header('Location: mail_accounts.php');
		exit(0);
	} else {
		// Get Message
		$query = '
			SELECT
				mail_auto_respond_text, mail_acc
 			FROM
				mail_users
			WHERE
				mail_id = ?
		';

		$rs = exec_query($sql, $query, array($mail_id));
		$mail_name = $rs->fields['mail_acc'];

		$tpl->assign('ARSP_MESSAGE', $rs->fields['mail_auto_respond_text']);
		return;
	}
}

// common page data.

if (isset($_GET['id'])) {
	$mail_id = (int) $_GET['id'];
} else if (isset($_POST['id'])) {
	$mail_id = (int) $_POST['id'];
} else {
	header('Location: mail_accounts.php');
	exit(0);
}

if (isset($_SESSION['email_support']) && $_SESSION['email_support'] == 'no') {
	header('Location: index.php');
}

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(
	array(
		'TR_PAGE_TITLE'	=> tr('Selity - Client/Enable Mail Auto Responder'),
		'THEME_COLOR_PATH'							=> '../themes/'.$theme_color,
		'THEME_CHARSET'								=> tr('encoding'),
		'ISP_LOGO'									=> get_logo($_SESSION['user_id'])
	)
);

// dynamic page data.

check_email_user($mail_id);
gen_page_dynamic_data($tpl, $sql, $mail_id);

// static page messages.

gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_email_accounts.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_email_accounts.tpl');

gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(
	array(
		'TR_ENABLE_MAIL_AUTORESPONDER'	=> tr('Enable mail auto responder'),
		'TR_ARSP_MESSAGE'				=> tr('Your message'),
		'TR_ENABLE'						=> tr('Save'),
		'TR_CANCEL'						=> tr('Cancel'),
		'ID'							=> $mail_id
	)
);
gen_page_message($tpl);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();
