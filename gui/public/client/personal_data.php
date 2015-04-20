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

function genData($admin) {

	$tpl = template::getInstance();

	$tpl->saveVariable(array(
		'FIRST_NAME'	=> is_null($admin->fname) ? '' : $admin->fname,
		'LAST_NAME'		=> is_null($admin->lname) ? '' : $admin->lname,
		'FIRM'			=> is_null($admin->firm) ? '' : $admin->firm,
		'ZIP'			=> is_null($admin->zip) ? '' : $admin->zip,
		'CITY'			=> is_null($admin->city) ? '' : $admin->city,
		'STATE'			=> is_null($admin->state) ? '' : $admin->state,
		'COUNTRY'		=> is_null($admin->country) ? '' : $admin->country,
		'STREET_1'		=> is_null($admin->street1) ? '' : $admin->street1,
		'STREET_2'		=> is_null($admin->street2) ? '' : $admin->street2,
		'EMAIL'			=> is_null($admin->email) ? '' : $admin->email,
		'PHONE'			=> is_null($admin->phone) ? '' : $admin->phone,
		'FAX'			=> is_null($admin->fax) ? '' : $admin->fax,
		'VL_MALE'		=> (($admin->gender == 'M') ? 'selected' : ''),
		'VL_FEMALE'		=> (($admin->gender == 'F') ? 'selected' : ''),
		'VL_UNKNOWN'	=> ((($admin->gender == 'U') || (is_null($admin->gender))) ? 'selected' : ''),
	));
}

function updData($admin) {
	$admin->fname	= clean_input($_POST['fname']);
	$admin->lname	= clean_input($_POST['lname']);
	$admin->gender	= clean_input($_POST['gender']);
	$admin->firm	= clean_input($_POST['firm']);
	$admin->zip		= clean_input($_POST['zip']);
	$admin->city	= clean_input($_POST['city']);
	$admin->state	= clean_input($_POST['state']);
	$admin->country	= clean_input($_POST['country']);
	$admin->street1	= clean_input($_POST['street1']);
	$admin->street2	= clean_input($_POST['street2']);
	$admin->email	= clean_input($_POST['email']);
	$admin->phone	= clean_input($_POST['phone']);
	$admin->fax		= clean_input($_POST['fax']);
	$admin->save();
	template::getInstance()->addMessage(tr('Personal data updated successfully!'));
}


$cfg = configs::getInstance();
$tpl = template::getInstance();
$theme_color = $cfg->USER_INITIAL_THEME;
$admin = new selity_user($_SESSION['user_id']);

if (array_key_exists('Submit', $_POST)) {
	updData($admin);
}

genData($admin);

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
));

$tpl->flushOutput('common/personal_data');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();


