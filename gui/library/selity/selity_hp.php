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


class selity_hp{

	private $props = array(
		'id'			=> null,
		'reseller_id'	=> null,
		'name'			=> '',
		//'limits'
		'max_als'		=> -1,
		'max_sub'		=> -1,
		'max_mail'		=> -1,
		'max_ftp'		=> -1,
		'max_sqldb'		=> -1,
		'max_sqlu'		=> -1,
		'max_traff'		=> null,
		'max_disk'		=> null,
		'php'			=> 'no',
		'cgi'			=> 'no',
		'support'		=> 'no',
		//
		'description'	=> '',
		'price'			=> 0,
		'setup_fee'		=> 0,
		'value'			=> 0,
		'payment'		=> 0,
		'status'		=> 0,
		'tos'			=> '',
	);

	private $mode		= 'reseller';
	private $errors		= array();


	public function __set($var, $value){
		if(array_key_exists($var, $this->props)){
			$this->props[$var] = $value;
			return;
		}
	}

	public function __get($var){
		if(array_key_exists($var, $this->props)){
			return $this->props[$var];
		}
	}

	public function setMode($mode){
		if(!in_array($mode, array('admin', 'reseller'))){
			throw new Exception(tr('%s invalid mode.', $mode));
		} else {
			$this->mode = $mode;
		}
	}

	public function validate(){
		if (!is_numeric($this->props['reseller_id']))
			$this->errors[] = tr('No owner provided!');
		if (!$this->props['name'])
			$this->errors[] = tr('Incorrect template name length!');
		if (!$this->props['description'])
			$this->errors[] = tr('Incorrect template description length!');
		if (!is_numeric($this->props['price']))
			$this->errors[] = tr('Incorrect price syntax! Example: 9.99');
		if (!is_numeric($this->props['setup_fee']))
			$this->errors[] = tr('Incorrect setup fee syntax! Example: 9.99');
		// limits start
			if (!is_numeric($this->props['max_als']) || $this->props['max_als'] < -1)
				$this->errors[] = tr('Incorrect domain limit!');
			if (!is_numeric($this->props['max_sub']) || $this->props['max_sub'] < -1)
				$this->errors[] = tr('Incorrect subdomains limit!');
			if (!is_numeric($this->props['max_mail']) || $this->props['max_mail'] < -1)
				$this->errors[] = tr('Incorrect mail accounts limit!');
			if (!is_numeric($this->props['max_ftp']) || $this->props['max_ftp'] < -1)
				$this->errors[] = tr('Incorrect FTP accounts limit!');
			if (!is_numeric($this->props['max_sqlu']) || $this->props['max_sqlu'] < -1)
				$this->errors[] = tr('Incorrect SQL users limit!');
			if (!is_numeric($this->props['max_sqldb']) || $this->props['max_sqldb'] < -1)
				$this->errors[] = tr('Incorrect SQL databases limit!');
			if (!(is_numeric($this->props['max_traff']) && $this->props['max_traff'] >=0 || is_null($this->props['max_traff'])))
				$this->errors[] = tr('Incorrect traffic limit!');
			if (!(is_numeric($this->props['max_disk']) && $this->props['max_disk'] >=0 || is_null($this->props['max_disk'])))
				$this->errors[] = tr('Incorrect disk quota limit!');
			if(!in_array($this->props['php'], array('no', 'yes', 0, 1)))
				$this->errors[] = tr('Incorrect php "%s" property!', $this->props['php']);
			if(!in_array($this->props['cgi'], array('no', 'yes', 0, 1)))
				$this->errors[] = tr('Incorrect cgi "%s" property!', $this->props['cgi']);
			if(!in_array($this->props['support'], array('no', 'yes', 0, 1)))
				$this->errors[] = tr('Incorrect support "%s" property!', $this->props['support']);
		// limits end
		return $this->errors == array();
	}

	public function get_errors(){
		return $this->errors;
	}

	private function _nameExists(){
		$sql = mysql::getInstance();
		if($this->mode == 'admin'){
			$query = '
				SELECT
					COUNT(*) AS `cnt`
				FROM
					`hosting_plans` AS `t1`
				LEFT JOIN
					`admin` AS `t2`
				ON
					`t1`.`reseller_id` = `t2`.`admin_id`
				WHERE
					`t2`.`admin_type` = ?
				AND
					`t1`.`name` = ?
			';
			$res = $sql->doQuery($query, 'admin', $this->props['name']);
		} else {
			$query = 'SELECT COUNT(*) AS `cnt` FROM `hosting_plans` WHERE `reseller_id` = ? AND `name` = ?';
			$res = $sql->doQuery($query, $this->props['reseller_id'], $this->props['name']);
		}
		return $res->cnt == 1;
	}

	private function _idExists(){
		$sql = mysql::getInstance();
		if($this->mode == 'admin'){
			$query = '
				SELECT
					COUNT(*) AS `cnt`
				FROM
					`hosting_plans` AS `t1`
				LEFT JOIN
					`admin` AS `t2`
				ON
					`t1`.`reseller_id` = `t2`.`admin_id`
				WHERE
					`t2`.`admin_type` = ?
				AND
					`t1`.`id` = ?
			';
			$res = $sql->doQuery($query, 'admin', $this->props['id']);
		} else {
			$query = 'SELECT COUNT(*) AS `cnt` FROM `hosting_plans` WHERE `reseller_id` = ? AND `id` = ?';
			$res = $sql->doQuery($query, $this->props['reseller_id'], $this->props['id']);
		}
		return $res->cnt == 1;
	}

	public function delete(){
		$sql = mysql::getInstance();
		if($this->mode == 'admin'){
			$query = '
				DELETE
					`hosting_plans`
				FROM
					`hosting_plans`
				LEFT JOIN
					`admin`
				ON
					`hosting_plans`.`reseller_id` = `admin`.`admin_id`
				WHERE
					`admin`.`admin_type` = ?
				AND
					`hosting_plans`.`id` = ?
			';
			$res = $sql->doQuery($query, 'admin', $this->props['id']);
		} else {
			$query = '
				DELETE FROM
					`hosting_plans`
				WHERE
					`id` = ?
				AND
					`reseller_id` = ?
			';
			$res = $sql->doQuery($query, $this->props['reseller_id'], $this->props['id']);
		}
		return ($res->countRows() === 1);
	}

	public function save(){
		if(!$this->validate()){
			return false;
		}
		if(!is_null($this->props['id']) && !$this->_idExists()){
			$this->errors[] = tr('Hosting plan with id "%s" do not exists!', $this->props['id']);
			return false;
		}

		if(is_null($this->props['id']) && $this->_nameExists()){
			$this->errors[] = tr('A hosting plan with same name "%s" already exists!', $this->props['name']);
			return false;
		}

		$sql = mysql::getInstance();

		$query = '
			REPLACE INTO
				`hosting_plans`
			(
				`id`, `reseller_id`, `name`,
				`description`, `max_als`, `max_sub`,
				`max_mail`, `max_ftp`, `max_sqldb`,
				`max_sqlu`, `max_disk`, `max_traff`,
				`php`, `cgi`, `support`,
				`price`, `setup_fee`, `value`,
				`payment`, `status`
			) VALUES (
				?, ?, ?,
				?, ?, ?,
				?, ?, ?,
				?, ?, ?,
				?, ?, ?,
				?, ?, ?,
				?, ?
			)
		';
		return $res = mysql::getInstance()->doQuery($query,
			$this->props['id'], $this->props['reseller_id'], $this->props['name'],
			$this->props['description'], $this->props['max_als'], $this->props['max_sub'],
			$this->props['max_mail'], $this->props['max_ftp'], $this->props['max_sqldb'],
			$this->props['max_sqlu'], $this->props['max_disk'], $this->props['max_traff'],
			$this->props['php'], $this->props['cgi'], $this->props['support'],
			$this->props['price'], $this->props['setup_fee'], $this->props['value'],
			$this->props['payment'], $this->props['status']
		);
		return $res->countRows();
	}

	public function loadData(){
		$sql = mysql::getInstance();
		if($this->mode == 'admin'){
			$query = '
				SELECT
					`hosting_plans`.*
				FROM
					`hosting_plans`
				LEFT JOIN
					`admin`
				ON
					`hosting_plans`.`reseller_id` = `admin`.`admin_id`
				WHERE
					`admin`.`admin_type` = ?
				AND
					`hosting_plans`.`id` = ?
			';
			$rs = $sql->doQuery($query, 'admin', $this->props['id']);
		} else {
			$query = '
				SELECT
					*
				FROM
					`hosting_plans`
				WHERE
					`id` = ?
				AND
					`reseller_id` = ?
			';
			$rs = $sql->doQuery($query, $this->id, $this->reseller_id);
		}
		if ($rs->countRows() == 0) {
			$this->errors[] = tr('Hosting plan with id "%s" do not exists!', $this->id);
			return false;
		} else {
			$this->props = $rs->fetchRow();
			return true;
		}
	}
}
