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


spl_autoload_register(function ($class) {
	if(!include_once($class.'.php')){
		throw new Exception ('Can not load '. $class);
	};
}, true);

//require('selity_passwords.php');

$cfg = configs::getInstance();
$cfg->initFileConfig();

mysql::setHost($cfg->DATABASE_HOST);
mysql::setDB($cfg->DATABASE_NAME);
mysql::setUser($cfg->DATABASE_USER);
mysql::setPass(decrypt_db_password($cfg->DATABASE_PASSWORD));

$cfg->initDbConfig();

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL|E_STRICT);

//session_name('Selity');
//
if (!isset($_SESSION)) {
	session_start();
}
//
selity_language::getInstance();
//new selity_login();
//
$cfg->LOSTPASSWORD				= true;
$cfg->LOSTPASSWORD_TIMEOUT		= 30;
$cfg->PASSWD_CHARS				= 6;
$cfg->PASSWD_STRONG				= true;
$cfg->BRUTEFORCE				= true;
$cfg->BRUTEFORCE_BETWEEN		= true;
$cfg->BRUTEFORCE_MAX_LOGIN		= 3;
$cfg->BRUTEFORCE_BLOCK_TIME		= 30;
$cfg->BRUTEFORCE_BETWEEN_TIME	= 30;
$cfg->BRUTEFORCE_MAX_CAPTCHA	= 5;


$cfg->HOSTING_PLANS_LEVEL	= 'reseller';

$cfg->CHECK_FOR_UPDATES		= true;

$cfg->ROOT_TEMPLATE_PATH	= '/themes/';
$cfg->LOGIN_TEMPLATE_PATH	= $cfg->ROOT_TEMPLATE_PATH . $cfg->USER_INITIAL_THEME;
$cfg->USER_ROWS_PER_PAGE	= 10;
$cfg->USER_INITIAL_LANG		= 'en_US';
$cfg->SUPPORT_SYSTEM		= true;
$cfg->CREATE_DEFAULT_EMAILS	= true;
$cfg->HARD_MAIL_SUSPENSION	= false;
$cfg->LOG_LEVEL				= E_USER_NOTICE;



// variable for development edition	=> shows all php variables under the pages
// false = disable, true = enable
$cfg->GUI_DEBUG				= false;

require('selity_layout.php');
//require('selity_input.php');
