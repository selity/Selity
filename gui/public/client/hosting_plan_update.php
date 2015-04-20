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
$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/hosting_plan_update.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('def_language', 'page');
$tpl->define_dynamic('logged_from', 'page');
$tpl->define_dynamic('hosting_plans', 'page');
$tpl->define_dynamic('hp_order', 'page');

/*
 *
 * page actions.
 *
 */

function gen_hp(&$tpl, &$sql, $user_id) {
	$availabe_order = 0;
	$hp_title = tr('Hosting plans available for update');
	// lets see if we have an order
	$query = '
		SELECT
			*
		FROM
			orders
		WHERE
			customer_id=?
		AND
			status<>?
	';
	$rs = mysql::getInstance()->doQuery($query, $user_id, 'added');

	if ($rs->countRows() > 0) {
		$availabe_order = 1;
		$availabe_hp_id = $rs->plan_id;

		$query = '
			SELECT
				*
			FROM
				hosting_plans
			WHERE
				id=?
		';

		$rs = mysql::getInstance()->doQuery($query, $availabe_hp_id);
		$count = 2;
		$purchase_text = tr('Cancel order');
		$purchase_link = 'delete_id';
		$hp_title = tr('Your order');
	} else {
		// generate all hosting plans available for purchasing
		if (Config::exists('HOSTING_PLANS_LEVEL') && Config::get('HOSTING_PLANS_LEVEL') === 'admin') {
			$query = '
				SELECT
					t1.*,
					t2.admin_id, t2.admin_type
				FROM
					hosting_plans as t1,
					admin as t2
				WHERE
					t2.admin_type = ?
				AND
					t1.reseller_id = t2.admin_id
				AND
					t1.status=1
				ORDER BY
					t1.name
			';

			$rs = mysql::getInstance()->doQuery($query, 'admin');

			$count = $rs->countRows();
			$count++;
		} else {
			$query = '
				SELECT
					*
				FROM
					hosting_plans
				WHERE
				 	reseller_id = ?
				AND
					status = ?
			';

			$count_query = '
				SELECT
					COUNT(id) AS cnum
				FROM
					hosting_plans
				WHERE
					reseller_id = ?
				AND
					status = ?
			';

			$cnt = mysql::getInstance()->doQuery($count_query, $_SESSION['user_created_by'], 1);
			$rs = mysql::getInstance()->doQuery($query, $_SESSION['user_created_by'], 1);
			$count = $cnt->cnum + 1;
		}

		$purchase_text = tr('Purchase');
		$purchase_link = 'order_id';
	}

	if ($rs->countRows() == 0) {
		$tpl->assign(
					array(
						'TR_HOSTING_PLANS' => $hp_title,
						'HOSTING_PLANS' => '',
						'HP_ORDER' => '',
						'COLSPAN' => 2
					)
			);

		set_page_message(tr('There are no available updates'));
		return;
	}

	$tpl->assign('COLSPAN', $count);
	$i = 0;
	while (!$rs->EOF) {
		$limits = unserialize($rs->limits);

		$details = '';
		$details = $limits['max_php'] === 'yes' ? tr('PHP Support: enabled') . "<br>" : tr('PHP Support: enabled') . "<br>";
		$details .= $limits['max_cgi'] === 'yes' ? tr('CGI Support: enabled') . "<br>" : tr('CGI Support: enabled') . "<br>";

		$hdd_usage = tr('Disk limit') . ": " . translate_limit_value($limits['hp_disk'], true) . "<br>";

		$traffic_usage = tr('Traffic limit') . ": " . translate_limit_value($limits['hp_traff'], true);

		$details .= tr('Aliases') . ": " . translate_limit_value($limits['max_als']) . "<br>";
		$details .= tr('Subdomains') . ": " . translate_limit_value($limits['max_sub']) . "<br>";
		$details .= tr('Emails') . ": " . translate_limit_value($limits['max_mail']) . "<br>";
		$details .= tr('FTPs') . ": " . translate_limit_value($limits['max_ftp']) . "<br>";
		$details .= tr('SQL Databases') . ": " . translate_limit_value($limits['max_sqldb']) . "<br>";
		$details .= tr('SQL Users') . ": " . translate_limit_value($limits['max_sqlu']) . "<br>";
		$details .= $hdd_usage . $traffic_usage;

		$price = $rs->price == 0 || $rs->price == '' ? $price = tr('free of charge') : $rs->price . " " . $rs->value . " " . $rs->fieldspayment;

		$check_query = '
			SELECT
				COUNT(*)
			FROM
				user_system_props
			WHERE
				user_admin_id=?
			AND
				max_mail=?
			AND
				max_ftp=?
			AND
				max_traff=?
			AND
				max_sqldb=?
			AND
				max_sqlu=?
			AND
				max_als=?
			AND
				max_sub=?
			AND
				max_disk=?
			AND
				php=?
			AND
				cgi=?
		';
		$check = mysql::getInstance()->doQuery($check_query,
			$_SESSION['user_id'],
			$limits['max_mail'],
			$limits['max_ftp'],
			$limits['hp_traff'],
			$limits['max_sqldb'],
			$limits['max_sqlu'],
			$limits['max_als'],
			$limits['max_sub'],
			$limits['hp_disk'],
			$limits['max_php'],
			$limits['max_cgi']
		);
		if ($check->countRows() == 0) {
			$tpl->assign(array(
				'HP_NAME' => stripslashes($rs->name),
				'HP_DESCRIPTION' => stripslashes($rs->description),
				'HP_DETAILS' => stripslashes($details),
				'HP_COSTS' => $price,
				'ID' => $rs->id,
				'TR_PURCHASE' => $purchase_text,
				'LINK' => $purchase_link,
				'TR_HOSTING_PLANS' => $hp_title,
			));

			$tpl->parse('HOSTING_PLANS', '.hosting_plans');
			$tpl->parse('HP_ORDER', '.hp_order');
			$i++;
		}

		$rs->nextRow();
	}
	if ($i == 0) {
		$tpl->assign(
					array(
						'HOSTING_PLANS' => '',
						'HP_ORDER' => '',
						'TR_HOSTING_PLANS' => $hp_title,
						'COLSPAN' => '2'
					)
				);

		set_page_message(tr('There are no available hosting plans for update'));
	}
}

$theme_color = Config::get('USER_INITIAL_THEME');
$tpl->assign(
			array(
				'TR_CLIENT_UPDATE_HP' => tr('Selity - Update hosting plan'),
				'THEME_COLOR_PATH' => '../themes/'.$theme_color,
				'THEME_CHARSET' => tr('encoding'),
				'ISP_LOGO' => get_logo($_SESSION['user_id'])
				)
	);

function add_new_order(&$tpl, &$sql, $order_id, $user_id) {
	$date = time();
	$status = "update";
	$query = '
			  INSERT INTO orders
				   (user_id,
					plan_id,
					date,
					domain_name,
					customer_id,
					fname,
					lname,
					firm,
					zip,
					city,
					country,
					email,
					phone,
					fax,
					street1,
					street2,
					status)
			  VALUES
				 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
';

	$rs = mysql::getInstance()->doQuery($query, $_SESSION['user_created_by'], $order_id, $date, $_SESSION['user_logged'],
										 $user_id, '', '', '', '', '', '', '', '', '', '', '', $status);
	set_page_message(tr('Your request for hosting pack update was added successfully'));

	$query = '
			SELECT
				t1.email AS reseller_mail,
				t2.email AS user_mail
			FROM
				admin AS t1,
				admin AS t2
			WHERE
				t1.admin_id = ?
			AND
				t2.admin_id = ?
';

	$rs = mysql::getInstance()->doQuery($query, $_SESSION['user_created_by'], $_SESSION['user_id']);

	$to = $rs->reseller_mail;
	$FROM = $rs->user_mail;

	$headers  = "From: " . $FROM . "\n";
	$headers .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\nContent-Transfer-Encoding: 7bit\n";
	$headers .= "X-Mailer: Selity auto mailer";

	$subject = tr("[Selity OrderPanel] - You have an update order");

	$message = tr('You have an update order for the account {ACCOUNT}


Please login into your Selity control panel for more details');

	$message = str_replace('{ACCOUNT}', $_SESSION['user_logged'], $message);

	$mail_result = mail($to, $subject, $message, $headers);
}

function del_order(&$tpl, &$sql, $order_id, $user_id) {
	$query = '
		DELETE FROM
			orders
		WHERE
			user_id=?
		AND
		  customer_id = ?
';

	$rs = mysql::getInstance()->doQuery($query, $_SESSION['user_created_by'], $user_id);
	set_page_message(tr('Your request for hosting pack update was removed successfully'));
}

/*
 *
 * static page messages.
 *
 */

if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
	del_order($tpl, $sql, $_GET['delete_id'], $_SESSION['user_id']);
}

if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
	add_new_order($tpl, $sql, $_GET['order_id'], $_SESSION['user_id']);
}

gen_hp($tpl, $sql, $_SESSION['user_id']);

gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_general_information.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_general_information.tpl');

gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(
			array(
				'TR_LANGUAGE' => tr('Language'),
				'TR_SAVE' => tr('Save'),
				)
			);

gen_page_message($tpl);

$tpl->parse('PAGE', 'page');

$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

