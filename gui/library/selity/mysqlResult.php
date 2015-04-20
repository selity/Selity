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


final class mysqlResult{

	private $_result			= null;
	private $_fields			= null;
	protected static $plugins	= array();

	public function __construct($result) {
		if (!$result instanceof PDOStatement)
			return false;
		$this->_result = $result;
	}

	public static function registerPlugin($plugin){
		self::$plugins[] = $plugin;
	}

	 public function  __call($method, $args){
		foreach (self::$plugins as $plugin) {
			$call = is_object($plugin) ? array($plugin, $method): $method;
			if (is_callable($call)){
				return call_user_func_array($call, $args);
			}
		}
		throw new Exception(sprintf('Method %s is not implemented in class %s, nor provided by any plugin', $method, __CLASS__));
	}

	public function getAllRows($mode = PDO::FETCH_ASSOC) {
		$mode = in_array($mode, array(PDO::FETCH_ASSOC, PDO::FETCH_NUM)) ? $mode : PDO::FETCH_ASSOC;
		return $this->_result->fetchAll($mode);
	}

	public function countRows(){
		return $this->_result->rowCount();
	}

	public function fetchRow() {
		return $this->_result->fetch(PDO::FETCH_ASSOC);
	}

	public function nextRow() {
		$this->_fields = $this->_result->fetch(PDO::FETCH_ASSOC);
	}

	public function __get($param) {
		if($this->_fields===null){
			$this->_fields = $this->_result->fetch(PDO::FETCH_ASSOC);
		}
		if ($param == 'EOF') {
			if ($this->_result->rowCount() == 0) {
				return true;
			}
			return !is_null($this->_fields) && !is_array($this->_fields);
		}
		if(array_key_exists($param, $this->_fields)){
			return $this->_fields[$param];
		}
		throw new Exception(sprintf('Unknown parameter: %s', $param));
	}
}
