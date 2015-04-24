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

function showLogs () {

	$sql		= mysql::getInstance();
	$tpl		= template::getInstance();
	$showRows	= 15;
	$startIndex	= isset($_GET['go']) ? (int) $_GET['go'] : 0;
	$cQuery		= ' SELECT COUNT(*) AS `cnt` FROM `log`';
	$query		= "
		SELECT
			DATE_FORMAT(`log_time`,'%Y-%m-%d %H:%i') AS `date`, `log_message`, `log_level`
		FROM
			`log`
		ORDER BY
			`log_time` DESC
		LIMIT
		   $startIndex, $showRows
	";

	$cnt = $sql->doQuery($cQuery)->cnt;
	$rs = $sql->doQuery($query);
	$prevIndex = $startIndex - $showRows < 0 ? 0 : $startIndex - $showRows;
	$nextIndex = $startIndex + $showRows >= $cnt ? $startIndex : $startIndex + $showRows;

	$tpl->saveVariable(array(
		'showing'		=> tr('showing %s resellers FROM a total of %s', $rs->countRows(), $cnt),
		'prv'			=> $prevIndex,
		'nxt'			=> $nextIndex,
	));

	if($prevIndex != $startIndex) $tpl->saveSection('prev');
	if($nextIndex != $startIndex) $tpl->saveSection('next');

	if(!$cnt) $tpl->addMessage(tr('Log is empty!'), 'info');

	$logs = array();

	while (!$rs->EOF) {
		$log_message = $rs->log_message;
		$date_formt = Config::get('DATE_FORMAT') . ' H:i';
		$logs[] = array(
			'LOG_MESSAGE'	=> $log_message,
			'LOG_DATE'		=> date($date_formt, strtotime($rs->date)),
			'STYLE'			=> $rs->log_level.'level',
		);
		$rs->nextRow();
	}
	$tpl->saveRepeats(array('LOGS' => $logs));
}

function clearLog() {
	$tpl = template::getInstance();
	$sql = mysql::getInstance();

	if (isset($_POST['select'])) {
		$query = null;
		$msg = '';
		switch ($_POST['select']) {
			case 0:
				$query = 'DELETE FROM `log`';
				$msg = sprintf('%s deleted the full admin log!', $_SESSION['user_logged']);
				break;
			case 7:
				$query = 'DELETE FROM `log` WHERE DATE_SUB(CURDATE(), INTERVAL 7 DAY) >= `log_time`';
				$msg = sprintf('%s deleted the admin log older than two weeks!', $_SESSION['user_logged']);
				break;
			case 30:
				$query = 'DELETE FROM `log` WHERE DATE_SUB(CURDATE(), INTERVAL 1 MONTH) >= `log_time`';
				$msg = sprintf('%s deleted the admin log older than one month!', $_SESSION['user_logged']);
				break;
			case 91:
				$query = 'DELETE FROM `log` WHERE DATE_SUB(CURDATE(), INTERVAL 3 MONTH) >= `log_time`';
				$msg = sprintf('%s deleted the admin log older than three months!', $_SESSION['user_logged']);
				break;
			case 182:
				$query = 'DELETE FROM `log` WHERE DATE_SUB(CURDATE(), INTERVAL 6 MONTH) >= `log_time`';
				$msg = sprintf('%s deleted the admin log older than six months!', $_SESSION['user_logged']);
				break;
			case 365;
				$query = ' DELETE FROM `log` WHERE DATE_SUB(CURDATE(), INTERVAL 1 YEAR) >= `log_time`';
				$msg = sprintf('%s deleted the admin log older than one year!', $_SESSION['user_logged']);
				break;
			default:
				$tpl->addMessage(tr('Invalid time period!'));
				break;
		}
		$rs = $sql->doQuery($query);
		write_log($msg);
	}
}

clearLog();
showLogs();

$tpl->saveVariable(array(
	'TR_PAGE_TITLE'		=> tr('Selity - Admin/Admin Log'),
	'THEME_COLOR_PATH'	=> '../themes/'.$theme_color,
	//'THEME_CHARSET'	=> tr('encoding'),
	'TR_ADMIN_LOG'		=> tr('Admin Log'),
	'TR_SEND'			=> tr('Clear log'),
	'TR_DATE'			=> tr('Date'),
	'TR_MESSAGE'		=> tr('Message'),
	'TR_DELETE'			=> tr('Delete from log:'),
	'TR_EVERYTHING'		=> tr('everything'),
	'TR_LAST_WEEK'		=> tr('older than 1 week'),
	'TR_LAST_MONTH'		=> tr('older than 1 month'),
	'TR_LAST_3_MONTHS'	=> tr('older than 3 months'),
	'TR_LAST_HALF_YEAR'	=> tr('older than 6 months'),
	'TR_LAST_YEAR'		=> tr('older than 12 months'),
));

genMainMenu();
genAdminToolsMenu();

$tpl->flushOutput('admin/selity_log');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();
