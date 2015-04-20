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

class selity_language{

	protected $DefaultLanguage					= 'us_US';
	protected $mylanguage_dir					= null;

	public static function getInstance(){
		static $instance=NULL;
		if($instance===NULL)$instance=new self;
		return $instance;
	}

	protected function __construct(){
		$this->objectdir=new file;
		$_SESSION['PreserveValue'] = 'CurentLanguage';
		$this->setLanguageDir(configs::getInstance()->GUI_ROOT_DIR.'/i18n/locales');
		$this->getDisponibleLanguages();
		$_SESSION['CurentLanguage'] = $this->getRequestedLanguage();
		$this->setLanguage($_SESSION['CurentLanguage']);
	}

	protected function setLanguageDir($dir){
		if(!is_dir($dir)){
			throw new Exception(tr('Directory not found: %s!',$dir));
			return false;
		}
		$this->mylanguage_dir=$dir.'/';
		return true;
	}

	public function getDisponibleLanguages(){
		$this->objectdir->setCurrentDir($this->mylanguage_dir);
		$this->mylanguages=$this->objectdir->getDirContent('dir', '/^.._..$/');
		if($this->mylanguages==array()){
			throw new Exception(tr('Languages not found!'));
		}
		return $this->mylanguages;
	}

	protected function getRequestedLanguage(){
		if(array_key_exists('language', $_POST)){
			return $_POST['language'];
		}
		elseif(array_key_exists('CurentLanguage', $_SESSION)){
			return $_SESSION['CurentLanguage'];
		}
		else return $this->DefaultLanguage;
	}

	public function setLanguage($language){
		if(!$language){
			$language = $this->DefaultLanguage;
		}
		setlocale( LC_MESSAGES, "$language.utf8");
		bindtextdomain($language, $this->mylanguage_dir.'/nocache');
		bindtextdomain($language, $this->mylanguage_dir);
		bind_textdomain_codeset($language, 'UTF-8');
		textdomain($language);

		$_SESSION['CurentLanguage'] = $language;
		return true;
	}

}

function tr(){
	$data = func_get_args();
	$text = array_shift($data);
	$text = gettext($text);
	return vsprintf($text, $data);
}

