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
	$mail_id = (int) $_GET['id'];
	$item_delete_status = Config::get('ITEM_DELETE_STATUS');

	$query = '
		select
			mail_id
		from
			mail_users
		where
			admin_id = ?
		and
			mail_id = ?
	';

  $rs = exec_query($sql, $query, array($_SESSION['user_id'], $mail_id));

	if ($rs -> RecordCount() == 0) {
		user_goto('mail_catchall.php');
	}


	$query = '
		update
			mail_users
		set
			status = ?
		where
			mail_id = ?
	';

	$rs = exec_query($sql, $query, array($item_delete_status, $mail_id));

	send_request();
	write_log($_SESSION['user_logged'].': deletes email catch all!');
	set_page_message(tr('Catch all account scheduled for deletion!'));
	user_goto('mail_catchall.php');

} else {
	user_goto('mail_catchall.php');
}

