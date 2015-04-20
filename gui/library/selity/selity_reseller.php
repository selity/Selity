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

class selity_reseller extends selity_user{

	protected $mainTable	=	'admin';
	protected $mainTableUid	=	'admin_id';
	protected $otherTables	=	array(
									'user_gui_props'	=> 'user_id',
									'reseller_props'	=> 'reseller_id'
								);
	protected $userType		=	'reseller';
	protected $values		=	array();
	protected $errors		=	array();
	protected $passModified	=	false;


	public function __construct($id = null){
		parent::__construct($id);
		$ips = @unserialize($this->values['reseller_props']->reseller_ips);
		if(!$ips){
			$this->values['reseller_props']->reseller_ips = array();
		} else {
			$this->values['reseller_props']->reseller_ips = $ips;
		}
	}

	public function validate(){
		parent::validate();

		if(is_null($this->admin_id)){
			if(mysql::getInstance()->doQuery('SELECT COUNT(*) AS `cnt` FROM `admin` WHERE `email`= ?', $this->email)->cnt){
				$this->errors[] = tr('Email %s already exist!', $this->email);
			}
		}

		if($this->max_usr === '' || (int) $this->max_usr < 0){
			$this->errors[] = tr('Incorrect user limit %s!', $this->max_usr);
		}
		if($this->max_als === '' || (int) $this->max_als < -1){
			$this->errors[] = tr('Incorrect aliases limit %s!', $this->max_als);
		}
		if($this->max_sub === '' || (int) $this->max_sub < -1){
			$this->errors[] = tr('Incorrect subdomains limit %s!', $this->max_sub);
		}
		if($this->max_ftp === '' || (int) $this->max_ftp < -1){
			$this->errors[] = tr('Incorrect FTP accounts limit %s!', $this->max_ftp);
		}
		if($this->max_mail === '' || (int) $this->max_mail < -1){
			$this->errors[] = tr('Incorrect mail accounts limit %s!', $this->max_mail);
		}
		if($this->max_sqldb === '' || (int) $this->max_sqldb < -1){
			$this->errors[] = tr('Incorrect SQL databases limit %s!', $this->max_sqldb);
		}
		if($this->max_sqlu === '' || (int) $this->max_sqlu < -1){
			$this->errors[] = tr('Incorrect SQL users limit %s!', $this->max_sqlu);
		}
		if($this->max_sqldb >= 0 && (int) $this->max_sqlu < -1){
			$this->errors[] = tr('Enabling database usage require sql users!');
		}
		if($this->max_traff === '' || (int) $this->max_traff < 0){
			$this->errors[] = tr('Incorrect trafic limit %s!', $this->max_traff);
		}
		if($this->max_disk === '' || (int) $this->max_disk < 0){
			$this->errors[] = tr('Incorrect disk quota limit %s!', $this->max_disk);
		}
		if(!in_array($this->php, array('yes', 'no'))){
			$this->errors[] = tr('Incorrect php value %s!', $this->php);
		}
		if(!in_array($this->cgi, array('yes', 'no'))){
			$this->errors[] = tr('Incorrect cgi value %s!', $this->cgi);
		}
		if(!in_array($this->support, array('yes', 'no'))){
			$this->errors[] = tr('Incorrect support system value %s!', $this->support);
		}

		$ips = @unserialize($this->reseller_ips);
		$ips = $ips === false ? $this->reseller_ips : $ips;

		if($ips == array()){
			$this->errors[] = tr('You must assign at least one IP number for a reseller!');
		}

		foreach($ips as $ip => $value){
			if(mysql::getInstance()->doQuery('SELECT COUNT(*) AS `cnt` FROM `server_ips` WHERE `ip_id` = ?', $ip)->cnt == 0){
				$this->errors[] = tr('Invalid ip id %s', $ip);
			}
		}
		return $this->errors == array();
	}

	public function save(){
		$this->values['reseller_props']->reseller_ips = serialize($this->reseller_ips);
		mysql::getInstance()->beginTransaction();
		$result = parent::save();
		mysql::getInstance()->commit();
		$this->values['reseller_props']->reseller_ips = unserialize($this->reseller_ips);
		return $result;
	}

	public function __set($var, $value){
		if($var == 'reseller_ips'){
			trigger_error('Reseller ip`s should be added using addIP method', E_USER_ERROR);
		}
		if(!is_null($this->admin_id) && preg_match('/^max_(?!usr)(.*)$/', $var, $service)){
			if(!$this->checkServiceLimit($service[1], $value)){
				return;
			}
		} elseif(!is_null($this->admin_id) && in_array($var, array('php', 'cgi', 'support'))){
			if(!$this->checkEnabled($var, $value)){
				return;
			}
		}
		parent::__set($var, $value);
	}

	public function checkEnabled($service, $value){
		if(!in_array($value, array('no', 'yes'))){
			$this->errors[] = tr('Invalid value for service %s!', $service);
			return false;
		} elseif($value == 'no') {
			$query = '
				SELECT
					COUNT(*) AS `cnt`
				FROM
					`hosting_plans`
				WHERE
					`reseller_id` = ?
				AND
					`'.$service.'` = ?
			';
			$cnt = (int) mysql::getInstance()->doQuery($query, $this->admin_id, 'yes')->cnt;
			if($cnt > 0) {
				$this->errors[] = tr('Reseller have hosting plans with service %s enabled!', $service);
				$rv =  false;
			}
		}
	}

	public function checkServiceLimit($service, $newValue){
		$rv = true;
		$query = '
			SELECT
				IF(`max_'.$service.'` = 0, 0, SUM(`max_'.$service.'`)) AS `max`
			FROM
				`user_system_props`
			LEFT JOIN
				`admin`
			ON
				`admin`.`admin_id` = `user_system_props`.`user_admin_id`
			WHERE
				`created_by` = ?
		';
		$max = mysql::getInstance()->doQuery($query, $this->admin_id)->max;
		if(is_null($max)){
		} elseif($max == 0 && $newValue > 0) {
			$this->errors[] = tr('Reseller already assigned unlimited resources for service %s!', $service);
			$rv =  false;
		} elseif($max > $newValue && $newValue > 0) {
			$this->errors[] = tr('Reseller already assigned more resources (%s) then new value "%s", for service %s!', $max, $newValue, $service);
			$rv =  false;
		}
		$query = '
			SELECT
				IF(`max_'.$service.'` = 0, 0, MAX(`max_'.$service.'`)) AS `max`
			FROM
				`hosting_plans`
			WHERE
				`reseller_id` = ?
		';
		$max = mysql::getInstance()->doQuery($query, $this->admin_id)->max;
		if(is_null($max)){
		} elseif($max == 0  && $newValue > 0){
			$this->errors[] = tr('Reseller have hosting plans with unlimited resources for service %s!', $service);
			$rv =  false;
		} elseif($max > $newValue && $newValue > 0) {
			$this->errors[] = tr('Reseller have hosting plans with more resources (%s) then new value "%s", for service %s!', $max, $newValue, $service);
			$rv =  false;
		}
		return $rv;
	}

	public function delete(){
		$sql = mysql::getInstance();
		$query = 'SELECT COUNT(*) as `cnt` FROM `admin` WHERE `created_by` = ?';
		$cnt = $sql->doQuery($query, $this->admin_id)->cnt;
		if($cnt){
			$this->errors[] = tr('This user have %s users! Not removed', $cnt);
			return false;
		}
		$sql->beginTransaction();
		$query = 'DELETE FROM `email_tpls` WHERE `owner_id` = ?';
		$sql->doQuery($query, $this->admin_id);
		$query = 'DELETE FROM `orders` WHERE `user_id` = ?';
		$sql->doQuery($query, $this->admin_id);
		$query = 'DELETE FROM `orders_settings` WHERE `user_id`  = ?';
		$sql->doQuery($query, $this->admin_id);
		$query = 'DELETE FROM `hosting_plans` WHERE `reseller_id` = ?';
		$sql->doQuery($query, $this->admin_id);
		parent::delete();
		$sql->commit();
		return true;
	}

	public function addIP($ip){
		if(!array_key_exists($ip, $this->reseller_ips)){
			$ips = $this->reseller_ips;
			$ips[$ip] = $ip;
			parent::__set('reseller_ips', $ips);
		}
	}

	public function removeIP($ip){
		if(array_key_exists($ip, $this->reseller_ips)){
			$ips = $this->reseller_ips;
			$ip = (int) $ip;
			$query = '
				SELECT
					COUNT(*) AS `cnt`
				FROM
					`user_system_props`
				LEFT JOIN
					`admin`
				ON
					`admin_id` = `user_admin_id`
				WHERE
					`created_by` = ?
				AND
					`user_ips` LIKE "%:'.$ip.';%"
			';
			$cnt = mysql::getInstance()->doQuery($query, $this->admin_id)->cnt;
			if($cnt > 0) {
				$query = 'SELECT `ip_number` FROM `server_ips` WHERE `ip_id` = ?';
				$ipNumber = mysql::getInstance()->doQuery($query, $ip)->ip_number;
				$this->errors[] = tr('Reseller have already assigned to users ip %s!', $ipNumber);
				return ;
			}
			unset($ips[$ip]);
			parent::__set('reseller_ips', $ips);
		}
	}
}
