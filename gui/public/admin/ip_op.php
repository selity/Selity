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

function getServerList() {
	$sql = mysql::getInstance();
	$tpl = template::getInstance();
	$query = 'SELECT * FROM `servers`';
	$rs = $sql->doQuery($query);
	$servers = array();
	if ($rs->countRows() == 0) {
		$tpl->addMessage(tr('Server list is empty! You must add at lest one!'));
	} else {
		while (!$rs->EOF) {
			$checked = array_key_exists('server', $_POST) && $_POST['server'] == $rs->server_id ? 'selected' : '';
			$servers[] = array(
				'SERVER_NAME'		=> $rs->server_name,
				'SERVER_ID'			=> $rs->server_id,
				'SERVER_SLT'		=> $checked
			);
			$rs->nextRow();
		}
	}
	$tpl->saveRepeats(array('SERVER' => $servers));
}

function addIP($ip) {

	$tpl = template::getInstance();

	if (array_key_exists('submit', $_POST)) {

		$ip->server_id		= clean_input($_POST['server']);
		$ip->ip_number		= clean_input($_POST['ipNumber']);
		$ip->ip_label		= clean_input($_POST['ipLabel']);
		$ip->ip_status		= ADD_STATUS;

		$msg = is_null($ip->ip_id) ? tr('IP added') : tr('IP saved');

		if($ip->save()){

			write_log(sprintf(
				'%s: added ip: %s (%s)',
				$_SESSION['user_logged'],
				$ip->ip_number,
				$ip->ip_label
			), 'info');
			send_request();
			$tpl->addMessage($msg);
			header('Location: ip_show.php');
			die();
		} else {
			$tpl->addMessage($ip->getMessage());
		}
	}
}

switch($_GET['op']){
	case 'add':
		$ip = new selity_ips();
		break;
	case 'delete':
		$id = (int) $_GET['ip_id'];
		try{
			$ip = new selity_ips($id);
		} catch (Exception $e) {
			$tpl->addMessage(tr('IP not found!'));
			header('Location: ip_show.php');
			die();
		}
		if($ip->delete()){
			$tpl->addMessage(tr('IP deleted!'));
		} else {
			$tpl->addMessage($ip->getMessage());
			$tpl->addMessage(tr('IP not deleted!'));
		}
		header('Location: ip_show.php');
		die();
		break;
	default:
		$tpl->addMessage(tr('Invalid operation!'));
		header('Location: ip_show.php');
		die();
}

genMainMenu();
genAdminServerMenu();

addIP($ip);
getServerList();

$tpl->saveVariable(array(
	'TR_PAGE_TITLE'		=> tr('Selity - Add ip'),
	'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
	//'THEME_CHARSET'	=> tr('encoding'),
	'TR_IP_OP'			=> $ip->ip_id ? tr('Edit ip') : tr('Add ip'),
	'TR_SERVER_NAME'	=> tr('Server name'),
	'TR_IP_NUMBER'		=> tr('IP number'),
	'TR_IP_LABEL'		=> tr('IP label'),
	'TR_SUBMIT'			=> tr('Save'),

	'IP_NUMBER'		=> $ip->ip_number,
	'IP_LABEL'		=> $ip->ip_label,
));

$tpl->flushOutput('admin/ip_add');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
