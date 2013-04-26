<?php

// include the parent module 
include_once $config->LIB_PATH.'modules/module.php';


class admin extends module
{
	/**
	 * 	post_constructor
	 *
	 **/
	protected function post_constructor()
	{
		$this->master_page = 'admin_master.html';
		$this->master_page_path = $this->module_templates;
		$this->page_title = "Site Admin"; 
		$this->meta_description = '';  
		$this->meta_keywords = "";
	}
	
	/**
	 * 	ajax
	 *
	 **/
	protected function ajax()
	{
		// nothing yet 
		$admin_ajax_module = $this->URI->slot[2] ;
		
		
		if ($admin_ajax_module == 'image')
		{
			global $config ;
			$return = '';
			$action = $this->URI->slot[3];
		
			switch($action)
			{	
				case 'delete':
					
					//  $this->URI->slot[4] 
					
					echo unlink($config->PUB_PATH."/images/uploaded/". $this->URI->slot[4] );
					
					break; 
			
			}
		
		} else if ($admin_ajax_module == 'menu')
		{
			global $config ;
			$return = '';
			$action = $this->URI->slot[3];
		
			switch($action)
			{	
				case 'delete':
					
					include_once $config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";
					
					$data = new Mysql_d();
					
					if (!$data->Execute('DELETE', 'site_navigation' , " WHERE id = ". $this->URI->slot[4] )) 
					{
						$return .= $data->ErrorString ;
					} else { 
						$return .= 'Item Deleted!';
					}
					
					break; 
				case 'refresh':
					
					include_once $config->LIB_PATH."d_lib/Grid_d/Grid_d.php";
		
					$items = new Grid_d('menu_items', 'menu_items', $config->LIB_PATH.'/modules/admin/templates/');
				
		
					$return = $items->Output("*",'site_navigation', 'ORDER BY `order`');
					
				
					break; 
					
				case 'save_new_order':
					
					include_once $config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";
					
					$data = new Mysql_d();
				
					parse_str($_REQUEST['data']);
								
					for ($i = 0; $i < count($list_items); $i++) 
					{
						
						$return .= $i . ' - '. $list_items[$i] . ' |';
						
						$data->ValuesHash['`order`'] = $i;
						
						if (!$data->Execute('UPDATE', 'site_navigation' , " WHERE id = ".$list_items[$i] )) 
						{
							$return .= $data->ErrorString ;
						}
						
						$data->clearValues();
					}
					
					break; 
							
				case 'save_edit':
					include_once $config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";
				
					$data = new Mysql_d();
						
					$data->ValuesHash['target'] = $_REQUEST['target'];	
					$data->ValuesHash['display_text'] = $_REQUEST['display_text'];
					$data->ValuesHash['url'] = $_REQUEST['url'];
					
					if (!$data->Execute('UPDATE', 'site_navigation' , " WHERE id = ".$_REQUEST['id'] )) 
					{
						$return .= $data->ErrorString ;
					} else { 
						$return .= 'Edit Successful!' ;
					}
					
					break; 
					
				case 'save_new':
					include_once $config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";
				
					$data = new Mysql_d();
					
					$highest_order = $data->GetSingleValue('site_navigation', '`order`', "ORDER BY `order` DESC LIMIT 1");
						
					$data->ValuesHash['target'] = $_REQUEST['target'];	
					$data->ValuesHash['display_text'] = $_REQUEST['display_text'];
					$data->ValuesHash['url'] = $_REQUEST['url'];
					$data->ValuesHash['`order`'] = ($highest_order + 1) ;
					
					
					if (!$data->Execute('INSERT', 'site_navigation' , "" )) 
					{
						$return .= $data->ErrorString ;
					} else { 
						$return .= 'New Successful!' ;
					}
						
					
					break; 
			}
			
			echo $return ;
			
			die;
		
		} else { 
		
			include_once $this->config->LIB_PATH . 'modules/'.$admin_ajax_module.'/'.$admin_ajax_module.'.php';
		
			$loaded_admin_ajax_module = new $admin_ajax_module();
	
			$loaded_admin_module->ajax();
		}
	}
	
	/**
	 * 	Output
	 *
	 **/
	public function Output()
	{
		$template = new Template_d('admin_content_area.html', $this->module_templates);	
		
		if ($this->URI->slot[2] == '')
		{
			$this->URI->slot[2] = 'content';
			$this->URI->slot[3] = 'edit';
			$this->URI->slot[4] = 'home';
		}
		
		$template->Set('left_nav',$this->build_left_menu());
		$template->Set('editor',$this->output_admin());
		
		return $template->Output();
	}
	
	/**
	 * 	build_left_menu
	 *
	 **/
	private function build_left_menu()
	{	
		$output = '' ;
		
		foreach ( $this->config->MODULES as $current_module)
		{
			//echo $current_module ;
			//$template = new Template_d('admin_content_area.html', $this->module_templates);	
			include_once $this->config->LIB_PATH . 'modules/'.$current_module.'/'.$current_module.'.php';
		
			$loaded_module = new $current_module();
		
			$output .= $loaded_module->OutputAdminLeftMenu();

			$loaded_module = '';
		}
		
		return $output; 
	
	}
	
	
	/**
	 * 	OutputAdmin
	 *
	 **/
	private function output_admin()
	{
		$admin_module = $this->URI->slot[2] ;
		global $config ; 
		
		switch ( $admin_module ) 
		{ 
			case 'menu':
				include_once $config->LIB_PATH."d_lib/Template_d/Template_d.php";
				include_once $config->LIB_PATH."d_lib/Grid_d/Grid_d.php";
				
				$output = new Template_d('menu_manager.html', $config->LIB_PATH.'/modules/admin/templates/');
				
				include_once $config->LIB_PATH."d_lib/Grid_d/Grid_d.php";
		
				$items = new Grid_d('menu_items', 'menu_items', $config->LIB_PATH.'/modules/admin/templates/');
				
				
				$output->Set('menu_items',$items->Output("*",'site_navigation', 'ORDER BY `order`'));
				
				return $output->Output();
				
				break;
			case 'images':
				$error = '<div style="error">'; 
				if (isset($_REQUEST['poo']))
				{
					if ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/png")))
					{
						if ($_FILES["file"]["error"] > 0)
						{
							$error .= "Return Code: " . $_FILES["file"]["error"] . "<br />";
						}
						else
						{
							//echo "Upload: " . $_FILES["file"]["name"] . "<br />";
							//echo "Type: " . $_FILES["file"]["type"] . "<br />";
							//echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
							//echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";
						
							if (file_exists($config->PUB_PATH."/images/uploaded/" . $_FILES["file"]["name"]))
							{
							  	$error .= $_FILES["file"]["name"] . " already exists. ";
							}
							else
							{
								move_uploaded_file($_FILES["file"]["tmp_name"], $config->PUB_PATH."/images/uploaded/" . $_FILES["file"]["name"]);
							  	$error .= "file upload";
							}
						}
					}
					else
					{
						$error .= $_FILES["file"]["type"] ." This isn't a supported image type!";
					}
				
				}
				
				$imagetypes = array("image/jpeg", "image/gif", "image/png");
				
				
				//array to hold return value 
				$retval = array(); 
				// add trailing slash if missing 
				$dir = "images/uploaded/"; 
				
				// full server path to directory  
				$fulldir = "{$_SERVER['DOCUMENT_ROOT']}/$dir"; 
				$d = @dir($fulldir) or die("getImages: Failed opening directory $dir for reading"); 
				
				while(false !== ($entry = $d->read())) { 
					// skip hidden files 
					if($entry[0] == ".") continue; 
					// check for image files 
					if(in_array(mime_content_type("$fulldir$entry"), $imagetypes)) 
					{ 
						$retval[] = array( "file" => "/$dir$entry", "size" => getimagesize("$fulldir$entry") );
					} 
				} 
				
				$d->close(); 
				
				
				$images = '';
				//return $retval; 
				foreach($retval as $img) {
					$images .= '<div class="form_box" style="width: 450px; height: 150px; margin: 0px 10px 0px 0px; "><h3>'.$img['file'].'</h3><img class="photo" width="70" src="'.$img['file'].'" alt=""><a onclick="delete_this(\''.$img['file'].'\'); return false; " href="#" ><img src="/images/icons/Trash-icon.png" width="20" style="margin: 0px 0px 0px 10px; " border="no"></a> </div>'; 
				}
				
				$this->form_action = '/admin/images/';
			
				$template = new Template_d('image_manager.html', $this->module_templates);	
				
				$template->Set('images', $images);
				
				return $error.$template->Output() ;

				break; 	
			case 'blog':
			case 'content':
			case 'questions':
				include_once $this->config->LIB_PATH . 'modules/'.$admin_module.'/'.$admin_module.'.php';
		
				$loaded_admin_module = new $admin_module();
	
				return $loaded_admin_module->OutputAdmin($this->URI);
				
				break; 
		}
		
		
	}
	
	/**
	 * 	OutputAdminLeftMenu
	 *
	 **/
	public function OutputAdminLeftMenu()
	{
		$output = '';
		
		return $output ; 
		
		//return "TEST";
	}
	
	/**
	 * 	OutputNavigation
	 *
	 **/
	public function OutputNavigation()
	{
		
		global $config;
		include_once $config->LIB_PATH."d_lib/Grid_d/Grid_d.php";
		
		$menu = new Grid_d('main_nav', 'main_nav', $config->LIB_PATH.'/modules/admin/templates/');
		
		$if = "target, @url:= url AS url, (SELECT IF(STRCMP(@url,'".$this->URI->uri."'),'left_nav','left_nav_active') ) AS ACTIVE, display_text ";
		
		//$if = '*' ;
		
		return $menu->Output($if, 'site_navigation', 'ORDER BY `order`');
		/**/
		//return "TEST";
	}
	
}


?>

