<?php
// there is a little duplication in code here .. to save time I did a copy and paste job rather than trying to add the direct pay functions to the existing classes .. 
class DoDirectPayment
{
	// user name
	private $API_UserName = 'ummastore_api1.gmail.com';

	// password
	private $API_Password = '2YSFS7E63ZSX72Q4';

	// signature
	private $API_Signature = 'AFcWxV21C7fd0v3bYYYRCpSSRl31AFzLF3MWV4W.fPl6s42Sa8DRBWyq';

	private $environment = 'sandbox'; //'sandbox';	// or 'beta-sandbox' or 'live'
	
	public $PAYMENTACTION ;//= $paymentType
	public $AMT ;//= $amount
	public $CREDITCARDTYPE ;//= $creditCardType
	public $ACCT ;//= $creditCardNumber
	public $EXPMONTH ;//=$padDateMonth$expDateYear
	public $EXPYEAR ;
	public $CVV2 ;//=$cvv2Number
	
	public $FIRSTNAME ;//=$firstName
	public $LASTNAME ;//=$lastName
	public $STREET ;//=$address1
	public $CITY ;//=$city
	public $STATE ;//=$state
	public $ZIP ;//=$zip
	public $COUNTRYCODE ;//=$country
	public $CURRENCYCODE ;//=$currencyID
	
	
	/**
	 * Send HTTP POST Request
	 *
	 * @param	string	The API method name
	 * @param	string	The POST Message fields in &name=value pair format
	 * @return	array	Parsed HTTP Response body
	 */
	private function PPHttpPost($methodName_, $nvpStr_) 
	{
		
		$API_Endpoint = "https://api-3t.paypal.com/nvp";
		$environment = $this->environment;
		if("sandbox" === $environment || "beta-sandbox" === $environment) {
			$API_Endpoint = "https://api-3t.$environment.paypal.com/nvp";
		}
		$version = urlencode('51.0');
	
		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
	
		// Set the API operation, version, and API signature in the request.
		$nvpreq = "METHOD=".$methodName_."&VERSION=".$version."&PWD=".urlencode($this->API_Password)."&USER=".urlencode($this->API_UserName)."&SIGNATURE=".urlencode($this->API_Signature).$nvpStr_;
		//echo $nvpreq;
		// Set the request as a POST FIELD for curl.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
	
		// Get response from the server.
		$httpResponse = curl_exec($ch);
	
		if(!$httpResponse) {
			exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
		}
	
		// Extract the response details.
		$httpResponseAr = explode("&", $httpResponse);
	
		$httpParsedResponseAr = array();
		foreach ($httpResponseAr as $i => $value) {
			$tmpAr = explode("=", $value);
			if(sizeof($tmpAr) > 1) {
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}
	
		if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
			exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
		}
	
		return $httpParsedResponseAr;
	}
	
	public function SubmitPayment()
	{
		
		// Add request-specific fields to the request string.
		$nvpStr =	"&PAYMENTACTION=Sale".
					"&IPADDRESS=".urlencode($_SERVER['REMOTE_HOST'] ).
					"&AMT=".urlencode($this->AMT).
					"&CREDITCARDTYPE=".urlencode($this->CREDITCARDTYPE).
					"&ACCT=".urlencode($this->ACCT).
					"&EXPDATE=".urlencode($this->EXPMONTH).'20'.urlencode($this->EXPYEAR).
					"&CVV2=".urlencode($this->CVV2).
					"&FIRSTNAME=".urlencode($this->FIRSTNAME).
					"&LASTNAME=".urlencode($this->LASTNAME).
					"&STREET=".urlencode($this->STREET).
					"&CITY=".urlencode($this->CITY).
					"&STATE=".urlencode($this->STATE).
					"&ZIP=".urlencode($this->ZIP).
					"&COUNTRYCODE=".urlencode($this->COUNTRYCODE).
					"&CURRENCYCODE=".urlencode($this->CURRENCYCODE)  ;
		
		// Execute the API operation; see the PPHttpPost function above.
		$httpParsedResponseAr = $this->PPHttpPost('DoDirectPayment', $nvpStr);
		
		if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
			//exit('Direct Payment Completed Successfully: '.print_r($httpParsedResponseAr, true));
			return 'SUCCESS';
		} else  {
		  $arrdump = "";
		  foreach ($httpParsedResponseAr as $k => $v) {
		    $arrdump .= "{$k} => {$v}<br />";
      }
      //return 'failed';
			return urldecode($httpParsedResponseAr['L_LONGMESSAGE0']);// . "<br/><br/><br/>" . str_replace("&", "<br/>&", $nvpStr); //print_r($httpParsedResponseAr);//$httpParsedResponseAr ;
			//exit('DoDirectPayment failed: ' . print_r($httpParsedResponseAr, true));
		}
	}		
}	


?>