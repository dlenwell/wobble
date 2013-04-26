<?php

class content extends module
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
		echo $action ; 
		switch($action)
		{
			case 'sendemail':
				echo 'poo';
				//error_reporting(E_ALL);
				//error_reporting(E_STRICT);
				
				//date_default_timezone_set('America/Toronto');
				
				require_once('/home/web/umma/public/scripts/PHPMailer_v5.1/class.phpmailer.php');
				//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded
				
				$mail             = new PHPMailer();
				
				$body             = "from:" . $_REQUEST['your_name'] ."<br>Email : ".$_REQUEST['your_email']."<br>Your Message : ".$_REQUEST['your_message'] ."<br>Okay to send promotional Offers : ".$_REQUEST['promote'];
				
				$mail->IsSMTP(); // telling the class to use SMTP
				$mail->Host       = "mail.gmail.com"; // SMTP server
				$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
														   // 1 = errors and messages
														   // 2 = messages only
				$mail->SMTPAuth   = true;                  // enable SMTP authentication
				$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
				$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
				$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
				$mail->Username   = "sender@unitedmedicalmarijuana.com";  // GMAIL username
				$mail->Password   = "75S495";            // GMAIL password
				
				$mail->SetFrom('info@unitedmedicalmarijuana.com', 'Customer Service');
				
				$mail->AddReplyTo($_REQUEST['your_email'],$_REQUEST['your_name']);
				
				$mail->Subject    = "Message From Website";
				
				$mail->AltBody    = "from:" . $_REQUEST['your_name'] ."\n Email : ".$_REQUEST['your_email']."\nYour Message : ".$_REQUEST['your_message'] ."\nOkay to send promotional Offers : ".$_REQUEST['promote'];
				$mail->MsgHTML($body);
				
				$address = "info@unitedmedicalmarijuana.com";
				$mail->AddAddress($address, "Customer Service");
				
				if(!$mail->Send()) {
				  $return = "Mailer Error: " . $mail->ErrorInfo;
				} else {
				  $return = "Message sent!";
				}
				
				
				break; 
			case 'delete':
				$data->Execute('DELETE', 'content_pages',"WHERE filename = '".$URI->slot[4]."'" );
				
				break; 
			case 'save':
				$data->ValuesHash['nav'] = $_REQUEST['nav'];
				$data->Execute('UPDATE', 'content_pages',"WHERE filename = '".$_REQUEST['filename']."'" );
				
			case 'new':
				$data->ValuesHash['filename'] = $_REQUEST['filename'];
				$data->ValuesHash['page_title'] = $_REQUEST['page_title'];
				$data->ValuesHash['meta_keywords'] = $_REQUEST['meta_keywords'];
				$data->ValuesHash['meta_description'] = $_REQUEST['meta_description'];
				$data->ValuesHash['page_content'] = $_REQUEST['page_content'];
				$data->ValuesHash['nav_copy'] = $_REQUEST['nav_copy'];
				//nav_copy
				
				
				$return = $data->Execute('INSERT', 'content_pages','');
				
				#$return = 'saved';
				
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
		
		if ($this->URI->slot[2] == '')
		{
			$requested_content = "home";
		
		} else { 
			$requested_content = $this->URI->slot[2] ;
		
		}
		
		$where = "WHERE filename = '".$requested_content."'";
		
		if ($d->Count('content_pages', $where) < 1)
		{
			header( 'Location: /fourofour/'. $requested_content ) ;	
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
			case 'edit':				
				$file_name = $URI->slot[4];
				
				$output = new Template_d('editor.html', $config->LIB_PATH.'/modules/content/templates/');
				
				include_once $config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";
				
				$data = new Mysql_d();
				$where = "WHERE filename = '".$file_name."'";
				$results = $data->Execute('SELECT', '* FROM content_pages', $where . ' ORDER BY timestamp DESC LIMIT 1' );
		
				while ($row = $results->fetch_assoc()) 
				{
					$output->Set('id', $row['id']);
					$output->Set('page_title', $row['page_title']);
					$output->Set('filename', $row['filename']);
					$output->Set('meta_description', $row['meta_description']);
					$output->Set('meta_keywords', $row['meta_keywords']);
					$output->Set('nav_copy', $row['nav_copy']);
					$output->Set('nav', $row['nav']);
					
					if ( $row['nav'] == 1)
					{
						$output->Set('no_checked','');
						$output->Set('yes_checked','checked');
					} else {
						$output->Set('no_checked','checked');
						$output->Set('yes_checked','');
					}
					
					
					$page_content = $row['page_content'];
					
					$output->Set('timestamp', $row['timestamp']);
				}
				
				// Include CKEditor class.
				include_once $config->PUB_PATH."/scripts/thirdParty/ckeditor/ckeditor.php";
				include_once $config->PUB_PATH."/scripts/thirdParty/ckfinder/ckfinder.php";
				
				// The initial value to be displayed in the editor.
				// Create class instance.
				$CKEditor = new CKEditor();
				
				
				// Path to CKEditor directory, ideally instead of relative dir, use an absolute path:
				//   $CKEditor->basePath = '/ckeditor/'
				// If not set, CKEditor will try to detect the correct path.
				$CKEditor->basePath = '/scripts/thirdParty/ckeditor/';
				// Create textarea element and attach CKEditor to it.
				$CKEditor->returnOutput = true;
				
				//CKFinder::SetupCKEditor( $CKEditor, '/scripts/thirdParty/ckfinder/' ) ;
				$editor = $CKEditor->editor("page_content", $page_content);
				
				$output->Set('page_content', $editor);
						
				$return = $output->Output();				
				
				break ;
				
			case 'new' :
				$output = new Template_d('new.html', $config->LIB_PATH.'/modules/content/templates/');
				
				// Include CKEditor class.
				include_once $config->PUB_PATH."/scripts/thirdParty/ckeditor/ckeditor.php";
				// The initial value to be displayed in the editor.
				// Create class instance.
				$CKEditor = new CKEditor();
				// Path to CKEditor directory, ideally instead of relative dir, use an absolute path:
				//   $CKEditor->basePath = '/ckeditor/'
				// If not set, CKEditor will try to detect the correct path.
				$CKEditor->basePath = '/scripts/thirdParty/ckeditor/';
				// Create textarea element and attach CKEditor to it.
				$CKEditor->returnOutput = true;
				
				$editor = $CKEditor->editor("page_content", '');
				
				$output->Set('page_content', $editor);
						
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
		global $config;
		include_once $config->LIB_PATH."d_lib/Grid_d/Grid_d.php";
		
		
		
		$menu = new Grid_d('edit_content_pages', 'admin_left_menu_grid', $config->LIB_PATH.'/modules/content/templates/');
		
		
		
		return $menu->Output('*', 'content_pages', 'GROUP BY filename');
		
		//return "TEST";
	}
	
	
	
	


}



?>

