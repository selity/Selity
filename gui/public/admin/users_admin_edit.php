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

function saveAdmin($user) {

	if (array_key_exists('submit', $_POST)) {
		if(!empty($_POST['pass'])){
			$user->admin_pass = clean_input($_POST['pass']);
		}
		$user->email	= clean_input($_POST['email']);

		$user->fname	= clean_input($_POST['fname']);
		$user->lname	= clean_input($_POST['lname']);
		$user->gender	= clean_input($_POST['gender']);
		$user->firm	= clean_input($_POST['firm']);
		$user->zip		= clean_input($_POST['zip']);
		$user->street1	= clean_input($_POST['street1']);
		$user->street2 = clean_input($_POST['street2']);
		$user->city	= clean_input($_POST['city']);
		$user->country	= clean_input($_POST['country']);
		$user->phone	= clean_input($_POST['phone']);
		$user->fax		= clean_input($_POST['fax']);

		if (check_user_data() && $user->save()) {
			write_log(sprintf(
				'%s: changes data/password for administrator: %s (%s)!',
				$_SESSION['user_logged'],
				$user->admin_name,
				$user->email
			));
			if (!empty($_POST['pass'])) {
				$query = 'DELETE FROM `login` WHERE user_name = ?';
				$rs = mysql::getInstance()->doQuery($query, $user->admin_name);
				if ($rs->countRows() != 0) {
					set_page_message(tr('User session was killed!'));
					write_log(sprintf(
						'%s killed %s\'s session because of password change',
						$_SESSION['user_logged'],
						$user->admin_name
					));
				}
				if (isset($_POST['send_data'])){
					send_add_user_auto_msg (
						$_SESSION['user_id'],
						$user->admin_name,
						clean_input($_POST['pass']),
						clean_input($_POST['email']),
						clean_input($_POST['fname']),
						clean_input($_POST['lname']),
						tr('Administrator')
					);
				}
			}

			template::getInstance()->addMessage(tr('Admin data updated'));
			header('Location: users_show.php');
			die();
		}
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
	$user = new selity_admin($id);
} catch (Exception $e) {
	$tpl->addMessage(tr('Invalid id!'));
	header('Location: users_show.php');
	die();
}

genMainMenu();
genAdminUsersMenu();

saveAdmin($user);

$tpl->saveSection('EDIT');
$tpl->saveVariable(array(
	'TR_PAGE_TITLE'		=> tr('Selity - Edit Administrator'),
	'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
	//'THEME_CHARSET'	=> tr('encoding'),
	'TR_ADD_ADMIN'		=> tr('Edit admin'),

	'TR_CORE_DATA'			=> tr('Core data'),
	'TR_USERNAME'			=> tr('Username'),
	'TR_PASSWORD'			=> tr('Password'),
	'TR_PASSWORD_REPEAT'	=> tr('Repeat password'),
	'TR_GENPAS'				=> tr('Generate password'),
	'TR_EMAIL'			=> tr('Email'),

	'TR_PERSONAL_DATA'	=> tr('Personal data'),
	'TR_FIRST_NAME'		=> tr('First name'),
	'TR_LAST_NAME'		=> tr('Last name'),
	'TR_COMPANY'		=> tr('Company'),
	'TR_ZIP'			=> tr('Zip/Postal code'),
	'TR_CITY'			=> tr('City'),
	'TR_STATE'			=> tr('State'),
	'TR_COUNTRY'		=> tr('Country'),
	'TR_STREET_1'		=> tr('Street 1'),
	'TR_STREET_2'		=> tr('Street 2'),
	'TR_PHONE'			=> tr('Phone'),
	'TR_FAX'			=> tr('Fax'),
	'TR_GENDER'			=> tr('Gender'),
	'TR_MALE'			=> tr('Male'),
	'TR_FEMALE'			=> tr('Female'),
	'TR_UNKNOWN'		=> tr('Unknown'),
	'TR_SEND_DATA'		=> tr('Send new login data'),
	'TR_SUBMIT'			=> tr('Send'),

	'GENPAS'		=> array_key_exists('genpass', $_POST) ? passgen() : '',
	'USERNAME'		=> $user->admin_name,
	'EMAIL'			=> array_key_exists('email', $_POST) ? $_POST['email'] : $user->email,

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

$tpl->flushOutput('admin/admin_add');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

