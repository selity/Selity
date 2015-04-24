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

$cfg = configs::getInstance();
$tpl = template::getInstance();

$theme_color = $cfg->USER_INITIAL_THEME;

function install_lang() {

	$tpl = template::getInstance();

	if (array_key_exists('Submit', $_POST)) {
		$file_type		= $_FILES['lang_file']['type'];
		$file_tmpname	= $_FILES['lang_file']['tmp_name'];
		$file_name		= $_FILES['lang_file']['name'];

		if (empty($_FILES['lang_file']['name']) || !file_exists($file_tmpname) || !is_readable($file_tmpname)) {
			$tpl->addMessage(tr('Upload file error!'));
			return;
		}

		if (!preg_match('/[a-z]{2}_[A-Z]{2}\.mo/', $file_name)) {
			$tpl->addMessage(tr('Invalid file name %s!', $file_name));
			return;
		}

		if ($file_type !== 'text/plain' && $file_type !== 'application/octet-stream') {
			$tpl->addMessage(tr('You can upload only mo files!'));
			return;
		} else {

			$dir = configs::getInstance()->GUI_ROOT_DIR.'/i18n/locales/'.str_replace('.mo', '', $file_name).'/LC_MESSAGES/';
			if(file_exists($dir)){
				$lang_update = true;
			} else {
				if (!mkdir($dir, 0750, true)) {
					$tpl->addMessage(tr('Failed to create %s folder', $dir));
					return;
				}
				$lang_update = false;
			}

			if (!move_uploaded_file($file_tmpname, $dir.$file_name)) {
				$tpl->addMessage(tr('Could not read language file!'));
				return;
			}

			if (!$lang_update) {
				write_log(sprintf('%s added new language: %s', $_SESSION['user_logged'], $file_name));
				$tpl->addMessage(tr('New language installed!'));
			} else {
				write_log(sprintf('%s updated language: %s', $_SESSION['user_logged'], $file_name));
				$tpl->addMessage(tr('Language was updated!'));
			}
		}
	}
}

function show_lang() {

	$tpl = template::getInstance();
	$cfg = configs::getInstance();
	$lng = selity_language::getInstance();

	$languageList = $lng->getDisponibleLanguages();
	$old_language = $_SESSION['user_def_lang'];
	$list = array();
	asort($languageList, SORT_STRING);
	foreach ($languageList as $lang) {
		$default_language = $cfg->USER_INITIAL_LANG;
		$lng->setLanguage($lang);
		$lname = gettext('Localised language');
		$list[] = array(
			'LANG_VALUE'	=> $lang,
			'LANG_NAME'		=> $lname == 'Localised language' ? tr('Unknown') : $lname,
			'TR_CHECKED'	=> $lang == $default_language ? 'checked' : '',
			'URL_DELETE'	=> 'multilanguage_delete.php?delete_lang=' . $lang,
			'URL_EXPORT'	=> 'multilanguage_export.php?export_lang=' . $lang,
		);
	}

	$tpl->saveRepeats(array('LANGUAGE'=>$list));
	$lng->setLanguage($old_language);
}

install_lang();
show_lang();

$tpl->saveVariable(array(
	'TR_PAGE_TITLE'				=> tr('Selity - Admin/Internationalisation'),
	'THEME_COLOR_PATH'			=> '../themes/'.$theme_color,
	//'THEME_CHARSET'				=> tr('encoding'),
	'TR_MULTILANGUAGE'			=> tr('Internationalisation'),
	'TR_INSTALLED_LANGUAGES'	=> tr('Installed languages'),
	'TR_LANGUAGE'				=> tr('Language'),
	'TR_MESSAGES'				=> tr('Messages'),
	'TR_DEFAULT'				=> tr('Default'),
	'TR_ACTION'					=> tr('Action'),
	'TR_SAVE'					=> tr('Save'),
	'TR_INSTALL_NEW_LANGUAGE'	=> tr('Install new language'),
	'TR_LANGUAGE_FILE'			=> tr('Language file'),
	'TR_INSTALL'				=> tr('Install'),
	'TR_UNINSTALL'				=> tr('Uninstall'),
	'TR_EXPORT'					=> tr('Export'),
	'TR_MESSAGE_DELETE'			=> tr('Are you sure you want to delete %s?', '%s'),
));

genAdminMainMenu();
genAdminSettingsMenu();

$tpl->flushOutput('admin/multilanguage');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
