<?php
/*
    wobble - another php web framework... 
    Copyright (C) 2013  David Lenwell

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* module abstract
* 
* @note			abstract class for modules .. 
* @file			module.php
* @author		David Lenwell
* @package		module
* @ 
*/
abstract class module
{
	protected $config ; 
	protected $URI ; 
	
	protected $module_templates ; 
	protected $module_images ; 
	protected $requested_module ; 
	
	public $master_page; 
	public $master_page_path; 
	public $page_title ; 
	public $meta_keywords ;
	public $meta_description ;
	public $form_action ; 
	
	
	function __construct()
	{	
		// wrap the config
		global $config ; 
		$this->config = $config ; 
		
		//wrap the uri object 
		global $URI ; 
		$this->URI = $URI ; 
		
		// can be overwritten in the child template to be what ever it needs to be
		$this->master_page = $config->MASTER_PAGE;
		
		// set up module paths 
		global $requested_module ; 
		
		$this->requested_module = $requested_module ;
		
		$this->module_templates = $config->LIB_PATH.'modules/'.$requested_module."/templates/";
		$this->module_files = $config->LIB_PATH.'modules/'.$requested_module."/files/";
		
		// set the default form action to self.. 
		$this->form_action = $_SERVER['REQUEST_URI'];
	
		// redirect an ajax request
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest") {
			// this is an ajax call divert to the ajax handling function and die 
			$this->ajax() ;
			die;
		}
		
		// read module specific files without auth
		if (isset($URI->slot[2]) && $URI->slot[2] == 'files')
		{	
			$this->get_file($URI->slot[3], $URI->slot[4]);
		}
		$this->post_constructor();
	}
	
	protected function get_file($type, $file_path)
	{
	
		//header("Pragma: public");
		//header("Expires: 0");
		//header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		//header("Cache-Control: private",false);
		
		switch($type)
		{
			case 'css':
				header('Content-type: text/css');
				readfile($this->module_files . $file_path);
				
				break;
			case 'jpg':
				ob_clean();
				//header("Content-Disposition: attachment; filename=\"".$file_path ."\";" );
				header("Content-Length: ".filesize($this->module_files . $file_path));
				header('Content-Type: image/jpeg');
				
				echo file_get_contents($this->module_files . $file_path);
				
				die;
				
				break;
			
		}
		
		
	}
	
	/**
	 * post_constructor
	 *
	 * @description	Called after the construct method.. it allows the child class to set variabls needed by the page
	 *
	 **/
	abstract protected function post_constructor();
	
	/**
	 * ajax
	 *
	 * @description	This function is called when $_SERVER['HTTP_X_REQUESTED_WITH'] = "XMLHttpRequest"
	 *
	 **/
	abstract protected function ajax();
	
	/**
	 * Output
	 *
	 * @description	default function required for all subclasses putputs the content area of the template
	 *
	 **/
	abstract protected function Output();
	
	/**
	 * OutputSecurityWrapper
	 *
	 * @description	
	 *
	 **/
	function OutputSecurityWrapper()
	{	
		if (! in_array($this->requested_module, $this->config->MODULES_ADMIN)) 
		{
			return $this->Output();
		} else { 
			global $AUTH; 
			// now we have to check to see if  they are logged in .. if not show them the login screen 
			if ($AUTH->status)
			{
				return $this->Output();
			} else {
				return $AUTH->OutputLogin();
			}
			
		}
	}

	function generateHash()
	{
      	$result = "";
      	$charPool = '0123456789abcdefghijklmnopqrstuvwxyz.@#';
      	
      	for($p = 0; $p<15; $p++)
      	{
      		$result .= $charPool[mt_rand(0,strlen($charPool)-1)];
      	}
      	
      	return sha1(md5(sha1($result)));
      }


}


?>