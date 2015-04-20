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


final class configs{

	private $fileRef	= null;
	private $dbRef		= null;

	protected function __construct(){;}

	public static function getInstance(){
		static $instance = null;
		if($instance===null){
			$instance=new self;
		}
		return $instance;
	}

	public function initFileConfig(){
		$this->fileRef = fileConfigs::getInstance();
	}

	public function initDbConfig(){
		$this->dbRef = dbConfigs::getInstance();
	}

	public function __get($name) {
		if($this->fileRef){
			try{
				return $this->fileRef->$name;
			} catch (Exception $e) {
				if($this->dbRef){
					return $this->dbRef->$name;
				}
			}
		}
		throw new Exception('Configs not loaded!');
	}

	public function __set($name, $value) {
		try{
			if($this->dbRef){
				$this->dbRef->$name = $value;
				return;
			}
		} catch (Exception $e) {
			if($this->fileRef){
				$this->fileRef->$name = $value;
				return;
			}
		}
		throw new Exception('Configs not loaded!');
	}

	public function __unset($param) {
		if($this->fileRef){
			try{
				unset($this->fileRef->$name);
			} catch (Exception $e) {
				if($this->dbRef){
					unset($this->dbRef->$name);
				}
			}
		}
		throw new Exception('Configs not loaded!');

	}

	public function __isset($name) {
		if($this->fileRef){
			try{
				$this->fileRef->$name;
				return true;
			} catch (Exception $e) {
				if($this->dbRef){
					try{
						$this->dbRef->$name;
						return true;
					} catch (Exception $e) {
						return false;
					}
				}
			}
		}
		return false;
	}


	public function save($name) {
		if($this->dbRef){
			$this->dbRef->save($name);
		} else {
			throw new Exception('Configs not loaded!');
		}
	}

	public function saveAll() {
		if($this->dbRef){
			$this->dbRef->saveAll();
		} else {
			throw new Exception(tr('Configs not loaded!'));
		}
	}
}
