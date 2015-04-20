<?php

/**
 * Selity - A server control panel
 *
 * @copyright	2009-2015 by Selity
 * @link 		http://selity.org
 * @author 		Daniel Andreca (sci2tech@gmail.com)
 *
 * @license
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require '../include/selity-lib.php';

check_login(__FILE__);

if (array_key_exists('action', $_GET) && $_GET['action'] === 'delete') {

	if(array_key_exists('del_id', $_GET) && !empty($_GET['del_id'])){
		$del_id = (int) $_GET['del_id'];
	} else {
		set_page_message(tr('Invalid id!'));
		header('Location: alias.php');
		die();
	}

	$reseller_id = $_SESSION['user_id'];
	$status = configs::getInstance()->ITEM_ORDERED_STATUS;
	$query = '
		DELETE
			`domain_aliasses`
		FROM
			`domain_aliasses`
		LEFT JOIN
			`admin`
		ON
			`domain_aliasses`.`admin_id` = `admin`.`admin_id`
		WHERE
			`alias_id` = ?
		AND
			`alias_status` = ?
		AND
			`domain_aliasses`.`admin_id` = ?
	';
	$rs = mysql::getInstance()->doQuery($query, $del_id, $status, $reseller_id);
	if($rs->countRows() == 0){
		set_page_message(tr('Ordered domain not removed!'));
	} else {
		set_page_message(tr('Ordered domain removed!'));
	}
	header('Location: alias.php');
	die();

} else if (array_key_exists('action', $_GET) && $_GET['action'] === 'activate') {

	if(array_key_exists('act_id', $_GET) && !empty($_GET['act_id']))
		$act_id = (int) $_GET['act_id'];
	else{
		set_page_message(tr('Ordered domain not activated!'));
		header('Location: alias.php');
		die();
	}

	$reseller_id	= $_SESSION['user_id'];
	$order_status	= configs::getInstance()->ITEM_ORDERED_STATUS;
	$add_status		= configs::getInstance()->ITEM_ADD_STATUS;
	$query = '
		SELECT
			`alias_name`.*
		FROM
			`domain_aliasses`
		LEFT JOIN
			`admin`
		ON
			`domain_aliasses`.`admin_id` = `admin`.`admin_id`
		WHERE
			`alias_id` = ?
		AND
			`alias_status` = ?
		AND
			`domain_aliasses`.`admin_id` = ?
	';
	$rs = mysql::getInstance()->doQuery($query, $act_id, $order_status, $reseller_id);

	if ($rs->countRows() == 0) {
		set_page_message(tr('Ordered domain not activated!'));
		header('Location: alias.php');
		die();
	}

	$alias_name = $rs->alias_name;
	$user_email = $rs->email;

	mysql::getInstance()->beginTransaction();
	try{

		$query = 'UPDATE domain_aliasses SET alias_status = ? WHERE alias_id = ?';
		$rs = mysql::getInstance()->doQuery($query, $add_status, $act_id);

		if (Config::get('CREATE_DEFAULT_EMAIL_ADDRESSES')){
			client_mail_add_default_accounts($domain_id, $user_email, $alias_name, 'alias', $act_id);
		}
	} catch(Exception $e){
		mysql::getInstance()->rollback();
		set_page_message(tr('Error while adding domain: %s', $e->getMessage()));
		header('Location: alias.php');
		die();
	}

	mysql::getInstance()->commit();
	send_request();

	write_log($_SESSION['user_logged'].': domain alias activated: '.$alias_name);
	set_page_message(tr('Alias scheduled for activation!'));
	header('Location: alias.php');
	die();
}

set_page_message(tr('Invalid operation!'));
header('Location: alias.php');
die();


