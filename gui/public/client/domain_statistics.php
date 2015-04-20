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

$tpl = new pTemplate();
$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/domain_statistics.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');
$tpl->define_dynamic('month_item', 'page');
$tpl->define_dynamic('year_item', 'page');
$tpl->define_dynamic('traff_list', 'page');
$tpl->define_dynamic('traff_item', 'traff_list');

// page functions.

function gen_page_date(&$tpl, $month, $year) {
	for ($i = 1; $i <= 12; $i++) {
		$tpl->assign(array('MONTH_SELECTED' => ($i == $month) ? 'selected' : '',
				'MONTH' => $i));
		$tpl->parse('MONTH_ITEM', '.month_item');
	}

	for ($i = $year - 1; $i <= $year + 1; $i++) {
		$tpl->assign(array('YEAR_SELECTED' => ($i == $year) ? 'selected' : '',
				'YEAR' => $i));
		$tpl->parse('YEAR_ITEM', '.year_item');
	}
}

function gen_page_post_data(&$tpl, $current_month, $current_year) {
	if (isset($_POST['uaction']) && $_POST['uaction'] === 'show_traff') {
		$current_month = $_POST['month'];
		$current_year = $_POST['year'];
	}

	gen_page_date($tpl, $current_month, $current_year);
	return array($current_month, $current_year);
}

function get_domain_trafic($from, $to, $admin_id) {
	$sql = Database::getInstance();

	$query = '
		select
			IFNULL(sum(dtraff_web), 0) as web_dr,
			IFNULL(sum(dtraff_ftp), 0) as ftp_dr,
			IFNULL(sum(dtraff_mail), 0) as mail_dr,
			IFNULL(sum(dtraff_pop), 0) as pop_dr
		from
			domain_traffic
		where
			admin_id = ?
		and
			dtraff_time >= ?
		  and
			dtraff_time <= ?
';

	$rs = exec_query($sql, $query, array($admin_id, $from, $to));

	if ($rs->RecordCount() == 0) {
		return array(0, 0, 0, 0);
	} else {
		return
		array($rs->fields['web_dr'], $rs->fields['ftp_dr'],
			$rs->fields['pop_dr'], $rs->fields['mail_dr']);
	}
}

function gen_dmn_traff_list(&$tpl, &$sql, $month, $year, $admin_id) {
	global $web_trf, $ftp_trf, $smtp_trf, $pop_trf,
	$sum_web, $sum_ftp, $sum_mail, $sum_pop;

	//$domain_admin_id = $_SESSION['user_id'];
	//$query = '
		//select
			//domain_id
		//from
			//domain
		//where
			//domain_admin_id = ?
	//';
//
	//$rs = exec_query($sql, $query, array($domain_admin_id));
	//$domain_id = $rs->fields('domain_id');

	$fdofmnth = mktime(0, 0, 0, $month, 1, $year);
	$ldofmnth = mktime(1, 0, 0, $month + 1, 0, $year);

	if ($month == date('m') && $year == date('Y')) {
		$curday = date('j');
	} else {
		$tmp = mktime(1, 0, 0, $month + 1, 0, $year);
		$curday = date('j', $tmp);
	}

	$curtimestamp = time();
	$firsttimestamp = mktime(0, 0, 0, $month, 1, $year);
	$all[0] = 0;
	$all[1] = 0;
	$all[2] = 0;
	$all[3] = 0;
	$all[4] = 0;
	$all[5] = 0;
	$all[6] = 0;
	$all[7] = 0;
	$counter = 0;

	for ($i = 1; $i <= $curday; $i++) {
		$ftm = mktime(0, 0, 0, $month, $i, $year);
		$ltm = mktime(23, 59, 59, $month, $i, $year);
		$query = '
			select
				dtraff_web,dtraff_ftp,dtraff_mail,dtraff_pop,dtraff_time
			from
				domain_traffic
			where
				admin_id = ?
			  and
				dtraff_time >= ?
			  and
				dtraff_time <= ?
		';

		$rs = exec_query($sql, $query, array($admin_id, $ftm, $ltm));

		$has_data = false;
		list($web_trf,
			$ftp_trf,
			$pop_trf,
			$smtp_trf) = get_domain_trafic($ftm, $ltm, $admin_id);


		$sum_web += $web_trf;
		$sum_ftp += $ftp_trf;
		$sum_mail += $smtp_trf;
		$sum_pop += $pop_trf;

		$date_formt = Config::get('DATE_FORMAT');
		$tpl->assign(array('DATE' => date($date_formt, strtotime($year . "-" . $month . "-" . $i)),
				'WEB_TRAFFIC' => sizeit($web_trf),
				'FTP_TRAFFIC' => sizeit($ftp_trf),
				'SMTP_TRAFFIC' => sizeit($smtp_trf),
				'POP3_TRAFFIC' => sizeit($pop_trf),
				'ALL_TRAFFIC' => sizeit($web_trf + $ftp_trf + $smtp_trf + $pop_trf),
				'WEB_TRAFF' => sizeit($web_trf),
				'FTP_TRAFF' => sizeit($ftp_trf),
				'SMTP_TRAFF' => sizeit($smtp_trf),
				'POP_TRAFF' => sizeit($pop_trf),
				'SUM_TRAFF' => sizeit($web_trf + $ftp_trf + $smtp_trf + $pop_trf),
				'CONTENT' => ($i % 2 == 0) ? 'content' : 'content2',
				'MONTH' => $month,
				'YEAR' => $year,
				//'DOMAIN_ID' => $domain_id,
				'WEB_ALL' => sizeit($sum_web),
				'FTP_ALL' => sizeit($sum_ftp),
				'SMTP_ALL' => sizeit($sum_mail),
				'POP_ALL' => sizeit($sum_pop),
				'SUM_ALL' => sizeit($sum_web + $sum_ftp + $sum_mail + $sum_pop)));
		$tpl->parse('TRAFF_ITEM', '.traff_item');
		$counter ++;
	}

}

// common page data.

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(array(
	'TR_PAGE_TITLE' => tr('Selity - Client/Domain Statistics'),
	'THEME_COLOR_PATH' => '../themes/'.$theme_color,
	'THEME_CHARSET' => tr('encoding'),
	'ISP_LOGO' => get_logo($_SESSION['user_id'])
));

// dynamic page data.

$current_month = date('m', time());
$current_year = date('Y', time());
list ($current_month, $current_year) = gen_page_post_data($tpl, $current_month, $current_year);
gen_dmn_traff_list($tpl, $sql, $current_month, $current_year, $_SESSION['user_id']);

// static page messages.

gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_statistics.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_statistics.tpl');

gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(array(
	'TR_DOMAIN_STATISTICS' => tr('Domain statistics'),
	'DOMAIN_URL' => 'http://' . $_SESSION['user_logged'] . '/stats/',
	'TR_AWSTATS' => tr('Web Stats'),
	'TR_MONTH' => tr('Month'),
	'TR_YEAR' => tr('Year'),
	'TR_SHOW' => tr('Show'),
	'TR_DATE' => tr('Date'),
	'TR_WEB_TRAFF' => tr('WEB'),
	'TR_FTP_TRAFF' => tr('FTP'),
	'TR_SMTP_TRAFF' => tr('SMTP'),
	'TR_POP_TRAFF' => tr('POP/IMAP'),
	'TR_SUM' => tr('Sum'),
	'TR_ALL' => tr('Total')
));

gen_page_message($tpl);
$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();
