<?php
/**
 * Selity - A server control panel
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2008 by ispCP | http://isp-control.net
 * @copyright	2012-2014 by Selity
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

$reseller_id = $_SESSION['user_id'];


if(isset($_GET['order_id']) && is_numeric($_GET['order_id'])){
	$order_id = $_GET['order_id'];
} else {
	set_page_message(tr('Wrong order ID!'));
	Header("Location: orders.php");
	die();
}

$query = '
	select
		id
	from
		orders
	where
			id = ?
		and
			user_id = ?
';

	$rs = exec_query($sql, $query, array($order_id, $reseller_id));

		if ($rs -> RecordCount() == 0) {

			set_page_message(tr('Permission deny!'));
			header('Location: orders.php');
			die();
		}

//delete all FTP Accounts
  $query = '
		  delete from
			  orders
		  where
			  id = ?
';
  $rs = exec_query($sql, $query, array($order_id));

set_page_message(tr('Customer order was removed successful!'));

write_log($_SESSION['user_logged'].": deletes customer order.");
header( "Location: orders.php");
die();
