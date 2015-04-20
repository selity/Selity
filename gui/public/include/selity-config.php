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

require_once(INCLUDEPATH . '/class.config.php');

$cfgfile = '/etc/selity/selity.conf';


// Load config variables from file
try {
	Config::load($cfgfile);
} catch (Exception $e) {
	die('<div style="text-align: center; color: red; font-weight: strong;">' . $e->getMessage() . '<br />Please contact your system administrator</div>');
}


function decrypt_db_password ($db_pass) {
	eval(@file_get_contents('/etc/selity/selity-db-keys'));

	if (empty($db_pass_key) || empty($db_pass_iv)) {
		throw new Exception('Database key and/or initialization vector was not generated.');
	}

	if (extension_loaded('mcrypt') || @dl('mcrypt.' . PHP_SHLIB_SUFFIX)) {
		$text = @base64_decode($db_pass . "\n");
		// Open the cipher
		$td = @mcrypt_module_open ('blowfish', '', 'cbc', '');

		// Intialize encryption
		@mcrypt_generic_init ($td, $db_pass_key, $db_pass_iv);
		// Decrypt encrypted string
		$decrypted = @mdecrypt_generic ($td, $text);
		@mcrypt_module_close ($td);

		// Show string
		return trim($decrypted);
	} else {
		system_message("ERROR: The php-extension 'mcrypt' not loaded!");
		die();
	}
}
