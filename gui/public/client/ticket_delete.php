<?php
/**
 * Selity - A server control panel
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2008 by ispCP | http://isp-control.net
 * @copyright	2012-2014 by Selity
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

if (isset($_GET['ticket_id']) && $_GET['ticket_id'] !== '') {

$ticket_id = $_GET['ticket_id'];
$user_id = $_SESSION['user_id'];

$query = '
	  select
		  ticket_status
	  from
		  tickets
	  where
		  ticket_id = ?
		and
		  (ticket_from = ? or ticket_to = ?)
';

  $rs = exec_query($sql, $query, array($ticket_id, $user_id, $user_id));
  if ($rs -> RecordCount() == 0) {
	header('Location: ticket_system.php');
	die();
  }
  $ticket_status = $rs -> fields['ticket_status'];
  if ($ticket_status == 0) {
	$back_url = "ticket_closed.php";
  }
  else {
	$back_url = "ticket_system.php";
  }

  $ticket_id = $_GET['ticket_id'];

  $query = '
		delete from
		  tickets
	  where
		  ticket_id = ?
		or
		  ticket_reply = ?
';

  $rs = exec_query($sql, $query, array($ticket_id, $ticket_id));
  while (!$rs -> EOF) {
	$rs -> MoveNext();
  }

  write_log($_SESSION['user_logged'].": deletes support ticket");
  set_page_message(tr('Support ticket deleted successfully!'));
  user_goto($back_url);
} elseif (isset($_GET['delete']) && $_GET['delete'] == 'open') {
  $user_id = $_SESSION['user_id'];

  $query = '
	  delete from
		  tickets
	  where
		  ticket_from = ?
		and
		  ticket_status != '0'
';

  $rs = exec_query($sql, $query, array($user_id));

  while (!$rs -> EOF) {
	$rs -> MoveNext();
  }
  set_page_message(tr('All open support tickets deleted successfully!'));
  user_goto('ticket_system.php');
} elseif (isset($_GET['delete']) && $_GET['delete'] == 'closed') {
  $user_id = $_SESSION['user_id'];

  $query = '
	  delete from
		tickets
	  where
		ticket_from = ?
	  and
		ticket_status = '0'
';

  $rs = exec_query($sql, $query, array($user_id));

  while (!$rs -> EOF) {
	$rs -> MoveNext();
  }
  set_page_message(tr('All closed support tickets deleted successfully!'));
  user_goto('ticket_closed.php');
} else {
  user_goto('ticket_system.php');
}

