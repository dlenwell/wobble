<?php

class faq extends module
{
	private $page_content; 
	/**
	 * 	post_constructor
	 *
	 **/
	protected function post_constructor()
	{
		$this->page_title = "This is a page title"; 
		$this->meta_description = 'this is a page desc';  
		$this->meta_keywords = "word,key,poop";
	}
	
	/**
	 * 	ajax
	 *
	 **/
	protected function ajax()
	{
		global $URI;
		global $config;
		
		include_once $config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";
		$data = new Mysql_d();
		
		$return = 'no action';
		$action = $URI->slot[3];
		
		switch($action)
		{	
			case 'delete':
				$return = 'delete';
				break; 
			
			case 'save':
				$data->ValuesHash['question'] = $_REQUEST['question'];
				$data->ValuesHash['answer'] = $_REQUEST['answer'];
				
				$return = $data->Execute('UPDATE', 'faq',"WHERE id = " . $URI->slot[4]);
				
				//$return = $_REQUEST['question'] ; 
				
				break; 
			
			case 'new':
				$_key = $this->generateHash() ; 
			
				$data->ValuesHash['question'] = $_REQUEST['question'];
				$data->ValuesHash['answer'] = $_REQUEST['answer'];
				$data->ValuesHash['_key'] = $_key;
				
				$return = $data->Execute('INSERT', 'faq','');
				
				
				$return = 'saved';
				
			
				
				break;
		}
		
		echo $return ;
	}
	
	/**
	 * 	Output
	 *
	 **/
	public function Output()
	{
		
		global $config;
		include_once $config->LIB_PATH."d_lib/Grid_d/Grid_d.php";
		
		$menu = new Grid_d('faq_display', 'faq_display', $config->LIB_PATH.'/modules/faq/templates/');
		
		
		
		return $menu->Output('*', 'faq', '');
		
		
		//return "";
	}
	
	/**
	 * 	OutputAdmin
	 *
	 **/
	public function OutputAdmin($URI)
	{
		global $config;
		include_once $config->LIB_PATH."d_lib/Template_d/Template_d.php";
		
		$return = '';
		
		switch($URI->slot[3])
		{	
			case 'manage' : 
				// populate grid
				include_once $this->config->LIB_PATH."d_lib/Grid_d/Grid_d.php";
		
				$admin_list = new Grid_d('admin_list', 'admin_list', $this->config->LIB_PATH.'/modules/faq/templates/');
		
				$return = $admin_list->Output('*', 'faq', '');
				
				//$return = 'manage';
				
				break;
			
			case 'edit':				
				$output = new Template_d('editor.html', $this->config->LIB_PATH.'/modules/faq/templates/');
				
				
				include_once $this->config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";
				$d = new Mysql_d();
				
				
				$id = $this->URI->slot[4] ;
				
				$where = "WHERE id = '".$id."'";
		
				$results = $d->Execute('SELECT', '* FROM faq', $where . ' LIMIT 1' );

				while ($row = $results->fetch_assoc()) 
				{
					$output->Set('question', $row['question']); 
					$output->Set('id', $row['id']); 
					$output->Set('answer', $row['answer']);

				}
				
				$return = $output->Output();
					
				break ;
				
				
				
				
			case 'new' :
				$output = new Template_d('new.html', $this->config->LIB_PATH.'/modules/faq/templates/');
				
				
				$return = $output->Output();
					
				break ;
	
		}
	
		return $return ;
	}
	
	
	/**
	 * 	OutputItemList
	 *
	 **/
	public function OutputAdminLeftMenu()
	{
		include_once $this->config->LIB_PATH."d_lib/Template_d/Template_d.php";
	
		$output = new Template_d('admin_nav.html', $this->config->LIB_PATH.'/modules/faq/templates/');
		
		return $output->Output() ; 
	
	}
	
	
	public function OutputNavigation()
	{
		
		return "TEST";
	}


}



?>

