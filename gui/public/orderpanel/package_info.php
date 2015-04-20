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

$tpl = new pTemplate();

$tpl->define_dynamic('page', Config::get('PURCHASE_TEMPLATE_PATH') . '/package_info.tpl');
$tpl->define_dynamic('purchase_list', 'page');
$tpl->define_dynamic('purchase_message', 'page');
$tpl->define_dynamic('purchase_header', 'page');
$tpl->define_dynamic('purchase_footer', 'page');

/*
* Functions start
*/

function translate_sse($value)
{
	if ($value == '_yes_') {
		return tr('Yes');
	} else if ($value == '_no_') {
		return tr('No');
	} else {
		return $value;
	}
}

function gen_plan_details(&$tpl, &$sql, $user_id, $plan_id) {
	$hp = new selity_hp();
	$hp->id = $plan_id;
	$hp->reseller_id = $user_id;
	$hp->setMode(configs::getInstance()->HOSTING_PLANS_LEVEL);


	if (!$hp->loadData()) {
		header('Location: index.php?user_id='.$user_id);
		die();
	} else {

		$price = ($hp->price == 0 || $hp->price == '') ? tr('free of charge') : $hp->price . ' ' . $hp->value . ' ' . $hp->payment;
		$setup_fee = ($hp->setup_fee == 0 || $hp->setup_fee == '') ? tr('free of charge') : $hp->setup_fee . ' ' . $hp->value . ' ' . $hp->payment;
		$setup_fee = $hp->setup_fee;


		$hp_disk = translate_limit_value($hp->hp_disk, true) . '<br>';
		$hp_traff = translate_limit_value($hp->hp_traff, true);

		$tpl->assign(array(
			'PACK_NAME'		=> $hp->name,
			'DESCRIPTION'	=> $hp->description,
			'PACK_ID'		=> $hp->id,
			'USER_ID'		=> $user_id,
			'PURCHASE'		=> tr('Purchase'),
			'ALIAS'			=> translate_limit_value($hp->max_als),
			'SUBDOMAIN'		=> translate_limit_value($hp->max_sub),
			'HDD'			=> $hp_disk,
			'TRAFFIC'		=> $hp_traff,
			'PHP'			=> translate_sse($hp->max_php),
			'CGI'			=> translate_sse($hp->max_cgi),
			'MAIL'			=> translate_limit_value($hp->max_mail),
			'FTP'			=> translate_limit_value($hp->max_ftp),
			'SQL_DB'		=> translate_limit_value($hp->max_sqldb),
			'SQL_USR'		=> translate_limit_value($hp->max_sqlu),
			'PRICE'			=> $price,
			'SETUP'			=> $setup_fee,
		));
	}
}

/*
* Functions end
*/

/*
*
* static page messages.
*
*/

if (isset($_GET['id'])) {
	$plan_id = $_GET['id'];
	$_SESSION['plan_id'] = $plan_id;
	if (isset($_SESSION['user_id'])) {
		$user_id = $_SESSION['user_id'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
		$_SESSION['user_id'] = $user_id;
	} else {
		system_message(tr('You do not have permission to access this interface!'));
	}
} else {
	system_message(tr('You do not have permission to access this interface!'));
}

gen_purchase_haf($tpl, $sql, $user_id);
gen_plan_details($tpl, $sql, $user_id, $plan_id);

gen_page_message($tpl);

$tpl->assign(
	array('TR_DOMAINS' => tr('Domains'),
		'TR_WEBSPACE' => tr('Webspace'),
		'TR_HDD' => tr('Disk limit'),
		'TR_TRAFFIC' => tr('Traffic limit'),
		'TR_FEATURES' => tr('Domain Features'),
		'TR_STANDARD_FEATURES' => tr('Package Features'),
		'TR_WEBMAIL' => tr('Webmail'),
		'TR_FILEMANAGER' => tr('Filemanager'),
		'TR_BACKUP' => tr('Backup and Restore'),
		'TR_ERROR_PAGES' => tr('Custom Error Pages'),
		'TR_HTACCESS' => tr('Protected Areas'),
		'TR_PHP_SUPPORT' => tr('PHP support'),
		'TR_CGI_SUPPORT' => tr('CGI support'),
		'TR_MYSQL_SUPPORT' => tr('SQL support'),
		'TR_SUBDOMAINS' => tr('Subdomains'),
		'TR_DOMAIN_ALIAS' => tr('Domain aliases'),
		'TR_MAIL_ACCOUNTS' => tr('Mail accounts'),
		'TR_FTP_ACCOUNTS' => tr('FTP accounts'),
		'TR_SQL_DATABASES' => tr('SQL databases'),
		'TR_SQL_USERS' => tr('SQL users'),
		'TR_STATISTICS' => tr('Statistics'),
		'TR_CUSTOM_LOGS' => tr('Custom Apache Logs'),
		'TR_ONLINE_SUPPORT' => tr('Web & E-Mail Support'),
		'TR_OWN_DOMAIN' => tr('Your Own Domain'),
		'TR_SELITY' => tr('Selity Control Panel'),
		'TR_UPDATES' => tr('Automatic Updates'),
		'TR_PRICE' => tr('Price'),
		'TRR_PRICE' => tr('Package Price'),
		'TR_SETUP_FEE' => tr('Setup Fee'),
		'TR_PERFORMANCE' => tr('Performance'),
		'TR_PURCHASE' => tr('Purchase'),
		'TR_BACK' => tr('Back'),
		'YES' => tr('Yes')
		)
	);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

