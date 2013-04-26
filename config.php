<?php
	class config
	{
	    public $BASE_DIR        = $current_file_path = dirname(__FILE__);
		public $THEME		    = "blue_robot";
		
		
		public $SITE_NAME 		= 'Wobble';
		public $LIB_PATH 		= $BASE_DIR.'/library/';
		public $PUB_PATH		= $BASE_DIR.'/public';
		public $MODULES 		= array('content','admin', 'fourofour', 'questions','blog');
		public $DEFAULT_MODULE 	= 'content';
		public $MODULES_ADMIN	= array("admin");
		public $redirects		= array("admin" => "/module/public/help");
		
		
		public $DB_USER 		= "wobble_user";
		public $DB_PASSWORD		= "4AZtq5yJ";
		public $DB_HOST 		= "localhost";
		public $DB_SCHEMA 		= "wobble";
		
	}



?>
