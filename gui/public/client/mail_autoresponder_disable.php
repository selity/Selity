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
	$mail_id = (int)$_GET['id'];
	if (who_owns_this($mail_id, 'mail_id') != $_SESSION['user_id']) {
		set_page_message(tr('User does not exist or you do not have permission to access this interface!'));
		header('Location: mail_accounts.php');
		die();
	}

	$item_change_status = Config::get('ITEM_CHANGE_STATUS');

	$query = '
		update
			mail_users
		set
			status = ?,
			mail_auto_respond = ?
		where
			mail_id = ?
	';

  	$rs = exec_query($sql, $query, array($item_change_status, 0, $mail_id));

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
	write_log($_SESSION['user_logged'].': disabled mail autoresponder: '.$mail_name);
	set_page_message(tr('Mail account scheduled for modification!'));
	header('Location: mail_accounts.php');
	exit(0);
} else {
  	header('Location: mail_accounts.php');
  	exit(0);
}

