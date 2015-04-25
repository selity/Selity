<?php
/**
 * Selity - A server control panel
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2008 by ispCP | http://isp-control.net
 * @copyright	2012-2015 by Selity
 * @link 		http://selity.org
 * @author 		ispCP Team
 *
 * @license
 *   This program is free software; you can redistribute it and/or modify it under
 *   the terms of the MPL General Public License as published by the Free Software
 *   Foundation; either version 1.1 of the License, or (at your option) any later
 *   version.
 *   You should have received a copy of the MPL Mozilla Public License along with
 *   this program; if not, write to the Open Source Initiative (OSI)
 *   http://opensource.org | osi@opensource.org
 */

require '../include/selity-lib.php';

check_login(__FILE__);

$tpl = new pTemplate();
$tpl->define_dynamic('page', Config::get('ADMIN_TEMPLATE_PATH') . '/server_status.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('service_status', 'page');

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(
		array(
			'TR_PAGE_TITLE' => tr('Selity Admin / System Tools / Server Status'),
			'THEME_COLOR_PATH' => '../themes/'.$theme_color,
			'THEME_CHARSET' => tr('encoding'),
			//'ISP_LOGO' => get_logo($_SESSION['user_id'])
			)
		);

/*
Site functions
*/

class status {
	var $all = array();
	var $log = false;

	// AddService adds a service to a multi-dimensional array
	function AddService($ip, $port, $service, $type) {
		$small_array = array('ip' => $ip, 'port' => $port, 'service' => $service, 'type' => $type, 'status' => '');
		array_push($this->all, $small_array);
		return $this->all;
	}

	// GetCount returns the number of services added
	function GetCount() {
		return count($this->all);
	}

	// CheckStatus checks the status
	function CheckStatus($timeout = 5) {
		$x = $this->GetCount();
		for($i = 0; $i <= $x - 1; $i++) {
			$ip = $this->all[$i]['ip'];
			$port = $this->all[$i]['port'];
			$errno = null;
			$errstr = null;

			if ($this->all[$i]['type'] == 'tcp') {
				$fp = @fsockopen($ip, $port, $errno, $errstr, $timeout);
			}
			else if ($this->all[$i]['type'] == 'udp') {
				$fp = @fsockopen('udp://' . $ip, $port, $errno, $errstr, $timeout);
			}
			else {
				die('FIXME: ' . __FILE__ . ':' . __LINE__);
			}

			if ($fp) {
				$this->all[$i]['status'] = true;
				if ($this->log) {
					$this->AddLog($this->all[$i]['ip'], $this->all[$i]['port'], $this->all[$i]['service'], $this->all[$i]['type'], 'TRUE');
					// $this->StatusUp(mysql_insert_id());
				}
			}
			else {
				$this->all[$i]['status'] = false;
				if ($this->log) {
					$this->AddLog($this->all[$i]['ip'], $this->all[$i]['port'], $this->all[$i]['service'], $this->all[$i]['type'], 'FALSE');
					// $this->StatusDown(mysql_insert_id());
				}
			}

			if ($fp)
				fclose($fp);
		}
	}

	// GetStatus a unecessary function to return the status
	function GetStatus() {
		return $this->all;
	}

	// GetSingleStatus will get the status of single address
	function GetSingleStatus($ip, $port, $type, $timeout = 5) {
		$errno = null;
		$errstr = null;
		if ($type == 'tcp') {
			$fp = @fsockopen($ip, $port, $errno, $errstr, $timeout);
		}
		else if ($type == 'udp') {
			$fp = @fsockopen('udp://' . $ip, $port, $errno, $errstr, $timeout);
		}
		else {
			die('FIXME: ' . __FILE__ . ':' . __LINE__);
		}

		if (!$fp)
			return false;

		fclose($fp);
		return true;
	}
}

function get_server_status(&$tpl, &$sql) {
	$query = '
		SELECT
			*
		FROM
			config
		WHERE
			name
		  LIKE
		  	\'PORT_%\'
		ORDER BY
			name ASC
';

	$rs = exec_query($sql, $query, array());

	$selity_status = new status;

	// Enable logging?
	$selity_status->log = false; // Default is false
	$selity_status->AddService('localhost', 9876, 'Selity Daemon', 'tcp');

	// Dynamic added Ports
	while (!$rs->EOF) {
		list($port, $protocol, $name, $status, $custom) = explode(";", $rs->fields['value']);
		if ($status) {
			$selity_status->AddService('localhost', (int)$port, $name, $protocol);
		}

		$rs->MoveNext();
	} //while

	$selity_status->CheckStatus(5);
	$data = $selity_status->GetStatus();
	$up = tr('UP');
	$down = tr('DOWN');

	for($i = 0, $c = count($data); $i < $c; $i++) {
		if ($data[$i]['status']) {
			$img = $up;
			$class = "content up";
		} else {
			$img = '<b>' . $down . '</b>';
			$class = "content down";
		}

		if ($data[$i]['port'] == 23/*telnet*/) {
			if ($data[$i]['status']) {
				$class = 'content2 down';
				$img = '<b>' . $up . '</b>';
			} else {
				$class = 'content2 up';
				$img = $down;
			}
		}

		$tpl->assign(
				array(
					'HOST' => $data[$i]['ip'],
					'PORT' => $data[$i]['port'],
					'SERVICE' => $data[$i]['service'],
					'STATUS' => $img,
					'CLASS' => $class,
					)
				);

		$tpl->parse('SERVICE_STATUS', '.service_status');
	}
}

/*
 *
 * static page messages.
 *
 */
gen_admin_mainmenu($tpl, Config::get('ADMIN_TEMPLATE_PATH') . '/main_menu_general_information.tpl');
gen_admin_menu($tpl, Config::get('ADMIN_TEMPLATE_PATH') . '/menu_general_information.tpl');

$tpl->assign(
		array(
			'TR_HOST' => tr('Host'),
			'TR_SERVICE' => tr('Service'),
			'TR_STATUS' => tr('Status'),
			'TR_SERVER_STATUS' => tr('Server status'),
			)
		);

get_server_status($tpl, $sql);

gen_page_message($tpl);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();

