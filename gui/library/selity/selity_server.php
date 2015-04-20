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
		if (!filter_var($this->server_ip, FILTER_VALIDATE_IP)) {
			$this->errors[] = tr('invalid server ip');
		}
		if($this->server_root_user == ''){
			$this->errors[] = tr('Root user name can not be empty');
		}
		if($this->server_root_pass == ''){
			$this->errors[] = tr('Server root password can not be empty');
		}
		return $this->errors == array();
	}
}

