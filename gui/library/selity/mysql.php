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


class mysql {
	private		$connection			= null;
	private		$lasterror			= '';
	protected	$transaction		= array();
	protected	$lastQuerry			= '';

	protected static $_sqlHost		= '';
	protected static $_sqlDB		= '';
	protected static $_sqlUser		= '';
	protected static $_sqlPass		= '';
	protected static $_sqlDriver	= 'mysql';

	protected static $plugins	= array();

	public static function registerPlugin($plugin){
		self::$plugins[] = $plugin;
	}

	public static function  __callStatic($method, $args){
		foreach (self::$plugins as $plugin) {
			$call = is_object($plugin) ? array($plugin, $method): $method;
			if (is_callable($call)){
				return call_user_func_array($call, $args);
			}
		}
		throw new Exception(sprintf('Method %s is not implemented in class %s, nor provided by any plugin', $method, __CLASS__));
	}

	public static function getInstance(){
		static $instance=null;
		if($instance===null)$instance=new self();
		return $instance;
	}

	public static function setHost($sqlHost){
		self::$_sqlHost = $sqlHost;
	}

	public static function setDB($sqlDB){
		self::$_sqlDB = $sqlDB;
	}

	public static function setUser($sqlUser){
		self::$_sqlUser = $sqlUser;
	}

	public static function setPass($sqlPass){
		self::$_sqlPass = $sqlPass;
	}

	private function __construct() {
		$this->connection = new PDO(self::$_sqlDriver.':host=' . self::$_sqlHost . ';dbname=' . self::$_sqlDB, self::$_sqlUser, self::$_sqlPass);
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}

	public function checkConnection(){
		return ($this->connection instanceof PDO);
	}

	public function beginTransaction() {
		if($this->transaction == array()){
			$this->connection->beginTransaction();
		}
		$this->transaction[] = '';
	}

	public function commit() {
		array_pop($this->transaction);
		if($this->transaction == array()){
			$this->connection->commit();
		}
	}

	public function rollback() {
		if ($this->transaction != array()){
			$this->connection->rollback();
			$this->transaction = array();
		}
	}

	protected function Prepare($sql) {
		$this->connection->setAttribute(PDO::MYSQL_ATTR_DIRECT_QUERY, false);
		return $this->connection->prepare($sql);
	}

	protected function Execute($query, $param = null) {
		if (is_array($param) && $param!=array())
			$ret = $query->execute($param);
		elseif (is_string($param) || is_int($param))
			$ret = $query->execute(array($param));
		else
			$ret = $query->execute();
		return $ret;
	}

	public function doQuery() {
		$params = func_get_args();
		$query = array_shift($params);
		$data = array();
		array_walk_recursive($params,function($v, $k) use (&$data){ $data[] = $v; });
		$this->lastQuerry = $query;
		//try{
			$query = $this->Prepare($query);
			$rs = $this->Execute($query, $data);
		//} catch (Exception $e) {
			//return false;
		//}
		if ( $rs ){
			$rs = new mysqlResult($query);
		}
		return $rs;
	}
	public function __destruct(){
		if($this->transaction){
			$this->rollback();
		}
	}
	public function lastId(){
		return $this->connection->lastInsertId();
	}
}

