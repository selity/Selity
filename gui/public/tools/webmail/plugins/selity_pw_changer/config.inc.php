<?php
/**
 * Selity - When virtual hosting becomes scalable
 * Copyright (C) 2010-2011 by i-MSCP team
 * Copyright (C) 2012-2014 by Selity team
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @category    Selity
 * @package     Selity Roundcube password changer
 * @copyright   2010-2011 by i-MSCP team
 * @copyright   2012 by Selity team
 * @author 		Sascha Bay
 * @link        http://www.i-mscp.net i-MSCP Home Site
 * @link        http://selity.org Selity Home Site
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL v2
 */

// Password Plugin options
// -----------------------
// A driver to use for password change. Default: "sql".
$rcmail_config['password_driver'] = 'sql';

// SQL Driver options
// ------------------
// PEAR database DSN for performing the query. By default
// Roundcube DB settings are used.
$rcmail_config['password_db_dsn'] = 'mysqli://roundcube_user:iDtzG1reQnW2GfU1@localhost/selity';

$rcmail_config['password_length'] = 6;

?>
