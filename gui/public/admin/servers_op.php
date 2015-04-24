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

function addServer($server) {

	$tpl = template::getInstance();

	if (array_key_exists('submit', $_POST)) {

		$server->server_name		= clean_input($_POST['serverName']);
		$server->server_ip			= clean_input($_POST['serverIP']);
		$server->server_root_user	= clean_input($_POST['rootUser']);
		$server->server_root_pass	= clean_input($_POST['rootPass']);
		$server->server_status		= ADD_STATUS;

		$msg = is_null($server->server_id) ? tr('Server added') : tr('Server data saved');

		if($server->save()){

			write_log(sprintf(
				'%s: added server: %s (%s)',
				$_SESSION['user_logged'],
				$server->server_name,
				$server->server_ip
			));
			send_request();
			$tpl->addMessage($msg);
			header('Location: servers_show.php');
			die();
		} else {
			$tpl->addMessage($server->getMessage());
		}
	}
}

switch($_GET['op']){
	case 'add':
		$server = new selity_server();
		break;
	case 'edit':
		$id = (int) $_GET['server_id'];
		try{
			$server = new selity_server($id);
		} catch (Exception $e) {
			$tpl->addMessage(tr('Server not found!'));
			header('Location: servers_show.php');
			die();
		}
		break;
	default:
		$tpl->addMessage(tr('Invalid operation!'));
		header('Location: servers_show.php');
		die();
}

genMainMenu();
genAdminServerMenu();

addServer($server);

$tpl->saveVariable(array(
	'TR_PAGE_TITLE'		=> tr('Selity - Add administrator'),
	'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
	//'THEME_CHARSET'	=> tr('encoding'),
	'TR_SERVER_DATA'	=> tr('Server data'),
	'TR_SERVER_NAME'	=> tr('Server name'),
	'TR_SERVER_IP'		=> tr('Server ip'),
	'TR_ROOT_USER'		=> tr('Root user'),
	'TR_ROOT_PASS'		=> tr('Root password'),
	'TR_SUBMIT'			=> tr('Add'),

	'SERVER_NAME'	=> $server->server_name,
	'SERVER_IP'		=> $server->server_ip,
	'ROOT_USER'		=> $server->server_root_user
));

$tpl->flushOutput('admin/servers_add');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
