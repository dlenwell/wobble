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
* Url 
* 
*
* @file			Url.php
* @author		David Lenwell
* @package		Url
* @subpackage	wobble 
*/

class Url {
	// set up 
	public $slot = array();
	public $uri ;
	
	
	/**
	 * __construct method
	 *
	 * calls private method _auth 
	 *
	 * @param	void
	 * @return 	void	 
	 * 
	 */
	function __construct() {
		// init function 
		if ($this->uri == '')
		{ 
			$this->uri = $_SERVER['REQUEST_URI'] ; 
		} 
		
		$this->_parse();
		
	}

	/**
	 * private method _parse
	 *
	 * parse's uri
	 *
	 * @param	void
	 * @return 	void	 
	 * 
	 */
	private function _parse() {
		$this->slot = split('[/]',$this->uri);
	} 
	
	
	/**
	 * __destruct method
	 *
	 * 
	 *
	 * @param	void
	 * @return 	void	 
	 * 
	 */
	function __destruct() {
		
	
	}
	
	
}

?>