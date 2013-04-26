<?php

class blog extends module
{
	private $page_content; 
	/**
	 * 	post_constructor
	 *
	 **/
	protected function post_constructor()
	{
		$this->page_title = ""; 
		$this->meta_description = '';  
		$this->meta_keywords = "";
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
				$data->Execute('DELETE', 'posts',"WHERE id = '".$_REQUEST['id']."'" );
				$return = 'deleted';
				break;
				
			case 'save':
				$data->ValuesHash['title'] = $_REQUEST['title'];
				$data->ValuesHash['tags'] = $_REQUEST['tags'];
				$data->ValuesHash['preview'] = $_REQUEST['preview'];
				$data->ValuesHash['post'] = $_REQUEST['post_content'];
				$data->ValuesHash['preview_image'] = $_REQUEST['preview_image'];
				
				$data->Execute('UPDATE', 'posts',"WHERE id = '".$_REQUEST['id']."'" );
				$return = 'saved';
				break;
				
			case 'new':
				$data->ValuesHash['title'] = $_REQUEST['title'];
				$data->ValuesHash['tags'] = $_REQUEST['tags'];
				$data->ValuesHash['preview'] = $_REQUEST['preview'];
				$data->ValuesHash['post'] = $_REQUEST['post_content'];
				$data->ValuesHash['preview_image'] = $_REQUEST['preview_image'];
				//preview_image
				
				$return = $data->Execute('INSERT', 'posts','');
				
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
		include_once $this->config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";
		$d = New Mysql_d();
		
		$output = $this->URI->slot[2] ; # '';
		
		switch($this->URI->slot[2])
		{
			case '':
			case 'list':	
				// show individule articles 
				$view_post = new Template_d('view_post.html',  $this->config->LIB_PATH.'/modules/blog/templates/');
				
				$id = $this->URI->slot[3] ;
				
				$newest_entry = $d->GetSingleValue('posts', 'id', "ORDER BY post_date DESC LIMIT 1");
				
				
				$previous_id = $d->GetSingleValue('posts', 'id', "WHERE id < ".$newest_entry." ORDER BY post_date DESC LIMIT 1");
				
				$previous_link = '<a href="/blog/view/'.$previous_id.'">< Previous Entry</a>';
				
				
				$next_link = ''; 
				
				$view_post->Set('previous_link',$previous_link);
				$view_post->Set('next_link',$next_link);
				
				
				$where = "WHERE id = '".$newest_entry."'";
		
				if ($d->Count('posts', $where) < 1)
				{
					header( 'Location: /404/'. $id ) ;	
				}
			
				
				$results = $d->Execute('SELECT', '* ,  DATE_FORMAT( post_date, "%M %D ,%Y") as formated_date FROM posts', $where . ' LIMIT 1' );

				while ($row = $results->fetch_assoc()) 
				{
					$this->page_title = $row['title']; 
					$this->meta_description = $row['preview'];
					$this->meta_keywords = $row['tags'];
					$view_post->Set('formated_date',$row['formated_date']);
					$view_post->Set('post',$row['post']);
					$view_post->Set('title',$row['title']);
					$view_post->Set('preview_image',$row['preview_image']);
					
				
				}
				
				$output = $view_post->Output();
								
				break; 
				
			case 'view':
				// show individule articles 
				$view_post = new Template_d('view_post.html',  $this->config->LIB_PATH.'/modules/blog/templates/');
				
				$id = $this->URI->slot[3] ;
				
				
				$previous_id = $d->GetSingleValue('posts', 'id', "WHERE id < ".$id." ORDER BY post_date DESC LIMIT 1");
				
				$previous_link = '';
				if ($previous_id != '')
				{
					$previous_link = '<a href="/blog/view/'.$previous_id.'">< Previous Entry</a>';
				} 
				
				$next_id = $d->GetSingleValue('posts', 'id', "WHERE id > ".$id." ORDER BY post_date DESC LIMIT 1");
				
				$next_link = '';
				if ($next_id != '')
				{
					$next_link = '<a href="/blog/view/'.$next_id.'">Next Entry ></a>';
				} 
				
				$view_post->Set('previous_link',$previous_link);
				$view_post->Set('next_link',$next_link);
				
				
				$where = "WHERE id = '".$id."'";
		
				if ($d->Count('posts', $where) < 1)
				{
					header( 'Location: /404/'. $id ) ;	
				}
			
				
				$results = $d->Execute('SELECT', '* ,  DATE_FORMAT( post_date, "%M %D ,%Y") as formated_date FROM posts', $where . ' LIMIT 1' );

				while ($row = $results->fetch_assoc()) 
				{
					$this->page_title = $row['title']; 
					$this->meta_description = $row['preview'];
					$this->meta_keywords = $row['tags'];
					$view_post->Set('formated_date',$row['formated_date']);
					$view_post->Set('post',$row['post']);
					$view_post->Set('title',$row['title']);
					$view_post->Set('preview_image',$row['preview_image']);
				
				}
				
				$output = $view_post->Output();
								
				break; 
				
		}
		
				
		
		/*
		
		$where = "WHERE filename = '".$requested_content."'";
		
		if ($d->Count('content_pages', $where) < 1)
		{
			header( 'Location: /404/'. $requested_content ) ;	
		}
		
		$results = $d->Execute('SELECT', '* FROM content_pages', $where . ' ORDER BY timestamp DESC LIMIT 1' );

		while ($row = $results->fetch_assoc()) 
		{
			$this->page_title = $row['page_title']; 
			$this->meta_description = $row['meta_description'];
			$this->meta_keywords = $row['meta_keywords'];
			
			$this->page_content =  $row['page_content'];
			
		
		}
		
		return $this->page_content;
		
		*/
		
		return $output;
		
	}
	
	/**
	 * 	OutputAdmin
	 *
	 **/
	public function OutputAdmin($URI)
	{
		include_once $this->config->LIB_PATH."d_lib/Template_d/Template_d.php";
		
		$return = '';
		
		switch($this->URI->slot[3])
		{	
			case 'manage' : 
				// populate grid
				include_once $this->config->LIB_PATH."d_lib/Grid_d/Grid_d.php";
		
				$admin_post_list = new Grid_d('admin_post_list', 'admin_post_list', $this->config->LIB_PATH.'/modules/blog/templates/');
		
				$return = $admin_post_list->Output('*', 'posts', '');
				
				break;
			case 'edit':				
				include_once $this->config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";
				$d = new Mysql_d();
				
				$output = new Template_d('editor.html', $this->config->LIB_PATH.'/modules/blog/templates/');
				
				$id = $this->URI->slot[4] ;
				
				$where = "WHERE id = '".$id."'";
		
				$results = $d->Execute('SELECT', '* FROM posts', $where . ' LIMIT 1' );

				while ($row = $results->fetch_assoc()) 
				{
					$output->Set('title', $row['title']); 
					$output->Set('id', $row['id']); 
					$output->Set('preview', $row['preview']);
					$output->Set('tags', $row['tags']);
					$preview_image = $row['preview_image'];
					$post_content = $row['post'];
				}
				
				// Include CKEditor class.
				include_once $this->config->PUB_PATH."/scripts/thirdParty/ckeditor/ckeditor.php";
				// The initial value to be displayed in the editor.
				// Create class instance.
				$CKEditor = new CKEditor();
				// Path to CKEditor directory, ideally instead of relative dir, use an absolute path:
				//   $CKEditor->basePath = '/ckeditor/'
				// If not set, CKEditor will try to detect the correct path.
				$CKEditor->basePath = '/scripts/thirdParty/ckeditor/';
				// Create textarea element and attach CKEditor to it.
				$CKEditor->returnOutput = true;
				
				$editor = $CKEditor->editor("post_content", $post_content);
				
				$output->Set('post_content', $editor);
				
				// image list code 
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
				
				
				$image_list = '<select id="preview_image"> ';
				//return $retval; 
				foreach($retval as $img) 
				{
					if ($img['file'] == $preview_image) 
					{
						$image_list .= '<option value="'.$img['file'].'" selected>'.$img['file'].'</option>';
					} else {
						$image_list .= '<option value="'.$img['file'].'" >'.$img['file'].'</option>';
						
					}
				}
				
				$image_list .= '</select>';
				
				$output->Set('image_list', $image_list);
				
						
				$return = $output->Output();				
				
				break ;
				
			case 'new' :
				$output = new Template_d('new.html', $this->config->LIB_PATH.'/modules/blog/templates/');
				
				// Include CKEditor class.
				include_once $this->config->PUB_PATH."/scripts/thirdParty/ckeditor/ckeditor.php";
				// The initial value to be displayed in the editor.
				// Create class instance.
				$CKEditor = new CKEditor();
				// Path to CKEditor directory, ideally instead of relative dir, use an absolute path:
				//   $CKEditor->basePath = '/ckeditor/'
				// If not set, CKEditor will try to detect the correct path.
				$CKEditor->basePath = '/scripts/thirdParty/ckeditor/';
				// Create textarea element and attach CKEditor to it.
				$CKEditor->returnOutput = true;
				
				$editor = $CKEditor->editor("post_content", '');
				
				$output->Set('post_content', $editor);
				
				
				// image list code 
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
				
				
				$image_list = '<select id="preview_image">';
				//return $retval; 
				foreach($retval as $img) {
					$image_list .= '<option value="'.$img['file'].'">'.$img['file'].'</option>';
				}
				
				$image_list .= '</select>';
				
				$output->Set('image_list', $image_list);
				
				$return = $output->Output();
				
				
				
					
				break ;
	
		}
	
		return $return ;
	}
	
	/**
	 * 	OutputAdminLeftMenu
	 *
	 **/
	public function OutputAdminLeftMenu()
	{
		include_once $this->config->LIB_PATH."d_lib/Template_d/Template_d.php";
		/*
		$menu = new Grid_d('edit_content_pages', 'admin_left_menu_grid', $config->LIB_PATH.'/modules/content/templates/');
		
		
		
		return $menu->Output('*', 'content_pages', 'GROUP BY filename');
		*/
		
		//$output = "blog menu";
		$output = new Template_d('admin_nav.html', $this->config->LIB_PATH.'/modules/blog/templates/');
		
		
		return $output->Output() ; 
		
		//return "TEST";
	}
	
	
	public function OutputNavigation()
	{
		include_once $this->config->LIB_PATH."d_lib/Grid_d/Grid_d.php";
		
		$menu = new Grid_d('main_navigation', 'main_nav', $this->config->LIB_PATH.'/modules/content/templates/');
		
		
		
		return $menu->Output('*', 'content_pages', 'WHERE nav = 1 GROUP BY filename');
		
		//return "TEST";
	}


}


?>

