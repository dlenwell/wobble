<?php
	class config
	{
		public $MASTER_PAGE				= "master.html";
		public $DEFAULT_TEMPLATE_PATH 	= '/home/web/vpsrobot.com/library/templates/';
		public $SITE_NAME 				= 'VPS Robot';
		public $LIB_PATH 				= '/home/web/vpsrobot.com/library/';
		public $PUB_PATH				= '/home/web/vpsrobot.com/public';
		public $MODULES 				= array('content','admin', 'fourofour', 'questions','blog');
		public $DEFAULT_MODULE 			= 'content';
		public $MODULES_ADMIN			= array("admin");
		//public $redirects				= array("admin" => "/module/public/help");
		public $MYSQL_USER 				= "web_user";
		public $MYSQL_PASSWORD			= "4AZtq5yJ";
		public $MYSQL_HOST 				= "mysql01.vpsrobot.com";
		public $MYSQLSCHEMA 			= "vpsrobot";
		public $MESSAGE_NOT_LOGGED_IN	= "Please Log in and we'll speed you on your way!";
	}


//'blog',	
?>
