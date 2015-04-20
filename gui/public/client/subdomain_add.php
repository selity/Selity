<?php
/**
 * Selity - A server control panel
 *
 * @copyright	2001-2006 by moleSoftware GmbH
 * @copyright	2006-2008 by ispCP | http://isp-control.net
 * @copyright	2012-2015 by Selity
 * @link 		http://selity.org
 * @author		ispCP Team
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
$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/subdomain_add.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');
$tpl->define_dynamic('als_list', 'page');

// page functions.

function check_subdomain_permissions($user_id) {

	$props = get_user_default_props($user_id);
	$dmn_subd_limit = $props->max_sub;
	$sub_cnt = get_user_running_sub_cnt($user_id);

	if ($props->max_sub != 0 && $sub_cnt >= $props->max_sub) {
		set_page_message(tr('Subdomains limit reached!'));
		header('Location: domains_manage.php');
		die();
	}
	if(get_user_running_als_cnt($user_id) == 0){
		set_page_message(tr('You must have domains added before use this feature!'));
		header('Location: domains_manage.php');
		die();
	}
}

function gen_form_data(&$tpl, $admin_id) {

	$ok_status = Config::get('ITEM_OK_STATUS');
	$post = isset($_POST['uaction']) && $_POST['uaction'] === 'add_subd';

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

	$rs = mysql::getInstance()->doQuery($query, $admin_id, $ok_status);
	while (!$rs->EOF) {

		$als_id = isset($_POST['als_id']) ? $_POST['als_id'] : '';
		$als_selected = $post && $als_id == $rs->alias_id ? $als_selected = 'selected' : '';

		$tpl->assign(array(
			'ALS_ID'		=> $rs->alias_id,
			'ALS_SELECTED'	=> $als_selected,
			'ALS_NAME'		=> decode_idna($rs->alias_name)
		));
		$tpl->parse('ALS_LIST', '.als_list');
		$rs->nextRow();
	}

	$tpl->assign(array(
		'SUBDOMAIN_NAME'		=> $post ? clean_input($_POST['subdomain_name']) : '',
		'SUBDOMAIN_MOUNT_POINT'	=> $post ? clean_input($_POST['subdomain_mnt_pt']) : '',
		'FORWARD'				=> $post ? clean_input($_POST['forward']) : ''
	));

}

function subdomain_schedule($user_id, $domain_id, $sub_name, $sub_mnt_pt, $forward) {
	$status_add = Config::get('ITEM_ADD_STATUS');
	$query = '
		INSERT INTO
			`subdomain_alias`
		(
			`alias_id`,
			`subdomain_alias_name`,
			`subdomain_alias_mount`,
			`subdomain_alias_status`,
			`subdomain_alias_url_forward`
		) VALUES (
			?, ?, ?, ?, ?
		)
	';
	$rs = mysql::getInstance()->doQuery($query, $domain_id, $sub_name, $sub_mnt_pt, $status_add, $forward);
	write_log($_SESSION['user_logged'] . ': adds new subdomain: ' . $sub_name);
	send_request();
}

function subdomain_exists($subname){
	$query = 'SELECT COUNT(*) AS `cnt` FROM `subdomain_alias` WHERE `subdomain_alias_name` = ?';
	return mysql::getInstance()->doQuery($query, $subname)->cnt;
}

function check_data(&$tpl, $admin_id) {

	if (isset($_POST['uaction']) && $_POST['uaction'] === 'add_subd') {

		if( !isset($_POST['als_id']) ){
			set_page_message(tr('No valid alias domain selected!'));
			return;
		}

		$domain_id = (int) $_POST['als_id'];

		if (empty($_POST['subdomain_name'])) {
			set_page_message(tr('Please specify subdomain name!'));
			return;
		}

		if(who_owns_this($domain_id, 'als') != $_SESSION['user_id']){
			set_page_message(tr('Domain do not exists!'));
			return;
		}

		$sub_name = strtolower($_POST['subdomain_name']);
		$sub_name = encode_idna($sub_name);

		if (isset($_POST['subdomain_mnt_pt']) && $_POST['subdomain_mnt_pt'] !== '') {
			$sub_mnt_pt = strtolower($_POST['subdomain_mnt_pt']);
			$sub_mnt_pt = array_encode_idna($sub_mnt_pt, true);
		} else {
			$sub_mnt_pt = '/';
		}

		if (isset($_POST['forward']) && $_POST['forward'] !== '') {
			$forward = strtolower(clean_input($_POST['forward']));
		} else {
			$forward = 'no';
		}

		$query_alias = '
			SELECT
				*
			FROM
				`domain_aliasses`
			WHERE
				`alias_id` = ?
		';

		$rs = mysql::getInstance()->doQuery($query_alias, $domain_id);
		$als_mnt = $rs->alias_mount;
		if( $sub_mnt_pt[0] != '/' )
			$sub_mnt_pt = '/'.$sub_mnt_pt;
		$sub_mnt_pt = $als_mnt.$sub_mnt_pt;
		$sub_mnt_pt = str_replace( '//', '/', $sub_mnt_pt );


		if (!chk_subdname($sub_name . '.' .$rs->alias_name)) {
			set_page_message(tr('Wrong subdomain syntax!'));
		} else if (subdomain_exists($sub_name)) {
			set_page_message(tr('Subdomain already exists or is not allowed!'));
		} else if (!chk_mountp($sub_mnt_pt)) {
			set_page_message(tr('Incorrect mount point syntax'));
		} else if (mount_point_exists($admin_id, array_decode_idna($sub_mnt_pt, true))) {
			set_page_message(tr('Mount point already in use!'));
		} else if ($forward != 'no' && !chk_forward_url($forward)) {
			set_page_message(tr('Incorrect forward syntax'));
		} else {
			if ($forward != 'no' && !preg_match('/\/$/', $forward)) {
				$forward .= '/';
			}
			// now lets fix the mountpoint
			$sub_mnt_pt = array_decode_idna($sub_mnt_pt, true);

			subdomain_schedule($user_id, $domain_id, $sub_name, $sub_mnt_pt, $forward);
			set_page_message(tr('Subdomain scheduled for addition!'));
			header('Location:domains_manage.php');
			die();
		}
	}
}

// common page data.

// check User sql permision
if (isset($_SESSION['subdomain_support']) && $_SESSION['subdomain_support'] == 'no') {
	header('Location: index.php');
}

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(
	array(
		'TR_PAGE_TITLE'	=> tr('Selity - Client/Add Subdomain'),
		'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
		'THEME_CHARSET'	=> tr('encoding'),
		'ISP_LOGO'	=> get_logo($_SESSION['user_id'])
	)
);

// dynamic page data.

check_subdomain_permissions($_SESSION['user_id']);
check_data($tpl, $_SESSION['user_id']);
gen_form_data($tpl, $_SESSION['user_id']);

// static page messages.

gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_manage_domains.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_manage_domains.tpl');

gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(
	array(
		'TR_DIR_TREE_SUBDOMAIN_MOUNT_POINT'	=> tr('Directory tree mount point'),
		'TR_ADD_SUBDOMAIN'	=> tr('Add subdomain'),
		'TR_SUBDOMAIN_DATA'	=> tr('Subdomain data'),
		'TR_SUBDOMAIN_NAME'	=> tr('Subdomain name'),
		'TR_ADD'			=> tr('Add'),
		'TR_FORWARD'		=> tr('Forward to URL'),
		'TR_DMN_HELP'		=> tr('You do not need \'www.\' Selity will add it on its own.')
	)
);

gen_page_message($tpl);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

