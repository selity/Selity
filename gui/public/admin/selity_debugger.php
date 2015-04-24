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

$tpl = template::getInstance();
$cfg = configs::getInstance();
$sql = mysql::getInstance();

$theme_color = $cfg->USER_INITIAL_THEME;

function generateSection($table, $name, $id, $status, $op_result, $title, $join){
	$errors = $pending = 0;
	$query = "
		SELECT $name AS `name`, `$id` AS `id`, `$status` AS `status`, `$op_result` AS `op_result`
		FROM `$table`
		$join
		WHERE `$status` NOT IN (?, ?)
		OR `$op_result` IS NOT NULL
	";
	$rs = mysql::getInstance()->doQuery($query, OK_STATUS, DISABLED_STATUS);
	$repeats = array();
	while(!$rs->EOF){
		if($rs->op_result){
			$errors += 1;
			$repeats[] = array(
				$table.'NAME'		=> $rs->name,
				$table.'ID'			=> $rs->id,
				$table.'STATUS'		=> $rs->status,
				$table.'OP_RESULT'	=> $rs->op_result,
			);
		} else {
			$pending += 1;
		}
		$rs->nextRow();
	}
	template::getInstance()->saveRepeats(array($table.'REPEATS' => $repeats));
	return array('errors' => $errors, 'pending' => $pending);
}

$statusArray = array(
	'servers' => array (
		'name'		=> 'server_name',
		'id'		=> 'server_id',
		'status'	=> 'server_status',
		'op_result'	=> 'server_op_result',
		'title'		=> tr('Server errors'),
		'join'		=> ''
	),
	'server_ips' => array (
		'name'		=> 'ip_number',
		'id'		=> 'ip_id',
		'status'	=> 'ip_status',
		'op_result'	=> 'ip_op_result',
		'title'		=> tr('IP errors'),
		'join'		=> ''
	),
	'user_system_props' => array (
		'name'		=> 'CONCAT(`admin`.`admin_name`," (",IFNULL(`admin`.`email`,""),")")',
		'id'		=> 'id',
		'status'	=> 'status',
		'op_result'	=> 'op_result',
		'title'		=> tr('Client errors'),
		'join'		=> 'LEFT JOIN `admin` ON `user_system_props`.`admin_id` =  `admin`.`admin_id`'
	),
	'domains' => array (
		'name'		=> 'dmn_name',
		'id'		=> 'dmn_id',
		'status'	=> 'dmn_status',
		'op_result'	=> 'dmn_op_result',
		'title'		=> tr('Domain errors'),
		'join'		=> ''
	),
	'subdomains' => array (
		'name'		=> 'sub_name',
		'id'		=> 'sub_id',
		'status'	=> 'sub_status',
		'op_result'	=> 'sub_op_result',
		'title'		=> tr('Subdomain errors'),
		'join'		=> ''
	),
	'mail_users' => array (
		'name'		=> 'mail_addr',
		'id'		=> 'mail_id',
		'status'	=> 'mail_status',
		'op_result'	=> 'mail_op_result',
		'title'		=> tr('Mail account errors'),
		'join'		=> ''
	),
	'sqld' => array (
		'name'		=> 'sqld_name',
		'id'		=> 'sqld_id',
		'status'	=> 'sqld_status',
		'op_result'	=> 'sqld_op_result',
		'title'		=> tr('Database errors'),
		'join'		=> ''
	),
	'sqlu' => array (
		'name'		=> 'sqlu_name',
		'id'		=> 'sqlu_id',
		'status'	=> 'sqlu_status',
		'op_result'	=> 'sqlu_op_result',
		'title'		=> tr('Database account errors'),
		'join'		=> ''
	),
	'ssl_certs' => array (
		'name'		=> 'cert_id',
		'id'		=> 'cert_id',
		'status'	=> 'cert_status',
		'op_result'	=> 'cert_op_result',
		'title'		=> tr('Certificate errors'),
		'join'		=> ''
	),
);

$errors = $pending = 0;
$type = array();

foreach($statusArray as $table => $status){
	$rs = generateSection($table, $status['name'], $status['id'], $status['status'], $status['op_result'], $status['title'], $status['join']);
	$errors += $rs['errors'];
	$pending += $rs['pending'];
	if($rs['errors'] > 0){
		$type[] = array(
			'TITLE'		=> $status['title'],
			'PREPEND'	=> $table,
		);
	}
}

$tpl->saveRepeats(array('ERRORS' => $type));
$tpl->saveSection($pending > 0 ? 'PENDINGS' : 'NOPENDINGS');

$tpl->saveVariable(array(
	'TR_PAGE_TITLE'		=> tr('Selity - Virtual Hosting Control System'),
	'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
	//'THEME_CHARSET'		=> tr('encoding'),
	'TR_DEBUGGER_TITLE'	=> tr('Selity debugger'),
	'TR_PENDING_OP'		=> tr('pending operations.'),
	'TR_CLICK'			=> tr('Click to execute'),
	'TR_ERRORS'			=> tr('Errors'),
	'TR_EXEC_REQUESTS'	=> tr('Execute requests'),
	'TR_CHANGE_STATUS'	=> tr('Force retry operation'),

	'PENDING_OP'	=> $pending,
	'ERRORS'		=> $errors
));


if (array_key_exists('action', $_GET) && ($pending > 0 || $errors > 0)) {
	if ($_GET['action'] == 'run_engine') {
		$tpl->addMessage(tr('Daemon returned %d as status code', send_request()));
	} else if ($_GET['action'] == 'change_status' && array_key_exists('id', $_GET) && array_key_exists('type', $_GET)) {
		if(!array_key_exists($_GET['type'], $statusArray)){
			$tpl->addMessage(tr('Unknown type!'));
			header('Location: selity_debugger.php');
			die;
		}
		echo $query = "UPDATE `{$_GET['type']}` SET `{$statusArray[$_GET['type']]['op_result']}` = NULL WHERE `{$statusArray[$_GET['type']]['id']}` = ?";
		try{
			$sql->doQuery($query, (int) $_GET['id']);
		} catch(Exception $e){
			$tpl->addMessage(tr('Unknown Error!'));
			$tpl->addMessage($e->getMessage());
			header('Location: selity_debugger.php');
			die;
		}
		$tpl->addMessage(tr('Done'));
		header('Location: selity_debugger.php');
		die;
	}
}

genMainMenu();
genAdminToolsMenu();

$tpl->flushOutput('admin/selity_debugger');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
