<?php
/**
 * Selity - A server control panel
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2008 by ispCP | http://isp-control.net
 * @copyright	2012-2015 by Selity
 * @link 		http://selity.org
 * @author 		ispCP Team (2007)
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

if(isset($_GET['del_id']) && !empty($_GET['del_id'])){
	$del_id = (int) $_GET['del_id'];
} else {
	$_SESSION['orderaldel'] = '_no_';
	header('Location: domains_manage.php');
	die();
}

$query = '
	DELETE FROM
		`domain_aliasses`
	WHERE
		`alias_id` = ?
	AND
		`admin_id` = ?
	AND
		`alias_status` = ?
';
$rs = mysql::getInstance()->doQuery(
	$query,
	$del_id,
	$_SESSION['user_id'],
	Config::get('ITEM_ORDERED_STATUS')
);

if($rs->countRows()) {
	set_page_message(tr('Order for domain alias deleted.'));
} else {
	set_page_message(tr('Order not found. Nothing been deleted.'));
}

header('Location: domains_manage.php');
die();

