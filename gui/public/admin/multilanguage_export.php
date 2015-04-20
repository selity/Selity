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

// Security
check_login(__FILE__);

if (isset($_GET['export_lang']) && $_GET['export_lang'] !== '') {
  $language_table = $_GET['export_lang'];
  $encoding  = $sql->Execute("SELECT `msgstr` FROM `$language_table` WHERE `msgid` = 'encoding';");
  if ($encoding && $encoding->RowCount() > 0 && $encoding->fields['msgstr'] != '') {
	  $encoding = $encoding->fields['msgstr'];
  } else {
	  $encoding = 'UTF-8';
  }
  $query = '
			SELECT
				msgid,
				msgstr
			FROM
				$language_table
';

	$rs = exec_query($sql, $query, array());

	if ($rs->RecordCount() == 0) {
		set_page_message( tr("Incorrect data input!"));
		header( "Location: multilanguage.php" );
		die();
	} else {
		$GLOBALS['class']['output']->showSize=false;
		header( "Content-type: text/plain; charset=".$encoding );
		while (!$rs -> EOF) {
			$msgid = $rs->fields['msgid'];
			$msgstr = $rs->fields['msgstr'];
			if ($msgid !== '' && $msgstr !== '') {
				echo $msgid." = ".$msgstr."\n";
			}
			$rs -> MoveNext();
		}
	}
} else {
	set_page_message(tr("Incorrect data input!"));
	header( "Location: multilanguage.php" );
	die();
}

