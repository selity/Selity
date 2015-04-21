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


class selity_user extends selity_multicontainer{

	protected $mainTable	=	'admin';
	protected $mainTableUid	=	'admin_id';
	protected $otherTables	=	array(
									'user_gui_props'	=> 'user_id'
								);

	protected $userType		=	'client';

	protected $values		=	array();
	protected $errors		=	array();
	protected $passModified	=	false;

	public function __construct($id = null){
		parent::__construct($id);
		if(is_null($id)){
			$this->admin_type = $this->userType;
		}
	}

	public function __get($var){
		return parent::__get($var);
	}

	public function __isset($var){
		return parent::__isset($var);
	}

	public function __set($var, $value){
		if($var == 'admin_pass'){
			$this->passModified = true;
		}
		parent::__set($var, $value);
	}

	public function __unset($var){
		trigger_error('Reseller properties are not removable', E_USER_ERROR);
	}

	public function checkPassword(){
		if (($this->passModified || is_null($this->admin_id)) && !chk_password($this->admin_pass)) {
			if(configs::getInstance()->PASSWD_STRONG){
				$this->errors[] = tr('The password must be at least %s long and contain letters and numbers to be valid.', configs::getInstance()->PASSWD_CHARS);
			} else {
				$this->errors[] = tr('Password data is shorter than %s signs or includes not permitted signs!', configs::getInstance()->PASSWD_CHARS);
			}
			return false;
		}
		return true;
	}

	public function checkEmail(){
		if (!chk_email($this->email)) {
			$this->errors[] = tr('Incorrect email syntax!');
			return false;
		}
		return true;
	}

	public function validate(){
		$this->checkPassword();
		$this->checkEmail();
		if(is_null($this->admin_id)){
			if(mysql::getInstance()->doQuery('SELECT COUNT(*) AS `cnt` FROM `admin` WHERE `email`= ?', $this->email)->cnt){
				$this->errors[] = tr('Email %s already exist!', $this->email);
			}
		}
		return $this->errors == array();
	}

	public function save(){
		if(!$this->validate()){
			return false;
		}
		if($this->passModified){
			$this->admin_pass = md5($this->admin_pass);
			$this->passModified = false;
		}
		mysql::getInstance()->beginTransaction();
		$result = parent::save();
		$newname = configs::getInstance()->SYSTEM_USER_PREFIX . ($this->admin_id + configs::getInstance()->SYSTEM_USER_MIN_UID);
		if ($this->admin_name != $newname){
			$this->admin_name = $newname;
		}
		$result &= parent::save();
		mysql::getInstance()->commit();
		return $result;
	}


}
