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

// Security
check_login(__FILE__);

if (isset($_GET['export_lang']) && $_GET['export_lang'] !== '') {
	$file = configs::getInstance()->GUI_ROOT_DIR.'/i18n/locales/'.$_GET['export_lang'].'/LC_MESSAGES/'.$_GET['export_lang'].'.mo';

	if (!file_exists($file)) {
		set_page_message( tr('Incorrect data input!'));
		header( 'Location: multilanguage.php' );
		die();
	} else {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		echo(file_get_contents($file));
	}
} else {
	set_page_message(tr('Incorrect data input!'));
	header( 'Location: multilanguage.php' );
	die();
}

