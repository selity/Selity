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

check_user_login();

if (!in_array($_SESSION['user_type'], array('reseller', 'admin'))) {
	header('Location: index.php');
	die();
}

$tpl = template::getInstance();
$cfg = configs::getInstance();
$theme_color = $cfg->USER_INITIAL_THEME;

$tpl->saveVariable(array(
	'ADMIN_TYPE'				=> $_SESSION['user_type'],
	'TR_PAGE_TITLE'				=> tr('Selity - Hosting plans'),
	'THEME_COLOR_PATH'			=> '../themes/'.$theme_color,
	'THEME_CHARSET'				=> tr('encoding'),
	'TR_HOSTING_PLANS'			=> tr('Hosting plans'),
	'TR_PAGE_MENU'				=> tr('Manage hosting plans'),
	'TR_ADD_HOSTING_PLAN'		=> tr('Add hosting plan'),
	'TR_TITLE_ADD_HOSTING_PLAN'	=> tr('Add new user hosting plan'),
	'TR_BACK'					=> tr('Back'),
	'TR_TITLE_BACK'				=> tr('Return to previous menu'),
	'TR_MESSAGE_DELETE'			=> tr('Are you sure you want to delete %s?', '%s'),


));

//gen_logged_from($tpl);

gen_hp_table($tpl, $_SESSION['user_id']);


function gen_hp_table(&$tpl, $reseller_id) {
	$tpl = template::getInstance();
	$cfg = configs::getInstance();
	$sql = mysql::getInstance();

	if ($cfg->HOSTING_PLANS_LEVEL !== $_SESSION['user_type']) {
		$query = '
			SELECT
				`t1`.*
			FROM
				`hosting_plans` AS `t1`
			LEFT JOIN
				`admin` AS `t2`
			ON
				`t1`.`reseller_id` = `t2`.`admin_id`
			WHERE
				`t2`.`admin_type` = ?
			%s
			ORDER BY
				`t1`.`name`
		';
		$query = sprintf($query, ($_SESSION['user_type'] == 'reseller'? 'AND `t1`.`status` = 1' : ''));
		$rs = $sql->doQuery($query, 'admin');
		$details = tr('View details');
	} else {
		$query = '
			SELECT
				*
			FROM
				`hosting_plans`
			WHERE
				`reseller_id` = ?
			ORDER BY
				`name`
		';
		$rs = $sql->doQuery($query, $_SESSION['user_id']);
		$details = tr('Edit');
	}

	if ($rs->countRows() == 0) {
		set_page_message(tr('Hosting plans not found!'));
	} else {
		$tpl->saveSection('HP');
		$tpl->saveVariable(array(
			'TR_PURCHASING'		=> tr('Purchasing'),
			'TR_NOM'			=> tr('No.'),
			'TR_EDIT'			=> $details,
			'TR_PLAN_NAME'		=> tr('Name'),
			'TR_ACTION'			=> tr('Action')
		));

		$i = 1;
		$hp = array();
		while (!$rs->EOF) {
			$status = $rs->status == 1 ? tr('Enabled') : tr('Disabled');
			$hp[] = array(
				'PLAN_NOM'		=> $i++,
				'PLAN_NAME'		=> stripslashes($rs->name),
				'PLAN_ACTION'	=> tr('Delete'),
				'PLAN_SHOW'		=> tr('Show hosting plan'),
				'PURCHASING'	=> $status,
				'HP_ID'			=> $rs->id,
				'RESELLER_ID'	=> $_SESSION['user_id']
			);
			$rs->nextRow();
		}
		$tpl->saveRepeats(array('HP' => $hp));
	}
}

genMainMenu();
genHPMenu();

$tpl->flushOutput('common/hp_show');

if (configs::getInstance()->GUI_DEBUG)
	dump_gui_debug();

unset_messages();
