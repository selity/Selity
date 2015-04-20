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


class file{

	function __construct($dir = null){
		if(!is_null($dir))
			$this->setCurrentDir($dir);
	}

	function setCurrentDir($dir){
		if(substr($dir,-1)!=='/')$dir.='/';
		$this->current_dir=$dir;
	}

	function getDirContent($what='', $regexp = "/.{0,}/"){

		$dirs = scandir($this->current_dir,0);

		if(!$dirs){
			throw new Exception(tr('Directory not found %s!', $this->current_dir));
		}

		switch ($what){
			case 'dir':
				foreach($dirs as $key=>$dir){
					if(!is_dir($this->current_dir.$dir) || $dir=='.' || $dir=='..' || !preg_match($regexp, $dir)){
						unset($dirs[$key]);
					}
				}
				return ($dirs);
				break;
			case 'file':
				foreach($dirs as $key=>$dir){
					 var_dump(preg_match($regexp, $dir));
					if(!is_file($this->current_dir.$dir) || !preg_match($regexp, $dir)){
						unset($dirs[$key]);
					}
				}
				return ($dirs);
				break;
			default:
				foreach($dirs as $key=>$dir){
					if($dir=='.' || $dir=='..' || !preg_match($regexp, $dir)){
						unset($dirs[$key]);
					}
				}
				return ($dirs);
		}
	}
}

