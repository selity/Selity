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
redirect_to_level_page();

$query = "
	update
		domain
	set
		domain_status = 'toadd'
";

$rs = execute_query($sql, $query);
print "Domains updated";

$query = "
	update
		domain_aliasses
	set
		alias_status = 'toadd'
";

$rs = execute_query($sql, $query);
print "Domain aliases updated";

$query = "
	update
		subdomain
	set
		subdomain_status = 'toadd'
";

$rs = execute_query($sql, $query);
print "Subdomains updated";

$query = "
	update
		subdomain_alias
	set
		subdomain_alias_status = 'toadd'
";

$rs = execute_query($sql, $query);
print "Subdomains alias updated";

$query = "
	update
		mail_users
	set
		status = 'toadd'
";

$rs = execute_query($sql, $query);
print "Emails updated";

