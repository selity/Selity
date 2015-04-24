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

function listSessions() {
	$tpl	= template::getInstance();
	$sql	= mysql::getInstance();

	if(isset($_GET['killID']) && $_GET['killID'] != session_id()){
		$sessionId = clean_input($_GET['killID']);
		$query = 'SELECT * FROM `login` LEFT JOIN `admin` ON `user_name` = `admin_name` WHERE `session_id` = ?';
		$rs = $sql->doQuery($query, $sessionId);
		if($rs->countRows() > 0){
			$username = $rs->admin_name ? sprintf('%s (%s), user type: %s', $rs->admin_name, $rs->email, $rs->admin_type) : tr('Unknown');
			$query = 'DELETE FROM `login` WHERE `session_id` = ?';
			$rs = $sql->doQuery($query, $sessionId);
			if($rs->countRows()){
				$msg	= tr('User session was killed!');
				$type	= 'info';
				$logMSG	= sprintf('%s: killed user session: %s!', $_SESSION['user_logged'], $username);
			} else {
				$msg	= tr('User session was not killed!');
				$type	= 'warning';
				$logMSG	= sprintf('%s: try to kill user session: %s. Unsuccesfull!', $_SESSION['user_logged'], $username);
			}
		} else {
			$msg	= tr('User session was not killed!');
			$type	= 'warning';
			$logMSG	= sprintf('%s: try to kill session having id: %s. Unsuccesfull!', $_SESSION['user_logged'], $sessionId);
		}
		$tpl->addMessage($msg, $type);
		write_log($logMSG, $type);
	}

	$rs		= $sql->doQuery('SELECT * FROM `login` LEFT JOIN `admin` ON `user_name` = `admin_name`');
	$list	= array();
	while (!$rs->EOF) {
		$list[] = array(
			'USERNAME'	=> $rs->admin_name == '' ? tr('Unknown') : sprintf('%s (%s)', $rs->admin_name, $rs->email),
			'TIME'		=> date('G:i:s', $rs->lastaccess),
			'ID'		=> $rs->session_id
		);
		if(session_id() != $rs->session_id)$tpl->saveSection('DELETE'.$rs->session_id);
		$rs->nextRow();
	}
	$tpl->saveRepeats(array('SESSIONS' => $list));
}

listSessions();

$tpl->saveVariable(array(
	'TR_PAGE_TITLE'			=> tr('Selity - Admin/Manage Sessions'),
	'THEME_COLOR_PATH'		=> '../themes/'.$theme_color,
	//'THEME_CHARSET'		=> tr('encoding'),
	'TR_MANAGE_SESSIONS'	=> tr('Manage user sessions'),
	'TR_USERNAME'			=> tr('Username'),
	'TR_LOGIN_ON'			=> tr('Last access'),
	'TR_OPTIONS'			=> tr('Options'),
	'TR_DELETE'				=> tr('Kill session'),
));

genMainMenu();
genAdminToolsMenu();

$tpl->flushOutput('admin/selity_sessions');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
