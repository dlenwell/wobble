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
    
	class config
	{
	    public $BASE_DIR        = dirname(__FILE__);
		public $THEME		    = "blue_robot";
		
		
		public $SITE_NAME 		= 'Wobble';
		public $LIB_PATH 		= $this->BASE_DIR.'/library/etc/';
		public $PUB_PATH		= $this->BASE_DIR.'/public/';
		public $MODULES 		= array('content','admin', 'fourofour', 'questions','blog');
		public $DEFAULT_MODULE 	= 'content';
		public $MODULES_ADMIN	= array("admin");
		
		public $THEME_PATH      = $this->BASE_DIR.'/library/themes/'.
		public $MASTER_PAGE     = $this->THEME_PATH.$this->THEME.'/master.html'
		
		// database
		public $DB_USER 		= "wobble_user";
		public $DB_PASSWORD		= "4AZtq5yJ";
		public $DB_HOST 		= "localhost";
		public $DB_SCHEMA 		= "wobble";
		
		public $redirects		= array("admin" => "/module/public/help");
	
	
	}



?>
