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

/* do we have a proper delete_id ? */

if (!isset($_GET['delete_lang'])) {
	header( 'Location: multilanguage.php' );
	die();
}

$delete_lang = $_GET['delete_lang'];

if ($delete_lang == Config::get('USER_INITIAL_LANG')) {
	/* ERR - we have domain that use this ip */
	set_page_message('Error we can\'t delete system default language!');
	header( 'Location: multilanguage.php' );
	die();
}

/* check if some one still use that lang */

$query = '
	select
		*
	from
		 user_gui_props
	where
		lang = ?
';

$rs = mysql::getInstance()->doQuery($query, $delete_lang);

if ($rs->countRows() > 0) {
	/* ERR - we have domain that use this ip */
	set_page_message('Error we have user that uses that language!');
	header( 'Location: multilanguage.php' );
	die();
}

$dir = configs::getInstance()->GUI_ROOT_DIR.'/i18n/locales/'.$_GET['delete_lang'];
$file = configs::getInstance()->GUI_ROOT_DIR.'/i18n/locales/'.$_GET['delete_lang'].'/LC_MESSAGES/'.$_GET['delete_lang'].'.mo';

if (!file_exists($dir) || !file_exists($file)) {
	set_page_message( tr('Incorrect data input!'));
	header( 'Location: multilanguage.php' );
	die();
}

recursiveRM($dir);

write_log(tr('%s removed language: %s', $_SESSION['user_logged'], $delete_lang));

set_page_message('Language was removed!');
header( 'Location: multilanguage.php' );
die();

function recursiveRM($dir) {
	$files = array_diff(scandir($dir), array('.','..'));
	foreach ($files as $file) {
		(is_dir("$dir/$file")) ? recursiveRM("$dir/$file") : unlink("$dir/$file");
	}
	return rmdir($dir);
}

