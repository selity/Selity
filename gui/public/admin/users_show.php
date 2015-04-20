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
			'NAME'				=> $rs->admin_name,
			'ADMIN_ID'			=> $rs->admin_id,
			'CREATED_BY'		=> is_null($rs->parent) ? tr('System') : $rs->parent,
			'URL_DELETE_ADMIN'	=> 'users_admin_delete.php?user_id=' . $rs->admin_id
		);
		$rs->nextRow();
	}
	$tpl->saveRepeats(array('ADMINS' => $admins));
}

function genResellerList(){
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
	$rs = $sql->doQuery($query, 'reseller');
	$admins = array();
	while (!$rs->EOF){
		$tpl->saveSection('RESELLER');
		$admins[] = array(
			'NAME'				=> $rs->admin_name,
			'ADMIN_ID'			=> $rs->admin_id,
			'CREATED_BY'		=> is_null($rs->parent) ? tr('System') : $rs->parent,
			'URL_DELETE_ADMIN'	=> 'users_reseller_delete.php?user_id=' . $rs->admin_id
		);
		$rs->nextRow();
	}
	$tpl->saveRepeats(array('RESELLER' => $admins));
}

function genClientList(){
	$tpl = template::getInstance();
	$sql = mysql::getInstance();
	$query = '
		SELECT `t1`.*, `t2`.`admin_name` AS `parent`, `t3`.`user_status`
		FROM `admin` AS `t1`
		LEFT JOIN `admin` AS `t2`
		ON `t1`.`created_by` = `t2`.`admin_id`
		LEFT JOIN `user_system_props` AS `t3`
		ON `t1`.`admin_id` = `t3`.`user_admin_id`
		WHERE t1.`admin_type` = ?
	';
	$rs = $sql->doQuery($query, 'user');
	$admins = array();
	while (!$rs->EOF){
		$tpl->saveSection('USERS');
		switch($rs->user_status){
			case configs::getInstance()->ITEM_OK_STATUS:
				$status = tr('Ok');
				$status_action = 'users_status_change.php?user_id='.$rs->admin_id;
				break;
			case configs::getInstance()->ITEM_DISABLED_STATUS:
				$status = tr('Disabled');
				$status_action = 'users_status_change.php?user_id='.$rs->admin_id;
				break;
			case configs::getInstance()->ITEM_ADD_STATUS:
			case configs::getInstance()->ITEM_RESTORE_STATUS:
			case configs::getInstance()->ITEM_CHANGE_STATUS:
			case configs::getInstance()->ITEM_TODISABLED_STATUS:
			case configs::getInstance()->ITEM_DELETE_STATUS:
			case configs::getInstance()->ITEM_TOENABLE_STATUS:
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
			'NAME'				=> $rs->admin_name,
			'ADMIN_ID'			=> $rs->admin_id,
			'CREATED_BY'		=> is_null($rs->parent) ? tr('System') : $rs->parent,
			'URL_DELETE_CLIENT'	=> 'users_client_delete.php?user_id='.$rs->admin_id
		);
		$rs->nextRow();
	}
	$tpl->saveRepeats(array('USERS' => $admins));
}

/*
 *
 * static page messages.
 *
 */

if (isset($_POST['details']) && !empty($_POST['details'])) {
	$_SESSION['details'] = $_POST['details'];
} else {
	if (!isset($_SESSION['details'])) {
		$_SESSION['details'] = 'hide';
	}
}

if (!Config::exists('HOSTING_PLANS_LEVEL') || strtolower(Config::get('HOSTING_PLANS_LEVEL')) !== 'admin') {
	$tpl->assign('EDIT_OPTION', '');
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
	//'TR_MANAGE_USERS'	=> tr('Manage'),
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
	'TR_CHANGE_USERS'	=>  tr('Switch'),
));



$tpl->flushOutput('common/users_show');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();


