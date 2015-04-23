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

function genMainMenu(){
	call_user_func('gen'.ucfirst($_SESSION['user_type']).'MainMenu');
}

function genAdminMainMenu(){
	template::getInstance()->saveVariable(
		array(
			'TR_MENU_INFO'		=> tr('General information'),
			'TR_GENERAL_INFO'	=> tr('General information'),
			'TR_MANAGE_SERVERS'	=> tr('Manage servers'),
			'TR_MANAGE_USERS'	=> tr('Manage users'),
			'TR_MANAGE_HP'		=> tr('Manage hosting plans'),
			'TR_SYSTEM_TOOLS'	=> tr('Manage system tools'),
			'TR_STATISTICS'		=> tr('Statistics'),
			'TR_SUPPORT'		=> tr('Ticket system'),
			'TR_SETTINGS'		=> tr('Settings'),
			'TR_LOGOUT'			=> tr('Logout')
		)
	);
	if (strtolower(configs::getInstance()->HOSTING_PLANS_LEVEL) == $_SESSION['user_type']) {
		template::getInstance()->saveSection('HP_MAIN_MENU');
	}
}

function genResellerMainMenu(){
	template::getInstance()->saveVariable(
		array(
			'TR_MENU_INFO'		=> tr('General information'),
			'TR_GENERAL_INFO'	=> tr('General information'),
			'TR_MANAGE_USERS'	=> tr('Manage users'),
			'TR_MANAGE_HP'		=> tr('Manage hosting plans'),
			'TR_MANAGE_ORDERS'	=> tr('Manage orders'),
			'TR_STATISTICS'		=> tr('Statistics'),
			'TR_SUPPORT'		=> tr('Ticket system'),
			'TR_LOGOUT'			=> tr('Logout')
		)
	);
}

function genUserMainMenu(){
	template::getInstance()->saveVariable(
		array(
			'TR_GENERAL_INFO'	=> tr('General information'),
			'TR_MANAGE_DOMAINS'	=> tr('Manage domains'),
			'TR_MANAGE_MAIL'	=> tr('Email accounts'),
			'TR_MANAGE_FTP'		=> tr('FTP accounts'),
			'TR_MANAGE_SQL'		=> tr('SQL accounts'),
			'TR_MANAGE_TOOLS'	=> tr('Web tools'),
			'TR_STATISTICS'		=> tr('Statistics'),
			'TR_SUPPORT'		=> tr('Ticket system'),
			'TR_LOGOUT'			=> tr('Logout')
		)
	);
}

function genGeneralMenu(){
	template::getInstance()->saveVariable(
		array(
			'TR_MENU_INFO'			=> tr('Manage personal data'),
			'TR_OVERVIEW'			=> tr('Overview'),
			'TR_CHANGE_PASSWORD'	=> tr('Change password'),
			'TR_CHANGE_PERSONAL'	=> tr('Change personal data'),
			'TR_CHANGE_LANGUAGE'	=> tr('Change language'),
			'TR_CHANGE_LAYOUT'		=> tr('Change layout'),
		)
	);
	template::getInstance()->saveSection('GENERAL_MENU');
}

function genAdminServerMenu(){
	template::getInstance()->saveVariable(
		array(
			'TR_SERVER_OVERVIEW'	=> tr('Servers list'),
			'TR_SERVER_ADD'			=> tr('Add server'),
			'TR_SERVER_STATUS'		=> tr('Servers status'),
		)
	);
	template::getInstance()->saveSection('SERVER_MENU');
}

function genAdminUsersMenu(){
	template::getInstance()->saveVariable(
		array(
			'TR_USERS_OVERVIEW'		=> tr('Users overview'),
			'TR_ADMIN_ADD'			=> tr('Add admin'),
			'TR_RESELLER_ADD'		=> tr('Add reseller'),
			'TR_USER_ASSIGMENT'		=> tr('User assignment'),
			'TR_EMAIL_MARKETING'	=> tr('Email marketing'),
		)
	);
	template::getInstance()->saveSection('USERS_MENU');
}

function genAdminToolsMenu(){
	template::getInstance()->saveVariable(
		array(
			'TR_TOOLS_OVERVIEW'		=> tr('Selity debugger'),
			'TR_LOGS'				=> tr('Logs'),
			'TR_SESSIONS'			=> tr('Session manager'),
			'TR_MAINTENANCE_MODE'	=> tr('Maintenance mode'),
			'TR_SELITY_UPDATE'		=> tr('Selity update'),
			'TR_DATABASE_UPDATE'	=> tr('Database update'),
		)
	);
	template::getInstance()->saveSection('TOOLS_MENU');
}

function genAdminStatsMenu(){
}


function genAdminSettingsMenu(){
	template::getInstance()->saveVariable(
		array(
			'TR_GENERAL_SETTINGS'				=> tr('Overview'),
			'TR_MENU_I18N'						=> tr('Internationalisation'),
			'TR_MENU_LAYOUT_TEMPLATES'			=> tr('Layout'),
			'TR_CUSTOM_MENUS'					=> tr('Custom menus'),
			'TR_MENU_MANAGE_IPS'				=> tr('Manage IPs'),
			'TR_MENU_SERVER_TRAFFIC_SETTINGS'	=> tr('Server traffic settings'),
			'TR_MENU_EMAIL_SETUP'				=> tr('Email setup'),
			'TR_MENU_LOSTPW_EMAIL'				=> tr('Lostpw email setup'),
			'TR_SERVERPORTS'					=> tr('Server ports')
		)
	);
	template::getInstance()->saveSection('SETTINGS_MENU');
}

function genHPMenu(){
	template::getInstance()->saveVariable(
		array(
			'TR_HP_OVERVIEW'	=> tr('Hosting plans overview'),
			'TR_HP_ADD'			=> tr('Add hosting plan')
		)
	);
	if (strtolower(configs::getInstance()->HOSTING_PLANS_LEVEL) == $_SESSION['user_type']) {
		template::getInstance()->saveSection('HP_ADD');
	}
	template::getInstance()->saveSection('HP_MENU');
}

function genUserDomainsMenu(){
	template::getInstance()->saveVariable(array(
		'TR_DOMAIN_OVERVIEW'	=> tr('Domain overview'),
		'TR_ADD_DOMAIN'			=> tr('Add domain'),
		'TR_ADD_SUBDOMAIN'		=> tr('Add subdomain'),
		'TR_CHANGE_HP'			=> tr('Update hosting package'),
	));
	template::getInstance()->saveSection('DOMAINS_MENU');
}

function genUserMailMenu(){
	template::getInstance()->saveVariable(array(
		'TR_MAIL_OVERVIEW'	=> tr('Email overview'),
		'TR_ADD_MAIL_USER'	=> tr('Add email user'),
		'TR_CATCH_ALL_MAIL'	=> tr('Catch all'),
		'TR_WEBMAIL_CLIENT'	=> tr('Webmail'),
	));
	template::getInstance()->saveSection('HP_MENU');
}

function genUserFTPMenu(){
	template::getInstance()->saveVariable(array(
		'TR_FTP_OVERVIEW'	=> tr('FTP overview'),
		'TR_ADD_FTP_USER'	=> tr('Add FTP user'),
		'TR_FTP_CLIENT'		=> tr('Filemanager'),
	));
	template::getInstance()->saveSection('FTP_MENU');
}

function genUserSQLMenu(){
	template::getInstance()->saveVariable(array(
		'TR_SQL_OVERVIEW'	=> tr('Database overview'),
		'TR_ADD_SQL_DB'		=> tr('Add SQL database'),
		'TR_PMA'			=> tr('PhpMyAdmin'),
	));
	template::getInstance()->saveSection('SQL_MENU');
}

function genUserToolsMenu(){
	template::getInstance()->saveVariable(array(
		'TR_TOOLS_OVERVIEW'	=> tr('Webtools overview'),
		'TR_HTACCESS'		=> tr('Protected areas'),
		'TR_HTACCESS_USERS'	=> tr('Group/User management'),
		'TR_ERROR_PAGES'	=> tr('Error pages'),
		'TR_BACKUP'			=> tr('Backups'),
		'TR_WEBMAIL_CLIENT'	=> tr('Webmail'),
		'TR_FTP_CLIENT'		=> tr('Filemanager'),
		'TR_PMA'			=> tr('PhpMyAdmin'),
		'TR_AWSTATS'		=> tr('PhpMyAdmin'),
	));
	template::getInstance()->saveSection('TOOLS_MENU');
}

function genUserStatsMenu(){
	template::getInstance()->saveVariable(array(
		'TR_DOMAIN_STATS'	=> tr('Domain statistics'),
		'TR_AWSTATS'		=> tr('Web statistics'),
	));
	template::getInstance()->saveSection('STATISTICS_MENU');
}

function genTicketMenu(){
	template::getInstance()->saveVariable(array(
		'TR_TICKET_OVERVIEW'	=> tr('Open tickets'),
		'TR_CLOSED_TICKET'		=> tr('Closed tickets'),
		'TR_NEW_TICKET'			=> tr('New ticket'),
	));
	template::getInstance()->saveSection('TICKET_MENU');
}
