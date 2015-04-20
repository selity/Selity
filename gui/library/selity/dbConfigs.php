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

final class dbConfigs{

	private $values = array();

	protected function __construct(){;}

	public static function getInstance(){
		static $instance = null;
		if($instance===null){
			$instance = new self();
			$sql = mysql::getInstance();
			$query = 'SELECT `name`, `value` FROM `config`';
			if (!$res = $sql->doQuery($query)) {
				throw new Exception(tr('Could not get config from database'));
			} else {
				while($row = $res->FetchRow()) {
					$instance->values[$row['name']] = $row['value'];
				}
			}
		}
		return $instance;
	}

	public function __get($name) {
		if(!isset($this->values[$name]))
			throw new Exception(tr("Config variable '%s' is missing!", $name));
		return $this->values[$name];
	}

	public function __set($name, $value) {
		$this->values[$name] = $value;
	}

	public function __unset($name) {
		unset($this->values[$name]);
	}

	public function save($name) {
		$sql = mysql::getInstance();
		if(isset($this->values[$name])){
			$query = 'REPLACE INTO `config` (`name`, `value`) VALUES (?, ?)';
			$sql->doQuery($query,array($name,$this->values[$name]));
		} else{
			$query = 'DELETE FROM `setting` WHERE `name` = ?';
			$sql->doQuery($query,$name);
		}
	}

	public function saveAll() {
		$sql = mysql::getInstance();
		foreach($this->values as $name=>$value){
			$this->save($name);
		}
	}
}
