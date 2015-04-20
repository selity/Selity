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


class template{

	protected $mytemplate_dir					= false;
	protected $curent_mytemplate_path			= '';
	protected $relative_mytemplates_dir			= '';
	protected $curent_relative_mytemplate_dir	= '';
	protected $mytemplates						= array();
	protected $curent_mytemplate				= false;

	protected $DefaultTemplate					= 'default';

	protected $pages							= array();
	protected $curent_module					= '';
	protected $output							= '';
	protected $messages							= array();
	protected $sections							= array();
	protected $variables						= array();
	protected $constructor						= array();
	protected $repeats							= array();

	public static function getInstance(){
		static $instance=NULL;
		if($instance===NULL)$instance=new self;
		return $instance;
	}

	protected function __construct(){
		$this->objectdir=new file;
		$_SESSION['PreserveValue'] = 'CurentTemplate';
		$this->setTemplateDir(configs::getInstance()->GUI_ROOT_DIR.'/public/themes');
		$this->getDisponibleTemplate();
		$_SESSION['CurentTemplate'] = $this->getRequestedTemplate();
		$this->setTemplate($_SESSION['CurentTemplate']);
	}

	protected function setTemplateDir($dir){
		if(!is_dir($dir)){
			throw new Exception(tr('Directory not found: %s!',$dir));
			return false;
		}
		$this->mytemplate_dir=$dir.'/';
		$this->relative_mytemplates_dir=$dir;
		return true;
	}

	protected function getDisponibleTemplate(){
		$this->objectdir->setCurrentDir($this->mytemplate_dir);
		$this->mytemplates=$this->objectdir->getDirContent('dir');
		if($this->mytemplates==array()){
			throw new Exception(tr('Templates not found!'));
			return false;
		}
		return true;
	}

	protected function getRequestedTemplate(){
		if(array_key_exists('template', $_POST)){
			return $_POST['template'];
		}
		elseif(array_key_exists('CurentTemplate', $_SESSION) && $_SESSION['CurentTemplate']!=''){
			return $_SESSION['CurentTemplate'];
		}
		else return $this->DefaultTemplate;
	}

	public function setTemplate($mytemplate){
		if(!is_dir(($this->mytemplate_dir.$mytemplate))){
			throw new Exception(tr('Templates director %s not found!',$this->mytemplate_dir.$mytemplate));
		}
		if(substr($mytemplate,-1)!=='/')$mytemplate.='/';
		$this->curent_mytemplate=$mytemplate;
		$this->curent_mytemplate_path=$this->mytemplate_dir.$mytemplate;
		$this->curent_relative_mytemplate_dir=$this->relative_mytemplates_dir.'/'.$mytemplate;
		$_SESSION['CurentTemplate'] = $mytemplate;
		return true;
	}

	public function getTemplates(){return $this->mytemplates;}

	public function getCurentTemplate(){return $this->curent_mytemplate;}

//////////////////////////////////////////////////////////////////////////

	public function saveSection($section){
		$this->sections[] = $section;
	}

	public function saveVariable($variables){
		foreach($variables as $key=>$value){
			if(is_array($value))$this->saveVariable($value);
			else $this->variables[$key] = $value;
		}
	}


	public function saveRepeats($repeats){
		$this->repeats = array_merge_recursive($this->repeats,$repeats);
	}

	public function addMessage($messages){
		if(is_array($messages))
			foreach($messages as $message)
				$this->addMessage($message);
		else{
			//$this->messages[]=$messages;
			if(!array_key_exists('PageMessage', $_SESSION)){
				$_SESSION['PageMessage'] = array();
			}
			$_SESSION['PageMessage'][] = $messages;
		}
	}

//////////////////////////////////////////////////////////////////////////
	public function registerOutput(&$output){
		$this->pages[]=&$output;
	}

	protected function loadlayout($weblayout, $ext = '.html'){
		$page_content=file_get_contents($this->curent_mytemplate_path.$weblayout.$ext);
		if($page_content==''){
			throw new Exception(tr('Template not found: %s!',$this->curent_mytemplate_path.$weblayout.$ext));
			return false;
		}
		return $page_content;
	}

	public function flushOutput($page){
		$this->output = $this->loadlayout($page);
		$this->processOutput();
		$this->deleteComments();
		echo $this->output;
	}

	protected function processOutput(){
		$level = 0;
		$level += $this->includeContent();
		$level += $this->processMessage();
		$level += $this->deleteSections();
		$level += $this->replaceRepeats();
		$level += $this->replaceVariable();
		$level += $this->replaceConstants();
		if($level > 0){
			$this->processOutput();
		}
	}

	protected function includeContent(){
		$inlocuiri=0;
		$inlocuiri=preg_match_all('/(?imsU)<!--Includes_:([a-zA-Z0-9_\.\/]+):-->/',$this->output,$substituts);
		foreach($substituts[0] as $key	=> $substituent){
			$x = $this->loadlayout(trim($substituts[1][$key]));
			$this->output=str_replace($substituent, $x, $this->output, $inlocuiri);
		}
		return $inlocuiri;
	}

	protected function deleteSections(){
		$inlocuiri = preg_match_all('/(?imsU)<!--Section_:([a-zA-Z0-9_]+):-->(?:.+)<!--:\1:_Section-->/',$this->output,$sections);
		if($inlocuiri>0){
			foreach($sections[0] as $ord	=> $Section){
				if(!in_array($sections[1][$ord], $this->sections)){
					$this->output=str_replace($Section,'',$this->output);
				}
				else{
					$this->output=str_replace('<!--Section_:'.$sections[1][$ord].':-->','',$this->output);
					$this->output=str_replace('<!--:'.$sections[1][$ord].':_Section-->','',$this->output);
				}
			}
		}
		return 0;
	}

	protected function processMessage(){
		if(!array_key_exists('PageMessage', $_SESSION)){
			return 0;
		}
		$messages='';
		foreach($_SESSION['PageMessage'] as $message){
			$messages .= $message."\n";
		}
		$messages=trim($messages);
		if($messages !== ''){
			$this->variables['PageMessage']=nl2br($messages);
			$this->sections[]='PageMessage';
			$inlocuiri = 1;
		} else {
			$this->sections[]='NoPageMessage';
		}
		$_SESSION['PageMessage'] = array();
		return 0;
	}

	protected function deleteComments(){
		$this->output=preg_replace('/(?msU)\<!--.+-->/','',$this->output);
	}

	protected function replaceConstants(){
		$inlocuiri=0;
		$matches=preg_match_all ('/{[a-zA-Z0-9\_]+}/',$this->output,$substituts);
		foreach($substituts[0] as $substituent){
			$x=$substituent;
			if($x!=$substituent){
				$this->output=str_replace($substituent,$x, $this->output, $inlocuiri);
			}
			$this->output = str_replace($substituent,defined(substr($substituent,1,-1))?nl2br(constant(substr($substituent,1,-1))):'',$this->output);
		}
		return $inlocuiri;
	}

	protected function replaceVariable(){
		$inlocuiri=0;
		foreach($this->variables as $name=>$value){
			$this->output=str_replace('{'.$name.'}',$value,$this->output, $inlocuiri);
		}
		return $inlocuiri;
	}

	protected function replaceRepeats(){
		$inlocuiri=0;
		foreach ($this->repeats as $what=>$with){
			$count=0;
			preg_match_all ('/(?imsU)(?<=<!--Repeats_:'.$what.':-->).+(?=<!--:'.$what.':_Repeats-->)/', $this->output, $substituiri);
			if ($substituiri[0]!=array()){
				$final='';
				foreach($with as $repetitie){
					$temp=$substituiri[0][0];
					foreach($repetitie as $key=>$value){
						$temp=str_replace('{'.$key.'}',$value,$temp);
					}
					$final.=$temp;
				}
				$this->output=str_replace('<!--Repeats_:'.$what.':-->'.$substituiri[0][0].'<!--:'.$what.':_Repeats-->',$final,$this->output, $count);
			}
			$inlocuiri += $count;
		}
		return $inlocuiri;
	}
}
