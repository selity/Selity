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


class selity_container{

	static $tableStruct = array(
		/*
		 * tableName	=> array(
		 * 							uniqueIdName	=> null,
		 * 							defaultValues	=> array(field	=> default value),
		 * )
		 */
	);

	protected $tableName	= null;
	protected $values	 	= null;
	protected $uid		 	= null;
	protected $modified		= false;

	protected $errors		= array();

	public function __construct($table = null){
		if($table != null)
			$this->tableName = $table;
		if(!array_key_exists($this->tableName, self::$tableStruct)){
			self::loadTableStruct($this->tableName);
		}
		$this->values	= self::$tableStruct[$this->tableName]['defaultValues'];
		$this->uid		= self::$tableStruct[$this->tableName]['uniqueIdName'];
	}

	public function init($id, $field = null){
		$uid = $field == null ? $this->uid : $field;
		$rs = mysql::getInstance()->doQuery('SELECT * FROM `'. $this->tableName .'` WHERE '. $uid . ' = ?', $id);
		if($rs->countRows()){
			$this->values = $rs->fetchRow();
		} else {
			throw new Exception(tr('In table %s there is no record for %s with the value %s', $this->tableName, $uid, $id));
		}
	}


/////////////
	public function __set($var, $value){
		if(array_key_exists($var, $this->values)){
			$this->values[$var] = $value;
		} else {
			throw new Exception (tr('Variable %s do not exists', $var));
		}
		$this->modified = true;
	}

	public function __isset($var){
		if(array_key_exists($var, $this->values)){
			return is_null($this->values[$var]);
		} else {
			throw new Exception (tr('Variable %s do not exists', $var));
		}
	}

	public function __get($var){
		if(array_key_exists($var, $this->values)){
			return $this->values[$var];
		} else {
			throw new Exception (tr('Variable %s do not exists', $var));
		}
	}

	public function __unset($var){
		if(array_key_exists($var, $this->values)){
			$this->values[$var] == null;
		} else {
			throw new Exception (tr('Variable %s do not exists', $var));
		}
		$this->modified = true;
	}

	public function delete(){
		$query = 'DELETE FROM `'. $this->tableName .'` WHERE `'. $this->uid. '` = ?';
		mysql::getInstance()->doQuery($query, $this->values[$this->uid]);
		$this->values	= self::$tableStruct[$this->tableName]['defaultValues'];
		$this->modified = true;
		return true;
	}

	public function save(){
		if(!$this->modified){
			return true;
		}
		if(is_callable(array($this, 'validate')) && !$this->validate()){
			return false;
		}
		$fields = array_keys($this->values);
		array_walk($fields, function(&$value, $key){
			$value = '`'.$value.'`';
		});
		$fields = implode(',', $fields);
		$values = array_values($this->values);
		$placeholders = implode(',', array_pad(array(), count($this->values), '?'));
		$query = '
			REPLACE INTO
				`'. $this->tableName .'`
			(
				' . $fields .'
			) VALUES (
				'.$placeholders.'
			)';
		array_unshift($values, $query);
		call_user_func_array(array(mysql::getInstance(), 'doQuery'), $values);
		if(is_null($this->values[$this->uid])){
			$this->values[$this->uid] = mysql::getInstance()->lastId();
		}
		$this->modified = false;
		return true;
	}

	protected static function loadTableStruct($table){
		self::$tableStruct[$table]['uniqueIdName']	= null;
		self::$tableStruct[$table]['defaultValues']	= array();
		self::$tableStruct[$table]['validators']	= array();
		$uniqueIdName	= null;
		$rs = mysql::getInstance()->doQuery('DESCRIBE '. $table);
		$fields = $rs->getAllRows();
		foreach($fields as $field){
			self::$tableStruct[$table]['defaultValues'][$field['Field']] = (preg_match('/int.*/', $field['Type']) ? (int)$field['Default'] : $field['Default']);
			if($field['Key'] === 'PRI' && $field['Extra'] === 'auto_increment'){
				$uniqueIdName = $field['Field'];
				self::$tableStruct[$table]['defaultValues'][$field['Field']] = null;
			}
		}
		if(is_null($uniqueIdName))
			throw new Exception (tr('No unique id for table %s', $table));
		self::$tableStruct[$table]['uniqueIdName'] = $uniqueIdName;
	}

	public function getMessage(){
		return $this->errors;
	}
}

