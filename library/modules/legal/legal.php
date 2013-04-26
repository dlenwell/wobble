<?php

class legal extends module
{
	private $page_content; 
	/**
	 * 	post_constructor
	 *
	 **/
	protected function post_constructor()
	{
		$this->page_title = "Legal Information"; 
		$this->meta_description = 'Legal Frequently Asked Questions.';  
		$this->meta_keywords = "Legal Info";
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
				$return = $data->Execute('DELETE', 'legal',"WHERE id = " . $URI->slot[4]);
				break; 
			
			case 'save':
				$data->ValuesHash['question'] = $_REQUEST['question'];
				$data->ValuesHash['answer'] = $_REQUEST['answer'];
				
				$return = $data->Execute('UPDATE', 'legal',"WHERE id = " . $URI->slot[4]);
				
				//$return = $_REQUEST['question'] ; 
				
				break; 
			
			case 'new':
				$_key = $this->generateHash() ; 
			
				$data->ValuesHash['question'] = $_REQUEST['question'];
				$data->ValuesHash['answer'] = $_REQUEST['answer'];
				$data->ValuesHash['_key'] = $_key;
				
				$return = $data->Execute('INSERT', 'legal','');
				
				
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
		
		$menu = new Grid_d('legal_display', 'legal_display', $config->LIB_PATH.'/modules/legal/templates/');
		
		
		
		return $menu->Output('*', 'legal', '');
		
		
		//return "";
	}
	
	/**
	 * 	OutputAdmin
	 *
	 **/
	public function OutputAdmin($URI)
	{
		include_once $this->config->LIB_PATH."d_lib/Template_d/Template_d.php";
		
		$return = '';
		
		switch($URI->slot[3])
		{	
			case 'manage' : 
				// populate grid
				include_once $this->config->LIB_PATH."d_lib/Grid_d/Grid_d.php";
		
				$admin_list = new Grid_d('admin_list', 'admin_list', $this->config->LIB_PATH.'/modules/legal/templates/');
		
				$return = $admin_list->Output('*', 'legal', '');
				
				//$return = 'manage';
				
				break;
			
			case 'edit':				
				$output = new Template_d('editor.html', $this->config->LIB_PATH.'/modules/legal/templates/');
				
				include_once $this->config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";
				$d = new Mysql_d();
				
				
				$id = $this->URI->slot[4] ;
				
				$where = " WHERE id = '".$id."'";
		
				$results = $d->Execute('SELECT', '* FROM legal', $where . ' LIMIT 1' );

				while ($row = $results->fetch_assoc()) 
				{
					$output->Set('question', $row['question']); 
					$output->Set('id', $row['id']); 
					$output->Set('answer', $row['answer']);
				}
				
				$return = $output->Output();
					
				break ;
				
				
			case 'new' :
				$output = new Template_d('new.html', $this->config->LIB_PATH.'/modules/legal/templates/');
				
				
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
	
		$output = new Template_d('admin_nav.html', $this->config->LIB_PATH.'/modules/legal/templates/');
		
		return $output->Output() ; 
	
	}
	
	
	public function OutputNavigation()
	{
		
		return "TEST";
	}


}



?>

