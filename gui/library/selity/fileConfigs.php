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


final class fileConfigs{

	private $cFile = '/etc/selity/selity.conf';

	private $values = array();

	protected function __construct($path = null)	{
		if (!is_null($path)) {
			$this->cFile = $path;
		}
		$this->_parse();
	}

	protected function _parse(){
		if (($file = file_get_contents($this->cFile)) == false) {
			throw new Exception(sprintf('Cannot open \'%s\'', $this->cFile));
		}

		$count = preg_match_all('/(?imsU)^([^=\n]+)=([^\n]+)\n$/',$file, $vars);

		if(!$count){
			throw new Exception(sprintf('Invalid file \'%s\'', $this->cFile));
		}
		foreach($vars[1] as $k	=> $name){
			$this->values[trim($name)] = trim($vars[2][$k]);
		}
	}


	public static function getInstance(){
		static $instance=null;
		if($instance===null)$instance=new self;
		return $instance;
	}

	public function __get($name) {
		if(!isset($this->values[$name]))
			throw new Exception("Config variable '$name' is missing!");
		return $this->values[$name];
	}

	public function __set($name, $value) {
		//if(!isset($this->values[$name]))
			//throw new Exception("Config variable '$name' is missing!");
		$this->values[$name] = $value;
	}

	public function __unset($name) {
		if(!isset($this->values[$name]))
			throw new Exception("Config variable '$name' is missing!");
		unset($this->values[$name]);
	}

	//this configs are read only. Use dbConfigs instead.
	private function save($name) {}
}
