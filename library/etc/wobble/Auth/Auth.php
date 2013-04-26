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
* Auth
* 
* @note			Redesigned to be more passive.
* @file			Auth.php
* @author		David Lenwell
* @package		Auth
* @ 
*/
include_once $config->LIB_PATH."Mysql/Mysql.php";

class Auth {
	// public variables 
	public $status = false;
	public $status_text = "Please Login To See This Page!";
	public $id = 0;
	public $email = ""; 
	
	/**
	 * __construct method
	 *
	 * calls private method _auth 
	 *
	 * @param	void
	 * @return 	void	 
	 * 
	 */
	function __construct() 
	{
		// init function 
		if ($_SERVER['REQUEST_URI'] == '/logout') {
			$this->status = false;
			$this->status_text = 'User Logged out';
			$this->id = '0';
			$this->email = "";
			echo '<center>Just a moment...</center><meta http-equiv="refresh" content="1;url=/">';
			die;
		} else { 
			$this->_auth();
		} 
	}

	/**
	 * private method _auth
	 *
	 * authenticates user  
	 *
	 * @param	void
	 * @return 	void	 
	 * 
	 */
	public function _auth() 
	{
		// check for valid session 
		if (!isset($_SESSION['status']) || $_SESSION['status'] == false) {
			// check for login data in the request 
			if (isset($_REQUEST['email']) && isset($_REQUEST['password'])) {
				// someone is logging in .. lets check it against the database 
				//echo " - user is trying to log in! <br>";
				
				$d = new Mysql();
				
				$where_string = "WHERE email= '" . $_REQUEST['email'] . "' AND password_hash = '" . md5($_REQUEST['password']) . "'";
				
				if ($d->Count('users', $where_string) > 0) {
					//echo " - user is valid<br>";
					
					$this->status = true;
					$this->status_text = "Logged in." ;
					$this->id = $d->GetSingleValue('users','id', $where_string);
					$this->email = $_REQUEST['email']; 
					
					//echo " - session is set<br>";
				} else { 
					$this->status = false;
					$this->status_text = 'Failed Authentication';
					$this->id = '0';
					$this->email = ""; 
				}
			}
			
		} else { 
			//echo " - user has session <bR>";
			$this->status = $_SESSION['status'];
			$this->status_text = $_SESSION['status_text'] ;
			$this->id = $_SESSION['id'] ;
			$this->email = $_SESSION['email'];
		}
		
		
	} 
	
	/**
	 * OutputLogin method
	 *
	 * out puts the loging 
	 *
	 * @param	void
	 * @return 	void	 
	 * 
	 */
	public function OutputLogin()
	{
		
		$template = new Template('login.html');	
		$template->Set('status_text', $this->status_text);
		
		return $template->Output();
	}
	
	/**
	 * __destruct method
	 *
	 * checks for an existing connection to this host... if is not there it creates it. 
	 *
	 * @param	void
	 * @return 	void	 
	 * 
	 */
	function __destruct() {
		$_SESSION['status'] = $this->status ;
		$_SESSION['status_text'] = $this->status_text; 
		$_SESSION['id'] = $this->id ;
		$_SESSION['email'] = $this->email ;
	
	}
	
	

	
	
	
	
	
}

?>