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
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the MPL General Public License as published by the Free Software
 * Foundation; either version 1.1 of the License, or (at your option) any later
 * version.
 * You should have received a copy of the MPL Mozilla Public License along with
 * this program; if not, write to the Open Source Initiative (OSI)
 * http://opensource.org | osi@opensource.org
 */

require '../include/selity-lib.php';

check_login(__FILE__);

$tpl = new pTemplate();

$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/mail_add.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');
$tpl->define_dynamic('als_list', 'page');
$tpl->define_dynamic('als_sub_list', 'page');
$tpl->define_dynamic('to_alias_domain', 'page');
$tpl->define_dynamic('to_alias_subdomain', 'page');

// page functions.

function gen_page_form_data(&$tpl, $admin_id, $post_check) {

	if ($post_check === 'no') {

		$tpl->assign(
			array(
				'USERNAME'				=> '',
				'MAIL_ALS_CHECKED'		=> 'checked',
				'MAIL_ALS_SUB_CHECKED'	=> '',
				'NORMAL_MAIL_CHECKED'	=> 'checked',
				'FORWARD_MAIL_CHECKED'	=> '',
				'FORWARD_LIST'			=> ''
			)
		);

	} else {
		$f_list = isset($_POST['forward_list']) ? $_POST['forward_list'] : '';

		$tpl->assign(
			array(
				'USERNAME'				=> clean_input($_POST['username']),
				'MAIL_ALS_CHECKED'		=> ($_POST['dmn_type'] === 'als') ? 'checked' : '',
				'MAIL_ALS_SUB_CHECKED'	=> ($_POST['dmn_type'] === 'als_sub') ? 'checked' : '',
				'NORMAL_MAIL_CHECKED'	=> (isset($_POST['mail_type_normal'])) ? 'checked' : '',
				'FORWARD_MAIL_CHECKED'	=> (isset($_POST['mail_type_forward'])) ? 'checked' : '',
				'FORWARD_LIST'			=> $f_list
			)
		);
	}
}

function gen_dmn_als_list(&$tpl, &$sql, $admin_id, $post_check) {
	$ok_status = Config::get('ITEM_OK_STATUS');

	$query = '
		SELECT
			`alias_id`, `alias_name`
		FROM
			`domain_aliasses`
		WHERE
			`admin_id` = ?
		AND
			`alias_status` = ?
		ORDER BY
			`alias_name`
	';

	$rs = exec_query($sql, $query, array($admin_id, $ok_status));
	if ($rs->RecordCount() == 0) {
		$tpl->assign(
			array(
				'ALS_ID'		=> '0',
				'ALS_SELECTED'	=> 'selected',
				'ALS_NAME'		=> tr('Empty list')
			)
		);
		$tpl->parse('ALS_LIST', 'als_list');
		$tpl->assign('TO_ALIAS_DOMAIN', '');
	} else {
		$first_passed = false;
		while (!$rs->EOF) {
			if ($post_check === 'yes') {
				if (!isset($_POST['als_id'])) {
					$als_id = '';
				} else {
					$als_id = $_POST['als_id'];
				}

				if ($als_id == $rs->fields['alias_id']) {
					$als_selected = 'selected';
				} else {
					$als_selected = '';
				}
			} else {
				if (!$first_passed) {
					$als_selected = 'selected';
				} else {
					$als_selected = '';
				}
			}

			$alias_name = decode_idna($rs->fields['alias_name']);
			$tpl->assign(
				array(
					'ALS_ID'		=> $rs->fields['alias_id'],
					'ALS_SELECTED'	=> $als_selected,
					'ALS_NAME'		=> $alias_name
				)
			);
			$tpl->parse('ALS_LIST', '.als_list');
			$rs->MoveNext();

			if (!$first_passed)
				$first_passed = true;
		}
	}
}

function gen_dmn_als_sub_list(&$tpl, &$sql, $admin_id, $post_check) {
	$ok_status = Config::get('ITEM_OK_STATUS');
	$query = '
		SELECT
			t1.`subdomain_alias_id` as als_sub_id, t1.`subdomain_alias_name` as als_sub_name, t2.`alias_name` as als_name
		FROM
			`subdomain_alias` as t1
		LEFT JOIN (`domain_aliasses` as t2) ON (t1.`alias_id`=t2.`alias_id`)
		WHERE
			t2.`admin_id` = ?
		AND
			t1.`subdomain_alias_status` = ?
		ORDER BY
			t1.`subdomain_alias_name`
	';

	$rs = exec_query($sql, $query, array($admin_id, $ok_status));

	if ($rs->RecordCount() == 0) {
		$tpl->assign(
			array(
				'ALS_SUB_ID'		=> '0',
				'ALS_SUB_SELECTED'	=> 'selected',
				'ALS_SUB_NAME'		=> tr('Empty list')
			)
		);
		$tpl->parse('ALS_SUB_LIST', 'als_sub_list');
		$tpl->assign('TO_ALIAS_SUBDOMAIN', '');
	} else {
		$first_passed = false;

		while (!$rs->EOF) {
			if ($post_check === 'yes') {
				if (!isset($_POST['als_sub_id'])) {
					$als_sub_id = '';
				} else {
					$als_sub_id = $_POST['als_sub_id'];
				}

				if ($als_sub_id == $rs->fields['als_sub_id']) {
					$als_sub_selected = 'selected';
				} else {
					$als_sub_selected = '';
				}
			} else {
				if (!$first_passed) {
					$als_sub_selected = 'selected';
				} else {
					$als_sub_selected = '';
				}
			}

			$als_sub_name = decode_idna($rs->fields['als_sub_name']);
			$als_name = decode_idna($rs->fields['als_name']);
			$tpl->assign(
				array(
					'ALS_SUB_ID'		=> $rs->fields['als_sub_id'],
					'ALS_SUB_SELECTED'	=> $als_sub_selected,
					'ALS_SUB_NAME'		=> $als_sub_name . '.' . $als_name
				)
			);
			$tpl->parse('ALS_SUB_LIST', '.als_sub_list');
			$rs->MoveNext();

			if (!$first_passed)
				$first_passed = true;
		}
	}
}

function schedule_mail_account(&$sql, $admin_id, $dmn_name, $mail_acc) {

	$mail_auto_respond = false;
	$mail_auto_respond_text = '';
	$mail_addr = '';
	$mail_addr = $mail_acc.'@'.decode_idna($dmn_name);

	if (array_key_exists('mail_type_normal',$_POST)) {
		$mail_pass = $_POST['pass'];
		$mail_forward = '_no_';
		if ($_POST['dmn_type'] === 'als_sub') {
			$mail_type[] = MT_ALSSUB_MAIL;
			$sub_id = $_POST['als_sub_id'];
		} else if ($_POST['dmn_type'] === 'als') {
			$mail_type[] = MT_ALIAS_MAIL;
			$sub_id = $_POST['als_id'];
		} else {
			set_page_message(tr('Unknown domain type'));
			return false;
		}
	}

	if (array_key_exists('mail_type_forward',$_POST)) {
		if ($_POST['dmn_type'] === 'als_sub') {
			$mail_type[] = MT_ALSSUB_FORWARD;
			$sub_id = $_POST['als_sub_id'];
		} else if ($_POST['dmn_type'] === 'als') {
			$mail_type[] = MT_ALIAS_FORWARD;
			$sub_id = $_POST['als_id'];
		} else {
			set_page_message(tr('Unknown domain type'));
			return false;
		}

		if (!isset($_POST['mail_type_normal'])) {
			$mail_pass = '_no_';
		}

		$mail_forward = $_POST['forward_list'];
		$farray = preg_split('/[\n,]+/', $mail_forward);
		$mail_accs = array();

		foreach ($farray as $value) {
			$value = trim($value);
			if (!chk_email($value) && $value !== '') {
				/* ERR .. strange :) not email in this line - warning */
				set_page_message(tr('Mailformat of an address in your forward list is incorrect!'));
				return false;
			} else if ($value === '') {
				set_page_message(tr('Mail forward list empty!'));
				return false;
			}
			$mail_accs[] = $value;
		}
		$mail_forward = implode(',', $mail_accs);
	}

	$mail_type = implode(',', $mail_type);
	list($dmn_type, $type) = explode('_', $mail_type, 2);

	$query = '
		SELECT
			COUNT(*) AS `cnt`
		FROM
			`mail_users`
		WHERE
			`mail_acc` = ?
		AND
			`admin_id` = ?
		AND
			`sub_id` = ?
		AND
			LEFT (`mail_type`, LOCATE("_", `mail_type`)-1) = ?
	';

	$rs = mysql::getInstance()->doQuery($query, $mail_acc, $domain_id, $sub_id, $dmn_type);

	if ($rs->cnt > 0) {
		set_page_message(tr('Mail account already exists!'));
		return false;
	}

	$query = '
		INSERT INTO `mail_users` (
			`mail_acc`,
			`mail_pass`,
			`mail_forward`,
			`admin_id`,
			`mail_type`,
			`sub_id`,
			`status`,
			`mail_auto_respond`,
			`mail_auto_respond_text`,
			`mail_addr`
		) VALUES (
			?, ?, ?, ?, ?, ?, ?, ?, ?, ?
		)
	';

	$rs = mysql::getInstance()->doQuery($query,
		$mail_acc, $mail_pass, $mail_forward,
		$admin_id, $mail_type, $sub_id,
		Config::get('ITEM_ADD_STATUS'), $mail_auto_respond, $mail_auto_respond_text,
		$mail_addr
	);

	write_log($_SESSION['user_logged'] . ': adds new mail account: ' . (isset($mail_addr) ? $mail_addr : $mail_acc));
	set_page_message(tr('Mail account scheduled for addition!'));
	send_request();
	header('Location: mail_accounts.php');
	exit(0);
}

function check_mail_acc_data(&$sql, $admin_id) {

	$mail_type_normal = isset($_POST['mail_type_normal']) ? $_POST['mail_type_normal'] : false;
	$mail_type_forward = isset($_POST['mail_type_forward']) ? $_POST['mail_type_forward'] : false;

	if (($mail_type_normal == false) && ($mail_type_forward == false)) {
		set_page_message(tr('Please SELECT at least one mail type!'));
		return false;
	}

	if ($mail_type_normal) {
		$pass = clean_input($_POST['pass']);
		$pass_rep = clean_input($_POST['pass_rep']);
	}

	if (!isset($_POST['username']) || $_POST['username'] === '') {
		set_page_message(tr('Please enter mail account username!'));
		return false;
	}

	$mail_acc = strtolower(clean_input($_POST['username']));
	if (selity_check_local_part($mail_acc) == '0') {
		set_page_message(tr('Invalid Mail Localpart Format used!'));
		return false;
	}

	if ($mail_type_normal) {
		if (trim($pass) === '' || trim($pass_rep) === '') {
			set_page_message(tr('Password data is missing!'));
			return false;
		} else if ($pass !== $pass_rep) {
			set_page_message(tr('Entered passwords differ!'));
			return false;
		} else if (!chk_password($pass, 50, '/[`\xb4\'"\\\\\x01-\x1f\015\012|<>^$]/i')) {
			// Not permitted chars
			if(Config::get('PASSWD_STRONG')){
				set_page_message(tr('The password must be at least %s long and contain letters and numbers to be valid.', Config::get('PASSWD_CHARS')));
			} else {
				set_page_message(tr('Password data is shorter than %s signs or includes not permitted signs!', Config::get('PASSWD_CHARS')));
			}
			return false;
		}
	}

	if ($_POST['dmn_type'] === 'als_sub'){
		$id='als_sub_id';
		$query='
			SELECT
				CONCAT(t1.`subdomain_alias_name`,\'.\',t2.`alias_name`) as name
			FROM
				`subdomain_alias` as t1
			LEFT JOIN (`domain_aliasses` as t2) ON (t1.`alias_id`=t2.`alias_id`)
			WHERE
				t1.`subdomain_alias_id`=?
			AND
				t2.`admin_id`=?
		';
		$type=tr('Subdomain alias');
	}

	if ($_POST['dmn_type'] === 'als'){
		$id='als_id';
		$query='SELECT `alias_name` as name FROM `domain_aliasses` WHERE `alias_id`=? AND `admin_id`=?';
		$type=tr('Alias');
	}

	if(in_array($_POST['dmn_type'], array('als_sub', 'als'))){
		if(!isset($_POST[$id])) {
			set_page_message(tr('%s list is empty! You cannot add mail accounts!',$type));
			return false;
		}
		if(!is_numeric($_POST[$id])){
			set_page_message(tr('%s id is invalid! You cannot add mail accounts!',$type));
			return false;
		}
		$rs = mysql::getInstance()->doQuery($query, $_POST[$id], $admin_id);
		if ($rs->countRows() == 0) {
			set_page_message(tr('%s id is invalid! You cannot add mail accounts!',$type));
			return false;
		}
		$dmn_name=$rs->name;
	} else {
		set_page_message(tr('Domain type %s is invalid! You cannot add mail accounts!',$_POST['dmn_type']));
		return false;
	}

	if ($mail_type_forward && empty($_POST['forward_list'])) {
		set_page_message(tr('Forward list is empty!'));
		return false;
	}

	schedule_mail_account($sql, $admin_id, $dmn_name, $mail_acc);
}

function gen_page_mail_acc_props(&$tpl, &$sql, $admin_id) {

	$props = get_user_default_props($admin_id);
	$cnt = get_user_running_mail_acc_cnt($admin_id);

	if ($props->max_mail != 0 && $cnt['cnt'] >= $props->max_mail) {
		set_page_message(tr('Mail accounts limit reached!'));
		header('Location: mail_accounts.php');
		die();
	} else {
		$post_check=isset($_POST['uaction'])?'yes':'no';
		gen_page_form_data($tpl, $admin_id, $post_check);
		gen_dmn_als_list($tpl, $sql, $admin_id, $post_check);
		gen_dmn_als_sub_list($tpl, $sql, $admin_id, $post_check);
		if (isset($_POST['uaction']) && $_POST['uaction'] === 'add_user') {
			check_mail_acc_data($sql, $admin_id);
		}
	}
}

// common page data.

if (isset($_SESSION['email_support']) && $_SESSION['email_support'] == 'no') {
	header('Location: index.php');
}

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(
	array(
		'TR_PAGE_TITLE'	=> tr('Selity - Client/Add Mail User'),
		'THEME_COLOR_PATH'					=> '../themes/'.$theme_color,
		'THEME_CHARSET'						=> tr('encoding'),
		'ISP_LOGO'							=> get_logo($_SESSION['user_id'])
	)
);

// dynamic page data.

gen_page_mail_acc_props($tpl, $sql, $_SESSION['user_id']);

// static page messages.

gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_email_accounts.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_email_accounts.tpl');

gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(
	array(
		'TR_ADD_MAIL_USER'		=> tr('Add mail users'),
		'TR_USERNAME'			=> tr('Username'),
		'TR_TO_MAIN_DOMAIN'		=> tr('To main domain'),
		'TR_TO_DMN_ALIAS'		=> tr('To domain alias'),
		'TR_TO_SUBDOMAIN'		=> tr('To subdomain'),
		'TR_TO_ALS_SUBDOMAIN'	=> tr('To alias subdomain'),
		'TR_NORMAL_MAIL'		=> tr('Normal mail'),
		'TR_PASSWORD'			=> tr('Password'),
		'TR_PASSWORD_REPEAT'	=> tr('Repeat password'),
		'TR_FORWARD_MAIL'		=> tr('Forward mail'),
		'TR_FORWARD_TO'			=> tr('Forward to'),
		'TR_FWD_HELP'			=> tr('Separate multiple email addresses with a line-break.'),
		'TR_ADD'				=> tr('Add'),
		'TR_EMPTY_DATA'			=> tr('You did not fill all required fields')
	)
);

gen_page_message($tpl);
$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();


