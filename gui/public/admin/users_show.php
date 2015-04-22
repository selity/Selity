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

$tpl = template::getInstance();
$cfg = configs::getInstance();

$theme_color = configs::getInstance()->USER_INITIAL_THEME;

function genAdminList(){
	$tpl = template::getInstance();
	$sql = mysql::getInstance();
	$query = '
		SELECT `t1`.*, `t2`.`admin_name` AS `parent`
		FROM `admin` AS `t1`
		LEFT JOIN `admin` AS `t2`
		ON
		`t1`.`created_by` = `t2`.`admin_id`
		WHERE t1.`admin_type` = ?
	';
	$rs = $sql->doQuery($query, 'admin');
	$admins = array();
	while (!$rs->EOF){
		if (!is_null($rs->parent) && $rs->admin_id != $_SESSION['user_id']) {
			$tpl->saveSection('DELETE_ADMIN'. $rs->admin_id);
		}
		$admins[] = array(
			'NAME'				=> sprintf('%s %s (%s %s)', $rs->admin_name, $rs->email, $rs->fname, $rs->lname),
			'ADMIN_ID'			=> $rs->admin_id,
			'CREATED_BY'		=> is_null($rs->parent) ? tr('System') : $rs->parent,
			'URL_DELETE_ADMIN'	=> 'users_admin_op.php?op=delete&user_id=' . $rs->admin_id
		);
		$rs->nextRow();
	}
	$tpl->saveRepeats(array('ADMINS' => $admins));
}

function genResellerList(){
	$tpl = template::getInstance();
	$sql = mysql::getInstance();
	$cfg = configs::getInstance();
	$add_query = '';

	$showRows	= $cfg->USER_ROWS_PER_PAGE;

	$startIndex = isset($_GET['go']) ? (int) $_GET['go'] : 0;
	if (array_key_exists('submitRSLR', $_POST)) {
		$_SESSION['rslSearchFor'] 	= trim(clean_input($_POST['rslSearchFor']));
		$_SESSION['rslSearchWhere']	= $_POST['rslSearchWhere'];
		$startIndex = 0;
	} else {
		if (isset($_SESSION['rslSearchFor']) && !isset($_GET['go'])) {
			unset($_SESSION['rslSearchFor']);
			unset($_SESSION['rslSearchWhere']);
		}
	}

	$data = array();

	if (isset($_SESSION['rslSearchFor']) && $_SESSION['rslSearchFor'] != '') {
		if ($_SESSION['rslSearchWhere'] === 'mail') {
			$add_query .= " AND `t1`.`email` rlike ? ";
			array_push($data, $_SESSION['rslSearchFor']);
		} else if ($_SESSION['rslSearchWhere'] === 'servName') {
			$rs = $sql->doQuery('SELECT `server_id` FROM `servers` WHERE `server_name` RLIKE ?', $_SESSION['rslSearchFor']);
			$add_query .= " AND (`t4`.`server_ids` rlike ?) ";
			array_push($data, ':"'.($rs->countRows() > 0 ? $rs->server_id : 'n/a').'";');
		} else if ($_SESSION['rslSearchWhere'] === 'name') {
			$add_query .= " AND (`t1`.`lname` rlike ? or `t1`.fname rlike ?) ";
			array_push($data, $_SESSION['rslSearchFor'], $_SESSION['rslSearchFor']);
		} else if ($_SESSION['rslSearchWhere'] === 'firm') {
			$add_query .= " AND `t1`.`firm` rlike ? ";
			array_push($data, $_SESSION['rslSearchFor']);
		} else if ($_SESSION['rslSearchWhere'] === 'city') {
			$add_query .= " AND `t1`.`city` rlike ? ";
			array_push($data, $_SESSION['rslSearchFor']);
		} else if ($_SESSION['rslSearchWhere'] === 'country') {
			$add_query .= " AND `t1`.`country` rlike ? ";
			array_push($data, $_SESSION['rslSearchFor']);
		} else if ($_SESSION['rslSearchWhere'] === 'dmn_name') {
			$add_query .= " AND `t5`.`dmn_name` rlike ? ";
			array_push($data, $_SESSION['rslSearchFor']);
		}
	}

	$cQuery = "
		SELECT COUNT(*) AS `cnt`
		FROM `admin` AS `t1`
		LEFT JOIN `admin` AS `t2` ON `t1`.`created_by` = `t2`.`admin_id` /* to obtain parent name */
		LEFT JOIN `admin` AS `t3` ON `t1`.`admin_id` = `t3`.`created_by` /* to obtain client list for reseller */
		LEFT JOIN `reseller_props` AS `t4` ON `t1`.`admin_id` = `t4`.`reseller_id` /* to obtain server list for reseller */
		LEFT JOIN `domains` AS `t5` ON `t5`.`admin_id` = `t3`.`admin_id` /* to obtain domains belonging to reseller`s clients */
		WHERE `t1`.`admin_type` = ?
		$add_query
		GROUP BY `t1`.`admin_id`
	";

	$cnt = $sql->doQuery($cQuery, 'reseller', $data)->countRows();

	$prevIndex = $startIndex - $showRows < 0 ? 0 : $startIndex - $showRows;
	$nextIndex = $startIndex + $showRows >= $cnt ? $startIndex : $startIndex + $showRows;

	$tpl->saveVariable(array(
		'rslPrv'			=> $prevIndex,
		'rslNxt'			=> $nextIndex,
		'RSL_SEARCH_FOR'		=> isset($_SESSION['rslSearchFor']) ? $_SESSION['rslSearchFor'] : '',
		'R_MAIL_SEL'		=> isset($_SESSION['rslSearchWhere']) && $_SESSION['rslSearchWhere'] == 'mail' ? 'selected' : '',
		'R_SERV_NAME_SEL'	=> isset($_SESSION['rslSearchWhere']) && $_SESSION['rslSearchWhere'] == 'servName' ? 'selected' : '',
		'R_NAME_SEL'		=> isset($_SESSION['rslSearchWhere']) && $_SESSION['rslSearchWhere'] == 'name' ? 'selected' : '',
		'R_COMP_SEL'		=> isset($_SESSION['rslSearchWhere']) && $_SESSION['rslSearchWhere'] == 'firm' ? 'selected' : '',
		'R_CITY_SEL'		=> isset($_SESSION['rslSearchWhere']) && $_SESSION['rslSearchWhere'] == 'city' ? 'selected' : '',
		'R_COUNTRY_SEL'		=> isset($_SESSION['rslSearchWhere']) && $_SESSION['rslSearchWhere'] == 'country' ? 'selected' : '',
		'R_DMN_NAME_SEL'	=> isset($_SESSION['rslSearchWhere']) && $_SESSION['rslSearchWhere'] == 'dmn_name' ? 'selected' : '',
	));
	if($prevIndex != $startIndex) $tpl->saveSection('prevReseller');
	if($nextIndex != $startIndex) $tpl->saveSection('nextReseller');


	$query = "
		SELECT `t1`.*, `t2`.`admin_name` AS `parent`
		FROM `admin` AS `t1`
		LEFT JOIN `admin` AS `t2` ON `t1`.`created_by` = `t2`.`admin_id` /* to obtain parent name */
		LEFT JOIN `admin` AS `t3` ON `t1`.`admin_id` = `t3`.`created_by` /* to obtain client list for reseller */
		LEFT JOIN `reseller_props` AS `t4` ON `t1`.`admin_id` = `t4`.`reseller_id` /* to obtain server list for reseller */
		LEFT JOIN `domains` AS `t5` ON `t5`.`admin_id` = `t3`.`admin_id` /* to obtain domains belonging to reseller`s clients */
		WHERE `t1`.`admin_type` = ?
		$add_query
		GROUP BY `t1`.`admin_id`
		ORDER BY `t1`.`admin_name` ASC
		LIMIT $startIndex, $showRows
	";
	$rs = $sql->doQuery($query, 'reseller', $data);


	$admins = array();
	while (!$rs->EOF){
		$tpl->saveSection('RESELLER');
		$admins[] = array(
			'NAME'				=> sprintf('%s %s (%s %s)', $rs->admin_name, $rs->email, $rs->fname, $rs->lname),
			'ADMIN_ID'			=> $rs->admin_id,
			'CREATED_BY'		=> is_null($rs->parent) ? tr('System') : $rs->parent,
			'URL_DELETE_RESELLER'	=> 'users_reseller_op.php?op=delete&user_id=' . $rs->admin_id
		);
		$rs->nextRow();
	}
	$tpl->saveRepeats(array('RESELLER' => $admins));
}

function genClientList(){
	$tpl = template::getInstance();
	$sql = mysql::getInstance();
	$query = '
		SELECT `t1`.*, `t2`.`admin_name` AS `parent`, `t3`.`status`
		FROM `admin` AS `t1`
		LEFT JOIN `admin` AS `t2`
		ON `t1`.`created_by` = `t2`.`admin_id`
		LEFT JOIN `user_system_props` AS `t3`
		ON `t1`.`admin_id` = `t3`.`admin_id`
		WHERE t1.`admin_type` = ?
	';
	$rs = $sql->doQuery($query, 'client');
	$admins = array();
	while (!$rs->EOF){
		$tpl->saveSection('USERS');
		switch($rs->status){
			case OK_STATUS:
				$status = tr('Ok');
				$status_action = 'users_status_change.php?user_id='.$rs->admin_id;
				break;
			case DISABLED_STATUS:
				$status = tr('Disabled');
				$status_action = 'users_status_change.php?user_id='.$rs->admin_id;
				break;
			case ADD_STATUS:
			case RESTORE_STATUS:
			case CHANGE_STATUS:
			case TODISABLE_STATUS:
			case DELETE_STATUS:
			case TOENABLE_STATUS:
				$status = tr('Changing');
				$status_action = '#';
				break;
			default:
				$status = tr('Error');
				$status_action = 'users_client_details.php?user_id='.$rs->admin_id;;
		}

		$admins[] = array(
			'STATUS_URL'		=> $status_action,
			'STATUS'			=> $status,
			'NAME'				=> sprintf('%s (%s %s)',$rs->email, $rs->fname, $rs->lname),
			'ADMIN_ID'			=> $rs->admin_id,
			'CREATED_BY'		=> is_null($rs->parent) ? tr('System') : $rs->parent,
			'URL_DELETE_CLIENT'	=> 'users_client_delete.php?user_id='.$rs->admin_id
		);
		$rs->nextRow();
	}
	$tpl->saveRepeats(array('USERS' => $admins));
}


if (isset($_POST['details']) && !empty($_POST['details'])) {
	$_SESSION['details'] = $_POST['details'];
} else {
	if (!isset($_SESSION['details'])) {
		$_SESSION['details'] = 'hide';
	}
}

if(array_key_exists('serverLST', $_SESSION)){
	unset($_SESSION['serverLST']);
}

genMainMenu();
genAdminUsersMenu();

genAdminList();
genResellerList();
genClientList();

$tpl->saveSection('ADMIN');
$tpl->saveVariable(array(
	'TR_PAGE_TITLE'		=> tr('Selity - Admin/Manage Users'),
	'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
	//'THEME_CHARSET'	=> tr('encoding'),
	'ADMIN_TYPE'		=> $_SESSION['user_type'],
	'TR_ADMINS'			=> tr('Administrators'),
	'TR_RESELLERS'		=> tr('Resellers'),
	'TR_USERS'			=> tr('Clients'),
	'TR_STATUS'			=> tr('Status'),
	'TR_USER_NAME'		=> tr('Username'),
	'TR_CREATED_BY'		=> tr('Created by'),
	'TR_ACTION'			=> tr('Options'),
	'TR_DELETE'			=> tr('Delete'),
	'TR_MESSAGE_DELETE'	=> tr('Are you sure you want to delete %s?', '%s'),
	'TR_EDIT'			=> tr('Edit'),
	'TR_CHANGE_USERS'	=> tr('Switch'),
	'U_MAIL'			=> tr('Email'),
	'U_SERV_NAME'		=> tr('Server name'),
	'U_NAME'			=> tr('Name'),
	'U_COMP'			=> tr('Company'),
	'U_CITY'			=> tr('City'),
	'U_COUNTRY'			=> tr('Country'),
	'U_DMN_NAME'		=> tr('Domain name'),
	'ALL'				=> tr('All'),
	'OK'				=> tr('ok'),
	'SUSPENDED'			=> tr('Suspended'),
	'TR_SEARCH'			=> tr('Search'),
	'TR_SHOW_DOMAINS'	=> tr('Show domains'),
	'TR_PREVIOUS'		=> tr('Previous'),
	'TR_NEXT'			=> tr('Next'),
));



$tpl->flushOutput('common/users_show');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();


