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
 *	 This program is free software; you can redistribute it and/or modify it under
 *	 the terms of the MPL General Public License as published by the Free Software
 *	 Foundation; either version 1.1 of the License, or (at your option) any later
 *	 version.
 *	 You should have received a copy of the MPL Mozilla Public License along with
 *	 this program; if not, write to the Open Source Initiative (OSI)
 *	 http://opensource.org | osi@opensource.org
 */

require '../include/selity-lib.php';

check_login(__FILE__);

if (isset($_GET['id']) && $_GET['id'] !== '') {
	$ftp_id = $_GET['id'];
	$dmn_name = $_SESSION['user_logged'];

	$query = '
		SELECT
			 t1.`userid`,
			 t1.`uid`,
			 t2.`user_uid`,
			 t2.`user_admin_id`
		FROM
			`ftp_users` AS t1,
			`user_system_props` AS t2
		WHERE
			t1.userid = ?
		AND
			t1.uid = t2.user_uid
		AND
			t2.user_admin_id = ?
	';

	$rs = exec_query($sql, $query, array($ftp_id, $_SESSION['user_id']));
	$ftp_name = $rs->fields['userid'];

	if ($rs -> RecordCount() == 0) {
		user_goto('ftp_accounts.php');
	}

	$query = '
		SELECT
			t1.gid,
			t2.members
		FROM
			ftp_users as t1,
			ftp_group as t2
		WHERE
			t1.gid = t2.gid
		AND
			t1.userid = ?
	';

	$rs = exec_query($sql, $query, array($ftp_id));

	$ftp_gid		= $rs -> fields['gid'];
	$ftp_members	= $rs->fields['members'];
	$ftp_members	= array_flip(explode(',', $ftp_members));
	if(array_key_exists($ftp_id, $ftp_members)){
		unset($ftp_members[$ftp_id]);
	}
	$members = implode(',', array_flip($ftp_members));

	if (strlen($members) == 0) {
		$query = 'DELETE FROM `ftp_group` WHERE `gid` = ? ';
		$rs = exec_query($sql, $query, array($ftp_gid));
	} else {
		$query = 'UPDATE `ftp_group` SET `members` = ? WHERE `gid` = ? ';
		$rs = exec_query($sql, $query, array($members, $ftp_gid));
	}

	$query = 'DELETE FROM `ftp_users` WHERE `userid` = ?';
	$rs = exec_query($sql, $query, array($ftp_id));

	write_log($_SESSION['user_logged'].': deletes FTP account: '.$ftp_name);
	set_page_message(tr('FTP account deleted successfully!'));
	user_goto('ftp_accounts.php');

} else {
	user_goto('ftp_accounts.php');
}

