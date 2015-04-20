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

if (!in_array($_SESSION['user_type'], array('reseller', 'admin'))) {
	header('Location: index.php');
	die();
}


// *******************************************************
// * Function definitions
// *

function loadHP() {

	$tpl = template::getInstance();

	if(!array_key_exists('hpid', $_GET) || !is_numeric($_GET['hpid'])){
		set_page_message(tr('Invalid id!'));
		header('Location: hosting_plan.php');
		die();
	}

	$hp = new selity_hp();
	$hp->id = $_SESSION['hpid'] = $_GET['hpid'];
	$hp->reseller_id = $_SESSION['user_id'];
	$hp->setMode(configs::getInstance()->HOSTING_PLANS_LEVEL);

	if (!$hp->loadData()) {
		set_page_message(tr('Invalid id!'));
		header('Location: hosting_plan.php');
		die();
	}

	$tpl->saveVariable(array(
		'READONLY'				=> '',
		'DISBLED'				=> '',
		'HP_NAME_VALUE'			=> $hp->name,
		'TR_MAX_SUB_LIMITS'		=> $hp->max_sub,
		'TR_MAX_ALS_VALUES'		=> $hp->max_als,
		'HP_MAIL_VALUE'			=> $hp->max_mail,
		'HP_FTP_VALUE'			=> $hp->max_ftp,
		'HP_SQL_DB_VALUE'		=> $hp->max_sqldb,
		'HP_SQL_USER_VALUE'		=> $hp->max_sqlu,
		'HP_TRAFF_VALUE'		=> $hp->max_traff,
		'HP_DISK_VALUE'			=> $hp->max_disk,
		'HP_DESCRIPTION_VALUE'	=> $hp->description,
		'HP_PRICE'				=> $hp->price,
		'HP_SETUPFEE'			=> $hp->setup_fee,
		'HP_CURRENCY'			=> $hp->value,
		'HP_PAYMENT'			=> $hp->payment,
		'TR_PHP_YES'			=> 'yes' === $hp->php ? 'checked' : '',
		'TR_PHP_NO'				=> 'yes' !== $hp->php ? 'checked' : '',
		'TR_CGI_YES'			=> 'yes' === $hp->cgi ? 'checked' : '',
		'TR_CGI_NO'				=> 'yes' !== $hp->cgi ? 'checked' : '',
		'TR_SUPPORT_YES'		=> 'yes' === $hp->support ? 'checked' : '',
		'TR_SUPPORT_NO'			=> 'yes' !== $hp->support ? 'checked' : '',
		'TR_STATUS_YES'			=> $hp->status == 1 ? 'checked' : '',
		'TR_STATUS_NO'			=> $hp->status != 1 ? 'checked' : '',
	));
}

function saveHP() {

	$ahp_error			= '_off_';
	$tpl = template::getInstance();

	if(!array_key_exists('hpid', $_SESSION) || !is_numeric($_SESSION['hpid'])){
		set_page_message(tr('Invalid id!'));
		header('Location: hosting_plan.php');
		die();
	}

	$hp = new selity_hp();
	$hp->id = $_SESSION['hpid'];
	$hp->reseller_id = $_SESSION['user_id'];
	$hp->setMode(configs::getInstance()->HOSTING_PLANS_LEVEL);

	if (!$hp->loadData()) {
		set_page_message(tr('Invalid id!'));
		header('Location: hosting_plan.php');
		die();
	}

	if($_SESSION['user_type'] == 'reseller' && $hp->reseller_id != $_SESSION['user_id']){
		set_page_message(tr('Operation not permited!'));
		header('Location: hosting_plan.php');
		die();
	}

	$hp->name			= clean_input($_POST['name']);
	$hp->max_als		= clean_input($_POST['max_als']);
	$hp->max_sub		= clean_input($_POST['max_sub']);
	$hp->max_mail		= clean_input($_POST['max_mail']);
	$hp->max_ftp		= clean_input($_POST['max_ftp']);
	$hp->max_sqldb		= clean_input($_POST['max_sqldb']);
	$hp->max_sqlu		= clean_input($_POST['max_sqlu']);
	$hp->max_traff		= clean_input($_POST['max_traff']);
	$hp->max_disk		= clean_input($_POST['max_disk']);
	$hp->php			= isset($_POST['php']) ? clean_input($_POST['php']) : 'no';
	$hp->cgi			= isset($_POST['cgi']) ? clean_input($_POST['cgi']) : 'no';
	$hp->support		= isset($_POST['support']) ? clean_input($_POST['support']) : 'no';
	$hp->description	= clean_input($_POST['description']);
	$hp->price			= clean_input($_POST['price']);
	$hp->setup_fee		= clean_input($_POST['setupfee']);
	$hp->value			= clean_input($_POST['currency']);
	$hp->payment		= clean_input($_POST['payment']);
	$hp->status			= clean_input($_POST['status']);

	if ($hp->save()) {
		set_page_message(tr('Hosting plan updated!'));
		header('Location: hosting_plan.php');
		die();
	} else {
		template::getInstance()->addMessage($hp->get_errors());
		template::getInstance()->saveVariable(array(
			'HP_NAME_VALUE'			=> clean_input($_POST['name']),
			'HP_DESCRIPTION_VALUE'	=> clean_input($_POST['description']),
			'TR_MAX_SUB_LIMITS'		=> clean_input($_POST['max_sub']),
			'TR_MAX_ALS_VALUES'		=> clean_input($_POST['max_als']),
			'HP_MAIL_VALUE'			=> clean_input($_POST['max_mail']),
			'HP_FTP_VALUE'			=> clean_input($_POST['max_ftp']),
			'HP_SQL_DB_VALUE'		=> clean_input($_POST['max_sqldb']),
			'HP_SQL_USER_VALUE'		=> clean_input($_POST['max_sqlu']),
			'HP_TRAFF_VALUE'		=> clean_input($_POST['hp_traff']),
			'HP_TRAFF'				=> clean_input($_POST['hp_traff']),
			'HP_DISK_VALUE'			=> clean_input($_POST['hp_disk']),
			'HP_PRICE_STYLE'		=> format_price(clean_input($_POST['hp_style'])),
			'HP_PRICE'				=> clean_input($_POST['price']),
			'HP_SETUPFEE'			=> clean_input($_POST['setupfee']),
			'HP_CURRENCY'			=> clean_input($_POST['currency']),
			'HP_PAYMENT'			=> clean_input($_POST['payment']),
			'TR_PHP_YES'			=> $_POST['php'] === 'yes' ? 'checked' : '',
			'TR_PHP_NO'				=> $_POST['php'] !== 'yes' ? 'checked' : '',
			'TR_CGI_YES'			=> $_POST['cgi'] === 'yes' ? 'checked' : '',
			'TR_CGI_NO'				=> $_POST['cgi'] !== 'yes' ? 'checked' : '',
			'TR_SUPPORT_YES'		=> $_POST['support'] === 'yes' ? 'checked' : '',
			'TR_SUPPORT_NO'			=> $_POST['support'] !== 'yes' ? 'checked' : '',
			'TR_STATUS_YES'			=> $_POST['status'] == 1 ? 'checked' : '',
			'TR_STATUS_NO'			=> $_POST['status'] != 1 ? 'checked' : '',
		));
	}
	return true;
}

$cfg = configs::getInstance();
$tpl = template::getInstance();

$theme_color = $cfg->USER_INITIAL_THEME;

$tpl->saveVariable(array(
	'ADMIN_TYPE'				=> $_SESSION['user_type'],
	'TR_PAGE_TITLE'				=> tr('Selity - Administrator/Edit hosting plan'),
	'THEME_COLOR_PATH'			=> '../themes/'.$theme_color,
	'THEME_CHARSET'				=> tr('encoding'),
	'TR_OP_HOSTING_PLAN'		=> tr('Edit hosting plan'),
	'TR_HOSTING_PLAN_PROPS'		=> tr('Hosting plan properties'),
	'TR_TEMPLATE_NAME'			=> tr('Template name'),
	'TR_MAX_SUBDOMAINS'			=> tr('Max subdomains&nbsp;<i>(-1 disabled, 0 unlimited)</i>'),
	'TR_MAX_ALIASES'			=> tr('Max aliases&nbsp;<i>(-1 disabled, 0 unlimited)</i>'),
	'TR_MAX_MAILACCOUNTS'		=> tr('Mail accounts limit&nbsp;<i>(-1 disabled, 0 unlimited)</i>'),
	'TR_MAX_FTP'				=> tr('FTP accounts limit&nbsp;<i>(-1 disabled, 0 unlimited)</i>'),
	'TR_MAX_SQL'				=> tr('SQL databases limit&nbsp;<i>(-1 disabled, 0 unlimited)</i>'),
	'TR_MAX_SQL_USERS'			=> tr('SQL users limit&nbsp;<i>(-1 disabled, 0 unlimited)</i>'),
	'TR_MAX_TRAFFIC'			=> tr('Traffic limit [MB]&nbsp;<i>(0 unlimited)</i>'),
	'TR_DISK_LIMIT'				=> tr('Disk limit [MB]&nbsp;<i>(0 unlimited)</i>'),
	'TR_PHP'					=> tr('PHP'),
	'TR_CGI'					=> tr('CGI / Perl'),
	'TR_BACKUP_RESTORE'			=> tr('Backup and restore'),
	'TR_APACHE_LOGS'			=> tr('Apache logfiles'),
	'TR_AWSTATS'				=> tr('AwStats'),
	'TR_YES'					=> tr('yes'),
	'TR_NO'						=> tr('no'),
	'TR_BILLING_PROPS'			=> tr('Billing Settings'),
	'TR_PRICE_STYLE'			=> tr('Price Style'),
	'TR_PRICE'					=> tr('Price'),
	'TR_SETUP_FEE'				=> tr('Setup fee'),
	'TR_VALUE'					=> tr('Currency'),
	'TR_PAYMENT'				=> tr('Payment period'),
	'TR_STATUS'					=> tr('Available for purchasing'),
	'TR_TEMPLATE_DESCRIPTON'	=> tr('Description'),
	'TR_EXAMPLE'				=> tr('(e.g. EUR)'),
	'TR_SEND'					=> tr('Update plan')
));

/*
* Dynamic page process
*
*/
if (array_key_exists('submit', $_POST)) {
	saveHP();
} else {
	loadHP();
}

genMainMenu();
genHPMenu();

$tpl->flushOutput('common/hp_add');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
