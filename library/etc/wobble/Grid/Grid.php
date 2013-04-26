<?php 

/**
* class Grid_d
* 
* @file			Grid_d.php
* @author		David Lenwell
* @package		Repeater_d
* @subpackage	D_lib 
**/


/** 
 *  Grid_d
 */
class Grid_d 
{	
	// parameters array 
	private $params = array();	

	/**
	 * Constructor 
	 * @description	sets the default variables
	 *
	 * @param	string	name of the grid
	 * @param	string	template name
	 * @param	bool	use paging
	 * @param	integer	page length
	 * @return	void
	 */
	function __construct($gridName, $templateSet, $templatePath = '',  $usePaging = 'FALSE', $pageLimit = '') {
		$this->params['gridName'] = $gridName;
		$this->params['templateSet'] = $templateSet;
		$this->params['usePaging'] = $usePaging;
		$this->params['pageLimit'] = $pageLimit;
		$this->params['templatePath'] = $templatePath;
	}
	
	/**
	 * public function Output() 
	 *
	 * @param	string select
	 * @param	string table
	 * @param	string where
	 * @return	string HTML output 
	 */
	public function Output($from, $table, $where = '')  {
		global $config; 
		include_once $config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";	
		// need a db object 
		$data = New Mysql_d();
		
		// header 
		$header_template = new Template_d($this->params['templateSet'].'/header.html',$this->params['templatePath'] );
		$header_template->Set('GridName',$this->params['gridName']);
			
		// sorting links 
		$orderby = '';
		if ($this->l_post('sortby') <> '' && $this->l_post('sortby') <> '{|sortby:val|}') {
			$orderby = ' ORDER BY '.$this->l_post('sortby').' '.$this->l_post('sortDirection');
			$header_template->Set('sortby:val',$this->l_post('sortby'));
			$header_template->Set('sortDirection:val',$this->l_post('sortDirection')); 
		}
		
		// Limit and paging 
		$limit = '';
		if ($this->params['usePaging'] == 'TRUE') {
			$limit = $this->build_limit();
			$header_template->Set('curPage:val',$this->l_post('curPage'));
			
			$recordCount = $data->Count($table,$where);
			
			// build paging links here 
			for($a = 1; $a <= $this->total_pages($recordCount); $a++) {
				$bold_open = '';
				$bold_close = '';		
				
				if($a == $this->current_page()) {
					$bold_open = '<b>';
					$bold_close = '</b>';
				}
				
				$pageLinks .= '<span style="padding: 3px;" class="pointer" onClick="gridChangePage(\''.$this->params['gridName'].'\',\''.$a.'\')">'. $bold_open.$a.$bold_close .'</span> -';
			}
		}
	
		$ReturnVal .= $header_template->Output();
	
		// defualt counter for checking odd numbered rows .. CHANGE THIS ITS STUPID!!
		$counter = 1; 	
		
		// build the body 
		$row_template = new Template_d($this->params['templateSet'].'/row.html',$this->params['templatePath'] );
		
		$results = $data->Execute('SELECT', $from.' FROM '.$table, $where.$orderby.$limit);

		while ($row = $results->fetch_assoc()) {
			foreach ($row as $key => $value) {
				$row_template->Set($key, $value);
			}
			if ($counter > 1) {
				$row_template->Set('RowClass', 'EVEN');
				$row_template->Set('GridName',$this->params['gridName']);
				$counter = 1;
			} else { 
				$row_template->Set('RowClass', 'ODD');
				$row_template->Set('GridName',$this->params['gridName']);
				$counter = $counter + 1;
			}
			$ReturnVal .= $row_template->Output();
		}
		// add the footer
		$footer_template = new Template_d($this->params['templateSet'].'/footer.html',$this->params['templatePath'] );
		
		$footer_template->Set('PageLinks', $pageLinks);
		$footer_template->Set('count', $recordCount);
	
		$ReturnVal .= $footer_template->Output();
		return($ReturnVal) ;
	
	}
	
	/**
	 * Build the Limit Clause
	 *
	 * @param	void
	 * @return	string	limit clause
	 */
	private function build_limit() {
		$returnVal = ' LIMIT ';
		$returnVal .= $this->current_page() * $this->params['pageLimit'] - $this->params['pageLimit'].', ';
		$returnVal .= $this->params['pageLimit'];
		
		return($returnVal);
	}
	
	/**
	 * private function l_post() 
	 *
	 * @param	string request
	 * @return	string request response  
	 */
	private function l_post($request) {	
		return($_REQUEST[$this->params['gridName'].':'.$request]);
	}
	
	/**
	 * current_page
	 *
	 * @param	void
	 * @return	int	current page number 
	 */
	private function current_page() {
		if ($this->l_post('curPage') == '{|curPage:val|}' || $this->l_post('curPage') == '') {
			$curPage = 1;
		} else { 
			$curPage = $this->l_post('curPage') ;
		}
		return($curPage);
	}
	
	/**
	 * total_pages
	 *
	 * @param	Integer count
	 * @return	int	current page number 
	 */
	private function total_pages($count) {
		return(ceil($count /$this->params['pageLimit'] ));	
	}
}
?>