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
	header('Location: hosting_plan.php');
	die();
}

// Function definitions

function newHP() {
	template::getInstance()->saveVariable(array(
		'TR_STATUS_NO'			=> 'checked',
		'TR_PHP_NO'				=> 'checked',
		'TR_CGI_NO'				=> 'checked',
		'TR_SUPPORT_NO'			=> 'checked',
	));

}

function addHP() {

	$hp = new selity_hp();
	$hp->setMode(configs::getInstance()->HOSTING_PLANS_LEVEL);

	$hp->reseller_id	= $_SESSION['user_id'];
	$hp->name			= clean_input($_POST['name']);
	$hp->max_sub		= clean_input($_POST['max_sub']);
	$hp->max_als		= clean_input($_POST['max_als']);
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
	$hp->price			= empty($_POST['price']) ? 0 : clean_input($_POST['price']);
	$hp->setup_fee		= empty($_POST['setupfee']) ? 0 : clean_input($_POST['setupfee']);
	$hp->value			= clean_input($_POST['currency']);
	$hp->payment		= clean_input($_POST['payment']);
	$hp->status			= $_POST['status'];

	if ($hp->save()) {
		set_page_message(tr('Hosting plan added!'));
		header('Location: hosting_plan.php');
		die();
	} else {
		template::getInstance()->addMessage($hp->get_errors());
		template::getInstance()->saveVariable(array(
			'HP_NAME_VALUE'			=> $hp->name,
			'TR_MAX_SUB_LIMITS'		=> $hp->max_sub,
			'TR_MAX_ALS_VALUES'		=> $hp->max_als,
			'HP_MAIL_VALUE'			=> $hp->max_mail,
			'HP_FTP_VALUE'			=> $hp->max_ftp,
			'HP_SQL_DB_VALUE'		=> $hp->max_sqldb,
			'HP_SQL_USER_VALUE'		=> $hp->max_sqlu,
			'HP_TRAFF_VALUE'		=> $hp->max_traff,
			'HP_DISK_VALUE'			=> $hp->max_disk,
			'TR_PHP_YES'			=> $hp->php == 'yes' ? 'checked' : '',
			'TR_PHP_NO'				=> $hp->php != 'no' ? 'checked' : '',
			'TR_CGI_YES'			=> $hp->cgi == 'yes' ? 'checked' : '',
			'TR_CGI_NO'				=> $hp->cgi != 'no' ? 'checked' : '',
			'TR_SUPPORT_YES'		=> $hp->support == 'yes' ? 'checked' : '',
			'TR_SUPPORT_NO'			=> $hp->support != 'no' ? 'checked' : '',

			'HP_DESCRIPTION_VALUE'	=> $hp->description,
			'HP_PRICE'				=> $hp->price,
			'HP_SETUPFEE'			=> $hp->setup_fee,
			'HP_VALUE'				=> $hp->value,
			'HP_PAYMENT'			=> $hp->payment,
			'TR_STATUS_YES'			=> $hp->status == 1 ? 'checked' : '',
			'TR_STATUS_NO'			=> $hp->status != 1 ? 'checked' : '',
		));
	}
	return true;
}

$cfg = configs::getInstance();
$tpl = template::getInstance();

$theme_color = $cfg->USER_INITIAL_THEME;

$tpl->saveVariable(array(
	'ADMIN_TYPE'				=> $_SESSION['user_type'],
	'TR_PAGE_TITLE'				=> tr('Selity - Add hosting plan'),
	'THEME_COLOR_PATH'			=> '../themes/'.$theme_color,
	'THEME_CHARSET'				=> tr('encoding'),
	'TR_OP_HOSTING_PLAN'		=> tr('Add hosting plan'),
	'TR_HOSTING_PLAN_PROPS'		=> tr('Hosting plan properties'),
	'TR_TEMPLATE_NAME'			=> tr('Template name'),
	'TR_MAX_ALIASES'			=> tr('Max aliases&nbsp;<i>(-1 disabled, 0 unlimited)</i>'),
	'TR_MAX_SUBDOMAINS'			=> tr('Max subdomains&nbsp;<i>(-1 disabled, 0 unlimited)</i>'),
	'TR_MAX_MAILACCOUNTS'		=> tr('Mail accounts limit&nbsp;<i>(-1 disabled, 0 unlimited)</i>'),
	'TR_MAX_FTP'				=> tr('FTP accounts limit&nbsp;<i>(-1 disabled, 0 unlimited)</i>'),
	'TR_MAX_SQL'				=> tr('SQL databases limit&nbsp;<i>(-1 disabled, 0 unlimited)</i>'),
	'TR_MAX_SQL_USERS'			=> tr('SQL users limit&nbsp;<i>(-1 disabled, 0 unlimited)</i>'),
	'TR_MAX_TRAFFIC'			=> tr('Traffic limit [MB]&nbsp;<i>(0 unlimited)</i>'),
	'TR_DISK_LIMIT'				=> tr('Disk limit [MB]&nbsp;<i>(0 unlimited)</i>'),
	'TR_PHP'					=> tr('PHP'),
	'TR_CGI'					=> tr('CGI'),
	'TR_YES'					=> tr('yes'),
	'TR_NO'						=> tr('no'),
	'TR_BILLING_PROPS'			=> tr('Billing Settings'),
	'TR_PRICE'					=> tr('Price'),
	'TR_SETUP_FEE'				=> tr('Setup fee'),
	'TR_VALUE'					=> tr('Currency'),
	'TR_PAYMENT'				=> tr('Payment period'),
	'TR_STATUS'					=> tr('Available for purchasing'),
	'TR_TEMPLATE_DESCRIPTON'	=> tr('Description'),
	'TR_SEND'					=> tr('Add plan')
));

if (array_key_exists('submit', $_POST)) {
	addHP();
} else {
	newHP($tpl);
}

genMainMenu();
genHPMenu();

$tpl->flushOutput('common/hp_add');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();
