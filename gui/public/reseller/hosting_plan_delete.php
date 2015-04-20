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

check_user_login();

if (strtolower(configs::getInstance()->HOSTING_PLANS_LEVEL) != $_SESSION['user_type']) {
	set_page_message(tr('Operation not allowed!'));
	header('Location: hosting_plan.php');
	die();
}

if (isset($_GET['hpid']) && is_numeric($_GET['hpid'])){
	$hpid = $_GET['hpid'];
} else {
	set_page_message(tr('Hosting plan was not deleted!'));
	header('Location: hosting_plan.php');
	die();
}

$hp = new selity_hp();
$hp->id = $hpid;
$hp->reseller_id = $_SESSION['user_id'];
$hp->setMode(configs::getInstance()->HOSTING_PLANS_LEVEL);

$query = '
	SELECT
		COUNT(*) AS `cnt`
	FROM
		`orders`
	WHERE
		`plan_id` = ?
	AND
		`status` = ?
';
$cnt = mysql::getInstance()->doQuery($query, $hpid, configs::getInstance()->ITEM_ORDERED_STATUS)->cnt;
if ($cnt > 0) {
	set_page_message(tr('There are orders! Hosting plan can not be deleted!'));
	header('Location: hosting_plan.php');
	die();
}

if($hp->delete()){
	set_page_message(tr('Hosting plan deleted!'));
} else {
	set_page_message(tr('Hosting plan not deleted!'));
}

header('Location: hosting_plan.php');
die();
