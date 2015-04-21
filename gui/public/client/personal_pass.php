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

$cfg = configs::getInstance();
$tpl = template::getInstance();
$theme_color = $cfg->USER_INITIAL_THEME;


function update_password($user) {
	$tpl = template::getInstance();
	$cfg = configs::getInstance();
	$sql = mysql::getInstance();

	if (array_key_exists('Submit', $_POST)) {
		if (empty($_POST['pass']) || empty($_POST['pass_rep']) || empty($_POST['curr_pass'])) {
			$tpl->addMessage(tr('Please fill up all data fields!'));
		} else if (!chk_password($_POST['pass'])) {
			if($cfg->PASSWD_STRONG){
				$tpl->addMessage(tr('The password must be at least %s long and contain letters and numbers to be valid.', $cfg->PASSWD_CHARS));
			} else {
				$tpl->addMessage(tr('Password data is shorter than %s signs or includes not permitted signs!', $cfg->PASSWD_CHARS));
			}
		} else if ($_POST['pass'] !== $_POST['pass_rep']) {
			$tpl->addMessage(tr('Passwords do not match!'));
		} else if ((crypt($_POST['curr_pass'], $user->admin_pass) == $user->admin_pass) || (md5($_POST['curr_pass']) == $user->admin_pass) === false) {
			$tpl->addMessage(tr('The current password is wrong!'));
		} else {
			$upass = crypt_user_pass($_POST['pass']);
			$_SESSION['user_pass'] = $upass;
			$user->admin_pass = $upass;
			$user->save();
			$tpl->addMessage(tr('User password updated successfully!'));
		}
	}
}

genMainMenu();
genGeneralMenu();

//gen_logged_from($tpl);

try{
	$user = new selity_user($_SESSION['user_id']);
} catch(Exception $e){
	template::getInstance()->addMessage(tr('Invalid user data!'));
	header('Location: index.php');
	die();
}

update_password($user);


$tpl->saveVariable(array(
	'ADMIN_TYPE'				=> $_SESSION['user_type'],
	'TR_PAGE_TITLE'			=> tr('Selity - Change Password'),
	'THEME_COLOR_PATH'		=> '../themes/'.$theme_color,
	'THEME_CHARSET'			=> tr('encoding'),
	'TR_CHANGE_PASSWORD'	=> tr('Change password'),
	'TR_PASSWORD_DATA'		=> tr('Password data'),
	'TR_PASSWORD'			=> tr('Password'),
	'TR_PASSWORD_REPEAT'	=> tr('Repeat password'),
	'TR_UPDATE_PASSWORD'	=> tr('Update password'),
	'TR_CURR_PASSWORD'		=> tr('Current password')
));

$tpl->flushOutput('common/personal_pass');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
