<?php

/**
* Auth_d 
* 
*
* @file			Uri_d.php
* @author		David Lenwell
* @package		Uri_d
* @subpackage	D_lib 
*/


class Uri_d {
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
		//echo $uri;
		//echo count($this->items);
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
		
	
	}
	
	

	
	
	
	
	
}

?>