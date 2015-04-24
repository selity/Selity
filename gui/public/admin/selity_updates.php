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
$sql = mysql::getInstance();

$theme_color = $cfg->USER_INITIAL_THEME;

if(isset($_GET['db_update'])) {
	databaseUpdate::getInstance()->executeUpdates();
	if(databaseUpdate::getInstance()->getErrorMessage()){
		$tpl->addMessage(databaseUpdate::getInstance()->getErrorMessage());
	}
}

if(!$cfg->CHECK_FOR_UPDATES){
	$tpl->saveVariable(array(
		'VERSION_MESSAGE'	=> tr('Update checking is disabled!'),
		'VERSION_INFOS' 	=> tr('Enable update at') . ' <a href="settings.php">' . tr('Settings') . '</a>'
	));
}elseif(versionUpdate::getInstance()->checkUpdateExists()){
	$tpl->saveVariable(array(
		'VERSION_MESSAGE'	=> tr('New selity update is now available'),
		'VERSION_INFOS'		=> tr('Get it at') . ' <a href="http://www.selity.org/download.html" class="link" target="_blank">http://www.selity.org/download.html</a>'
	));
} elseif(versionUpdate::getInstance()->getErrorMessage()) {
	$tpl->saveVariable(array(
		'VERSION_MESSAGE'	=>  tr('Error reading version'),
		'VERSION_INFOS'		=>  versionUpdate::getInstance()->getErrorMessage(),
	));
} else{
	$tpl->saveVariable(array(
		'VERSION_MESSAGE'	=>  tr('No new Selity updates available')
	));
}

if(databaseUpdate::getInstance()->checkUpdateExists()) {
	$tpl->saveVariable(array(
		'DB_MESSAGE'			=> tr('New Database update is now available'),
		'DB_INFOS'				=> '<a href="?db_update">' . tr('Execute the updates now') . '</a>'
	));
} else {
	$tpl->saveVariable(array(
		'DB_MESSAGE'	=> tr('No database updates available'),
		'DB_INFOS'		=> ''
	));
}

$tpl->saveVariable(array(
	'TR_PAGE_TITLE'		=> tr('Selity - Updates'),
	'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
	'THEME_CHARSET'		=> tr('encoding'),
	'TR_VERSION_TITLE'	=> tr('Selity updates'),
	'TR_DB_TITLE'		=> tr('Available database updates'),
));

genMainMenu();
genAdminToolsMenu();

$tpl->flushOutput('admin/selity_updates');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
