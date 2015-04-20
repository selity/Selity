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

$cfg = configs::getInstance();
$tpl = template::getInstance();

$theme_color = $cfg->USER_INITIAL_THEME;

function gen_system_message() {
	$tpl = template::getInstance();
	$user_id = $_SESSION['user_id'];

	$query = '
		SELECT
			count(`ticket_id`) as cnum
		FROM
			`tickets`
		WHERE
			`ticket_to` = ?
		AND
			(`ticket_status` = ? or `ticket_status` = ?)
		AND
			`ticket_reply` = ?
	';

	$rs = mysql::getInstance()->doQuery($query, $user_id, 2, 5, 0);

	$num_question = $rs->cnum;

	if ($num_question != 0) {
		$tpl->addMessage(tr('You have <b>%d</b> new support questions', $num_question));
	}
}

function get_update_infos() {

	$cfg = configs::getInstance();
	$tpl = template::getInstance();

	if(databaseUpdate::getInstance()->checkUpdateExists()) {
		$tpl->addMessage('<a href="database_update.php" class="link">' . tr('A database update is available') . '</a>');
	}

	if (!$cfg->CHECK_FOR_UPDATES) {
		$tpl->addMessage(tr('Update checking is disabled!'));
		return;
	}

	if (versionUpdate::getInstance()->checkUpdateExists()) {
		$tpl->addMessage('<a href="selity_updates.php" class="link">' . tr('New Selity update is now available') . '</a>');
	} else {
		if( versionUpdate::getInstance()->getErrorMessage() != '' ) {
			$tpl->addMessage(versionUpdate::getInstance()->getErrorMessage());
		}
	}
}

function gen_server_trafic() {

	$tpl = template::getInstance();
	$query = 'SELECT `straff_max`, `straff_warn` FROM `straff_settings`';
	$rs = mysql::getInstance()->doQuery($query);
	$straff_max = (($rs->straff_max) * 1024) * 1024;
	$fdofmnth = mktime(0, 0, 0, date('m'), 1, date('Y'));
	$ldofmnth = mktime(1, 0, 0, date('m') + 1, 0, date('Y'));
	$query = '
		SELECT
			IFNULL((sum(`bytes_in`) + sum(`bytes_out`)), 0) AS traffic
		FROM
			`server_traffic`
		WHERE
			`traff_time` > ?
		AND
			`traff_time` < ?
	';
	$rs1 = mysql::getInstance()->doQuery($query, $fdofmnth, $ldofmnth);
	$traff = $rs1->traffic;
	$mtraff = sprintf('%.2f', $traff);

	if ($straff_max == 0) {
		$pr = 0;
	} else {
		$pr = ($traff / $straff_max) * 100;
	}

	if (($straff_max != 0 || $straff_max != '') && ($mtraff > $straff_max)) {
		$tpl->addMessage(tr('You are exceeding your traffic limit!'));
	}

	$bar_value = calc_bar_value($traff, $straff_max , 400);

	$traff_msg = '';
	if ($straff_max == 0) {
		$traff_msg = tr('%1$d%% [%2$s of unlimited]', $pr, sizeit($mtraff));
	} else {
		$traff_msg = tr('%1$d%% [%2$s of %3$s]', $pr, sizeit($mtraff), sizeit($straff_max));
	}

	$tpl->saveVariable(
		array(
			'TRAFFIC_WARNING' => $traff_msg
		)
	);
}

/*
 *
 * static page messages.
 *
 */

$tpl->saveVariable(
	array(
		'TR_PAGE_TITLE'		=> tr('Selity - Admin/Main Index'),
		'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
		//'ISP_LOGO'		=> get_logo($_SESSION['user_id']),
		'THEME_CHARSET'		=> tr('encoding')
	)
);

$tpl->saveVariable(
	array(
		'TR_ACCOUNT_NAME'			=> tr('Account name'),
		'TR_ADMIN_USERS'			=> tr('Admin users'),
		'TR_RESELLER_USERS'			=> tr('Reseller users'),
		'TR_NORMAL_USERS'			=> tr('Normal users'),
		'TR_SERVERS'				=> tr('Servers'),
		'TR_DOMAINS'				=> tr('Domains'),
		'TR_SUBDOMAINS'				=> tr('Subdomains'),
		'TR_MAIL_ACCOUNTS'			=> tr('Mail accounts'),
		'TR_FTP_ACCOUNTS'			=> tr('FTP accounts'),
		'TR_SQL_DATABASES'			=> tr('SQL databases'),
		'TR_SQL_USERS'				=> tr('SQL users'),
		'TR_TRAFFIC'				=> tr('Traffic this month')
	)
);
$sql = mysql::getInstance();

$tpl->saveVariable(
	array(
		'ACCOUNT_NAME'		=> $_SESSION['user_logged'],
		'ADMIN_USERS'		=> $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `admin` WHERE `admin_type` = ?', 'admin')->cnt,
		'RESELLER_USERS'	=> $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `admin` WHERE `admin_type` = ?', 'reseller')->cnt,
		'NORMAL_USERS'		=> $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `admin` WHERE `admin_type` = ?', 'user')->cnt,
		//'SERVERS'			=> '', $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `servers`')->cnt,
		'DOMAINS'			=> $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `domain_aliasses`')->cnt,
		'SUBDOMAINS'		=> $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `subdomain_alias`')->cnt,
		'MAIL_ACCOUNTS'		=> $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `mail_users`')->cnt,
		'FTP_ACCOUNTS'		=> $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `ftp_users`')->cnt,
		'SQL_DATABASES'		=> $sql->doQuery('SELECT COUNT(*) AS `cnt` FROM `sql_database`')->cnt,
		'SQL_USERS'			=> $sql->doQuery('SELECT COUNT(DISTINCT(`sqlu_name`)) AS `cnt` FROM `sql_user`')->cnt,
	)
);


genAdminMainMenu();
genGeneralMenu();
$tpl->saveSection('GENERAL_MENU');

get_update_infos();
gen_system_message();
gen_server_trafic();

$tpl->flushOutput('admin/index');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();


