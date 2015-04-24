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
$theme_color = $cfg->USER_INITIAL_THEME;



function addAdmin($user) {

	$sql = mysql::getInstance();
	$tpl = template::getInstance();
	$cfg = configs::getInstance();

	if($user->admin_id){
		$tpl->saveSection('EDIT');
		$isNew = false;
	} else {
		$isNew = true;
	}

	if (array_key_exists('submit', $_POST)) {

		if($_POST['pass'] != $_POST['pass_rep']){
			$tpl->addMessage(tr('Password do not match!'));
			$_POST['pass'] = '';
		}

		if($_POST['pass'] != ''){
			$user->admin_pass	= clean_input($_POST['pass']);
		}

		if($isNew) $user->created_by	= $_SESSION['user_id'];
		if($isNew) $user->created_on	= time();

		$user->email		= clean_input($_POST['email']);

		$user->fname	= clean_input($_POST['fname']);
		$user->lname	= clean_input($_POST['lname']);
		$user->gender	= clean_input($_POST['gender']);
		$user->firm	= clean_input($_POST['firm']);
		$user->zip		= clean_input($_POST['zip']);
		$user->city	= clean_input($_POST['city']);
		$user->state	= clean_input($_POST['state']);
		$user->country	= clean_input($_POST['country']);
		$user->phone	= clean_input($_POST['phone']);
		$user->fax		= clean_input($_POST['fax']);
		$user->street1	= clean_input($_POST['street1']);
		$user->street2	= clean_input($_POST['street2']);

		if($isNew) $user->lang		= $cfg->USER_INITIAL_LANG;
		if($isNew) $user->layout	= $cfg->USER_INITIAL_THEME;

		if($isNew){
			$logMess	= '%s: added administrator: %s (%s)!';
			$showMess	= tr('Administrator added');
		} else {
			$logMess	= '%s: changes data/password for administrator: %s (%s)!';
			$showMess	= tr('Administrator saved');
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
						tr('Administrator')
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
		$user = new selity_admin($id);
	} catch (Exception $e) {
		$tpl->addMessage(tr('User not found!'));
		header('Location: users_show.php');
		die();
	}
	if(array_key_exists('op', $_GET) && $_GET['op'] == 'delete'){
		if($user->delete()){
			$tpl->addMessage(tr('User deleted!'));
		}else{
			$tpl->addMessage(tr('User not deleted!'));
		}
		header('Location: users_show.php');
		die();
	}
} else {
	$user = new selity_admin();
}

genMainMenu();
genAdminUsersMenu();

addAdmin($user);

$tpl->saveVariable(array(
	'TR_PAGE_TITLE'			=> tr('Selity - Add administrator'),
	'THEME_COLOR_PATH'		=> '../themes/'.$theme_color,
	//'THEME_CHARSET'		=> tr('encoding'),
	'TR_ADD_ADMIN'			=> tr('Add admin'),
	'TR_CORE_DATA'			=> tr('Core data'),
	'TR_USERNAME'			=> tr('User name'),
	'TR_EMAIL'				=> tr('Email'),
	'TR_PASSWORD'			=> tr('Password'),
	'TR_PASSWORD_REPEAT'	=> tr('Repeat password'),
	'TR_GENPAS'				=> tr('Generate password'),

	'TR_PERSONAL_DATA'	=> tr('Personal data'),
	'TR_FIRST_NAME'		=> tr('First name'),
	'TR_LAST_NAME'		=> tr('Last name'),
	'TR_GENDER'			=> tr('Gender'),
	'TR_MALE'			=> tr('Male'),
	'TR_FEMALE'			=> tr('Female'),
	'TR_UNKNOWN'		=> tr('Unknown'),
	'TR_COMPANY'		=> tr('Company'),
	'TR_ZIP'			=> tr('Zip/Postal code'),
	'TR_CITY'			=> tr('City'),
	'TR_STATE'			=> tr('State'),
	'TR_COUNTRY'		=> tr('Country'),
	'TR_STREET_1'		=> tr('Street 1'),
	'TR_STREET_2'		=> tr('Street 2'),
	'TR_PHONE'			=> tr('Phone'),
	'TR_FAX'			=> tr('Fax'),
	'TR_PHONE'			=> tr('Phone'),
	'TR_SEND_DATA'			=> tr('Send new login data'),
	'TR_SUBMIT'			=> tr('Add'),

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
	'SEND_DATA_CHK'		=> array_key_exists('send_data', $_POST) ? 'checked' : ''
));


$tpl->flushOutput('admin/admin_add');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
