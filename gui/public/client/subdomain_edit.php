<?php
/**
 * Selity - A server control panel
 *
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
$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/subdomain_edit.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(array(
	'TR_PAGE_TITLE'		=> tr('Selity - Manage Subdomain/Edit Subdomain'),
	'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
	'THEME_CHARSET'		=> tr('encoding'),
	'ISP_LOGO'			=> get_logo($_SESSION['user_id'])
));

/*
 *
 * static page messages.
 *
*/
$tpl->assign(array(
		'TR_MANAGE_SUBDOMAIN'	=> tr('Manage subdomain'),
		'TR_EDIT_SUBDOMAIN'		=> tr('Edit subdomain'),
		'TR_SUBDOMAIN_NAME'		=> tr('Subdomain name'),
		'TR_FORWARD'			=> tr('Forward to URL'),
		'TR_MOUNT_POINT'		=> tr('Mount Point'),
		'TR_MODIFY'				=> tr('Modify'),
		'TR_CANCEL'				=> tr('Cancel'),
		'TR_ENABLE_FWD'			=> tr('Enable Forward'),
		'TR_ENABLE'				=> tr('Enable'),
		'TR_SUBDOMAIN_IP'		=> tr('Subdomain IP'),
		'TR_DISABLE'			=> tr('Disable'),
));

gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_manage_domains.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_manage_domains.tpl');

gen_logged_from($tpl);
// "Modify" button has been pressed
if (isset($_POST['uaction']) && ($_POST['uaction'] === 'modify')) {
	if (isset($_GET['edit_id'])) {
		$editid = $_GET['edit_id'];
	} else if (isset($_SESSION['edit_ID'])) {
		$editid = $_SESSION['edit_ID'];
	} else {
		unset($_SESSION['edit_ID']);
		$_SESSION['subedit'] = '_no_';
		user_goto('domains_manage.php');
	}
	// Save data to db
	if (check_fwd_data($tpl, $sql, $editid)) {
		$_SESSION['subedit'] = '_yes_';
		user_goto('domains_manage.php');
	}
} else {
	// Get user id that comes for edit
	if (isset($_GET['edit_id'])) {
		$editid = $_GET['edit_id'];
	}

	$_SESSION['edit_ID'] = $editid;
	$tpl->assign('PAGE_MESSAGE', '');
}

gen_editsubdomain_page($tpl, $sql, $editid);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
unset_messages();

// Begin function block

/**
 * Show user data
 */
function gen_editsubdomain_page(&$tpl, &$sql, $edit_id) {
	// Get data FROM sql
	$admin_id = $_SESSION['user_id'];

	$query = '
		SELECT
			t1.`subdomain_alias_name` AS subdomain_name,
			t1.`subdomain_alias_mount` AS subdomain_mount,
			t1.`subdomain_alias_url_forward` AS subdomain_url_forward,
			t2.`alias_name` AS domain_name, t2.`alias_ips`,
			t3.`ip_number`, t3.`ip_alias`
		FROM
			`subdomain_alias` t1
		LEFT JOIN
			(`domain_aliasses` AS t2) ON (t1.`alias_id` = t2.`alias_id`)
		LEFT JOIN
			`server_ips` as `t3` ON `t2`.`alias_ips` = `t3`.`ip_id`
		WHERE
			t1.`alias_id` IN (SELECT `alias_id` FROM `domain_aliasses` WHERE `admin_id` = ?)
		AND
			`subdomain_alias_id` = ?
	';
	$res = exec_query($sql, $query, array($admin_id, $edit_id));

	if ($res->RecordCount() <= 0) {
		$_SESSION['subedit'] = '_no_';
		user_goto('domains_manage.php');
	}
	$data = $res->FetchRow();

	$domain_name = $data['domain_name'];

	if (isset($_POST['uaction']) && ($_POST['uaction'] == 'modify')) {
		$url_forward = strtolower(clean_input($_POST['forward']));
	} else {
		$url_forward = decode_idna(preg_replace('(ftp://|https://|http://)', '', $data['subdomain_url_forward']));

		if ($data['subdomain_url_forward'] == '' || $data['subdomain_url_forward'] == 'no') {
			$check_en		= '';
			$check_dis		= 'checked';
			$url_forward	= '';
			$tpl->assign(array(
					'READONLY_FORWARD'	=> ' readonly',
					'DISABLE_FORWARD'	=> ' disabled',
					'HTTP_YES'			=> '',
					'HTTPS_YES'			=> '',
					'FTP_YES'			=> ''
			));
		} else {
			$check_en	= 'checked';
			$check_dis	= '';
			$tpl->assign(array(
					'READONLY_FORWARD'	=> '',
					'DISABLE_FORWARD'	=> '',
					'HTTP_YES'			=> (preg_match('/http:\/\//', $data['subdomain_url_forward'])) ? 'selected' : '',
					'HTTPS_YES'			=> (preg_match('/https:\/\//', $data['subdomain_url_forward'])) ? 'selected' : '',
					'FTP_YES'			=> (preg_match('/ftp:\/\//', $data['subdomain_url_forward'])) ? 'selected' : ''
			));
		}
		$tpl->assign(array(
			'CHECK_EN'	=> $check_en,
			'CHECK_DIS'	=> $check_dis,
			'DOMAIN_IP'	=> $data['ip_number'] . ' (' . $data['ip_alias'] . ')'
		));
	}
	// Fill in the fields
	$tpl->assign(array(
		'SUBDOMAIN_NAME'	=> decode_idna($data['subdomain_name']) . '.' . $domain_name,
		'FORWARD'			=> $url_forward,
		'MOUNT_POINT'		=> $data['subdomain_mount'],
		'ID'				=> $edit_id,
	));

}

/**
 * Check input data
 */
function check_fwd_data(&$tpl, &$sql, $subdomain_id) {

	$forward_url = strtolower(clean_input($_POST['forward']));
	$status = $_POST['status'];
	// unset errors
	$ed_error = '_off_';
	$admin_login = '';

	if (isset($_POST['status']) && $_POST['status'] == 1) {
		$forward_prefix = clean_input($_POST['forward_prefix']);
		if (substr_count($forward_url, '.') <= 2) {
			$ret = validates_dname($forward_url);
		} else {
			$ret = validates_dname($forward_url, true);
		}
		if (!$ret) {
			$ed_error = tr('Wrong domain part in forward URL!');
		} else {
			$forward_url = encode_idna($forward_prefix.$forward_url);
		}
		$check_en = 'checked';
		$check_dis = '';
		$tpl->assign(array(
				'FORWARD'	=> $forward_url,
				'CHECK_EN'	=> $check_en,
				'CHECK_DIS'	=> $check_dis,
		));
	} else {
		$check_en = '';
		$check_dis = 'checked';
		$forward_url = 'no';
		$tpl->assign(array(
				'READONLY_FORWARD'	=> ' readonly',
				'DISABLE_FORWARD'	=> ' disabled',
				'CHECK_EN'			=> $check_en,
				'CHECK_DIS'			=> $check_dis,
		));
	}
	if ($ed_error === '_off_') {
		$query = '
			UPDATE
				`subdomain_alias`
			SET
				`subdomain_alias_url_forward` = ?,
				`subdomain_alias_status` = ?
			WHERE
				`subdomain_alias_id` = ?
		';

		exec_query($sql, $query, array($forward_url, Config::get('ITEM_CHANGE_STATUS'), $subdomain_id));
		send_request();
		write_log($_SESSION['user_logged'] . ': change subdomain forward: ' . $subdomain_id);
		unset($_SESSION['edit_ID']);
		set_page_message(tr('Subdomain scheduled for modification!'));
		return true;
	} else {
		$tpl->assign('MESSAGE', $ed_error);
		$tpl->parse('PAGE_MESSAGE', 'page_message');
		return false;
	}
}
