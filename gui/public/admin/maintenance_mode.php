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


if (isset($_POST['submit'])) {
	$cfg->MAINTENANCEMODE = (int) $_POST['mMode'];
	$cfg->MAINTENANCEMODE_MESSAGE = clean_input($_POST['mModeMsg']);
	$cfg->save('MAINTENANCEMODE');
	$cfg->save('MAINTENANCEMODE_MESSAGE');
	$tpl->addMessage(tr('Settings saved!'));
}

$tpl->saveVariable(array(
	'TR_PAGE_TITLE'			=> tr('Selity - Admin/Maintenance mode'),
	'THEME_COLOR_PATH'		=> '../themes/'.$theme_color,
	'THEME_CHARSET'			=> tr('encoding'),
	'TR_MAINTENANCEMODE'	=> tr('Maintenance mode'),
	'TR_MESSAGE_INFO'		=> tr('Under this mode only administrators can login'),
	'TR_MESSAGE'			=> tr('Message'),
	'MESSAGE_VALUE'			=> $cfg->MAINTENANCEMODE_MESSAGE,
	'SELECTED_ON'			=> $cfg->MAINTENANCEMODE ? 'selected' : '',
	'SELECTED_OFF'			=> $cfg->MAINTENANCEMODE ? '' : 'selected',
	'TR_ENABLED'			=> tr('Enabled'),
	'TR_DISABLED'			=> tr('Disabled'),
	'TR_APPLY_CHANGES'		=> tr('Apply changes')
));

genMainMenu();
genAdminToolsMenu();

$tpl->flushOutput('admin/maintenance_mode');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
