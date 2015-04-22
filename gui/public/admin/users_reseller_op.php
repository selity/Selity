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


function getServerList($user) {
	$sql = mysql::getInstance();
	$tpl = template::getInstance();
	$query = 'SELECT * FROM `servers`';
	$rs = $sql->doQuery($query);
	$servers = array();
	if ($rs->countRows() == 0) {
		$tpl->addMessage(tr('Server list is empty! You must add at lest one!'));
	} else {
		while (!$rs->EOF) {
			$checked = in_array($rs->server_id, $_SESSION['serverLST']) ? 'checked' : '';
			$servers[] = array(
				'SERVER_NAME'		=> $rs->server_name,
				'SERVER_ID'			=> $rs->server_id,
				'SERVER_CHK'		=> $checked
			);
			$rs->nextRow();
		}
	}
	$tpl->saveRepeats(array('SERVER' => $servers));
}

function getIp($user, $serverList) {
	$sql = mysql::getInstance();
	$tpl = template::getInstance();
	$ips = array();
	if(is_array($serverList) && $serverList != array()){
		$inQuery = implode(',', array_fill(0, count($serverList), '?'));
		$query = 'SELECT * FROM `server_ips` WHERE `server_id` IN ('.$inQuery.') ORDER BY `ip_number`';
		$rs = $sql->doQuery($query, $serverList);
		if ($rs->countRows() == 0) {
			$tpl->addMessage(tr('IP list is empty! You must add some ip before!'));
		} else {
			while (!$rs->EOF) {
				$checked = array_key_exists('ip', $_POST) && is_array($_POST['ip']) && in_array($rs->ip_id, $_POST['ip']) ? 'checked' : in_array($rs->ip_id, $user->reseller_ips) ? 'checked' : '';
				$ips[] = array(
					'IP_NUMBER'		=> $rs->ip_number,
					'IP_ID'			=> $rs->ip_id,
					'IP_CHK'		=> $checked
				);
				$rs->nextRow();
			}
		}
	}
	$tpl->saveRepeats(array('IPS' => $ips));
}

function addReseller($user) {

	$sql = mysql::getInstance();
	$tpl = template::getInstance();
	$cfg = configs::getInstance();

	if($user->admin_id){
		$tpl->saveSection('EDIT');
	}

	if (array_key_exists('submit', $_POST)) {

		if($_POST['pass'] != $_POST['pass_rep']){
			$tpl->addMessage(tr('Password do not match!'));
			$_POST['pass'] = '';
		}

		if($_POST['pass'] != ''){
			$user->admin_pass	= clean_input($_POST['pass']);
		}
		$user->created_by	= $_SESSION['user_id'];
		$user->created_on	= time();
		$user->email		= clean_input($_POST['email']);

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

		$user->lang		= $cfg->USER_INITIAL_LANG;
		$user->layout	= $cfg->USER_INITIAL_THEME;

		$user->max_usr		= clean_input($_POST['max_usr']);
		$user->max_dmn		= clean_input($_POST['max_als']);
		$user->max_sub		= clean_input($_POST['max_sub']);
		$user->max_mail		= clean_input($_POST['max_mail']);
		$user->max_ftp		= clean_input($_POST['max_ftp']);
		$user->max_mysqld	= clean_input($_POST['max_sqldb']);
		$user->max_mysqlu	= clean_input($_POST['max_sqlu']);
		$user->max_traff	= clean_input($_POST['max_traffic']);
		$user->max_disk		= clean_input($_POST['max_disk']);
		$user->php			= clean_input(isset($_POST['php']) ? $_POST['php'] : '');
		$user->cgi			= clean_input(isset($_POST['cgi']) ? $_POST['cgi'] : '');
		$user->support		= clean_input(isset($_POST['support']) ? $_POST['support'] : '');

		$newServers = array_key_exists('serverLST', $_POST) && is_array($_POST['serverLST']) ? $_POST['serverLST'] : array();
		$toAdd = array_diff($newServers, $user->server_ids);
		foreach($toAdd as $server){
			$user->addServer($server);
		}
		$toRemove = array_diff($user->server_ids, $newServers);
		foreach($toRemove as $server){
			$user->removeServer($server);
		}


		$newIps = array_key_exists('ip', $_POST) && is_array($_POST['ip']) ? $_POST['ip'] : array();
		$toAdd = array_diff($newIps, $user->reseller_ips);
		foreach($toAdd as $ip){
			$user->addIP($ip);
		}
		$toRemove = array_diff($user->reseller_ips, $newIps);
		foreach($toRemove as $ip){
			$user->removeIP($ip);
		}


		if(!$user->admin_id){
			$logMess	= '%s: added reseller: %s (%s)!';
			$showMess	= tr('Reseller added');
			$isNew		= true;
		} else {
			$logMess	= '%s: changes data/password for reseller: %s (%s)!';
			$showMess	= tr('Reseller saved');
			$isNew		= false;
		}

		if($user->save()){
			write_log(sprintf(
				$logMess,
				$_SESSION['user_logged'],
				$user->admin_name,
				$user->email
			));
			if (!$isNew && !empty($_POST['pass'])) {
				$query = 'DELETE FROM `login` WHERE `user_name` = ?';
				$rs = mysql::getInstance()->doQuery($query,$user->admin_name);
				if ($rs->countRows() != 0) {
					$tpl->addMessage(tr('User session was killed!'));
					write_log(sprintf(
						'%s killed %s\'s session because of password change',
						$_SESSION['user_logged'],
						$user->admin_name
					));
				}
				if ($isNew || isset($_POST['send_data']) && clean_input($_POST['pass'])){
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

			$tpl->addMessage($showMess);
			header('Location: users_show.php');
			die();

		} else {
			$tpl->addMessage($user->getMessage());
		}
	}
}

if(array_key_exists('user_id', $_GET)){
$id = (int) $_GET['user_id'];
	try{
		$user = new selity_reseller($id);
	} catch (Exception $e) {
		$tpl->addMessage(tr('Reseller not found!'));
		header('Location: users_show.php');
		die();
	}
} else {
	$user = new selity_reseller();
}

genMainMenu();
genAdminUsersMenu();

if(!array_key_exists('serverLST', $_SESSION)){
	$_SESSION['serverLST'] = $user->server_ids;
}if(array_key_exists('select', $_POST)){
	$_SESSION['serverLST'] = array_key_exists('serverLST', $_POST) && is_array($_POST['serverLST']) ? $_POST['serverLST'] : array();
}
if($_SESSION['serverLST'] != array()){
	$tpl->saveSection('resellerData');
}

addReseller($user);

getServerList($user);
getIp($user, $_SESSION['serverLST']);


$tpl->saveVariable(array(
	'TR_PAGE_TITLE'				=> tr('Selity - Add reseller'),
	'THEME_COLOR_PATH'			=> '../themes/'.$theme_color,
	//'THEME_CHARSET'				=> tr('encoding'),
	'TR_SERVER_LIST'			=> tr('Servers list'),
	'TR_SELECT_SERVER'			=> tr('Select server'),
	'TR_SELECT'					=> tr('Select'),
	'TR_ADD_RESELLER'			=> tr('Add reseller'),
	'TR_CORE_DATA'				=> tr('Core data'),
	'TR_USERNAME'				=> tr('User name'),
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
	'TR_SEND_DATA'			=> tr('Send new login data'),
	'TR_SUBMIT'				=> tr('Add'),
	'TR_DISABLED_MSG'		=> tr('-1 disabled'),
	'TR_UNLIMITED_MSG'		=> tr('0 unlimited'),


	'USERNAME'		=> $user->admin_name,
	'EMAIL'			=> isset($_POST['email']) ? clean_input($_POST['email']) : $user->email,
	'GENPAS'		=> array_key_exists('genpass', $_POST) ? passgen() : '',
	'FIRST_NAME'	=> isset($_POST['fname']) ? clean_input($_POST['fname']) : $user->fname,
	'LAST_NAME'		=> isset($_POST['lname']) ? clean_input($_POST['lname']) : $user->lname,
	'FIRM'			=> isset($_POST['firm']) ? clean_input($_POST['firm']) : $user->firm,
	'ZIP'			=> isset($_POST['zip']) ? clean_input($_POST['zip']) : $user->zip,
	'CITY'			=> isset($_POST['city']) ? clean_input($_POST['city']) : $user->city,
	'STATE'			=> isset($_POST['state']) ? clean_input($_POST['state']) : $user->state,
	'COUNTRY'		=> isset($_POST['country']) ? clean_input($_POST['country']) : $user->country,
	'STREET_1'		=> isset($_POST['street1']) ? clean_input($_POST['street1']) : $user->street1,
	'STREET_2'		=> isset($_POST['street2']) ? clean_input($_POST['street2']) : $user->street2,
	'PHONE'			=> isset($_POST['phone']) ? clean_input($_POST['phone']) : $user->phone,
	'FAX'			=> isset($_POST['fax']) ? clean_input($_POST['fax']) : $user->fax,
	'VL_MALE'		=> isset($_POST['gender']) ? $_POST['gender'] == 'M' ? 'selected' : '' : $user->gender == 'M' ? 'selected' : '',
	'VL_FEMALE'		=> isset($_POST['gender']) ? $_POST['gender'] == 'F' ? 'selected' : '' : $user->gender == 'F' ? 'selected' : '',
	'VL_UNKNOWN'	=> isset($_POST['gender']) ? in_array($_POST['gender'], array('M', 'F'))  ? '' : 'selected' : in_array($user->gender, array('M', 'F'))  ? '' : 'selected',
	'MAX_USR_COUNT'		=> isset($_POST['max_usr']) ? clean_input($_POST['max_usr']) : $user->max_usr,
	'MAX_ALS_COUNT'		=> isset($_POST['max_als']) ? clean_input($_POST['max_als']) : $user->max_dmn,
	'MAX_SUB_COUNT'		=> isset($_POST['max_sub']) ? clean_input($_POST['max_sub']) : $user->max_sub,
	'MAX_MAIL_COUNT'	=> isset($_POST['max_mail']) ? clean_input($_POST['max_mail']) : $user->max_mail,
	'MAX_FTP_COUNT'		=> isset($_POST['max_ftp']) ? clean_input($_POST['max_ftp']) : $user->max_ftp,
	'MAX_SQLDB_COUNT'	=> isset($_POST['max_sqldb']) ? clean_input($_POST['max_sqldb']) : $user->max_mysqld,
	'MAX_SQLU_COUNT'	=> isset($_POST['max_sqlu']) ? clean_input($_POST['max_sqlu']) : $user->max_mysqlu,
	'MAX_TRAFF_COUNT'	=> isset($_POST['max_traffic']) ? clean_input($_POST['max_traffic']) : $user->max_traff,
	'MAX_DISK_AMOUNT'	=> isset($_POST['max_disk']) ? clean_input($_POST['max_disk']) : $user->max_disk,
	'PHP_YES'			=> isset($_POST['php']) ? $_POST['php'] == 'yes' ? 'checked' : '' : $user->php == 'yes' ? 'checked' : '' ,
	'PHP_NO'			=> isset($_POST['php']) ? $_POST['php'] != 'yes' ? 'checked' : '' : $user->php != 'yes' ? 'checked' : '' ,
	'CGI_YES'			=> isset($_POST['cgi']) ? $_POST['cgi'] == 'yes' ? 'checked' : '' : $user->cgi == 'yes' ? 'checked' : '' ,
	'CGI_NO'			=> isset($_POST['cgi']) ? $_POST['cgi'] != 'yes' ? 'checked' : '' : $user->cgi != 'yes' ? 'checked' : '' ,
	'SUPPORT_YES'		=> isset($_POST['support']) ? $_POST['support'] == 'yes' ? 'checked' : '' : $user->support == 'yes' ? 'checked' : '' ,
	'SUPPORT_NO'		=> isset($_POST['support']) ? $_POST['support'] != 'yes' ? 'checked' : '' : $user->support != 'yes' ? 'checked' : '' ,
	'SEND_DATA_CHK'		=> array_key_exists('send_data', $_POST) ? 'checked' : ''
));
//}

$tpl->flushOutput('admin/reseller_add');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

