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



function addAdmin() {

	$sql = mysql::getInstance();
	$tpl = template::getInstance();
	$cfg = configs::getInstance();

	if (array_key_exists('submit', $_POST)) {

		$admin = new selity_admin();

		if($_POST['pass'] != $_POST['pass_rep']){
			$tpl->addMessage(tr('Password do not match!'));
			$_POST['pass'] = '';
		}

		$admin->admin_pass	= clean_input($_POST['pass']);
		$admin->created_by	= $_SESSION['user_id'];
		$admin->created_on	= time();
		$admin->email		= clean_input($_POST['email']);

		$admin->fname	= clean_input($_POST['fname']);
		$admin->lname	= clean_input($_POST['lname']);
		$admin->gender	= clean_input($_POST['gender']);
		$admin->firm	= clean_input($_POST['firm']);
		$admin->zip		= clean_input($_POST['zip']);
		$admin->city	= clean_input($_POST['city']);
		$admin->state	= clean_input($_POST['state']);
		$admin->country	= clean_input($_POST['country']);
		$admin->phone	= clean_input($_POST['phone']);
		$admin->fax		= clean_input($_POST['fax']);
		$admin->street1	= clean_input($_POST['street1']);
		$admin->street2	= clean_input($_POST['street2']);

		$admin->lang	= $cfg->USER_INITIAL_LANG;
		$admin->layout	= $cfg->USER_INITIAL_THEME;

		if($admin->save()){

			$user_logged = $_SESSION['user_logged'];

			write_log($_SESSION['user_logged'].': added administrator: '. $admin->admin_name.' ('.$admin->email.')');

			send_add_user_auto_msg ($user_id,
				$admin->admin_name,
				clean_input($_POST['pass']),
				clean_input($_POST['email']),
				clean_input($_POST['fname']),
				clean_input($_POST['lname']),
				tr('Administrator')
			);

			$tpl->addMessage(tr('Administrator added'));
			header('Location: users_show.php');
			die();
		} else {
			$tpl->addMessage($admin->getMessage());

		}
	}
}

genMainMenu();
genAdminUsersMenu();

addAdmin();

$tpl->saveVariable(array(
	'TR_PAGE_TITLE'		=> tr('Selity - Add administrator'),
	'THEME_COLOR_PATH'		=> '../themes/'.$theme_color,
	//'THEME_CHARSET'		=> tr('encoding'),
	'TR_ADD_ADMIN'			=> tr('Add admin'),
	'TR_CORE_DATA'			=> tr('Core data'),
	'TR_EMAIL'		=> tr('Email'),
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
	'TR_SUBMIT'			=> tr('Add'),
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
		'COUNTRY'		=> clean_input($_POST['country']),
		'STATE'			=> clean_input($_POST['state']),
		'STREET_1'		=> clean_input($_POST['street1']),
		'STREET_2'		=> clean_input($_POST['street2']),
		'PHONE'			=> clean_input($_POST['phone']),
		'FAX'			=> clean_input($_POST['fax']),
		'VL_MALE'		=> (($_POST['gender'] == 'M') ? 'selected' : ''),
		'VL_FEMALE'		=> (($_POST['gender'] == 'F') ? 'selected' : ''),
		'VL_UNKNOWN'	=> ((($_POST['gender'] == 'U') || (empty($_POST['gender']))) ? 'selected' : ''),
	));
}

$tpl->flushOutput('admin/admin_add');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

