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
$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/sql_user_add.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');
$tpl->define_dynamic('mysql_prefix_no', 'page');
$tpl->define_dynamic('mysql_prefix_yes', 'page');
$tpl->define_dynamic('mysql_prefix_infront', 'page');
$tpl->define_dynamic('mysql_prefix_behind', 'page');
$tpl->define_dynamic('mysql_prefix_all', 'page');
$tpl->define_dynamic('sqluser_list', 'page');
$tpl->define_dynamic('show_sqluser_list', 'page');
$tpl->define_dynamic('create_sqluser', 'page');

if (isset($_GET['id'])) {
	$db_id = $_GET['id'];
} else if (isset($_POST['id'])) {
	$db_id = $_POST['id'];
} else {
	user_goto('sql_manage.php');
}

// page functions.

function check_sql_permissions(&$tpl, $sql, $admin_id, $db_id, $sqluser_available) {

	$props = get_user_default_props($admin_id);
	list($sqld_acc_cnt, $sqlu_acc_cnt) = get_user_running_sql_acc_cnt($admin_id);

	if ($props->max_sqlu != 0 && $sqlu_acc_cnt >= $props->max_sqlu) {
		if (!$sqluser_available) {
			set_page_message(tr('SQL users limit reached!'));
			header("Location: sql_manage.php");
			die();
		} else {
			$tpl->assign('CREATE_SQLUSER', '');
		}
	}

	$dmn_name = $_SESSION['user_logged'];

	$query = "
		SELECT
			t1.`sqld_id`, t2.`admin_id`, t2.`admin_name`
		FROM
			`sql_database` as t1,
			`admin` as t2
		WHERE
			t1.`sqld_id` = ?
		AND
			t2.`admin_id` = t1.`admin_id`
		AND
			t2.`admin_id` = ?
	";

	$rs = exec_query($sql, $query, array($db_id, $admin_id));

	if ($rs->RecordCount() == 0) {
		set_page_message(tr('User does not exist or you do not have permission to access this interface!'));
		header('Location: sql_manage.php');
		die();
	}
}
// Returns an array with a list of the sqlusers of the current database
function get_sqluser_list_of_current_db(&$sql, $db_id) {
	$query = "SELECT `sqlu_name` FROM `sql_user` WHERE `sqld_id` = ?";

	$rs = exec_query($sql, $query, array($db_id));

	if ($rs->RecordCount() == 0) {
		return false;
	} else {
		while (!$rs->EOF) {
			$userlist[] = $rs->fields['sqlu_name'];
			$rs->MoveNext();
		}
	}

	return $userlist;
}

function gen_sql_user_list(&$sql, &$tpl, $admin_id, $db_id) {
	$first_passed = true;
	$user_found = false;
	$oldrs_name = '';
	$userlist = get_sqluser_list_of_current_db($sql, $db_id);
	// Lets SELECT all sqlusers of the current domain except the users of the current database
	$query = "
		SELECT
			t1.`sqlu_name`,
			t1.`sqlu_id`
		FROM
			`sql_user` AS t1,
			`sql_database` AS t2
		WHERE
			t1.`sqld_id` = t2.`sqld_id`
			AND
			t2.`admin_id` = ?
		AND
			t1.`sqld_id` <> ?
		ORDER BY
			t1.`sqlu_name`
	";

	$rs = exec_query($sql, $query, array($admin_id, $db_id));

	while (!$rs->EOF) {
		// Checks if it's the first element of the combobox and set it as selected
		if ($first_passed) {
			$SELECT = "selected";
			$first_passed = false;
		} else {
			$SELECT = '';
		}
		// 1. Compares the sqluser name with the record before (Is set as '' at the first time, see above)
		// 2. Compares the sqluser name with the userlist of the current database
		if ($oldrs_name != $rs->fields['sqlu_name'] && @!in_array($rs->fields['sqlu_name'], $userlist)) {
			$user_found = true;
			$oldrs_name = $rs->fields['sqlu_name'];
			$tpl->assign(
				array(
					'SQLUSER_ID' => $rs->fields['sqlu_id'],
					'SQLUSER_SELECTED' => $SELECT,
					'SQLUSER_NAME' => $rs->fields['sqlu_name']
				)
			);
			$tpl->parse('SQLUSER_LIST', '.sqluser_list');
		}
		$rs->MoveNext();
	}
	// Lets hide the combobox in case there are no other sqlusers
	if (!$user_found) {
		$tpl->assign('SHOW_SQLUSER_LIST', '');
		return false;
	} else {
		return true;
	}
}

function check_db_user(&$sql, $db_user) {
	$query = "SELECT count(*) AS cnt FROM mysql.user WHERE User=?";

	$rs = exec_query($sql, $query, array($db_user));
	return $rs->fields['cnt'];
}

function add_sql_user(&$sql, $admin_id, $db_id) {
	if (!isset($_POST['uaction'])) return;

	// let's check user input;

	if (empty($_POST['user_name']) && !isset($_POST['Add_Exist'])) {
		set_page_message(tr('Please type user name!'));
		return;
	}

	if (empty($_POST['pass']) && empty($_POST['pass_rep']) && !isset($_POST['Add_Exist'])) {
		set_page_message(tr('Please type user password!'));
		return;
	}

	if ((isset($_POST['pass']) AND isset($_POST['pass_rep'])) && $_POST['pass'] !== $_POST['pass_rep'] AND !isset($_POST['Add_Exist'])) {
		set_page_message(tr('Entered passwords do not match!'));
		return;
	}

	if (isset($_POST['pass']) AND strlen($_POST['pass']) > Config::get('MAX_SQL_PASS_LENGTH') && !isset($_POST['Add_Exist'])) {
		set_page_message(tr('Too user long password!'));
		return;
	}

	if (isset($_POST['pass']) AND !chk_password($_POST['pass']) AND !isset($_POST['Add_Exist'])) {
		if(Config::get('PASSWD_STRONG')){
	  set_page_message(sprintf(tr('The password must be at least %s long and contain letters and numbers to be valid.'), Config::get('PASSWD_CHARS')));
	} else {
	  set_page_message(sprintf(tr('Password data is shorter than %s signs or includes not permitted signs!'), Config::get('PASSWD_CHARS')));
	}
		return;
	}

	if (isset($_POST['Add_Exist'])) {
		$query = "SELECT `sqlu_pass` FROM `sql_user` WHERE `sqlu_id` = ?";
		$rs = exec_query($sql, $query, array($_POST['sqluser_id']));

		if ($rs->RecordCount() == 0) {
			set_page_message(tr('SQL-user not found! Maybe it was deleted by another user!'));
			return;
		}
		$user_pass = $rs->fields['sqlu_pass'];
	} else {
		$user_pass = $_POST['pass'];
	}

	if (!isset($_POST['Add_Exist'])) {

		// we'll use domain_id in the name of the database;
		if (isset($_POST['use_dmn_id']) && $_POST['use_dmn_id'] === 'on' && isset($_POST['id_pos']) && $_POST['id_pos'] === 'start') {
			$db_user = $admin_id . "_" . clean_input($_POST['user_name']);
		} else if (isset($_POST['use_dmn_id']) && $_POST['use_dmn_id'] === 'on' && isset($_POST['id_pos']) && $_POST['id_pos'] === 'end') {
			$db_user = clean_input($_POST['user_name']) . "_" . $admin_id;
		} else {
			$db_user = clean_input($_POST['user_name']);
		}
	} else {
		$query = "SELECT sqlu_name FROM sql_user WHERE sqlu_id = ?";
		$rs = exec_query($sql, $query, array($_POST['sqluser_id']));
		$db_user = $rs->fields['sqlu_name'];
	}

	if (strlen($db_user) > Config::get('MAX_SQL_USER_LENGTH')) {
		set_page_message(tr('User name too long!'));
		return;
	}
	// are wildcards used?

	if (preg_match("/[%|\?]+/", $db_user)) {
		set_page_message(tr('Wildcards as %% and ? are not allowed!'));
		return;
	}

	// have we such sql user in the system?!

	if (check_db_user($sql, $db_user) && !isset($_POST['Add_Exist'])) {
		set_page_message(tr('Specified SQL username name already exists!'));
		return;
	}

	// add user in the selity table;

	$query = "
		INSERT INTO `sql_user`
			(`sqld_id`, `sqlu_name`, `sqlu_pass`)
		VALUES
			(?, ?, ?)
	";

	$rs = exec_query($sql, $query, array($db_id, $db_user, $user_pass));

	$query = "
		SELECT
			`sqld_name` as `db_name`
		FROM
			`sql_database`
		WHERE
			`sqld_id` = ?
		AND
			`admin_id` = ?
	";

	$rs = exec_query($sql, $query, array($db_id, $admin_id));
	$db_name = $rs->fields['db_name'];

	// add user in the mysql system tables;

	$new_db_name = ereg_replace("_", "\\_", $db_name);
	$query = 'grant all on ' . quoteIdentifier($new_db_name) . '.* to ?@\'localhost\' identified by ?';
	$rs = exec_query($sql, $query, array($db_user, $user_pass));
	$query = 'grant all on ' . quoteIdentifier($new_db_name) . '.* to ?@\'%\' identified by ?';
	$rs = exec_query($sql, $query, array($db_user, $user_pass));

	write_log($_SESSION['user_logged'] . ": add SQL user: " . $db_user);
	set_page_message(tr('SQL user successfully added!'));
	user_goto('sql_manage.php');
}

function gen_page_post_data(&$tpl, $db_id) {
	if (Config::get('MYSQL_PREFIX') === 'yes') {
		$tpl->assign('MYSQL_PREFIX_YES', '');
		if (Config::get('MYSQL_PREFIX_TYPE') === 'behind') {
			$tpl->assign('MYSQL_PREFIX_INFRONT', '');
			$tpl->parse('MYSQL_PREFIX_BEHIND', 'mysql_prefix_behind');
			$tpl->assign('MYSQL_PREFIX_ALL', '');
		} else {
			$tpl->parse('MYSQL_PREFIX_INFRONT', 'mysql_prefix_infront');
			$tpl->assign('MYSQL_PREFIX_BEHIND', '');
			$tpl->assign('MYSQL_PREFIX_ALL', '');
		}
	} else {
		$tpl->assign('MYSQL_PREFIX_NO', '');
		$tpl->assign('MYSQL_PREFIX_INFRONT', '');
		$tpl->assign('MYSQL_PREFIX_BEHIND', '');
		$tpl->parse('MYSQL_PREFIX_ALL', 'mysql_prefix_all');
	}

	if (isset($_POST['uaction']) && $_POST['uaction'] === 'add_user') {
		$tpl->assign(array('USER_NAME' => (isset($_POST['user_name'])) ? $_POST['user_name'] : '',
				'USE_DMN_ID' => (isset($_POST['use_dmn_id']) && $_POST['use_dmn_id'] === 'on') ? 'checked' : '',
				'START_ID_POS_CHECKED' => (isset($_POST['id_pos']) && $_POST['id_pos'] !== 'end') ? 'checked' : '',
				'END_ID_POS_CHECKED' => (isset($_POST['id_pos']) && $_POST['id_pos'] === 'end') ? 'checked' : ''));
	} else {
		$tpl->assign(array('USER_NAME' => '',
				'USE_DMN_ID' => '',
				'START_ID_POS_CHECKED' => '',
				'END_ID_POS_CHECKED' => 'checked'));
	}

	$tpl->assign('ID', $db_id);
}

// common page data.

if (isset($_SESSION['sql_support']) && $_SESSION['sql_support'] == "no") {
	user_goto('index.php');
}

$theme_color = Config::get('USER_INITIAL_THEME');
$tpl->assign(array('TR_PAGE_TITLE' => tr('Selity - Client/Add SQL User'),
		'THEME_COLOR_PATH' => '../themes/'.$theme_color,
		'THEME_CHARSET' => tr('encoding'),
		'ISP_LOGO' => get_logo($_SESSION['user_id'])));

// dynamic page data.

$sqluser_available = gen_sql_user_list($sql, $tpl, $_SESSION['user_id'], $db_id);
check_sql_permissions($tpl, $sql, $_SESSION['user_id'], $db_id, $sqluser_available);
gen_page_post_data($tpl, $db_id);
add_sql_user($sql, $_SESSION['user_id'], $db_id);

// static page messages.

gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_manage_sql.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_manage_sql.tpl');

gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(array('TR_ADD_SQL_USER' => tr('Add SQL user'),
		'TR_USER_NAME' => tr('SQL user name'),
		'TR_USE_DMN_ID' => tr('Use numeric ID'),
		'TR_START_ID_POS' => tr('In front the name'),
		'TR_END_ID_POS' => tr('Behind the name'),
		'TR_ADD' => tr('Add'),
		'TR_CANCEL' => tr('Cancel'),
		'TR_ADD_EXIST' => tr('Add existing user'),
		'TR_PASS' => tr('Password'),
		'TR_PASS_REP' => tr('Repeat password'),
		'TR_SQL_USER_NAME' => tr('Existing SQL users')));

gen_page_message($tpl);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

