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
* class Template
* 
* @file			Template.php
* @author		David Lenwell
* @package		Template
* @subpackage	wobble 
**/

class Template {

	// constants 
	public $template_base_path;

	// arrays 
	protected $params = array();
	protected $keys = array();

	/**
	 * __contrusct method
	 *
	 * 
	 *
	 * @param	string	instance_name
	 * @param 	string 	template_name
	 * 
	 */
	function __construct($template_name, $newpath = "") {
		global $config; 
		
		$this->template_base_path = $config->DEFAULT_TEMPLATE_PATH ;
		
		// update path 
		if ($newpath <> ""){
			$this->template_base_path = $newpath ;
		}
		// read the file into the params array
		$this->params['template'] = file_get_contents($this->template_base_path . $template_name);
	}
		
	/**
	 * Set method
	 *
	 * @param	string	key
	 * @param 	string 	value
	 * 
	 */
	public function Set($key,$value) {
		$this->keys[$key] = $value;
	}
	
	
	/**
	 * MySqlSet
	 *
	 * @param	string	key
	 * @param 	string 	value
	 * 
	 */
	public function MySqlSet($sql)  {
		// need a db object 
		global $config; 
		include_once $config->lib_path."d_lib/Mysql_d/Mysql_d.php";
		$data = New Mysql_d();
		
		$results = $data->Execute($sql);

		while ($row = $results->fetch_assoc()) {
			foreach ($row as $key => $value) {
				$this->keys[$key] = $value;
			}
		}
	}
	
	/**
	 * Output method
	 *
	 * outputs html 
	 */
	public function Output($output = FALSE) {
		// read in the template file 
		$template_file = $this->params['template'];
		
		// loop through and replace values 
		foreach ($this->keys as $key => $value){
			//echo $key;
			$template_file = str_replace('{|'.$key.'|}', $value, $template_file);		
		}
		
		// output the results 
		
		if ($output) {
			echo $template_file; 
		} else { 
			return $template_file;
		} 
		// clear the array 
		$this->ClearValues();
		
	}
	
	/**
	 * Reset the replacement values array
	 *
	 * @param	void
	 * @return	void
	 */
	 
	public function ClearValues() {
		$this->keys = array(); 
	}

}

?>