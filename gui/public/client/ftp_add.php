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
require '../include/class.vfs.php';

check_login(__FILE__);

$tpl = new pTemplate();

$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/ftp_add.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');
$tpl->define_dynamic('als_list', 'page');
$tpl->define_dynamic('sub_list', 'page');
$tpl->define_dynamic('to_subdomain', 'page');
$tpl->define_dynamic('to_alias_domain', 'page');
// JavaScript
$tpl->define_dynamic('js_to_subdomain', 'page');
$tpl->define_dynamic('js_to_alias_domain', 'page');
$tpl->define_dynamic('js_to_all_domain', 'page');
$tpl->define_dynamic('js_not_domain', 'page');

// page functions.

function get_alias_mount_point(&$sql, $alias_name) {
	$query = '
		SELECT
			`alias_mount`
		FROM
			`domain_aliasses`
		WHERE
			`alias_name` = ?
	';
	$rs = exec_query($sql, $query, array($alias_name));
	return $rs->fields['alias_mount'];
}

function gen_page_form_data(&$tpl, $dmn_name, $post_check) {
	$dmn_name = decode_idna($dmn_name);
	if ($post_check === 'no') {
		$tpl->assign(array(
			'FTP_USERNAME'			=> '',
			'USER_NAME'				=> $dmn_name,
			'DMN_TYPE_CHECKED'		=> 'checked',
			'ALS_TYPE_CHECKED'		=> '',
			'SUB_TYPE_CHECKED'		=> '',
			'OTHER_DIR'				=> '',
			'USE_OTHER_DIR_CHECKED'	=> ''
		));
	} else {
		$tpl->assign(array(
			'FTP_USERNAME'			=> clean_input($_POST['ftp_username']),
			'USER_NAME'				=> $dmn_name,
			'DMN_TYPE_CHECKED'		=> ($_POST['dmn_type'] === 'dmn') ? 'checked' : '',
			'ALS_TYPE_CHECKED'		=> ($_POST['dmn_type'] === 'als') ? 'checked' : '',
			'SUB_TYPE_CHECKED'		=> ($_POST['dmn_type'] === 'sub') ? 'checked' : '',
			'OTHER_DIR'				=> clean_input($_POST['other_dir']),
			'USE_OTHER_DIR_CHECKED'	=> (isset($_POST['use_other_dir']) && $_POST['use_other_dir'] === 'on') ? 'checked' : ''
		));
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
		$tpl->assign(array(
			'ALS_ID'		=> 'n/a',
			'ALS_SELECTED'	=> 'selected',
			'ALS_NAME'		=> tr('Empty List')
		));
		$tpl->parse('ALS_LIST', 'als_list');
		$tpl->assign('TO_ALIAS_DOMAIN', '');
		$_SESSION['alias_count'] = 'no';
	} else {
		$first_passed = false;
		while (!$rs->EOF) {
			if ($post_check === 'yes') {
				$als_id			= !isset($_POST['als_id']) ? '' : $_POST['als_id'];
				$als_selected	= $als_id == $rs->fields['alias_name'] ? 'selected' : '';
			} else {
				if (!$first_passed) {
					$als_selected = 'selected';
				} else {
					$als_selected = '';
				}
			}
			$als_menu_name = decode_idna($rs->fields['alias_name']);
			$tpl->assign(array(
				'ALS_ID'		=> $rs->fields['alias_name'],
				'ALS_SELECTED'	=> $als_selected,
				'ALS_NAME'		=> $als_menu_name
			));
			$tpl->parse('ALS_LIST', '.als_list');
			$rs->MoveNext();
			if (!$first_passed) $first_passed = true;
		}
	}
}

function gen_dmn_sub_list(&$tpl, &$sql, $admin_id, $dmn_name, $post_check) {
	$ok_status = Config::get('ITEM_OK_STATUS');
	$query = '
		SELECT
			`subdomain_alias_id` AS `sub_id`,
			CONCAT(`subdomain_alias_name`, ".", `alias_name`) AS `sub_name`
		FROM
			`subdomain_alias`
		LEFT JOIN
			`domain_aliasses`
		ON
			`subdomain_alias`.`alias_id` = `domain_aliasses`.`alias_id`
		WHERE
			`subdomain_alias`.`alias_id` IN (SELECT `alias_id` FROM `domain_aliasses` WHERE `admin_id` = ?)
		AND
			`subdomain_alias_status` = ?
		ORDER BY
			`subdomain_alias_name`
		';

	$rs = exec_query($sql, $query, array($admin_id, $ok_status));

	if ($rs->RecordCount() == 0) {
		$tpl->assign(array(
			'SUB_ID'		=> 'n/a',
			'SUB_SELECTED'	=> 'selected',
			'SUB_NAME'		=> tr('Empty list')
		));
		$tpl->parse('SUB_LIST', 'sub_list');
		$tpl->assign('TO_SUBDOMAIN', '');
		$_SESSION['subdomain_count'] = 'no';
	} else {
		$first_passed = false;
		while (!$rs->EOF) {
			if ($post_check === 'yes') {
				$sub_id = !isset($_POST['sub_id']) ? '' : $_POST['sub_id'];
				$sub_selected = $sub_id == $rs->fields['sub_name'] ? 'selected' : '';
			} else {
				if (!$first_passed) {
					$sub_selected = 'selected';
				} else {
					$sub_selected = '';
				}
			}
			$sub_menu_name = decode_idna($rs->fields['sub_name']);
			//$dmn_menu_name = decode_idna($dmn_name);
			$tpl->assign(array(
				'SUB_ID'	=> $rs->fields['sub_name'],
				'SUB_SELECTED'	=> $sub_selected,
				//'SUB_NAME'	=> $sub_menu_name . '.' . $dmn_menu_name
				'SUB_NAME'	=> $sub_menu_name
			));
			$tpl->parse('SUB_LIST', '.sub_list');
			$rs->MoveNext();
			if (!$first_passed) $first_passed = true;
		}
	}
}

function get_ftp_user_gid(&$sql, $dmn_name, $ftp_user) {
	global $last_gid;
	global $max_gid;

	$query = 'SELECT gid, members FROM ftp_group WHERE groupname = ?';

	$rs = exec_query($sql, $query, array($dmn_name));

	if ($rs->RecordCount() == 0) { // there is no such group. we'll need a new one.
		$props = get_user_default_props($_SESSION['user_id']);

		$query = '
			insert into ftp_group
				(groupname, gid, members)
			values
				 (?, ?, ?)
		';

		$rs = exec_query($sql, $query, array($dmn_name, $props->user_gid, $ftp_user));
		// add entries in the quota tables
		// first check if we have it by one or other reason
		$query = 'SELECT count(*) as `cnt` FROM `quotalimits` WHERE `name` = ?';
		$rs = exec_query($sql, $query, array($dmn_name));
		if ($rs->fields['cnt'] == 0) {
			// ok insert it
			if ($props->max_disk == 0) {
				$dlim = 0;
			} else {
				$dlim = $props->max_disk * 1024 * 1024;
			}

			$query = '
				INSERT INTO quotalimits
					(name, quota_type, per_session, limit_type, bytes_in_avail, bytes_out_avail, bytes_xfer_avail, files_in_avail, files_out_avail, files_xfer_avail)
				VALUES
					(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			';

			$rs = exec_query($sql, $query, array($dmn_name, 'group', 'false', 'hard', $dlim, 0, 0, 0, 0, 0));
		}

		return $temp_dmn_gid;
	} else {
		$ftp_gid = $rs->fields['gid'];
		$members = $rs->fields['members'];

		if (preg_match('/' . $ftp_user . '/', $members) == 0) {
			$members .= ',' . $ftp_user;
		}
		$query = '
			update
				ftp_group
			set
				members = ?
			WHERE
				gid = ?
			and
				groupname = ?
		';
		$rs = exec_query($sql, $query, array($members, $ftp_gid, $dmn_name));
		return $ftp_gid;
	}
}

function get_ftp_user_uid(&$sql, $dmn_name, $ftp_user, $ftp_user_gid) {
	global $max_uid;

	$query = '
		SELECT
			uid
		FROM
			ftp_users
		WHERE
			userid = ?
		AND
			gid = ?
	';

	$rs = exec_query($sql, $query, array($ftp_user, $ftp_user_gid));
	if ($rs->RecordCount() > 0) {
		set_page_message(tr('FTP account already exists!'));
		return -1;
	}
	$props = get_user_default_props($_SESSION['user_id']);

	return $props->user_uid;
}

function add_ftp_user(&$sql, $dmn_name) {
	$username = strtolower(clean_input($_POST['ftp_username']));
	$res_uname = preg_match('/\./', $username, $match);
	if ($res_uname == 1) {
		set_page_message(tr('Incorrect username length or syntax!'));
		return;
	}

	if (!chk_username($username)) {
		set_page_message(tr('Incorrect username length or syntax!'));
		return;
	}
	// Set default values ($ftp_home may be overriden if user
	// has specified a mount point
	switch ($_POST['dmn_type']) {
		// Default moint point for a domain
		case 'dmn':
			$ftp_user = $username . Config::get('FTP_USERNAME_SEPARATOR') . $dmn_name;
			$ftp_home = Config::get('FTP_HOMEDIR') . '/'.$dmn_name;
			break;
		// Default mount point for an alias domain
		case 'als':
			$ftp_user = $username . Config::get('FTP_USERNAME_SEPARATOR') . $_POST['als_id'];
			$alias_mount_point = get_alias_mount_point($sql, $_POST['als_id']);
			$ftp_home = Config::get('FTP_HOMEDIR') . '/'.$dmn_name . $alias_mount_point;
			break;
		// Default mount point for a subdomain
		case 'sub':
			$ftp_user = $username . Config::get('FTP_USERNAME_SEPARATOR') . $_POST['sub_id'] . '.' . $dmn_name;
			$ftp_home = Config::get('FTP_HOMEDIR') . '/'.$dmn_name . clean_input($_POST['sub_id']);
			break;
		// Unknown domain type (?)
		default:
			set_page_message(tr('Unknown domain type'));
			return;
			break;
	}
	// User-specified mount point
	if (isset($_POST['use_other_dir']) && $_POST['use_other_dir'] === 'on') {
		$ftp_vhome = clean_input($_POST['other_dir'], false);
		// Strip possible double-slashes
		$ftp_vhome = str_replace('//', '/', $ftp_vhome);
		// Check for updirs '..'
		$res = preg_match('/\.\./', $ftp_vhome);
		if ($res !== 0) {
			set_page_message(tr('Incorrect mount point length or syntax'));
			return;
		}
		$ftp_home = Config::get('FTP_HOMEDIR') . '/'.$dmn_name . $ftp_vhome;
		// Strip possible double-slashes
		$ftp_home = str_replace('//', '/', $ftp_home);
		// Check for $ftp_vhome existance
		// Create a virtual filesystem (it's important to use =&!)
		$vfs = new vfs($_SESSION['user_id'], $sql);
		// Check for directory existance
		$res = $vfs->exists($ftp_vhome);

		if (!$res) {
			set_page_message(tr('%s does not exist', $ftp_vhome));
			return;
		}
	} // End of user-specified mount-point
	$ftp_gid = get_ftp_user_gid($sql, $dmn_name, $ftp_user);
	$ftp_uid = get_ftp_user_uid($sql, $dmn_name, $ftp_user, $ftp_gid);

	if ($ftp_uid == -1) return;

	$ftp_shell = Config::get('CMD_SHELL');
	$ftp_passwd = crypt_user_pass_with_salt($_POST['pass']);

	$query = '
		INSERT INTO `ftp_users`
			(`admin_id`, `userid`, `passwd`, `uid`, `gid`, `shell`, `homedir`)
		VALUES
			(?, ?, ?, ?, ?, ?, ?)
	';

	$rs = exec_query($sql, $query, array( $_SESSION['user_id'], $ftp_user, $ftp_passwd, $ftp_uid, $ftp_gid, $ftp_shell, $ftp_home));
	write_log($_SESSION['user_logged'] . ': add new FTP account: $ftp_user');
	set_page_message(tr('FTP account added!'));
	header('Location: ftp_accounts.php');
	exit(0);
}

function check_ftp_acc_data(&$tpl, &$sql, $admin_id, $dmn_name) {
	if (!isset($_POST['ftp_username']) || $_POST['ftp_username'] === '') {
		set_page_message(tr('Please enter FTP account username!'));
		return;
	}

	if (!isset($_POST['pass']) || empty($_POST['pass']) || !isset($_POST['pass_rep']) || $_POST['pass_rep'] === '') {
		set_page_message(tr('Password data is missing!'));
		return;
	}

	if ($_POST['pass'] !== $_POST['pass_rep']) {
		set_page_message(tr('Entered passwords differ FROM the another!'));
		return;
	}

	if (!chk_password($_POST['pass'])) {
		if(Config::get('PASSWD_STRONG')){
		  set_page_message(tr('The password must be at least %s long and contain letters and numbers to be valid.', Config::get('PASSWD_CHARS')));
		} else {
		  set_page_message(tr('Password data is shorter than %s signs or includes not permitted signs!', Config::get('PASSWD_CHARS')));
		}
		return;
	}

	if ($_POST['dmn_type'] === 'sub' && $_POST['sub_id'] === 'n/a') {
		set_page_message(tr('Subdomain list is empty! You can not add FTP accounts there!'));
		return;
	}

	if ($_POST['dmn_type'] === 'als' && $_POST['als_id'] === 'n/a') {
		set_page_message(tr('Alias list is empty! You can not add FTP accounts there!'));
		return;
	}

	if (isset($_POST['use_other_dir']) && $_POST['use_other_dir'] === 'on' && empty($_POST['other_dir'])) {
		set_page_message(tr('Please specify other FTP account dir!'));
		return;
	}

	add_ftp_user($sql, $dmn_name);
}

function gen_page_ftp_acc_props(&$tpl, &$sql, $admin_id) {

	$props		= get_user_default_props($admin_id);
	$ftp_cnt	= get_user_running_ftp_acc_cnt($admin_id);

	if ($props->max_ftp != 0 && $ftp_cnt['cnt'] >= $props->max_ftp) {
		set_page_message(tr('FTP accounts limit reached!'));
		header('Location: ftp_accounts.php');
		die();
	} else {
		if (!isset($_POST['uaction'])) {
			gen_page_form_data($tpl, $_SESSION['user_logged'], 'no');
			gen_dmn_als_list($tpl, $sql, $admin_id, 'no');
			gen_dmn_sub_list($tpl, $sql, $admin_id, $_SESSION['user_logged'], 'no');
			gen_page_js($tpl);
		} else if (isset($_POST['uaction']) && $_POST['uaction'] === 'add_user') {
			gen_page_form_data($tpl, $_SESSION['user_logged'], 'yes');
			gen_dmn_als_list($tpl, $sql, $admin_id, 'yes');
			gen_dmn_sub_list($tpl, $sql, $admin_id, $_SESSION['user_logged'], 'yes');
			check_ftp_acc_data($tpl, $sql, $admin_id, $_SESSION['user_logged']);
		}
	}
}

function gen_page_js(&$tpl) {
	if (isset($_SESSION['subdomain_count']) && isset($_SESSION['alias_count'])) { // no subdomains and no alias
		$tpl->parse('JS_NOT_DOMAIN', 'js_not_domain');
		$tpl->assign('JS_TO_SUBDOMAIN', '');
		$tpl->assign('JS_TO_ALIAS_DOMAIN', '');
		$tpl->assign('JS_TO_ALL_DOMAIN', '');
	} else if (isset($_SESSION['subdomain_count']) && !isset($_SESSION['alias_count'])) { // no subdomains - alaias available
		$tpl->assign('JS_NOT_DOMAIN', '');
		$tpl->assign('JS_TO_SUBDOMAIN', '');
		$tpl->parse('JS_TO_ALIAS_DOMAIN', 'js_to_alias_domain');
		$tpl->assign('JS_TO_ALL_DOMAIN', '');
	} else if (!isset($_SESSION['subdomain_count']) && isset($_SESSION['alias_count'])) { // no alias - subdomain available
		$tpl->assign('JS_NOT_DOMAIN', '');
		$tpl->parse('JS_TO_SUBDOMAIN', 'js_to_subdomain');
		$tpl->assign('JS_TO_ALIAS_DOMAIN', '');
		$tpl->assign('JS_TO_ALL_DOMAIN', '');
	} else { // ther are subdomains and aliases
		$tpl->assign('JS_NOT_DOMAIN', '');
		$tpl->assign('JS_TO_SUBDOMAIN', '');
		$tpl->assign('JS_TO_ALIAS_DOMAIN', '');
		$tpl->parse('JS_TO_ALL_DOMAIN', 'js_to_all_domain');
	}

	if (isset($GLOBALS['subdomain_count']))
		unset($GLOBALS['subdomain_count']);
	if (isset($GLOBALS['alias_count']))
		unset($GLOBALS['alias_count']);
	if (isset($_SESSION['subdomain_count']))
		unset($_SESSION['subdomain_count']);
	if (isset($_SESSION['alias_count']))
		unset($_SESSION['alias_count']);
}

// common page data.

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(array(
	'TR_PAGE_TITLE'	=> tr('Selity - Client/Add FTP User'),
	'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
	'THEME_CHARSET'		=> tr('encoding'),
	'ISP_LOGO'		=> get_logo($_SESSION['user_id'])
));

// dynamic page data.

gen_page_ftp_acc_props($tpl, $sql, $_SESSION['user_id']);

// static page messages.

gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_ftp_accounts.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_ftp_accounts.tpl');

gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(array(
	'TR_ADD_FTP_USER'		=> tr('Add FTP user'),
	'TR_USERNAME'			=> tr('Username'),
	'TR_TO_MAIN_USER'		=> tr('To main user'),
	'TR_TO_DOMAIN_ALIAS'	=> tr('To domain alias'),
	'TR_TO_SUBDOMAIN'		=> tr('To subdomain'),
	'TR_PASSWORD'			=> tr('Password'),
	'TR_PASSWORD_REPEAT'	=> tr('Repeat password'),
	'TR_USE_OTHER_DIR'		=> tr('Use other dir'),
	'TR_ADD'				=> tr('Add'),
	'CHOOSE_DIR'			=> tr('Choose dir'),
	'FTP_SEPARATOR'			=> Config::get('FTP_USERNAME_SEPARATOR')
));

gen_page_message($tpl);
$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
