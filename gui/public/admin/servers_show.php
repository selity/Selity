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

function genServerList(){
	$tpl = template::getInstance();
	$sql = mysql::getInstance();
	$query = 'SELECT * FROM `servers`';
	$rs = $sql->doQuery($query);
	$admins = array();
	while (!$rs->EOF){
		switch($rs->server_status){
			case OK_STATUS:
				$status = tr('Ok');
				$status_action = '#';
				break;
			case DISABLED_STATUS:
				$status = tr('Disabled');
				$status_action = '#';
				break;
			case ADD_STATUS:
			case RESTORE_STATUS:
			case CHANGE_STATUS:
			case TODISABLE_STATUS:
			case DELETE_STATUS:
			case TOENABLE_STATUS:
			case NEW_STATUS:
				$status = tr('Changing');
				$status_action = '';
				break;
			default:
				$status = tr('Error');
				$status_action = '#';
		}

		$online = @fsockopen($rs->server_ip, $rs->server_ssh_port,  $ern, $ers, SERVER_TIMEOUT);
		$color = $online ? '#090' : '#900';

		$admins[] = array(
			'STATUS_URL'		=> $status_action,
			'STATUS'			=> $status,
			'NAME'				=> sprintf('%s (%s)', $rs->server_name, $rs->server_ip),
			'SERVER_ID'			=> $rs->server_id,
			'ONLINE'			=> $online ? tr('Online') : tr('Offline'),
			'COLOR'				=> $color,
		);
		$rs->nextRow();
	}
	$tpl->saveRepeats(array('SERVER_LIST' => $admins));
}



genMainMenu();
genAdminServerMenu();

genServerList();

$tpl->saveSection('ADMIN');
$tpl->saveVariable(array(
	'TR_PAGE_TITLE'		=> tr('Selity - Admin/Manage Servers'),
	'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
	//'THEME_CHARSET'	=> tr('encoding'),
	'TR_STATUS'			=> tr('Status'),
	'TR_SERVER_NAME'		=> tr('Server name'),
	'TR_ONLINE'			=> tr('Online'),
	'TR_ACTION'			=> tr('Options'),
	//'TR_DELETE'			=> tr('Delete'),
	//'TR_MESSAGE_DELETE'	=> tr('Are you sure you want to delete %s?', '%s'),
	'TR_EDIT'			=> tr('Edit'),
	'TR_DETAILS'		=>  tr('Details'),
));



$tpl->flushOutput('admin/servers_show');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
