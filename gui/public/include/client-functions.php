<?php
/**
 * Selity - A server control panel
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2008 by ispCP | http://isp-control.net
 * @version		SVN: $Id$
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

function get_user_default_props($admin_id) {
	static $props = null;
	if($props == null){
		$query = '
			SELECT
				*
			FROM
				`user_system_props`
			WHERE
				`user_admin_id` = ?
		';
		$props = mysql::getInstance()->doQuery($query, $admin_id);
	}
	return $props;
}

function get_user_running_props_cnt($admin_id) {

	$sql = mysql::getInstance();
	return array(
		'sub_cnt'	=> $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `subdomain_alias` WHERE `alias_id` IN (SELECT `alias_id` FROM `domain_aliasses` WHERE `admin_id` =?)', $admin_id)->cnt,
		'als_cnt'	=> $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `domain_aliasses` WHERE `admin_id` =?', $admin_id)->cnt,
		'mail_cnt'	=> $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `mail_users` WHERE `admin_id` = ? AND `mail_type` NOT RLIKE \'_catchall\'', $admin_id)->cnt,
		'ftp_cnt'	=> $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `ftp_users` WHERE `admin_id` =?', $admin_id)->cnt,
		'sqld_cnt'	=> $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `sql_database` WHERE `admin_id` =?', $admin_id)->cnt,
		'sqlu_cnt'	=> $sql->doQuery('SELECT DISTINCT t1.sqlu_name FROM sql_user AS t1, sql_database AS t2 WHERE t2.admin_id = ? AND t2.sqld_id = t1.sqld_id', $admin_id)->countRows(),
	);
}

function get_user_running_sqld_acc_cnt($admin_id) {
	$query = '
		SELECT
			COUNT(*) AS `cnt`
		FROM
			`sql_database`
		WHERE
			`admin_id` = ?
	';
	return mysql::getInstance()->doQuery($query, array($admin_id))->cnt;
}

function get_user_running_sqlu_acc_cnt($admin_id) {
	$query = '
		SELECT DISTINCT
			`t1`.`sqlu_name`
		FROM
			`sql_user` AS `t1`, `sql_database` AS `t2`
		WHERE
			`t2`.`admin_id` = ?
		AND
			`t2`.`sqld_id` = `t1`.`sqld_id`
	';
	return mysql::getInstance()->doQuery($query, $admin_id)->countRows();
}

function get_user_running_sql_acc_cnt($admin_id) {
	$sqld_acc_cnt = get_user_running_sqld_acc_cnt($admin_id);
	$sqlu_acc_cnt = get_user_running_sqlu_acc_cnt($admin_id);
	return array($sqld_acc_cnt, $sqlu_acc_cnt);
}

function get_user_running_ftp_acc_cnt($admin_id) {

	$usr_ftp_acc_cnt = get_user_running_usr_ftp_acc_cnt($admin_id);
	$sub_ftp_acc_cnt = get_user_running_sub_ftp_acc_cnt($admin_id);
	$als_ftp_acc_cnt = get_user_running_als_ftp_acc_cnt($admin_id);

	return array(
		'cnt'		=> $usr_ftp_acc_cnt + $sub_ftp_acc_cnt + $als_ftp_acc_cnt,
		'usr_cnt'	=> $usr_ftp_acc_cnt,
		'sub_cnt'	=> $sub_ftp_acc_cnt,
		'als_cnt'	=> $als_ftp_acc_cnt
	);
}

function get_user_running_usr_ftp_acc_cnt($admin_id) {
	$ftp_separator=Config::get('FTP_USERNAME_SEPARATOR');
	$query = '
		SELECT
			COUNT(*) AS `cnt`
		FROM
			`ftp_users`
		WHERE
			`userid` RLIKE CONCAT(?, ".", (SELECT `admin_name` FROM `admin` WHERE admin_id = ?))
	';
	return  mysql::getInstance()->doQuery($query, $ftp_separator, $admin_id)->cnt;
}

function get_user_running_sub_ftp_acc_cnt($admin_id) {
	$ftp_separator		= Config::get('FTP_USERNAME_SEPARATOR');
	$sub_ftp_acc_cnt	= 0;
	$query = '
		SELECT
			CONCAT(`subdomain_alias_name`,".",`alias_name`) AS `name`
		FROM
			`domain_aliasses`
		LEFT JOIN
			`subdomain_alias`
		ON
			`subdomain_alias`.`alias_id` = `domain_aliasses`.`alias_id`
		WHERE
			`admin_id` = ?
		ORDER BY
			`subdomain_alias_id`
	';
	$rs = mysql::getInstance()->doQuery($query, $admin_id);
	while (!$rs->EOF) {
		$query = '
			SELECT
				COUNT(*) AS `cnt`
			FROM
				`ftp_users`
			WHERE
				`userid` RLIKE ?
		';

		$rs_cnt = mysql::getInstance()->doQuery($query, $ftp_separator.$rs->name);
		$sub_ftp_acc_cnt += $rs_cnt->cnt;
		$rs->nextRow();
	}
	return $sub_ftp_acc_cnt;
}

function get_user_running_als_ftp_acc_cnt($admin_id) {
	$ftp_separator=Config::get('FTP_USERNAME_SEPARATOR');
	$query = '
		SELECT
			COUNT(*) AS `cnt`
		FROM
			`ftp_users`
		WHERE
			`userid` RLIKE (SELECT `alias_name` FROM `domain_aliasses` WHERE `admin_id` = ?)
	';
	return mysql::getInstance()->doQuery($query, $admin_id)->cnt;
}

function get_user_running_als_cnt($admin_id) {
	$query = 'SELECT COUNT(*) AS `cnt` FROM `domain_aliasses` WHERE `admin_id` = ?';
	return mysql::getInstance()->doQuery($query, $admin_id)->cnt;
}

function get_user_running_sub_cnt($admin_id) {
	$query = '
		SELECT
			COUNT(*) AS `cnt`
		FROM
			`subdomain_alias`
		WHERE
			`alias_id` IN (SELECT `alias_id` FROM `domain_aliasses` WHERE `admin_id`=?)
	';
	return mysql::getInstance()->doQuery($query, $admin_id)->cnt;
}

function get_user_running_mail_acc_cnt($admin_id) {

	$query = '
		SELECT
			COUNT(*) AS `cnt`
		FROM
			`mail_users`
		WHERE
			`mail_type` RLIKE \'alias_\'
		AND
			`mail_type` NOT LIKE \'alias_catchall\'
		AND
			`admin_id` = ?
	';
	$rs = mysql::getInstance()->doQuery($query, $admin_id);
	$als_mail_acc = $rs->cnt;

	$query = '
		SELECT
			COUNT(*) AS `cnt`
		FROM
			`mail_users`
		WHERE
			`mail_type` RLIKE \'alssub_\'
		AND
			`mail_type` NOT LIKE \'alssub_catchall\'
		AND
			`admin_id` = ?
	';

	$rs = mysql::getInstance()->doQuery($query, $admin_id);
	$alssub_mail_acc = $rs->cnt;

	return array(
		'cnt'		=> $als_mail_acc + $alssub_mail_acc,
		'sub_cnt'	=> $als_mail_acc,
		'als_cnt'	=> $alssub_mail_acc
	);
}

//////////

function gen_client_mainmenu(&$tpl, $menu_file) {
	$sql = Database::getInstance();

	$tpl->define_dynamic('menu', $menu_file);
	$tpl->define_dynamic('isactive_awstats', 'menu');
	$tpl->define_dynamic('isactive_domain', 'menu');
	$tpl->define_dynamic('isactive_email', 'menu');
	$tpl->define_dynamic('isactive_ftp', 'menu');
	$tpl->define_dynamic('isactive_sql', 'menu');
	$tpl->define_dynamic('isactive_support', 'menu');
	$tpl->define_dynamic('custom_buttons', 'menu');

	$tpl->assign(
			array(
				'TR_MENU_GENERAL_INFORMATION' => tr('General information'),
				'TR_MENU_CHANGE_PASSWORD' => tr('Change password'),
				'TR_MENU_CHANGE_PERSONAL_DATA' => tr('Change personal data'),
				'TR_MENU_MANAGE_DOMAINS' => tr('Manage domains'),
				'TR_MENU_ADD_SUBDOMAIN' => tr('Add subdomain'),
				'TR_MENU_MANAGE_USERS' => tr('Email and FTP accounts'),
				'TR_MENU_ADD_MAIL_USER' => tr('Add mail user'),
				'TR_MENU_ADD_FTP_USER' => tr('Add FTP user'),
				'TR_MENU_MANAGE_SQL' => tr('Manage SQL'),
				'TR_MENU_ERROR_PAGES' => tr('Error pages'),
				'TR_MENU_ADD_SQL_DATABASE' => tr('Add SQL database'),
				'TR_MENU_DOMAIN_STATISTICS' => tr('Domain statistics'),
				'TR_MENU_DAILY_BACKUP' => tr('Daily backup'),
				'TR_MENU_QUESTIONS_AND_COMMENTS' => tr('Support system'),
				'TR_MENU_NEW_TICKET' => tr('New ticket'),
				'TR_MENU_LOGOUT' => tr('Logout'),
				'PHP_MY_ADMIN' => tr('PhpMyAdmin'),
				'TR_WEBMAIL' => tr('Webmail'),
				'TR_FILEMANAGER' => tr('Filemanager'),
				'TR_MENU_WEBTOOLS' => tr('Webtools'),
				'TR_HTACCESS' => tr('Protected areas'),
				'TR_AWSTATS' => tr('Web statistics'),
				'TR_HTACCESS_USER' => tr('Group/User management'),
				'TR_MENU_OVERVIEW' => tr('Overview'),
				'TR_MENU_EMAIL_ACCOUNTS' => tr('Email Accounts'),
				'TR_MENU_FTP_ACCOUNTS' => tr('FTP Accounts'),
				'TR_MENU_LANGUAGE' => tr('Language'),
				'TR_MENU_CATCH_ALL_MAIL' => tr('Catch all'),
				'TR_MENU_ADD_ALIAS' => tr('Add alias'),
				'TR_MENU_UPDATE_HP' => tr('Update Hosting Package'),
				'SUPPORT_SYSTEM_PATH' => Config::get('SELITY_SUPPORT_SYSTEM_PATH'),
				'SUPPORT_SYSTEM_TARGET' => Config::get('SELITY_SUPPORT_SYSTEM_TARGET'),
				'WEBMAIL_PATH' => Config::get('WEBMAIL_PATH'),
				'WEBMAIL_TARGET' => Config::get('WEBMAIL_TARGET'),
				'PMA_PATH' => Config::get('PMA_PATH'),
				'PMA_TARGET' => Config::get('PMA_TARGET'),
				'FILEMANAGER_PATH' => Config::get('FILEMANAGER_PATH'),
				'FILEMANAGER_TARGET' => Config::get('FILEMANAGER_TARGET'),
			)
		);

	$query = '
		SELECT
			*
		FROM
			custom_menus
		WHERE
			menu_level = \'user\'
		  OR
			menu_level = \'all\'
';

	$rs = exec_query($sql, $query, array());
	if ($rs->RecordCount() == 0) {
		$tpl->assign('CUSTOM_BUTTONS', '');
	} else {
		global $i;
		$i = 100;

		while (!$rs->EOF) {
			$menu_name = $rs->fields['menu_name'];
			$menu_link = get_menu_vars($rs->fields['menu_link']);
			$menu_target = $rs->fields['menu_target'];
			$menu_link = str_replace('{selity_uname}', $_SESSION['user_logged'], $menu_link);

			if ($menu_target === '') {
				$menu_target = "";
			} else {
				$menu_target = "target=\"" . $menu_target . "\"";
			}

			$tpl->assign(
					array(
						'BUTTON_LINK' => $menu_link,
						'BUTTON_NAME' => $menu_name,
						'BUTTON_TARGET' => $menu_target,
						'BUTTON_ID' => $i,
						)
				);

			$tpl->parse('CUSTOM_BUTTONS', '.custom_buttons');
			$rs->MoveNext();
			$i++;
		} // end while
	} // end else

	$props = get_user_default_props($_SESSION['user_id']);

	if ($props->max_mail == -1) $tpl->assign('ISACTIVE_EMAIL', '');
	if (($props->max_als == -1) && ($dmn_subd_limit == -1)) $tpl->assign('ISACTIVE_DOMAIN', '');
	if ($props->max_ftp == -1) $tpl->assign('ISACTIVE_FTP', '');
	if ($props->max_sqldb == -1) $tpl->assign('ISACTIVE_SQL', '');

	if (!Config::get('SELITY_SUPPORT_SYSTEM')) {
		$tpl->assign('ISACTIVE_SUPPORT', '');
	}

	if (Config::get('AWSTATS_ACTIVE') == 'no') {
		$tpl->assign('ISACTIVE_AWSTATS', '');
	} else {
		$tpl->assign(
			array(
				'AWSTATS_PATH' => 'http://' . $_SESSION['user_logged'] . '/stats/',
				'AWSTATS_TARGET' => '_blank'
				)
			);
	}

	$tpl->parse('MAIN_MENU', 'menu');
}

function gen_client_menu(&$tpl, $menu_file) {
	$sql = Database::getInstance();

	$tpl->define_dynamic('menu', $menu_file);
	$tpl->define_dynamic('custom_buttons', 'menu');
	$tpl->define_dynamic('isactive_update_hp', 'menu');

	$tpl->assign(
			array(
				'TR_MENU_GENERAL_INFORMATION' => tr('General information'),
				'TR_MENU_CHANGE_PASSWORD' => tr('Change password'),
				'TR_MENU_CHANGE_PERSONAL_DATA' => tr('Change personal data'),
				'TR_MENU_MANAGE_DOMAINS' => tr('Manage domains'),
				'TR_MENU_ADD_SUBDOMAIN' => tr('Add subdomain'),
				'TR_MENU_MANAGE_USERS' => tr('Email and FTP accounts'),
				'TR_MENU_ADD_MAIL_USER' => tr('Add mail user'),
				'TR_MENU_ADD_FTP_USER' => tr('Add FTP user'),
				'TR_MENU_MANAGE_SQL' => tr('Manage SQL'),
				'TR_MENU_ERROR_PAGES' => tr('Error pages'),
				'TR_MENU_ADD_SQL_DATABASE' => tr('Add SQL database'),
				'TR_MENU_DOMAIN_STATISTICS' => tr('Domain statistics'),
				'TR_MENU_DAILY_BACKUP' => tr('Daily backup'),
				'TR_MENU_QUESTIONS_AND_COMMENTS' => tr('Support system'),
				'TR_MENU_NEW_TICKET' => tr('New ticket'),
				'TR_MENU_LOGOUT' => tr('Logout'),
				'PHP_MY_ADMIN' => tr('PhpMyAdmin'),
				'TR_WEBMAIL' => tr('Webmail'),
				'TR_FILEMANAGER' => tr('Filemanager'),
				'TR_MENU_WEBTOOLS' => tr('Webtools'),
				'TR_HTACCESS' => tr('Protected areas'),
				'TR_AWSTATS' => tr('Web statistics'),
				'TR_HTACCESS_USER' => tr('Group/User management'),
				'TR_MENU_OVERVIEW' => tr('Overview'),
				'TR_MENU_EMAIL_ACCOUNTS' => tr('Email Accounts'),
				'TR_MENU_FTP_ACCOUNTS' => tr('FTP Accounts'),
				'TR_MENU_LANGUAGE' => tr('Language'),
				'TR_MENU_CATCH_ALL_MAIL' => tr('Catch all'),
				'TR_MENU_ADD_ALIAS' => tr('Add alias'),
				'TR_MENU_UPDATE_HP' => tr('Update Hosting Package'),
				'SUPPORT_SYSTEM_PATH' => Config::get('SELITY_SUPPORT_SYSTEM_PATH'),
				'SUPPORT_SYSTEM_TARGET' => Config::get('SELITY_SUPPORT_SYSTEM_TARGET'),
				'WEBMAIL_PATH' => Config::get('WEBMAIL_PATH'),
				'WEBMAIL_TARGET' => Config::get('WEBMAIL_TARGET'),
				'PMA_PATH' => Config::get('PMA_PATH'),
				'PMA_TARGET' => Config::get('PMA_TARGET'),
				'FILEMANAGER_PATH' => Config::get('FILEMANAGER_PATH'),
				'FILEMANAGER_TARGET' => Config::get('FILEMANAGER_TARGET'),
				'VERSION' => Config::get('Version'),
				'BUILDDATE' => Config::get('BuildDate'),
				'CODENAME' => Config::get('CodeName')
				)
		);

	$query = "
		SELECT
			*
		FROM
			`custom_menus`
		WHERE
			`menu_level` = 'user'
		OR
			`menu_level` = 'all'
	";

	$rs = exec_query($sql, $query, array());
	if ($rs->RecordCount() == 0) {
		$tpl->assign('CUSTOM_BUTTONS', '');
	} else {
		global $i;
		$i = 100;

		while (!$rs->EOF) {
			$menu_name = $rs->fields['menu_name'];
			$menu_link = get_menu_vars($rs->fields['menu_link']);
			$menu_target = $rs->fields['menu_target'];

			if ($menu_target === '') {
				$menu_target = "";
			} else {
				$menu_target = "target=\"" . $menu_target . "\"";
			}

			$tpl->assign(
				array(
					'BUTTON_LINK' => $menu_link,
					'BUTTON_NAME' => $menu_name,
					'BUTTON_TARGET' => $menu_target,
					'BUTTON_ID' => $i,
					)
				);

			$tpl->parse('CUSTOM_BUTTONS', '.custom_buttons');
			$rs->MoveNext();
			$i++;
		} // end while
	} // end else
	if (!Config::get('SELITY_SUPPORT_SYSTEM')) {
		$tpl->assign('SUPPORT_SYSTEM', '');
	}

	$props = get_user_default_props($_SESSION['user_id']);

	if ($props->max_mail == -1) $tpl->assign('ACTIVE_EMAIL', '');

	if (Config::get('AWSTATS_ACTIVE') != 'yes') {
		$tpl->assign('ACTIVE_AWSTATS', '');
	} else {
		$tpl->assign(
			array(
				'AWSTATS_PATH' => 'http://' . $_SESSION['user_logged'] . '/stats/',
				'AWSTATS_TARGET' => '_blank'
				)
			);
	}

	# Hide 'Update Hosting Package'-Button, if there are none
	$query = '
		SELECT
			id
		FROM
			hosting_plans
		WHERE
			reseller_id = ?
		AND
			status = \'1\'
	';

	$rs = exec_query($sql, $query, array($_SESSION['user_created_by']));
	if ($rs->RecordCount() == 0) {
		$tpl->assign('ISACTIVE_UPDATE_HP', '');
	}

	$tpl->parse('MENU', 'menu');
}

function user_trans_mail_type($mail_type) {
	if ($mail_type === MT_NORMAL_MAIL) {
		return tr('Domain mail');
	} else if ($mail_type === MT_NORMAL_FORWARD) {
		return tr('Email forward');
	} else if ($mail_type === MT_ALIAS_MAIL) {
		return tr('Alias mail');
	} else if ($mail_type === MT_ALIAS_FORWARD) {
		return tr('Alias forward');
	} else if ($mail_type === MT_SUBDOM_MAIL) {
		return tr('Subdomain mail');
	} else if ($mail_type === MT_SUBDOM_FORWARD) {
		return tr('Subdomain forward');
	} else if ($mail_type === MT_ALSSUB_MAIL) {
		return tr('Alias subdomain mail');
	} else if ($mail_type === MT_ALSSUB_FORWARD) {
		return tr('Alias subdomain forward');
	} else if ($mail_type === MT_NORMAL_CATCHALL) {
		return tr('Domain mail');
	} else if ($mail_type === MT_ALIAS_CATCHALL) {
		return tr('Domain mail');
	} else {
		return tr('Unknown type');
	}
}

function user_goto($dest) {
	header("Location: $dest");
	exit(0);
}

function count_sql_user_by_name(&$sql, $sqlu_name) {
	$query = '
		SELECT
			COUNT(sqlu_id) AS cnt
		FROM
			sql_user
		WHERE
			sqlu_name = ?
';

	$rs = exec_query($sql, $query, array($sqlu_name));

	return $rs->fields['cnt'];
}

function sql_delete_user(&$sql, $admin_id, $db_user_id) {
	// let's get sql user common data;
	$query = '
		 SELECT
			t1.sqld_id, t1.sqlu_name, t2.sqld_name, t1.sqlu_name
		 FROM
			sql_user AS t1,
			sql_database AS t2
		 WHERE
			t1.sqld_id = t2.sqld_id
		   AND
			t2.admin_id = ?
		   AND
			t1.sqlu_id = ?
	';

	$rs = exec_query($sql, $query, array($admin_id, $db_user_id));

	if ($rs->RecordCount() == 0) {
		//dirty hack admin can't delete users without database
		if($_SESSION['user_type']==='admin' || $_SESSION['user_type']==='reseller')
			return;
		user_goto('sql_manage.php');
	}
	// remove FROM selity sql_user table.
	$query = 'delete FROM sql_user where sqlu_id = ?';
	exec_query($sql, $query, array($db_user_id));

	$db_name = quoteIdentifier($rs->fields['sqld_name']);
	$db_user_name = $rs->fields['sqlu_name'];

	if (count_sql_user_by_name($sql, $rs->fields['sqlu_name']) == 0) {
		$db_id = $rs->fields['sqld_id'];

		// revoke grants on global level, if any;
		$query = 'REVOKE ALL ON *.* FROM ?@\'%\'';
		$rs = exec_query($sql, $query, array($db_user_name));

		$query = 'REVOKE ALL ON *.* FROM ?@localhost';
		$rs = exec_query($sql, $query, array($db_user_name));

		// delete user record FROM mysql.user table;
		$query = 'DROP USER ?@\'%\'';
		$rs = exec_query($sql, $query, array($db_user_name));

		$query = 'DROP USER ?@\'localhost\';';
		$rs = exec_query($sql, $query, array($db_user_name));

		// flush privileges.
		$query = 'FLUSH PRIVILEGES;';
		$rs = exec_query($sql, $query, array());
	} else {
		$new_db_name = str_replace("_", "\\_", $db_name);

		$query = 'REVOKE ALL ON $new_db_name.* FROM ?@\'%\'';
		$rs = exec_query($sql, $query, array($db_user_name));

		$query = 'REVOKE ALL ON $new_db_name.* FROM ?@localhost';
		$rs = exec_query($sql, $query, array($db_user_name));
	}
}

function check_permissions(&$tpl) {
	if (isset($_SESSION['sql_support']) && $_SESSION['sql_support'] == 'no') {
		$tpl->assign('SQL_SUPPORT', '');
	}
	if (isset($_SESSION['email_support']) && $_SESSION['email_support'] == 'no') {
		$tpl->assign('ADD_EMAIL', '');
	}
	if (isset($_SESSION['subdomain_support']) && $_SESSION['subdomain_support'] == 'no') {
		$tpl->assign('SUBDOMAIN_SUPPORT', '');
	}
	if (isset($_SESSION['alias_support']) && $_SESSION['alias_support'] == 'no') {
		$tpl->assign('DOMAINALIAS_SUPPORT', '');
	}
	if (isset($_SESSION['subdomain_support']) && $_SESSION['subdomain_support'] == 'no') {
		$tpl->assign('SUBDOMAIN_SUPPORT_CONTENT', '');
	}
	if (isset($_SESSION['alias_support']) && $_SESSION['alias_support'] == 'no') {
		$tpl->assign('DOMAINALIAS_SUPPORT_CONTENT', '');
	}
	if (isset($_SESSION['alias_support']) && $_SESSION['alias_support'] == 'no' && isset($_SESSION['subdomain_support']) && $_SESSION['subdomain_support'] == 'no') {
		$tpl->assign('DMN_MNGMNT', '');
	}
}

function check_usr_sql_perms(&$sql, $db_user_id) {
	if (who_owns_this($db_user_id, 'sqlu_id') != $_SESSION['user_id']) {
		set_page_message(tr('User does not exist or you do not have permission to access this interface!'));
		header('Location: sql_manage.php');
		die();
	}
}

function check_db_sql_perms(&$sql, $db_id) {
	if (who_owns_this($db_id, 'sqld_id') != $_SESSION['user_id']) {
		set_page_message(tr('User does not exist or you do not have permission to access this interface!'));
		header('Location: sql_manage.php');
		die();
	}
}

function check_ftp_perms($sql, $ftp_acc) {
	if (who_owns_this($ftp_acc, 'ftp_user') != $_SESSION['user_id']) {
		set_page_message(tr('User does not exist or you do not have permission to access this interface!'));
		header('Location: ftp_accounts.php');
		die();
	}
}

function delete_sql_database(&$sql, $admin_id, $db_id) {
	$query = '
		SELECT
			sqld_name AS db_name
		FROM
			sql_database
		WHERE
			admin_id = ?
		  AND
			sqld_id = ?
	';
	$rs = exec_query($sql, $query, array($admin_id, $db_id));

	if ($rs->RecordCount() == 0) {
		if($_SESSION['user_type'] === 'admin' || $_SESSION['user_type'] === 'reseller') {
			return;
		}
		user_goto('sql_manage.php');
	}

	$db_name = quoteIdentifier($rs->fields['db_name']);
	// have we any users assigned to this database;
	$query = '
		SELECT
			t2.sqlu_id AS db_user_id,
			t2.sqlu_name AS db_user_name
		FROM
			sql_database AS t1,
			sql_user AS t2
		WHERE
			t1.sqld_id = t2.sqld_id
		  AND
			t1.admin_id = ?
		  AND
			t1.sqld_id = ?
	';

	$rs = exec_query($sql, $query, array($admin_id, $db_id));

	if ($rs->RecordCount() != 0) {
		while (!$rs->EOF) {
			$db_user_id = $rs->fields['db_user_id'];

			$db_user_name = $rs->fields['db_user_name'];

			sql_delete_user($sql, $admin_id, $db_user_id);

			$rs->MoveNext();
		}
	}
	// drop desired database;
	$query = "DROP DATABASE IF EXISTS ".$db_name.";";

	$rs = exec_query($sql, $query, array());

	write_log($_SESSION['user_logged'] . ": delete SQL database: " . $db_name);
	// delete desired database FROM the selity sql_database table;
	$query = '
		DELETE FROM
			sql_database
		WHERE
			admin_id = ?
		  AND
			sqld_id = ?
	';

	$rs = exec_query($sql, $query, array($admin_id, $db_id));
}

function get_gender_by_code($code, $nullOnBad = false) {
	switch (strtolower($code)) {
		case 'm':
			return tr('Male');
			break;
		case 'f':
			return tr('Female');
			break;
		default:
			if (!$nullOnBad) {
				return tr('Unknown');
			} else {
				return null;
			}
			break;
	}
}

function mount_point_exists($admin_id, $mnt_point){
	$sql = Database::getInstance();
	$query = '
		SELECT
			`t1`.`alias_mount`, `t2`.`subdomain_alias_mount`
		FROM
			`domain_aliasses` AS `t1`
		LEFT JOIN
			`subdomain_alias` AS `t2`
		ON
			`t1`.`alias_id` = `t2`.`alias_id`
		WHERE
			`t1`.`admin_id` = ?
		AND
			(
				`alias_mount` = ?
			OR
				`subdomain_alias_mount` = ?
			)
	';
	$rs = exec_query($sql, $query, array($admin_id, $mnt_point, $mnt_point));
	if ($rs->RowCount() > 0) return true;
	return false;
}

