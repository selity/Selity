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

class selity_multicontainer{

	protected $mainTable	= null;
	protected $mainTableUid	= null;
	protected $otherTables	= array(/*table_name => linkedid*/);
	protected $values		= array();

	public function __construct($id = null){
		$mainTable		= $this->mainTable;
		$mainTableUid	= $this->mainTableUid;

		$this->values[$mainTable] = new selity_container($mainTable);
		if(!is_null($id))
			$this->values[$mainTable]->init($id);

		$otherTables	= array_keys($this->otherTables);
		foreach($otherTables as $table){
			$this->values[$table]		= new selity_container($table);
			try{
				$this->values[$table]->init($this->values[$mainTable]->$mainTableUid, $this->otherTables[$table]);
			} catch (Exception $e){
				$idField = $this->otherTables[$table];
				$idvalue = $this->values[$mainTable]->$mainTableUid;
				$this->values[$table]->$idField = $idvalue;
			}
		}
	}

	public function __get($var){
		$containers = array_keys($this->values);
		foreach($containers as $container){
			try{
				return $this->values[$container]->$var;
			} catch (Exception $e){}
		}
		throw new Exception (tr('Variable %s do not exists', $var));
	}

	public function __isset($var){
		$containers = array_keys($this->values);
		foreach($containers as $container){
			try{
				if(isset($this->values[$container]->$var)){
					return true;
				}
			} catch (Exception $e){}
		}
		return false;
	}

	public function __set($var, $value){
		$containers = array_keys($this->values);
		foreach($containers as $container){
			try{
				$this->values[$container]->$var = $value;
				return;
			} catch (Exception $e){}
		}
		throw new Exception (tr('Variable %s do not exists', $var));
	}

	public function __unset($var){
		$containers = array_keys($this->values);
		foreach($containers as $container){
			try{
				unset($this->values[$container]->$var);
				return;
			} catch (Exception $e){}
		}
		throw new Exception (tr('Variable %s do not exists', $var));
	}

	public function save(){
		$containers = array_keys($this->values);
		mysql::getInstance()->beginTransaction();
		$this->values[$this->mainTable]->save();
		$uid = $this->values[$this->mainTable]->{$this->mainTableUid};
		foreach($containers as $container){
			if($container == $this->mainTable) continue;
			if($this->values[$container]->{$this->otherTables[$container]} != $uid){
				$this->values[$container]->{$this->otherTables[$container]} = $uid;
			}
			try{
				$this->values[$container]->save();
			} catch (Exception $e){
				mysql::getInstance()->rollback();
				return false;
			}
		}
		mysql::getInstance()->commit();
		return true;
	}

	public function delete(){
		if($this->values[$this->mainTable]->{$this->mainTableUid} == null){
			return true;
		}
		$containers = array_keys($this->values);
		mysql::getInstance()->beginTransaction();
		foreach($containers as $container){
			try{
				$this->values[$container]->delete();
			} catch (Exception $e){
				mysql::getInstance()->rollback();
				return false;
			}
		}
		mysql::getInstance()->commit();
		return true;
	}

	public function validate(){
		return true;
	}

	public function getMessage(){
		sort($this->errors);
		return $this->errors;
	}
}
