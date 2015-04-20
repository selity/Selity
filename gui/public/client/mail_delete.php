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
	global $delete_id;
	$delete_id = $_GET['id'];
} else {
	user_goto('mail_accounts.php');
}

/* do we have a proper delete_id ? */
if (!isset($delete_id)) {
	header('Location: mail_accounts.php');
	die();
}

if (!is_numeric($delete_id)) {
	header('Location: mail_accounts.php');
	die();
}

if(who_owns_this($delete_id, 'mail_id') != $_SESSION['user_id']){
	set_page_message(tr('User does not exist or you do not have permission to access this interface!'));
	header('Location: mail_accounts.php');
	die();
}


/* check for catchall assigment !! */
$query = 'SELECT mail_acc, sub_id, mail_type FROM mail_users WHERE mail_id=?';
$res = exec_query($sql, $query, array($delete_id));
$data = $res->FetchRow();

if (preg_match('/'.MT_ALIAS_MAIL.'/', $data['mail_type']) || preg_match('/'.MT_ALIAS_FORWARD.'/', $data['mail_type'])) {
	/* mail to domain alias*/
	$res_tmp = exec_query($sql, 'SELECT alias_name FROM domain_aliasses WHERE alias_id=?', array($data['sub_id']));
	$dat_tmp = $res_tmp->FetchRow();
	$mail_name = $data['mail_acc'] . '@' . $dat_tmp['alias_name'];
} else if (preg_match('/'.MT_ALSSUB_MAIL.'/', $data['mail_type']) || preg_match('/'.MT_ALSSUB_FORWARD.'/', $data['mail_type'])) {
	/* mail to subdomain*/
	$res_tmp = exec_query($sql, 'SELECT subdomain_alias_name, alias_name FROM subdomain_alias AS t1, domain_aliasses AS t2 WHERE t1.alias_id=t2.alias_id AND subdomain_alias_id=?', array($data['sub_id']));
	$dat_tmp = $res_tmp->FetchRow();
	$mail_name = $data['mail_acc'] . '@' . $dat_tmp['subdomain_alias_name'].'.'.$dat_tmp['alias_name'];
}

$query = 'SELECT `mail_id` FROM `mail_users` WHERE `mail_acc`=? OR `mail_acc` LIKE ? OR `mail_acc` LIKE ? OR `mail_acc` LIKE ?';
$res_tmp = exec_query($sql, $query, array($mail_name, $mail_name.',%', '%,'.$mail_name.',%', '%,'.$mail_name));
$num = $res_tmp->RowCount();
if ($num > 0) {
	$catchall_assigned = 1;
	set_page_message(tr('Please delete first CatchAll account for this email!'));
	$_SESSION['catchall_assigned'] = 1;
	header('Location: mail_accounts.php');
	die();
}
/* if we are locket wait to unlock */

$query = 'UPDATE mail_users SET status=? WHERE mail_id = ?';
exec_query($sql, $query, array(Config::get('ITEM_DELETE_STATUS'), $delete_id));

send_request();
$admin_login = decode_idna($_SESSION['user_logged']);
write_log($admin_login.': deletes mail account: ' . $mail_name);
$maildel = 1;
$_SESSION['maildel'] = 1;
header('Location: mail_accounts.php');
die();

