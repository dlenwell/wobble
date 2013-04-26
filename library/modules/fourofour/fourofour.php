<?php

// include the parent module 
include_once $config->LIB_PATH.'modules/module.php';


class fourofour extends module
{
	/**
	 * 	post_constructor
	 *
	 **/
	protected function post_constructor()
	{
		$this->page_title = "404 - File Not Found!"; 
		$this->meta_description = 'file not found';  
		$this->meta_keywords = "";
		
		
		
	}
	
	/**
	 * 	ajax
	 *
	 **/
	protected function ajax()
	{
		// nothing yet 
	}
	
	/**
	 * 	Output
	 *
	 **/
	public function Output()
	{
		
		$template = new Template_d('404.html', $this->module_templates);	
		
		
		return $template->Output();
	}
	
	/**
	 * 	OutputAdmin
	 *
	 **/
	public function OutputAdmin()
	{
		// 
		
		
		
		return $_SERVER['PHP_SELF'];
	}
	
	public function OutputAdminLeftMenu()
	{
		// 
		
		return '';	
	}
	
}


?>

