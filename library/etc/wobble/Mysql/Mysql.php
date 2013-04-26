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
* This class is a wrapper for mysqli 
* 
*
* @file			Mysql.php
* @author		David Lenwell
* @package		Mysql
* @subpackage	D_lib 
*/


class Mysql {
	
	protected $params = array();
	public $ValuesHash = array();
	public $Error;
	public $ErrorString;
	private $Commit = FALSE;
	
	/**
	 * real_escape_wrapper method
	 *
	 * simple wrapper for  mysqli_real_escape_string
	 *
	 * @param	string	value
	 * 
	 */
	public function real_escape_wrapper($value)
	{
		global $connectionPool;
		return mysqli_real_escape_string($connectionPool[$this->params['db_host']], $value);
	}
	
	/**
	 * __contrusct method
	 *
	 * checks for an existing connection to this host... if is not there it creates it. 
	 *
	 * @param	string	db_name
	 * @param 	string 	db_host	 
	 * 
	 */
	function __construct() {
		// bring the global var in the local scope.
		global $connectionPool;
		global $config ;
		
		$db_host = $config->MYSQL_HOST ;
		$mysql_user = $config->MYSQL_USER ;
		$mysql_pw = $config->MYSQL_PASSWORD ;
		$db_name = $config->MYSQLSCHEMA ;
		
		$this->params['db_host'] = $db_host;
		
		// If the connection does not exist then create it.  
		if (!isset($connectionPool[$db_host])) {
		  $connectionPool[$db_host] = new mysqli($db_host,$mysql_user,$mysql_pw,$db_name);
		  if ( mysqli_connect_error() ) {
		     echo 'Could not establish mysql connection, error: ' . mysqli_connect_error();
		  } else {
		      return(TRUE);
	      }
		}	
	}
	/**
	 * Count method
	 *
	 * Returns the total count(*) from specified table.
	 * 
	 * @param	string	qryStr
	 * @param	string	qryStr
	 * @returns	integer	count(*) total
	 *
	 */	
	public function Count($table, $where = '') {
		// bring the global var in the local scope.
		global $connectionPool;
		
		$qrystr = 'SELECT count(*) as RESULT FROM '.$table.' '.$where.';';
		
		//echo $qrystr ;
		
		// give me m y results !!
		if ( ! $result = $connectionPool[$this->params['db_host']]->query($qrystr) ) {
			// handle error 
			echo "mysql error: " . $connectionPool[$this->params['db_host']]->error . "<br>" ;
		} else {
		
			$row = mysqli_fetch_assoc($result);
      
			return($row['RESULT']);
		}
	}
	
	/**
	 * Execute
	 *
	 * used for inserting or updating 
	 *
	 */	
	public function Execute($type, $table, $where, $array = null) {
		global $connectionPool;
		$this->Error = FALSE;
		
		switch(strtolower($type)) {
			case 'delete':
				global $connectionPool;
				
				$sql = 'DELETE from '.$table.' '.$where.';';
				
				// give me m y results !!
				if ( ! $result = $connectionPool[$this->params['db_host']]->query($sql) ) {
					// handle error 
					return('error');
				} else {
					return('success');
				}
				
				break;
				
			case 'select':
				global $connectionPool;
				
				$sql = 'SELECT '.$table.' '.$where.';';
			
				// give me m y results !!
				if ( ! $result = $connectionPool[$this->params['db_host']]->query($sql) ) {
					// handle error 
					$this->Error = TRUE;
					$this->ErrorString = $connectionPool[$this->params['db_host']]->error;
					return(FALSE);
				} else {
					return($result);
				}
				
				break;
				
			case 'insert':
				$sql = 'INSERT INTO '.$table.' (';
				$keys = '';
				$values = '';
				
				if (!is_null($array)) {
					// we have a populated values array and now need to loop through it and build the insert 
					foreach ($array as $key => $value){
						$keys .= mysqli_real_escape_string($connectionPool[$this->params['db_host']], $key) .',';
						$values .= "'".mysqli_real_escape_string($connectionPool[$this->params['db_host']], $value)."',";
					}
				} else { 
					if(count($this->ValuesHash) == 0) { 
						return('error: nothing to insert');
					} else { 
						foreach ($this->ValuesHash as $key => $value){
							$keys .= mysqli_real_escape_string($connectionPool[$this->params['db_host']], $key).',';
							$values .= "'".mysqli_real_escape_string($connectionPool[$this->params['db_host']], $value)."',";
						}
					}
				} 
				
				$sql .= trim($keys,',').') VALUES('.trim($values,',').') '.$where.';';
				
				break;
				
			case 'update':
				$sql = 'UPDATE '.$table.' SET ';
				$values = '';
				
				if (!is_null($array)) {
					// we have a populated values array and now need to loop through it and build the insert 
					foreach ($array as $key => $value){
						$values .= mysqli_real_escape_string($connectionPool[$this->params['db_host']], $key)." = '".mysqli_real_escape_string($connectionPool[$this->params['db_host']], $value)."',";
					}
				} else { 
					if (count($this->ValuesHash) == 0) { 
						return('error: nothing to update');
					} else { 
						foreach ($this->ValuesHash as $key => $value){
							$values .= mysqli_real_escape_string($connectionPool[$this->params['db_host']], $key)." = '".mysqli_real_escape_string($connectionPool[$this->params['db_host']], $value)."',";
						}
					}
				} 
				
				$sql .= trim($values,',') .$where.';';
				
				break;		
		}

		if ($connectionPool[$this->params['db_host']]->query($sql) === TRUE) {
   			return('success');
		} else {
			$this->Error = true ; 
			$this->ErrorString = $connectionPool[$this->params['db_host']]->error ;
			return(false);
		}
		
	}
	
	/**
	 * ExecuteMultiple
	 *
	 * used for inserting or updating 
	 *
	 */
	public function ExecuteMultiple($sql , $commit = TRUE) {
		$this->Error = FALSE;
		global $connectionPool;
		
		if ($connectionPool[$this->params['db_host']]->multi_query($sql) === TRUE) {
   			$return = $connectionPool[$this->params['db_host']]->affected_rows ; //.' - '. $sql;
			
			$connectionPool[$this->params['db_host']]->commit();
			
			return($return);
		} else {
			$this->Error = TRUE;
			$this->ErrorString = $connectionPool[$this->params['db_host']]->error.' - '. $sql;
			$return = $this->ErrorString;
			return($return);
		}
	
	}
	
	/**
	 * ExecuteCustom
	 *
	 * used for inserting or updating 
	 *
	 */
	public function ExecuteCustom($sql) {
		$this->Error = FALSE;
		global $connectionPool;
		
		if ($connectionPool[$this->params['db_host']]->query($sql) === TRUE) {
   			$return = $connectionPool[$this->params['db_host']]->info ;
			return($return);
		} else {
			$this->Error = TRUE;
			$this->ErrorString = $connectionPool[$this->params['db_host']]->error.' - '. $sql;
			$return = $this->ErrorString;
			return($return);
		}
	
	}
	
	
	/**
	 * clearValues
	 *
	 * used for inserting or updating 
	 *
	 */	
	public function clearValues() {
		unset($this->ValuesHash);
		$this->ValuesHash = array();
	}
	
	
	/**
	 * GetSingleValue method
	 *
	 * Returns the value for the key in specified table.
	 * 
	 * @param	table 	string	
	 * @param	key 	string
	 * @param	where 	string	
	 * @returns	value 	string	
	 *
	 */	
	public function GetSingleValue($table, $key, $where) {
		// bring the global var in the local scope.
		global $connectionPool;
		
		$qrystr = 'SELECT '.$key.' as RESULT FROM '.$table.' '.$where.' ;';
		
		// give me m y results !!
		if ( ! $result = $connectionPool[$this->params['db_host']]->query($qrystr) ) {
			// handle error 
			$this->Error = TRUE;
			$this->ErrorString = $connectionPool[$this->params['db_host']]->error.' - '. $sql;
			return(FALSE);
		} else {
			$row = mysqli_fetch_assoc($result);
      		return($row['RESULT']);
		}
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
		global $connectionPool;
		
		if 	($this->Commit) { 
			
		}
	}
}
?>
