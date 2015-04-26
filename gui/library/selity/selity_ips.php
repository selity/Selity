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


class selity_ips extends selity_container{

	protected $tableName	= 'server_ips';

	public function __construct($id = null){
		parent::__construct();
		if(!is_null($id)){
			$this->init($id);
		}
	}

	public function __set($var, $value){
		if($var == 'ip_number'){
			if(filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
				$hex = unpack('H*hex', inet_pton($value));
				$value = substr(preg_replace('/([A-f0-9]{4})/', "$1:", $hex['hex']), 0, -1);
			}
		}
		parent::__set($var, $value);
	}

	public function validate(){
		if(!is_numeric($this->server_id)){
			$this->errors[] = tr('Invalid server id!');
		}
		$cnt = mysql::getInstance()->doQuery('SELECT COUNT(*) AS `cnt` FROM `servers` WHERE `server_id` = ?', $this->server_id)->cnt;
		if(!$cnt){
			$this->errors[] = tr('No such server!');
		}
		$cnt = mysql::getInstance()->doQuery('SELECT COUNT(*) AS `cnt` FROM `server_ips` WHERE `ip_number` = ? AND `server_id` != ?', $this->ip_number, $this->server_id)->cnt;
		if($cnt){
			$this->errors[] = tr('Already assigned to another server!');
		}
		if (!filter_var($this->ip_number, FILTER_VALIDATE_IP)) {
			$this->errors[] = tr('Invalid server ip');
		}
		return $this->errors == array();
	}


	public function delete(){
		$query = '
			SELECT
				COUNT(*) AS `cnt`
			FROM
				`reseller_props`
			WHERE
				`reseller_ips` LIKE ?
		';
		$cnt = mysql::getInstance()->doQuery($query, '%:'.$this->ip_id.';%')->cnt;
		if($cnt > 0) {
			$this->errors[] = tr('Ip is already assigned to reseller!');
			return false;
		}
		return parent::delete();
	}
}
