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

if (isset($_GET['id']) && $_GET['id'] !== '') {

	$als_id = (int)$_GET['id'];

	$query = '
		SELECT
			`alias_id`,
			`alias_name`
		FROM
			`domain_aliasses`
		WHERE
			`admin_id` = ?
		AND
			`alias_id` = ?
	';

	$rs = mysql::getInstance()->doQuery($query, $_SESSION['user_id'], $als_id);
	$alias_name = $rs->alias_name;

	if ($rs->countRows() == 0) {
		user_goto('domains_manage.php');
	}

	// check for subdomains
	$query = '
		SELECT
			COUNT(*) as `cnt`
		FROM
			`subdomain_alias`
		WHERE
			`alias_id` = ?
	';

	$rs = mysql::getInstance()->doQuery($query, $als_id);
	if ($rs->cnt > 0 ) {
		set_page_message(tr('Domain alias you are trying to remove has subdomains!<br>First remove them!'));
		header('Location: domains_manage.php');
		exit(0);
	}

	// check for mail accounts
	$query = '
		SELECT
			COUNT(*) as `cnt`
		FROM
			`mail_users`
		WHERE
			(
				`sub_id` = ?
			AND
				`mail_type` LIKE "%alias_%"
			)
		OR
			(
				`sub_id` IN (SELECT `subdomain_alias_id` FROM `subdomain_alias` WHERE `alias_id`=?)
			AND
				`mail_type` LIKE "%alssub_%"
			)
	';

	$rs = mysql::getInstance()->doQuery($query, $als_id, $als_id);

	if ($rs->cnt > 0 ) {
		set_page_message(tr('Domain alias you are trying to remove has email accounts !<br>First remove them!'));
		header('Location: domains_manage.php');
		exit(0);
	}

	// check for ftp accounts
	$query = '
		SELECT
			COUNT(*) as `ftpnum`
		FROM
			`ftp_users`
		WHERE
			`userid` LIKE ?
	';

	$rs = mysql::getInstance()->doQuery($query, '%'.Config::get('FTP_USERNAME_SEPARATOR').$alias_name);
	if ($rs->ftpnum > 0 ) {
		set_page_message(tr('Domain alias you are trying to remove has FTP accounts!<br>First remove them!'));
		header('Location: domains_manage.php');
		exit(0);
	}

	$query = '
		UPDATE
			`domain_aliasses`
		SET
			`alias_status` = ?
		WHERE
			`alias_id` = ?
	';
	$rs = mysql::getInstance()->doQuery($query, 'delete', $als_id);

	send_request();
	write_log($_SESSION['user_logged'].': delete alias '.$alias_name.'!');
	set_page_message(tr('Alias scheduled for deletion!'));
	header('Location: domains_manage.php');
	exit(0);
} else {
	header('Location: domains_manage.php');
	exit(0);
}

