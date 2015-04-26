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

function showIPs() {
	$tpl = template::getInstance();
	$sql = mysql::getInstance();

	$query = 'SELECT * FROM `server_ips` LEFT JOIN `servers` ON `server_ips`.`server_id` = `servers`.`server_id`';

	$rs = $sql->doQuery($query);

	$list = array();

	while (!$rs->EOF) {
		$list[] = array(
			'IP' => $rs->ip_number,
			'LABEL' => $rs->ip_label,
			'SERVER' => $rs->server_name,
			'ID' => $rs->ip_id,
		);
		$rs->nextRow();
	}
	$tpl->saveRepeats(array('IPS' => $list));
}

showIPs();

$tpl->saveVariable(array(
	'TR_PAGE_TITLE'		=> tr('Selity - Admin/IP manage'),
	'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
	'THEME_CHARSET'		=> tr('encoding'),
	'LIST_IPS'		=> tr('IPs list'),
	'TR_AVAILABLE_IPS'	=> tr('Available IPs'),
	'TR_IP'				=> tr('IP'),
	'TR_LABEL'			=> tr('Label'),
	'TR_SERVER'			=> tr('Assigned to server'),
	'TR_ACTION'			=> tr('Action'),
	'TR_DELETE'			=> tr('Delete'),

	'TR_MESSAGE_DELETE'	=> tr('Are you sure you want to delete this IP: %s?', '%s')
));

genMainMenu();
genAdminServerMenu();

$tpl->flushOutput('admin/ip_show');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
