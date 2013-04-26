<?php

class card extends module
{
	/**
	 * 	card module
	 *
	 **/
	private $page_content; 
	/**
	 * 	post_constructor
	 *
	 **/
	protected function post_constructor()
	{
		$this->page_title = "Get Your Card today."; 
		$this->meta_description = '';  
		$this->meta_keywords = "";
	}
	
	private function run_cc()
	{
		// run the credit card here return true or false.. 
		// for now 
		if ($_REQUEST['card_number'] == '4111111111111111')
		{
			return true; 
		} else {
			$_SESSION['cc_error'] = 'error: Declined, Please double check all information and try again.';
			return false; 
		}
	}
	
	/**
	 * 	ajax
	 *
	 **/
	protected function ajax()
	{
		global $URI;
		global $config;
		
		include_once $config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";
		$data = new Mysql_d();
		
		$return = 'no action';
		$action = $URI->slot[3];
		
		switch($action)
		{	
			case 'submit_order':
				$_SESSION['shipping_address_1'] = $_REQUEST['shipping_address_1'];
				$_SESSION['shipping_address_2'] = $_REQUEST['shipping_address_2'];
				$_SESSION['shipping_city'] = $_REQUEST['shipping_city'];
				$_SESSION['shipping_zip'] = $_REQUEST['shipping_zip'];
				
				$_SESSION['billing_address_1'] = $_REQUEST['billing_address_1'];
				$_SESSION['billing_address_2'] = $_REQUEST['billing_address_2'];
				$_SESSION['billing_city'] = $_REQUEST['billing_city'];
				$_SESSION['billing_zip'] = $_REQUEST['billing_zip'];
				
				$patient_id = $this->save_order(); 
				
				// if cc billing .. we can submit the payment 
				if ($_REQUEST['billing_type'] == 'cc')
				{
					// call cc processing function and return results 
					if ($this->run_cc()) // if cc successful 
					{
						$return = 'success';
						// update patient record to indicate payment and the type of payment. 
						// send thank you email and direct user to the thank you page.
					} else { // cc declined 
						// return an error to the user 
						$return = $_SESSION['cc_error'] ;
					}
				} else if ($_REQUEST['billing_type'] == 'cash') {
					// update database to reflect .. waiting on a cash payment .. 
				} else if ($_REQUEST['billing_type'] == 'paypal') {
					// do something for paypal
				}
				
				break; 
			case 'new':
				$return = 'new';
				
				break;
			/*
			
			case 'direct_pay_submit':
				include_once $config->lib_path."d_lib/Paypal_d/Paypal_d_direct_payment.php";
				
				$paypal = new DoDirectPayment();
				
				/*
				billing_match
				amount
				shipping_name
				shipping_address_1
				shipping_address_2
				shipping_city
				shipping_state
				shipping_zip
				card_name
				card_number
				exp_month
				exp_year
				cvv2_code
				billing_address_1
				billing_address_2
				billing_state
				billing_city
				billing_zip
				
				AMT
				CREDITCARDTYPE
				ACCT
				EXPDATE
				CVV2				
				FIRSTNAME 
				LASTNAME
				STREET
				CITY
				STATE 
				ZIP
				COUNTRYCODE 
				CURRENCYCODE
				
				/
				
				$paypal->PAYMENTACTION = 'Sale';
				$paypal->AMT = $_REQUEST['amount'];
				$paypal->CREDITCARDTYPE = $_REQUEST['card_type'];
				$paypal->ACCT = $_REQUEST['card_number'];
				$paypal->EXPMONTH = $_REQUEST['exp_month'] ;
				$paypal->EXPYEAR = $_REQUEST['exp_year'];
				$paypal->CVV2 = $_REQUEST['cvv2_code'];
				$paypal->CURRENCYCODE = 'USD';
				
				$paypal->FIRSTNAME = $_REQUEST['card_first_name'];
				$paypal->LASTNAME = $_REQUEST['card_last_name'];
	
				if ($_REQUEST['billing_match'] == 'true')
				{
					// we can use the shipping address as the billing data 
				
					$paypal->STREET = $_REQUEST['shipping_address_1'];
					$paypal->CITY = $_REQUEST['shipping_city'];
          if ($_REQUEST['shipping_country'] == 'US') {
  					$paypal->STATE = $_REQUEST['shipping_state'];
  					$paypal->ZIP = $_REQUEST['shipping_zip'];
  				}
  				else
  				{
  					$paypal->STATE = $_REQUEST['shipping_provance'];
  					$paypal->ZIP = $_REQUEST['shipping_postal_code'];
  				}
					$paypal->COUNTRYCODE = $_REQUEST['shipping_country'];
				} else { 
					$paypal->STREET = $_REQUEST['billing_address_1'];
					$paypal->CITY = $_REQUEST['billing_city'];
          if ($_REQUEST['billing_country'] == 'US') {
  					$paypal->STATE = $_REQUEST['billing_state'];
  					$paypal->ZIP = $_REQUEST['billing_zip'];
  				}
  				else
  				{
  					$paypal->STATE = $_REQUEST['billing_provance'];
  					$paypal->ZIP = $_REQUEST['billing_postal_code'];
  				}
					$paypal->COUNTRYCODE = $_REQUEST['billing_country'];
					
				}
				$response = $paypal->SubmitPayment() ;
				//echo "test";
				if ($response != 'SUCCESS') 
				{
//					echo "Could not process transaction. Please double check the credit card information.";
//					echo "Could not process transaction. Please double check the credit card information. {$response}";
					echo "{$response}";
					
				} else { 
					echo 'SUCCESS';
					
					$_SESSION['shipping_name'] = $_REQUEST['shipping_name'];
					$_SESSION['shipping_address_1'] = $_REQUEST['shipping_address_1'];
					$_SESSION['shipping_address_2'] = $_REQUEST['shipping_address_2'];
					$_SESSION['shipping_city'] = $_REQUEST['shipping_city'];
					$_SESSION['shipping_state'] = $_REQUEST['shipping_state'];
					$_SESSION['shipping_zip'] = $_REQUEST['shipping_zip'];
					$_SESSION['shipping_country'] = $_REQUEST['shipping_country'];
					$_SESSION['form_shipping_total'] = $_REQUEST['form_shipping_total'];
					$_SESSION['discount'] = $_REQUEST['discount'];
					$_SESSION['form_grand_total'] = $_REQUEST['form_grand_total'];
					$_SESSION['form_total_price'] = $_REQUEST['form_total_price'];
					
					// now we process the sale 
					// make this one query 
					$email_address = $data->GetSingleValue('users', 'email',"WHERE id = '".$AUTH->user_id."'");
					$first_name = $data->GetSingleValue('user_properties', 'value',"WHERE id_property = '1' and id_users = '".$AUTH->user_id."'");
					$last_name = $data->GetSingleValue('user_properties', 'value',"WHERE id_property = '2' and id_users = '".$AUTH->user_id."'");
					$order_id = $_SESSION['pending_order_id'] ;
					
					if ($AUTH->status != TRUE) {
  					$email_address = $_REQUEST['billing_email'];
  					$first_name = $_REQUEST['card_first_name'];
  					$last_name = $_REQUEST['card_last_name'];
					}
					
					// write order data to mysql 
					// If they used a promo code, increment the used_impressions count for that code
					if (isset($_REQUEST['promo_code']))
					{
						$data->ValuesHash['value'] = $_SESSION['used_impressions'] + 1 ;
						$data->Execute('INSERT', 'promo_codes' , "where promo_code = '".$_REQUEST['promo_code']."'");
						$data->clearValues();
					}
					
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_REQUEST['shipping_name'] ;
					$data->ValuesHash['id_property'] =  '12' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_REQUEST['shipping_address_1'] ;
					$data->ValuesHash['id_property'] =  '13' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_REQUEST['shipping_address_2'] ;
					$data->ValuesHash['id_property'] =  '14' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_REQUEST['shipping_city'] ;
					$data->ValuesHash['id_property'] =  '15' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					// now we need to add the requested item to the order_items table .. then we are done 
          if ($_REQUEST['shipping_country'] == 'US') {
					 $data->ValuesHash['value'] =  $_REQUEST['shipping_state'] ;
					}
					else {
					 $data->ValuesHash['value'] =  $_REQUEST['shipping_provance'] ;
					}
					$data->ValuesHash['id_property'] =  '16' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_REQUEST['shipping_country_long'] ;
					$data->ValuesHash['id_property'] =  '17' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					// now we need to add the requested item to the order_items table .. then we are done 
          if ($_REQUEST['shipping_country'] == 'US') {
  					$data->ValuesHash['value'] =  $_REQUEST['shipping_zip'] ;
  				}
  				else {
  					$data->ValuesHash['value'] =  $_REQUEST['shipping_postal_code'] ;
  				}
					$data->ValuesHash['id_property'] =  '18' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					
					// other data  form_grand_total  form_total_price form_shipping_total discount_form
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_REQUEST['form_grand_total'] ;
					$data->ValuesHash['id_property'] =  '19' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					
					$data->ValuesHash['value'] =  $_REQUEST['form_total_price'] ;
					$data->ValuesHash['id_property'] =  '20' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					
					$data->ValuesHash['value'] =  $_REQUEST['form_shipping_total'] ;
					$data->ValuesHash['id_property'] =  '21' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					
					$data->ValuesHash['value'] =  $_REQUEST['discount_form'] ;
					$data->ValuesHash['id_property'] =  '22' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					
					$data->ValuesHash['value'] =  $_REQUEST['shipping_method'] ;
					$data->ValuesHash['id_property'] =  '23' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					
					$data->ValuesHash['value'] =  $_REQUEST['billing_email'] ;
					$data->ValuesHash['id_property'] =  '24' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();

					// payment record 
					$_key = $this->get_uuid();
					$data->ValuesHash['id_payment_methods'] =  '3' ;
					$data->ValuesHash['amount'] = $_REQUEST['amount'] ;
					$data->ValuesHash['_key'] = $_key ;
					$data->Execute('INSERT', 'payments' , "");
					$data->clearValues();
					
					$payment_id = $data->GetSingleValue('payments', 'id',"WHERE _key = '".$_key."'");
					
					// set order payment id and status .. 
					$data->ValuesHash['status'] =  '2' ;
					$data->ValuesHash['id_payments'] =  $payment_id ;
					$data->Execute('UPDATE', 'orders' , "WHERE id = '".$_SESSION['pending_order_id']."'");
					$data->clearValues();
					
					// update download until date for the sound track 
					$this->update_download_dates($order_id);
					
					$download_links = new Repeater_c('download_links','download_links');
				
					$from = 'id,  name, download_until,  DATEDIFF(download_until,CURDATE()) AS date_diff ';
					$download_links = $download_links->Output($from, 'soundtracks', "WHERE id_users = '". $AUTH->user_id."' AND download_until > CURDATE()");
					
					
					
					// send the bloody email 
					include_once "/home/www.cuddletunes.com/library/PHPMailer_v5.0.0/class.phpmailer.php";
					include_once $config->lib_path."c_lib/Repeater_c/Repeater_c.php";
					
					$mail = new PHPMailer(false); // the true param means it will throw exceptions on errors, which we need to catch
					$mail->IsSMTP(); // telling the class to use SMTP
					
					try {
						$mail->Host = "smtp.gmail.com"; // SMTP server
						$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
						$mail->SMTPAuth   = true;                  // enable SMTP authentication
						$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
						$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
						$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
						$mail->Username   = "service@cuddletunes.com";  // GMAIL username
						$mail->Password   = "cuddl3tun3s01";            // GMAIL password
						$mail->SetFrom('service@cuddletunes.com', 'Customer Service');
						$mail->AddAddress($email_address, $first_name." ".$last_name);
						$mail->Subject = "Cuddletunes Reciept";
						$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
						$html_body = new Template_c('reciept_email.html');
				
						$list = new Repeater_c('my_cart','cart_grid2');
						$from = 'IF(color = "", "", CONCAT("with a ",color," ribbon")) as color, @id_soundtracks := id_soundtracks AS id_soundtracks, (SELECT name FROM soundtracks WHERE id = @id_soundtracks) AS soundtrack_name,@id_products := id_products AS id_products, (SELECT `desc` FROM products WHERE id = @id_products) AS product_desc, sum(qty) AS qty, price AS unit_price,sum(qty) * price AS total_price';
						$html_body->Set('cart', $list->Output($from, 'order_items', "WHERE id_orders = ".$order_id." GROUP BY id_products, color, id_soundtracks"));
						$html_body->Set('form_grand_total',$_REQUEST['form_grand_total']);
						// $order_id
						$html_body->Set('order_id',$order_id);
						$html_body->Set('form_total_price',$_REQUEST['form_total_price']);
						$html_body->Set('form_shipping_total',$_REQUEST['form_shipping_total']);
						$html_body->Set('discount',$_REQUEST['discount']);
						$html_body->Set('shipping_name',$_REQUEST['shipping_name']);
						$html_body->Set('shipping_address_1',$_REQUEST['shipping_address_1']);
						$html_body->Set('shipping_address_2',$_REQUEST['shipping_address_2']);
						$html_body->Set('shipping_city',$_REQUEST['shipping_city']);
						$html_body->Set('shipping_state',$_REQUEST['shipping_state']);
						$html_body->Set('shipping_zip',$_REQUEST['shipping_zip']);
						$html_body->Set('shipping_country',$_REQUEST['shipping_country']);
						$html_body->Set('shipping_method',$_REQUEST['shipping_method']);
						$html_body->Set('download_links',$download_links);
						$mail->MsgHTML($html_body->Output());
						
						$mail->Send();
						
					} catch (phpmailerException $e) {
						//echo $e->errorMessage(); //Pretty error messages from PHPMailer
					} catch (Exception $e) {
						//echo $e->getMessage(); //Boring error messages from anything else!
					}
				} 
				
				break; 
				
			case 'paypal_finalize':
				$output = new Template_c('paypal_finished.html');
				
				include_once $config->lib_path."c_lib/Paypal_c/Paypal_c.php";
							
				$doPay = new DoExpressCheckoutPayment($_SESSION['form_grand_total']);   
				$result = $doPay->getResponse();
				
				echo $result['ACK'];
				
				if ($result['ACK'] == 'Success') 
				{
					// make this one query 
					$email_address = $data->GetSingleValue('users', 'email',"WHERE id = '".$AUTH->user_id."'");
					$first_name = $data->GetSingleValue('user_properties', 'value',"WHERE id_property = '1' and id_users = '".$AUTH->user_id."'");
					$last_name = $data->GetSingleValue('user_properties', 'value',"WHERE id_property = '2' and id_users = '".$AUTH->user_id."'");
					$order_id = $_SESSION['pending_order_id'] ;
					
					// write order data to mysql 
					// If they used a promo code, increment the used_impressions count for that code
					if (isset($_SESSION['promo_code']))
					{
						$data->ValuesHash['value'] = $_SESSION['used_impressions'] + 1 ;
						$data->Execute('INSERT', 'promo_codes' , "where promo_code = '".$_SESSION['promo_code']."'");
						$data->clearValues();
					}
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_SESSION['shipping_name'] ;
					$data->ValuesHash['id_property'] =  '12' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_SESSION['shipping_address_1'] ;
					$data->ValuesHash['id_property'] =  '13' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_SESSION['shipping_address_2'] ;
					$data->ValuesHash['id_property'] =  '14' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_SESSION['shipping_city'] ;
					$data->ValuesHash['id_property'] =  '15' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_SESSION['shipping_state'] ;
					$data->ValuesHash['id_property'] =  '16' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_SESSION['shipping_country'] ;
					$data->ValuesHash['id_property'] =  '17' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_SESSION['shipping_zip'] ;
					$data->ValuesHash['id_property'] =  '18' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					
					// other data  form_grand_total  form_total_price form_shipping_total discount_form
					// now we need to add the requested item to the order_items table .. then we are done 
					$data->ValuesHash['value'] =  $_SESSION['form_grand_total'] ;
					$data->ValuesHash['id_property'] =  '19' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					
					$data->ValuesHash['value'] =  $_SESSION['form_total_price'] ;
					$data->ValuesHash['id_property'] =  '20' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					
					$data->ValuesHash['value'] =  $_SESSION['form_shipping_total'] ;
					$data->ValuesHash['id_property'] =  '21' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					
					$data->ValuesHash['value'] =  $_SESSION['discount_form'] ;
					$data->ValuesHash['id_property'] =  '22' ;
					$data->ValuesHash['id_orders'] =  $order_id ;
					$data->Execute('INSERT', 'shipping_properties' , "");
					$data->clearValues();
					
					// payment record 
					$_key = $this->get_uuid();
					$data->ValuesHash['id_payment_methods'] =  '1' ;
					$data->ValuesHash['amount'] = $_SESSION['form_grand_total'] ;
					$data->ValuesHash['_key'] = $_key ;
					$data->Execute('INSERT', 'payments' , "WHERE id = '".$order_id."'");
					$data->clearValues();
					
					$payment_id = $data->GetSingleValue('payments', 'id',"WHERE _key = '".$_key."'");
					
					// set order payment id and status .. 
					$data->ValuesHash['status'] =  '2' ;
					$data->ValuesHash['id_payments'] =  $payment_id ;
					$data->Execute('UPDATE', 'orders' , "WHERE id = '".$_SESSION['pending_order_id']."'");
					$data->clearValues();
					
					// update download until date for the sound track 
					$this->update_download_dates($order_id);
					
					$list = new Repeater_c('download_links','download_links');
				
					$from = 'id,  name, download_until,  DATEDIFF(download_until,CURDATE()) AS date_diff ';
					$download_links = $list->Output($from, 'soundtracks', "WHERE id_users = '". $AUTH->user_id."' AND download_until > CURDATE()");
					
					
					// send the bloody email 
					include_once "/home/www.cuddletunes.com/library/PHPMailer_v5.0.0/class.phpmailer.php";
					include_once $config->lib_path."c_lib/Repeater_c/Repeater_c.php";
					
					$mail = new PHPMailer(false); // the true param means it will throw exceptions on errors, which we need to catch
					$mail->IsSMTP(); // telling the class to use SMTP
					
					try {
						$mail->Host = "smtp.gmail.com"; // SMTP server
						$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
						$mail->SMTPAuth   = true;                  // enable SMTP authentication
						$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
						$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
						$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
						$mail->Username   = "service@cuddletunes.com";  // GMAIL username
						$mail->Password   = "cuddl3tun3s01";            // GMAIL password
						$mail->SetFrom('service@cuddletunes.com', 'Customer Service');
						$mail->AddAddress($email_address, $first_name." ".$last_name);
						$mail->Subject = "Cuddletunes Reciept";
						$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
						$html_body = new Template_c('reciept_email.html');
				
						$list = new Repeater_c('my_cart','cart_grid2');
						$from = 'IF(color = "", "", CONCAT("with a ",color," ribbon")) as color, @id_soundtracks := id_soundtracks AS id_soundtracks, (SELECT name FROM soundtracks WHERE id = @id_soundtracks) AS soundtrack_name,@id_products := id_products AS id_products, (SELECT `desc` FROM products WHERE id = @id_products) AS product_desc, sum(qty) AS qty, price AS unit_price,sum(qty) * price AS total_price';
						$html_body->Set('cart', $list->Output($from, 'order_items', "WHERE id_orders = ".$order_id." GROUP BY id_products, color, id_soundtracks"));
						$html_body->Set('form_grand_total',$_SESSION['form_grand_total']);
						// $order_id
						$html_body->Set('download_links',$download_links);
						$html_body->Set('order_id',$order_id);
						$html_body->Set('form_total_price',$_SESSION['form_total_price']);
						$html_body->Set('form_shipping_total',$_SESSION['form_shipping_total']);
						$html_body->Set('shipping_name',$_SESSION['shipping_name']);
						$html_body->Set('shipping_address_1',$_SESSION['shipping_address_1']);
						$html_body->Set('shipping_address_2',$_SESSION['shipping_address_2']);
						$html_body->Set('shipping_city',$_SESSION['shipping_city']);
						$html_body->Set('shipping_state',$_SESSION['shipping_state']);
						$html_body->Set('shipping_zip',$_SESSION['shipping_zip']);
						$html_body->Set('shipping_method',$_SESSION['shipping_method']);
						$html_body->Set('shipping_country',$_SESSION['shipping_country']);
						
						$mail->MsgHTML($html_body->Output());
						
						$mail->Send();
						
					} catch (phpmailerException $e) {
						//echo $e->errorMessage(); //Pretty error messages from PHPMailer
					} catch (Exception $e) {
						//echo $e->getMessage(); //Boring error messages from anything else!
					}
				} 
				break; 
				*/
		}
		
		echo $return ;
	}
	
	/**
	 * 	save_order
	 *
	 **/
	private function save_order()
	{
		$_key = $this->generateHash() ;
		
		include_once $this->config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";
		
		$data = new Mysql_d();
		
		$data->ValuesHash['state'] = $_SESSION['state'];
		$data->ValuesHash['email'] = $_SESSION['email'];
		$data->ValuesHash['state_lower'] = $_SESSION['state_lower'];
		$data->ValuesHash['full_state'] = $_SESSION['full_state'] ;
		$data->ValuesHash['first_name'] = $_SESSION['first_name'];
		$data->ValuesHash['last_name'] = $_SESSION['last_name'];
		$data->ValuesHash['address'] = $_SESSION['address_1'];
		$data->ValuesHash['address_2'] = $_SESSION['address_2'];
		$data->ValuesHash['city'] = $_SESSION['city'] ;
		$data->ValuesHash['zip'] = $_SESSION['zip'] ;
		$data->ValuesHash['dob'] = $_SESSION['dob_year'].'-'.$_SESSION['dob_month'].'-'.$_SESSION['dob_day'];
		
		$data->ValuesHash['shipping_address'] = $_SESSION['shipping_address_1'];
		$data->ValuesHash['shipping_address_2'] = $_SESSION['shipping_address_2'];
		$data->ValuesHash['shipping_city'] = $_SESSION['shipping_city'] ;
		$data->ValuesHash['shipping_zip'] = $_SESSION['shipping_zip'] ;
		
		$data->ValuesHash['billing_address'] = $_SESSION['billing_address_1'];
		$data->ValuesHash['billing_address_2'] = $_SESSION['billing_address_2'];
		$data->ValuesHash['billing_city'] = $_SESSION['billing_city'] ;
		$data->ValuesHash['billing_zip'] = $_SESSION['billing_zip'] ;
		
		
		if ($_SESSION['temp_file_name'] == "") 
		{
			$data->ValuesHash['has_photo'] = 0;
		} else { 
			$data->ValuesHash['has_photo'] = 1;
			$data->ValuesHash['photo_file_name'] = $_SESSION['temp_file_name'];
		}
		
		$data->ValuesHash['_key'] = $_key;
		
		
		$data->Execute('INSERT', 'patients',"" );
		
		
		return $data->GetSingleValue('patients', 'id',"WHERE _key = '".$_key."'");
		
	}
	
	
	/**
	 * 	Output
	 *
	 **/
	public function Output()
	{
		include_once $this->config->LIB_PATH."d_lib/Mysql_d/Mysql_d.php";
		include_once $this->config->LIB_PATH."d_lib/Template_d/Template_d.php";
		
		$data = new Mysql_d();
		
		$return = 'no action';
		$action = $this->URI->slot[2];
		
		
		switch($action)
		{	
			case '':
			case 'step1';
				$this->page_title = "Step 1 > Basic Information"; 
				$output = new Template_d('step1.html', $this->config->LIB_PATH.'/modules/card/templates/');
				
				$this->form_action = '/card/photo';
				
				$return = $output->Output();
				
				break;
			
			case 'photo';
				
				$this->page_title = "Step 2 > Photo"; 
				$output = new Template_d('photo.html', $this->config->LIB_PATH.'/modules/card/templates/');
				
				
				$this->form_action = '/card/crop';
				
				$output->Set('state', $_REQUEST['state']) ;
				$output->Set('state_lower', strtolower($_REQUEST['state'])) ;
				$output->Set('full_state', $_REQUEST['full_state']) ;
				$output->Set('first_name', $_REQUEST['first_name']) ;
				$output->Set('last_name', $_REQUEST['last_name']) ;
				$output->Set('address_1', $_REQUEST['address_1']) ;
				$output->Set('address_2', $_REQUEST['address_2']) ;
				$output->Set('city', $_REQUEST['city']) ;
				$output->Set('state', $_REQUEST['state']) ;
				$output->Set('zip', $_REQUEST['zip']) ;
				$output->Set('dob_month', $_REQUEST['dob_month']) ;
				$output->Set('dob_day', $_REQUEST['dob_day']) ;
				$output->Set('dob_year', $_REQUEST['dob_year']) ;
				
				$_SESSION['state'] =  $_REQUEST['state'];
				$_SESSION['email'] =  $_REQUEST['email'];
				$_SESSION['state_lower'] = strtolower($_REQUEST['state']) ;
				$_SESSION['full_state'] =  $_REQUEST['full_state'];
				$_SESSION['first_name'] =  $_REQUEST['first_name'];
				$_SESSION['last_name'] =  $_REQUEST['last_name'];
				$_SESSION['address_1'] =  $_REQUEST['address_1'];
				$_SESSION['address_2'] =  $_REQUEST['address_2'];
				$_SESSION['city'] =  $_REQUEST['city'];
				$_SESSION['zip'] =  $_REQUEST['zip'];
				$_SESSION['dob_month'] =  $_REQUEST['dob_month'];
				$_SESSION['dob_day'] =  $_REQUEST['dob_day'];
				$_SESSION['dob_year'] =  $_REQUEST['dob_year'];
				
				$return = $output->Output();
				
				break;
			
			case 'crop';
				$this->form_action = '/card/finish';
				// process the image upload 
				if (isset($_REQUEST['poo']))
				{
					if (($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/pjpeg"))
					{
						if ($_FILES["file"]["error"] > 0)
						{
							$error .= "Return Code: " . $_FILES["file"]["error"] . "<br />";
						}
						else
						{
							//echo "Upload: " . $_FILES["file"]["name"] . "<br />";
							//echo "Type: " . $_FILES["file"]["type"] . "<br />";
							//echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
							//echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";
							$ext_arr = explode('.',$_FILES["file"]["name"]);
							
							$ext = '.'.$ext_arr[1] ;
							
							
							$temp_file_name = $this->generateHash() . $ext = '.'.$ext_arr[1] ;
							
							
							move_uploaded_file($_FILES["file"]["tmp_name"], "/tmp/" . $temp_file_name );
							
						}
					}
					else
					{
						$error .= $_FILES["file"]["type"] ." This isn't a supported image type!";
					
						echo $error; 
					}
				
					// resize it to something managible .. (300 wide x max of 400 tall) 
					include_once $this->config->LIB_PATH."d_lib/Image_d/Image_d.php";
					
					$img = new Image_d("/tmp/".$temp_file_name);
					
					$img->Resize('300','400');
					
					$img_x = $img->GetX() ; 
					$img_y = $img->Gety() ;
					
					// save it to a public place 
					$img->Write($this->config->PUB_PATH.'/images/uploaded/user/'. $temp_file_name);
					
					$_SESSION['temp_file_name'] = $temp_file_name ;
					
					// load the croper
					$this->page_title = "Step 2b > Crop Photo"; 
					$output = new Template_d('crop.html', $this->config->LIB_PATH.'/modules/card/templates/');
					
					$output->Set('temp_image',  $temp_file_name) ;
					$output->Set('img_x', $img_x );
					$output->Set('img_y', $img_y );
					
					$output->Set('state', $_SESSION['state']) ;
					$output->Set('state_lower', $_SESSION['state_lower']) ;
					$output->Set('full_state', $_SESSION['full_state']) ;
					$output->Set('first_name', $_SESSION['first_name']) ;
					$output->Set('last_name', $_SESSION['last_name']) ;
					$output->Set('address_1', $_SESSION['address_1']) ;
					$output->Set('address_2', $_SESSION['address_2']) ;
					$output->Set('city', $_SESSION['city']) ;
					$output->Set('state', $_SESSION['state']) ;
					$output->Set('zip', $_SESSION['zip']) ;
					$output->Set('dob_month', $_SESSION['dob_month']) ;
					$output->Set('dob_day', $_SESSION['dob_day']) ;
					$output->Set('dob_year', $_SESSION['dob_year']) ;
					
					$return = $output->Output();
				
				} else { 
					// not sure .. either we push them back and give a photo error .. or we suck on chinese nutts 
				}
				
				break;
			
			case 'finish';
				// process photo crop 
				$output = new Template_d('finish.html', $this->config->LIB_PATH.'/modules/card/templates/');
				if ( $this->URI->slot[3] == 'without')
				{
					$output->Set('image_preview', 'without') ;		
				} else { 
					include_once $this->config->LIB_PATH."d_lib/Image_d/Image_d.php";
						
					$img = new Image_d( $this->config->PUB_PATH.'/images/uploaded/user/'.$_SESSION['temp_file_name']);
					
					$x1 = $_REQUEST['x1'] ;
					$y1 = $_REQUEST['y1'] ;
					$x2 = $_REQUEST['width'] ;
					$y2 = $_REQUEST['height'] ;
					
					$img->Crop($x1,$y1,$x2,$y2);
					
					// save it to a public place 
					$img->Write($this->config->PUB_PATH.'/images/uploaded/user/cropped/'. $_SESSION['temp_file_name']);
				
					$output->Set('image_preview', $_SESSION['temp_file_name']) ;
				}	
				
				
				
				
				$output->Set('state', $_SESSION['state']) ;
				$output->Set('state_lower', $_SESSION['state_lower']) ;
				$output->Set('full_state', $_SESSION['full_state']) ;
				$output->Set('first_name', $_SESSION['first_name']) ;
				$output->Set('last_name', $_SESSION['last_name']) ;
				$output->Set('address_1', $_SESSION['address_1']) ;
				$output->Set('address_2', $_SESSION['address_2']) ;
				$output->Set('city', $_SESSION['city']) ;
				$output->Set('state', $_SESSION['state']) ;
				$output->Set('zip', $_SESSION['zip']) ;
				$output->Set('dob_month', $_SESSION['dob_month']) ;
				$output->Set('dob_day', $_SESSION['dob_day']) ;
				$output->Set('dob_year', $_SESSION['dob_year']) ;
				
				
				
				$return = $output->Output();
				
				break;
				
			case 'checkout';
				
				$output = new Template_d('checkout.html', $this->config->LIB_PATH.'/modules/card/templates/');
				
				$return = $output->Output();
				break;
				
			case 'thankyou';
				
				$output = new Template_d('thankyou.html', $this->config->LIB_PATH.'/modules/card/templates/');
				
				$return = $output->Output();
				
				break;
		}
		
		return $return ;
	}
	
	/**
	 * 	OutputAdmin
	 *
	 **/
	public function OutputAdmin($URI)
	{
		global $config;
		include_once $config->LIB_PATH."d_lib/Template_d/Template_d.php";
		
		$return = '';
		
		switch($URI->slot[3])
		{	
			case 'orders' : 
				
				$return = 'orders';
				
				break;
			
			case 'states' : 
				
				$return = 'states';
				
				break;
			
	
		}
	
		return $return ;
	}
	
	
	/**
	 * 	OutputItemList
	 *
	 **/
	public function OutputAdminLeftMenu()
	{
		include_once $this->config->LIB_PATH."d_lib/Template_d/Template_d.php";
	
		$output = new Template_d('admin_nav.html', $this->config->LIB_PATH.'/modules/card/templates/');
		
		return $output->Output() ; 
	
	}
	

}



?>

