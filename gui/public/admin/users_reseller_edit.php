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

function check_user_data() {
	if ($_POST['pass'] != $_POST['pass_rep']) {
		template::getInstance()->addMessage(tr('Entered passwords do not match!'));
		return false;
	}
	return true;
}

function saveReseller($user) {

	if (array_key_exists('submit', $_POST)) {
		if(!empty($_POST['pass'])){
			$user->admin_pass = clean_input($_POST['pass']);
		}
		$user->email	= clean_input($_POST['email']);
		$user->fname	= clean_input($_POST['fname']);
		$user->lname	= clean_input($_POST['lname']);
		$user->gender	= clean_input($_POST['gender']);
		$user->firm		= clean_input($_POST['firm']);
		$user->zip		= clean_input($_POST['zip']);
		$user->city		= clean_input($_POST['city']);
		$user->state	= clean_input($_POST['state']);
		$user->country	= clean_input($_POST['country']);
		$user->phone	= clean_input($_POST['phone']);
		$user->fax		= clean_input($_POST['fax']);
		$user->street1	= clean_input($_POST['street1']);
		$user->street2	= clean_input($_POST['street2']);

		$user->max_usr		= clean_input($_POST['max_usr']);
		$user->max_als		= clean_input($_POST['max_als']);
		$user->max_sub		= clean_input($_POST['max_sub']);
		$user->max_mail		= clean_input($_POST['max_mail']);
		$user->max_ftp		= clean_input($_POST['max_ftp']);
		$user->max_sqldb	= clean_input($_POST['max_sqldb']);
		$user->max_sqlu		= clean_input($_POST['max_sqlu']);
		$user->max_traff	= clean_input($_POST['max_traffic']);
		$user->max_disk		= clean_input($_POST['max_disk']);
		$user->php			= clean_input(isset($_POST['php']) ? $_POST['php'] : '');
		$user->cgi			= clean_input(isset($_POST['cgi']) ? $_POST['cgi'] : '');
		$user->support		= clean_input(isset($_POST['support']) ? $_POST['support'] : '');

		$newIps = array_key_exists('ip', $_POST) && is_array($_POST['ip']) ? $_POST['ip'] : array();
		$toAdd = array_diff($newIps, $user->reseller_ips);
		foreach($toAdd as $ip){
			$user->addIP($ip);
		}
		$toRemove = array_diff($user->reseller_ips, $newIps);
		foreach($toRemove as $ip){
			$user->removeIP($ip);
		}

		if (check_user_data() && $user->save()) {
			write_log(sprintf(
				'%s: changes data/password for reseller: %s (%s)!',
				$_SESSION['user_logged'],
				$user->admin_name,
				$user->email
			));
			if (!empty($_POST['pass'])) {
				$query = 'DELETE FROM `login` WHERE user_name = ?';
				$rs = mysql::getInstance()->doQuery($query,$user->admin_name);
				if ($rs->countRows() != 0) {
					set_page_message(tr('User session was killed!'));
					write_log(sprintf(
						'%s killed %s\'s session because of password change',
						$_SESSION['user_logged'],
						$user->admin_name
					));
				}
				if (isset($_POST['send_data'])){
					send_add_user_auto_msg ($_SESSION['user_id'],
						$user->admin_name,
						clean_input($_POST['pass']),
						clean_input($_POST['email']),
						clean_input($_POST['fname']),
						clean_input($_POST['lname']),
						tr('Reseller')
					);
				}
			}
			template::getInstance()->addMessage(tr('Reseller data updated'));
			header('Location: users_show.php');
			die();
		} else {
			template::getInstance()->addMessage($user->getMessage());
		}
	}
}

function getIp($user) {
	$sql = mysql::getInstance();
	$tpl = template::getInstance();
	$query = 'SELECT * FROM `server_ips`  ORDER BY `ip_number`';
	$rs = $sql->doQuery($query);
	if ($rs->countRows() == 0) {
		$tpl->addMessage(tr('Reseller IP list is empty!'));
	} else {
		$ips = array();
		while (!$rs->EOF) {
			if(array_key_exists('submit', $_POST)){
				$checked = array_key_exists('ip', $_POST) && is_array($_POST['ip']) && in_array($rs->ip_id, $_POST['ip']) ? 'checked' : '';
			} else {
				$checked = in_array($rs->ip_id, $user->reseller_ips) ? 'checked' : '';
			}
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


if (array_key_exists('user_id', $_GET)) {
	$id = (int) $_GET['user_id'];
} else {
	$tpl->addMessage(tr('Invalid id!'));
	header('Location: users_show.php');
	die();
}

try{
	$user = new selity_reseller($id);
} catch (Exception $e) {
	$tpl->addMessage(tr('Invalid id!'));
	header('Location: users_show.php');
	die();
}

genMainMenu();
genAdminUsersMenu();

getIp($user);
saveReseller($user);

$tpl->saveSection('EDIT');
$tpl->saveVariable(array(
	'TR_PAGE_TITLE'			=> tr('Selity - Modify reseller'),
	'THEME_COLOR_PATH'		=> '../themes/'.$theme_color,
	//'THEME_CHARSET'		=> tr('encoding'),
	'TR_ADD_RESELLER'		=> tr('Modify reseller'),
	'TR_CORE_DATA'			=> tr('Core data'),
	'TR_USERNAME'			=> tr('Username'),
	'TR_PASSWORD'			=> tr('Password'),
	'TR_PASSWORD_REPEAT'	=> tr('Repeat password'),
	'TR_GENPAS'				=> tr('Generate password'),
	'TR_EMAIL'				=> tr('Email'),
	'TR_UNLIMITED'			=> tr('Unlimited'),
	'TR_DISABLED'			=> tr('Disabled'),
	'TR_MAX_USER_COUNT'		=> tr('User limit'),
	'TR_MAX_SUB_COUNT'		=> tr('Subdomains limit'),
	'TR_MAX_ALS_COUNT'		=> tr('Aliases limit'),
	'TR_MAX_MAIL_COUNT'		=> tr('Mail accounts limit'),
	'TR_MAX_FTP_COUNT'		=> tr('FTP accounts limit'),
	'TR_MAX_SQLDB_COUNT'	=> tr('SQL databases limit'),
	'TR_MAX_SQLU_COUNT'		=> tr('SQL users limit'),
	'TR_MAX_TRAFF_COUNT'	=> tr('Traffic limit [MB]'),
	'TR_MAX_DISK_AMOUNT'	=> tr('Disk limit [MB]'),
	'TR_PHP'				=> tr('PHP'),
	'TR_CGI'				=> tr('CGI'),
	'TR_SUPPORT'			=> tr('Ticket system enabled'),
	'TR_YES'				=> tr('yes'),
	'TR_NO'					=> tr('no'),
	'TR_RESELLER_IPS'		=> tr('Reseller IPs'),
	'TR_IP_ASSIGN'			=> tr('Assign'),
	'TR_IP_NUMBER'			=> tr('Number'),
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
	'TR_SEND_DATA'			=> tr('Send new login data'),
	'TR_SUBMIT'				=> tr('Send'),
	'TR_DISABLED_MSG'		=> tr('-1 disabled'),
	'TR_UNLIMITED_MSG'		=> tr('0 unlimited'),

	'GENPAS'			=> array_key_exists('genpass', $_POST) ? passgen() : '',
	'USERNAME'			=> $user->admin_name,
	'EMAIL'				=> array_key_exists('email', $_POST) ? $_POST['email'] : $user->email,

	'MAX_USR_COUNT'		=> array_key_exists('max_usr', $_POST) ? $_POST['max_usr'] : $user->max_usr,
	'MAX_ALS_COUNT'		=> array_key_exists('max_als', $_POST) ? $_POST['max_als'] : $user->max_als,
	'MAX_SUB_COUNT'		=> array_key_exists('max_sub', $_POST) ? $_POST['max_sub'] : $user->max_sub,
	'MAX_MAIL_COUNT'	=> array_key_exists('max_mail', $_POST) ? $_POST['max_mail'] : $user->max_mail,
	'MAX_FTP_COUNT'		=> array_key_exists('max_ftp', $_POST) ? $_POST['max_ftp'] : $user->max_ftp,
	'MAX_SQLDB_COUNT'	=> array_key_exists('max_sqldb', $_POST) ? $_POST['max_sqldb'] : $user->max_sqldb,
	'MAX_SQLU_COUNT'	=> array_key_exists('max_sqlu', $_POST) ? $_POST['max_sqlu'] : $user->max_sqlu,
	'MAX_TRAFF_COUNT'	=> array_key_exists('max_traffic', $_POST) ? $_POST['max_traffic'] : $user->max_traff,
	'MAX_DISK_AMOUNT'	=> array_key_exists('max_disk', $_POST) ? $_POST['max_disk'] : $user->max_disk,
	'PHP_YES'			=> array_key_exists('php', $_POST) ? $_POST['php'] == 'yes' ? 'checked' : '' : $user->php == 'yes' ? 'checked' : '',
	'PHP_NO'			=> array_key_exists('php', $_POST) ? $_POST['php'] != 'yes' ? 'checked' : '' : $user->php != 'yes' ? 'checked' : '',
	'CGI_YES'			=> array_key_exists('cgi', $_POST) ? $_POST['cgi'] == 'yes' ? 'checked' : '' : $user->cgi == 'yes' ? 'checked' : '',
	'CGI_NO'			=> array_key_exists('cgi', $_POST) ? $_POST['cgi'] != 'yes' ? 'checked' : '' : $user->cgi != 'yes' ? 'checked' : '',
	'SUPPORT_YES'		=> array_key_exists('support', $_POST) ? $_POST['support'] == 'yes' ? 'checked' : '' : $user->support == 'yes' ? 'checked' : '',
	'SUPPORT_NO'		=> array_key_exists('support', $_POST) ? $_POST['support'] != 'yes' ? 'checked' : '' : $user->support != 'yes' ? 'checked' : '',

	'FIRST_NAME'	=> array_key_exists('fname', $_POST) ? $_POST['fname'] : $user->fname,
	'LAST_NAME'		=> array_key_exists('lname', $_POST) ? $_POST['lname'] : $user->lname,
	'VL_MALE'		=> array_key_exists('gender', $_POST) && $_POST['gender'] == 'M' ? 'selected' : ($user->gender == 'M') ? 'selected' : '',
	'VL_FEMALE'		=> array_key_exists('gender', $_POST) && $_POST['gender'] == 'F' ? 'selected' : ($user->gender == 'F') ? 'selected' : '',
	'VL_UNKNOWN'	=> array_key_exists('gender', $_POST) && !in_array($_POST['gender'], array('M', 'F')) ? 'selected' : !in_array($user->gender, array('M', 'F')) ? 'selected' : '',
	'FIRM'			=> array_key_exists('firm', $_POST) ? $_POST['firm'] : $user->firm,
	'ZIP'			=> array_key_exists('zip', $_POST) ? $_POST['zip'] : $user->zip,
	'CITY'			=> array_key_exists('city', $_POST) ? $_POST['city'] : $user->city,
	'STATE'			=> array_key_exists('state', $_POST) ? $_POST['state'] : $user->state,
	'COUNTRY'		=> array_key_exists('country', $_POST) ? $_POST['country'] : $user->country,
	'STREET_1'		=> array_key_exists('street1', $_POST) ? $_POST['street1'] : $user->street1,
	'STREET_2'		=> array_key_exists('street2', $_POST) ? $_POST['street2'] : $user->street2,
	'PHONE'			=> array_key_exists('phone', $_POST) ? $_POST['phone'] : $user->phone,
	'FAX'			=> array_key_exists('fax', $_POST) ? $_POST['fax'] : $user->fax,
	'SEND_DATA_CHK'	=> array_key_exists('send_data', $_POST) ? 'checked' : ''
));

$tpl->flushOutput('admin/reseller_add');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

