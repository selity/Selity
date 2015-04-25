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
 *  @license
 *   This program is free software; you can redistribute it and/or
 *   modify it under the terms of the GPL General Public License
 *   as published by the Free Software Foundation; either version 2.0
 *   of the License, or (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GPL General Public License for more details.
 *
 *   You may have received a copy of the GPL General Public License
 *   along with this program.
 *
 *   An on-line copy of the GPL General Public License can be found
 *   http://www.fsf.org/licensing/licenses/gpl.txt
 **/


/**
 * Implementing abstract class selityUpdate for database update functions
 *
 * @author	Daniel Andreca <sci2tech@gmail.com>
 * @copyright 	2006-2008 by ispCP | http://isp-control.net
 * @version	1.0
 * @since	r1355
 */
class databaseUpdate extends selityUpdate{
	protected $databaseVariableName="DATABASE_REVISION";
	protected $functionName="_databaseUpdate_";
	protected $errorMessage="Database update %s failed";

	public static function getInstance(){
		static $instance=null;
		if($instance===null)$instance= new self();
		return $instance;
	}

	/*
	* Insert the update functions below this entry. The revision has to be ascending and unique.
	* Each databaseUpdate function has to return a array. Even if the array contains only one entry.
	*/

	/**
	 * Initital Update. Insert the first Revision.
	 *
	 * @author		Jochen Manz <zothos@zothos.net>
	 * @copyright	2006-2008 by ispCP | http://isp-control.net
	 * @version		1.0
	 * @since		r1355
	 *
	 * @access		protected
	 * @return		sql statements to be performed
	 */
	protected function _databaseUpdate_1() {
		$sqlUpd = array();

		$sqlUpd[] = "REPLACE INTO `config` (name, value) VALUES ('DATABASE_REVISION' , '1')";

		return $sqlUpd;
	}
}

