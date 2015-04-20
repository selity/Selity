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
	$sub_id = (int) $_GET['id'];

	$query = '
		SELECT
			`subdomain_alias_id`,
			`subdomain_alias_name`
		FROM
			`subdomain_alias`
		LEFT JOIN
			`domain_aliasses`
		ON
			`subdomain_alias`.`alias_id` = `domain_aliasses`.`alias_id`
		WHERE
			`admin_id` = ?
		AND
			`subdomain_alias_id` = ?
	';

	$rs = exec_query($sql, $query, array($_SESSION['user_id'], $sub_id));

	if ($rs -> RecordCount() == 0) {
		user_goto('domains_manage.php');
	}
	$sub_name = $rs->fields['subdomain_name'];

	// check for mail accounts
	$query = '
		SELECT
			COUNT(*) as `cnt`
		FROM
			`mail_users`
		WHERE
			(`mail_type` LIKE ? OR `mail_type` = ?)
		AND
			`sub_id` = ?
	';
	$rs = exec_query($sql, $query, array(MT_ALSSUB_MAIL.'%', MT_ALSSUB_FORWARD, $sub_id));

	if ($rs -> fields['cnt'] > 0 ) {
		set_page_message(tr('Subdomain you are trying to remove has email accounts !<br>First remove them!'));
		header('Location: domains_manage.php');
		exit(0);
	}


	$query = '
		update
			subdomain_alias
		set
			subdomain_alias_status = ?
		where
			subdomain_alias_id = ?
	';

	$rs = exec_query($sql, $query, array('delete', $sub_id));
	send_request();
	write_log($_SESSION['user_logged'].': deletes subdomain: '.$sub_name);
	set_page_message(tr('Subdomain scheduled for deletion!'));
	header('Location: domains_manage.php');
	die();

} else {
	header('Location: domains_manage.php');
	die();
}

