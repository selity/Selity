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


class selity_server extends selity_container{

	protected $tableName	= 'servers';

	public function __construct($id = null){
		parent::__construct();
		if(!is_null($id)){
			$this->init($id);
		}
	}

	public function validate(){
		if($this->server_name == ''){
			$this->errors[] = tr('Server name can not be empty');
		}
		if($this->server_root_user == ''){
			$this->errors[] = tr('Root user name can not be empty');
		}
		if($this->server_root_pass == ''){
			$this->errors[] = tr('Server root password can not be empty');
		}
		return $this->errors == array();
	}

	public function save(){
		mysql::getInstance()->beginTransaction();
		parent::save();
		$ip = new selity_ips();
		try{
			$ip->init($this->server_ip, 'ip_number');
		} catch (Exception $e){
			$ip->ip_number = $this->server_ip;
		}
		$ip->server_id = $this->server_id;
		if($ip->save()){
			mysql::getInstance()->commit();
			return true;
		} else {
			$this->errors[] = $ip->getMessage();
			mysql::getInstance()->rollback();
			return false;
		}
	}

	public function delete(){
		mysql::getInstance()->beginTransaction();
		$query = '
			SELECT
				COUNT(*) AS `cnt`
			FROM
				`reseller_props`
			WHERE
				`server_ids` LIKE ?
		';
		$cnt = mysql::getInstance()->doQuery($query, '%:'.$this->server_id.';%')->cnt;
		if($cnt > 0) {
			$this->errors[] = tr('Server is already assigned to reseller!');
			return false;
		}
		$query = '
			SELECT
				`ip_id`
			FROM
				`server_ips`
			WHERE
				`server_id` = ?
		';
		$rs = mysql::getInstance()->doQuery($query, $this->server_id);
		$rv = true;
		while(!$rs->EOF){
			$ip = new selity_ips();
			$ip->init($rs->ip_id);
			if(!$ip->delete()){
				$this->errors[] = $ip->getMessage();
				$rv = false;
			}
			$rs->nextRow();
		}
		if(!$rv){
			mysql::getInstance()->rollback();
			return false;
		}
		$rv &= parent::delete();
		mysql::getInstance()->commit();
		return true;
	}
}

