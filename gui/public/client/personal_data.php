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

function updData($user) {
	$user->fname	= clean_input($_POST['fname']);
	$user->lname	= clean_input($_POST['lname']);
	$user->gender	= clean_input($_POST['gender']);
	$user->firm		= clean_input($_POST['firm']);
	$user->zip		= clean_input($_POST['zip']);
	$user->city		= clean_input($_POST['city']);
	$user->state	= clean_input($_POST['state']);
	$user->country	= clean_input($_POST['country']);
	$user->street1	= clean_input($_POST['street1']);
	$user->street2	= clean_input($_POST['street2']);
	$user->email	= clean_input($_POST['email']);
	$user->phone	= clean_input($_POST['phone']);
	$user->fax		= clean_input($_POST['fax']);
	if($user->save()){
		template::getInstance()->addMessage(tr('Personal data updated successfully!'));
	} else{
		template::getInstance()->addMessage($user->getMessage());
	}
}

$cfg = configs::getInstance();
$tpl = template::getInstance();
$theme_color = $cfg->USER_INITIAL_THEME;

try{
	$user = new selity_user($_SESSION['user_id']);
} catch(Exception $e){
	template::getInstance()->addMessage(tr('Invalid user data!'));
	header('Location: index.php');
	die();
}

if (array_key_exists('Submit', $_POST)) {
	updData($user);
}

genMainMenu();
genGeneralMenu();

//gen_logged_from($tpl);

$tpl->saveVariable(array(
	'ADMIN_TYPE'				=> $_SESSION['user_type'],
	'TR_PAGE_TITLE'				=> tr('Selity - Change Personal Data'),
	'THEME_COLOR_PATH'			=> '../themes/'.$theme_color,
	'THEME_CHARSET'				=> tr('encoding'),
	'TR_CHANGE_PERSONAL_DATA'	=> tr('Change personal data'),
	'TR_PERSONAL_DATA'			=> tr('Personal data'),
	'TR_FIRST_NAME'				=> tr('First name'),
	'TR_LAST_NAME'				=> tr('Last name'),
	'TR_COMPANY'				=> tr('Company'),
	'TR_ZIP_POSTAL_CODE'		=> tr('Zip/Postal code'),
	'TR_CITY'					=> tr('City'),
	'TR_STATE'					=> tr('State'),
	'TR_COUNTRY'				=> tr('Country'),
	'TR_STREET_1'				=> tr('Street 1'),
	'TR_STREET_2'				=> tr('Street 2'),
	'TR_EMAIL'					=> tr('Email'),
	'TR_PHONE'					=> tr('Phone'),
	'TR_FAX'					=> tr('Fax'),
	'TR_GENDER'					=> tr('Gender'),
	'TR_MALE'					=> tr('Male'),
	'TR_FEMALE'					=> tr('Female'),
	'TR_UNKNOWN'				=> tr('Unknown'),
	'TR_UPDATE_DATA'			=> tr('Update data'),

	'FIRST_NAME'	=> $user->fname,
	'LAST_NAME'		=> $user->lname,
	'FIRM'			=> $user->firm,
	'ZIP'			=> $user->zip,
	'CITY'			=> $user->city,
	'STATE'			=> $user->state,
	'COUNTRY'		=> $user->country,
	'STREET_1'		=> $user->street1,
	'STREET_2'		=> $user->street2,
	'EMAIL'			=> $user->email,
	'PHONE'			=> $user->phone,
	'FAX'			=> $user->fax,
	'VL_MALE'		=> $user->gender == 'M' ? 'selected' : '',
	'VL_FEMALE'		=> $user->gender == 'F' ? 'selected' : '',
	'VL_UNKNOWN'	=> in_array($user->gender, array('M', 'F')) ? '' : 'selected'
));

$tpl->flushOutput('common/personal_data');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

