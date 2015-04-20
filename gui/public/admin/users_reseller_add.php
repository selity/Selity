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
$theme_color = configs::getInstance()->USER_INITIAL_THEME;


function getIp() {
	$sql = mysql::getInstance();
	$tpl = template::getInstance();
	$query = 'SELECT * FROM `server_ips`  ORDER BY `ip_number`';
	$rs = $sql->doQuery($query);
	if ($rs->countRows() == 0) {
		$tpl->addMessage(tr('Reseller IP list is empty!'));
	} else {
		$ips = array();
		while (!$rs->EOF) {
			$checked = array_key_exists('ip', $_POST) && is_array($_POST['ip']) && in_array($rs->ip_id, $_POST['ip']) ? 'checked' : '';
			$ips[] = array(
				'IP_NUMBER'		=> $rs->ip_number,
				'IP_ID'			=> $rs->ip_id,
				'IP_CHK'		=> $checked
			);
			$rs->nextRow();
		}
		$tpl->saveRepeats(array('IPS' => $ips));
	}
}

function addReseller() {

	$sql = mysql::getInstance();
	$tpl = template::getInstance();
	$cfg = configs::getInstance();

	if (array_key_exists('submit', $_POST)) {

		$reseller = new selity_reseller();

		if($_POST['pass'] != $_POST['pass_rep']){
			$tpl->addMessage(tr('Password do not match!'));
			$_POST['pass'] = '';
		}

		$reseller->admin_pass		= clean_input($_POST['pass']);
		$reseller->created_by		= $_SESSION['user_id'];
		$reseller->created_on		= time();
		$reseller->email			= clean_input($_POST['email']);

		$reseller->fname	= clean_input($_POST['fname']);
		$reseller->lname	= clean_input($_POST['lname']);
		$reseller->gender	= clean_input($_POST['gender']);
		$reseller->firm		= clean_input($_POST['firm']);
		$reseller->zip		= clean_input($_POST['zip']);
		$reseller->city		= clean_input($_POST['city']);
		$reseller->state	= clean_input($_POST['state']);
		$reseller->country	= clean_input($_POST['country']);
		$reseller->phone	= clean_input($_POST['phone']);
		$reseller->fax		= clean_input($_POST['fax']);
		$reseller->street1	= clean_input($_POST['street1']);
		$reseller->street2	= clean_input($_POST['street2']);

		$reseller->lang		= $cfg->USER_INITIAL_LANG;
		$reseller->layout	= $cfg->USER_INITIAL_THEME;

		$reseller->max_usr		= clean_input($_POST['max_usr']);
		$reseller->max_als		= clean_input($_POST['max_als']);
		$reseller->max_sub		= clean_input($_POST['max_sub']);
		$reseller->max_mail		= clean_input($_POST['max_mail']);
		$reseller->max_ftp		= clean_input($_POST['max_ftp']);
		$reseller->max_sqldb	= clean_input($_POST['max_sqldb']);
		$reseller->max_sqlu		= clean_input($_POST['max_sqlu']);
		$reseller->max_traff	= clean_input($_POST['max_traffic']);
		$reseller->max_disk		= clean_input($_POST['max_disk']);
		$reseller->php			= clean_input(isset($_POST['php']) ? $_POST['php'] : '');
		$reseller->cgi			= clean_input(isset($_POST['cgi']) ? $_POST['cgi'] : '');
		$reseller->support		= clean_input(isset($_POST['support']) ? $_POST['support'] : '');

		if(array_key_exists('ip', $_POST) && is_array($_POST['ip'])){
			foreach($_POST['ip'] as $ip_id){
				$reseller->addIP($ip_id);
			}
		}

		if($reseller->save()){

			write_log($_SESSION['user_logged'].': add reseller: '. $reseller->admin_name.' ('.$reseller->email.')');

			send_add_user_auto_msg (
				$user_id,
				$reseller->admin_name,
				clean_input($_POST['pass']),
				clean_input($_POST['email']),
				clean_input($_POST['fname']),
				clean_input($_POST['lname']),
				tr('Reseller'),
				$gender
			);

			$tpl->addMessage(tr('Reseller added'));
			header('Location: users_show.php');
			die();

		} else {
			$tpl->addMessage($reseller->getMessage());
		}
	}
}

genMainMenu();
genAdminUsersMenu();

getIp();
addReseller();

$tpl->saveVariable(array(
	'TR_PAGE_TITLE'				=> tr('Selity - Add reseller'),
	'THEME_COLOR_PATH'			=> '../themes/'.$theme_color,
	//'THEME_CHARSET'				=> tr('encoding'),
	'TR_ADD_RESELLER'			=> tr('Add reseller'),
	'TR_CORE_DATA'				=> tr('Core data'),
	'TR_EMAIL'					=> tr('Email'),
	'TR_PASSWORD'				=> tr('Password'),
	'TR_PASSWORD_REPEAT'		=> tr('Repeat password'),
	'TR_GENPAS'					=> tr('Generate password'),
	'TR_UNLIMITED'				=> tr('Unlimited'),
	'TR_DISABLED'				=> tr('Disabled'),
	'TR_MAX_USER_COUNT'			=> tr('User limit'),
	'TR_MAX_SUB_COUNT'			=> tr('Subdomains limit'),
	'TR_MAX_ALS_COUNT'			=> tr('Aliases limit'),
	'TR_MAX_MAIL_COUNT'			=> tr('Mail accounts limit'),
	'TR_MAX_FTP_COUNT'			=> tr('FTP accounts limit'),
	'TR_MAX_SQLDB_COUNT'		=> tr('SQL databases limit'),
	'TR_MAX_SQLU_COUNT'			=> tr('SQL users limit'),
	'TR_MAX_TRAFF_COUNT'		=> tr('Traffic limit [MB]'),
	'TR_MAX_DISK_AMOUNT'		=> tr('Disk limit [MB]'),
	'TR_PHP'					=> tr('PHP'),
	'TR_CGI'					=> tr('CGI'),
	'TR_SUPPORT'				=> tr('Ticket system enabled'),
	'TR_YES'					=> tr('yes'),
	'TR_NO'						=> tr('no'),

	'TR_RESELLER_IPS'			=> tr('Reseller IPs'),
	'TR_IP_ASSIGN'				=> tr('Assign'),
	'TR_IP_NUMBER'				=> tr('Number'),

	'TR_PERSONAL_DATA'		=> tr('Personal data'),
	'TR_FIRST_NAME'			=> tr('First name'),
	'TR_LAST_NAME'			=> tr('Last name'),
	'TR_GENDER'				=> tr('Gender'),
	'TR_MALE'				=> tr('Male'),
	'TR_FEMALE'				=> tr('Female'),
	'TR_UNKNOWN'			=> tr('Unknown'),
	'TR_COMPANY'			=> tr('Company'),
	'TR_STREET_1'			=> tr('Street 1'),
	'TR_STREET_2'			=> tr('Street 2'),
	'TR_ZIP'				=> tr('Zip/Postal code'),
	'TR_CITY'				=> tr('City'),
	'TR_STATE'				=> tr('State'),
	'TR_COUNTRY'			=> tr('Country'),
	'TR_PHONE'				=> tr('Phone'),
	'TR_FAX'				=> tr('Fax'),
	'TR_SUBMIT'				=> tr('Add'),
	'TR_DISABLED_MSG'		=> tr('-1 disabled'),
	'TR_UNLIMITED_MSG'		=> tr('0 unlimited'),
));

if(array_key_exists('submit', $_POST) || array_key_exists('genpass', $_POST)){
	$tpl->saveVariable(array(

		'EMAIL'			=> clean_input($_POST['email']),
		'GENPAS'		=> array_key_exists('genpass', $_POST) ? passgen() : '',
		'FIRST_NAME'	=> clean_input($_POST['fname']),
		'LAST_NAME'		=> clean_input($_POST['lname']),
		'FIRM'			=> clean_input($_POST['firm']),
		'ZIP'			=> clean_input($_POST['zip']),
		'CITY'			=> clean_input($_POST['city']),
		'STATE'			=> clean_input($_POST['state']),
		'COUNTRY'		=> clean_input($_POST['country']),
		'STREET_1'		=> clean_input($_POST['street1']),
		'STREET_2'		=> clean_input($_POST['street2']),
		'PHONE'			=> clean_input($_POST['phone']),
		'FAX'			=> clean_input($_POST['fax']),
		'VL_MALE'		=> (($_POST['gender'] == 'M') ? 'selected' : ''),
		'VL_FEMALE'		=> (($_POST['gender'] == 'F') ? 'selected' : ''),
		'VL_UNKNOWN'	=> ((($_POST['gender'] == 'U') || (empty($_POST['gender']))) ? 'selected' : ''),

		'MAX_USR_COUNT'		=> clean_input($_POST['max_usr']),
		'MAX_ALS_COUNT'		=> clean_input($_POST['max_als']),
		'MAX_SUB_COUNT'		=> clean_input($_POST['max_sub']),
		'MAX_MAIL_COUNT'	=> clean_input($_POST['max_mail']),
		'MAX_FTP_COUNT'		=> clean_input($_POST['max_ftp']),
		'MAX_SQLDB_COUNT'	=> clean_input($_POST['max_sqldb']),
		'MAX_SQLU_COUNT'	=> clean_input($_POST['max_sqlu']),
		'MAX_TRAFF_COUNT'	=> clean_input($_POST['max_traffic']),
		'MAX_DISK_AMOUNT'	=> clean_input($_POST['max_disk']),
		'PHP_YES'			=> isset($_POST['php']) && $_POST['php'] == 'yes' ? 'checked' : '',
		'PHP_NO'			=> isset($_POST['php']) && $_POST['php'] == 'yes' ? '' : 'checked',
		'CGI_YES'			=> isset($_POST['cgi']) && $_POST['cgi'] == 'yes' ? 'checked' : '',
		'CGI_NO'			=> isset($_POST['cgi']) && $_POST['cgi'] == 'yes' ? '' : 'checked',
		'SUPPORT_YES'		=> isset($_POST['support']) && $_POST['support'] == 'yes' ? 'checked' : '',
		'SUPPORT_NO'		=> isset($_POST['support']) && $_POST['support'] == 'yes' ? '' : 'checked',
	));
}

$tpl->flushOutput('admin/reseller_add');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

