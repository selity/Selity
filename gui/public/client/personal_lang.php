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

function genList(){
	$tpl = template::getInstance();
	$cfg = configs::getInstance();
	$lng = selity_language::getInstance();
	$languageList = $lng->getDisponibleLanguages();
	$languageDir = $cfg->GUI_ROOT_DIR.'/i18n/locales';
	$list = array();
	$old_language = $_SESSION['user_def_lang'];
	foreach($languageList as $lang){
		$lng->setLanguage($lang);
		$list[] = array('LANGUAGE' => $lang, 'TR_LANGUAGE'=>tr('Localised language'), 'TR_SELECTED' => $_SESSION['user_def_lang'] == $lang ? 'selected' : '');
	}
	$lng->setLanguage($old_language);
	$tpl->saveRepeats(array('LANGUAGE'=>$list));
}


if (array_key_exists('Submit', $_POST)) {

	$admin = new selity_user($_SESSION['user_id']);
	$admin->lang = $_POST['language'];
	$admin->save();
	$_SESSION['user_def_lang'] = $_POST['language'];
	$tpl->addMessage(tr('User language updated successfully!'));
}

if (!isset($_SESSION['logged_from']) && !isset($_SESSION['logged_from_id'])) {
		list($user_def_lang, $user_def_layout) = get_user_gui_props($sql, $_SESSION['user_id']);
} else {
		$user_def_layout = $_SESSION['user_theme'];
		$user_def_lang = $_SESSION['user_def_lang'];
}

genList();


genMainMenu();
genGeneralMenu();

//gen_logged_from($tpl);

$tpl->saveVariable(array(
	'ADMIN_TYPE'			=> $_SESSION['user_type'],
	'TR_PAGE_TITLE'			=> tr('Selity - Change Language'),
	'THEME_COLOR_PATH'		=> '../themes/'.$theme_color,
	'THEME_CHARSET'			=> tr('encoding'),
	'TR_CHANGE_LANGUAGE'	=> tr('Change language'),
	'TR_LANGUAGES'			=> tr('Choose your language'),
	'TR_UPDATE'				=> tr('Update'),
));

$tpl->flushOutput('common/personal_lang');


if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

