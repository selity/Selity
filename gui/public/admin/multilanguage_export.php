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

