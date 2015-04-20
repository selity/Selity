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
$theme_color = configs::getInstance()->USER_INITIAL_THEME;

if (array_key_exists('del_id', $_GET)){
	$del_id = (int) $_GET['del_id'];
} else {
	set_page_message(tr('Invalid id!'));
	header('Location: alias.php');
	die();
}

$reseller_id = $_SESSION['user_id'];

$query = '
	SELECT
		t1.*
	FROM
		`domain_aliasses` AS `t1`
	LEFT JOIN
		`admin` AS `t2`
	ON
		`t1`.`admin_id` = `t2`.`admin_id`
	WHERE
		`t1`.`alias_id` = ?
	AND
		`t2`.`created_by` = ?
';

$rs = mysql::getInstance()->doQuery($query, $del_id, $reseller_id);
if ($rs->countRows() == 0) {
	set_page_message(tr('No such domain!'));
	header('Location: alias.php');
	die();
}

$alias_name = $rs->alias_name;
$status = configs::getInstance()->ITEM_DELETE_STATUS;

mysql::getInstance()->beginTransaction();

try{

	$query = '
		UPDATE
			`mail_users`
		SET
			`status` = ?
		WHERE
			(
				`sub_id` = ?
			AND
				`mail_type` LIKE ?
			)
		OR
			(
				`sub_id` IN (
					SELECT `subdomain_alias_id` FROM `subdomain_alias` WHERE `alias_id`=?
				)
			AND
				`mail_type` LIKE ?
			)
	';
	$rs = mysql::getInstance()->doQuery($query, $status, $del_id, '%alias_%', $del_id, '%alssub_%');

	$query = '
		UPDATE
			`subdomain_alias`
		SET
			`subdomain_alias_status` = ?
		WHERE
			`alias_id`=?
	';
	mysql::getInstance()->doQuery($query, $status, $del_id);

	$query = '
		UPDATE
			`domain_aliasses`
		SET
			`alias_status`= ?
		WHERE
			`alias_id` = ?
	';
	mysql::getInstance()->doQuery($query,  $status, $del_id);
} catch(Exception $e){
	mysql::getInstance()->rollback();
	set_page_message(tr('Error while deleting domain: %s', $e->getMessage()));
	header('Location: alias.php');
	die();
}
mysql::getInstance()->commit();
send_request();

write_log($_SESSION['user_logged'].': deletes domain alias: ' . $alias_name);

set_page_message(tr('Domain added for termination!'));
header('Location: alias.php');
die();

