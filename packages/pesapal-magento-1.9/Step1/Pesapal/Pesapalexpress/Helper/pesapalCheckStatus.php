<?php

//include_once('oauth.php');
require_once (Mage::getBaseDir('base') .DS. 'app'.DS.'code'.DS.'local'.DS.'Pesapal'.DS.'Pesapalexpress'.DS . 'Helper' . DS .'OAuth.php');
require_once (Mage::getBaseDir('base') .DS. 'app'.DS.'code'.DS.'local'.DS.'Pesapal'.DS.'Pesapalexpress'.DS . 'Helper' . DS .'pesapal-iframe.php');


class pesapalCheckStatus {

	var $consumer_key; // merchant key
	var $consumer_secret;//  merchant secret
	var $signature_method;//
	var $statusrequest;
	var $detailedstatusrequest;
	
	public function __construct($key,$secret,$isdemo=false)
	{
		// PHPMailer has an issue using the relative path for it's language files
		$pesapal=new JPesapal($key,$secret,$isdemo);
		//$this->token = $this->params = NULL;
		$this->consumer_key = $pesapal->consumer_key;
		$this->consumer_secret = $pesapal->consumer_secret;
		$this->signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		$this->statusrequest = $pesapal->statusrequest;
		$this->detailedstatusrequest= $pesapal->detailedstatusrequest;
	}
	

	function simplecheckStatus($pesapal_tracking_id,$reference){

		$token = $params = NULL;
		
		$consumer = new OAuthConsumer($this->consumer_key, $this->consumer_secret);
		
		//get transaction status
		$request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $this->statusrequest, $params);
		$request_status->set_parameter("pesapal_merchant_reference", $reference);
		$request_status->set_parameter("pesapal_transaction_tracking_id",$pesapal_tracking_id);
		$request_status->sign_request($this->signature_method, $consumer, $token);
		
		$status = $this->curlRequest($request_status); 
		
		return $status;
	}
		
	function detailedcheckStatus($pesapal_tracking_id,$reference){

		$token = $params = NULL;
		
		$consumer = new OAuthConsumer($this->consumer_key, $this->consumer_secret);
		
		//$guid = $transaction_id;//replace transaction_id with Transaction ID associated with the order
		//get transaction status
		$request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $this->detailedstatusrequest, $params);
		//$request_status->set_parameter("pesapal_request_data", $guid);
		$request_status->set_parameter("pesapal_merchant_reference", $reference);
		$request_status->set_parameter("pesapal_transaction_tracking_id",$pesapal_tracking_id);
		$request_status->sign_request($this->signature_method, $consumer, $token);
		
		$responseData = $this->curlRequest($request_status);
		
		$pesapalResponse = explode(",", $responseData);
		$pesapalResponseArray=array('pesapal_transaction_tracking_id'=>$pesapalResponse[0],
				   'payment_method'=>$pesapalResponse[1],
				   'status'=>$pesapalResponse[2],
				   'pesapal_merchant_reference'=>$pesapalResponse[3]);
				   
		return $pesapalResponseArray;
	}
	
	function curlRequest($request_status){
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_status);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		if(defined('CURL_PROXY_REQUIRED')) if (CURL_PROXY_REQUIRED == 'True'){
			$proxy_tunnel_flag = (
					defined('CURL_PROXY_TUNNEL_FLAG') 
					&& strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE'
				) ? false : true;
			curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
			curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
		}
		
		$response 					= curl_exec($ch);
		$header_size 				= curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$raw_header  				= substr($response, 0, $header_size - 4);
		$headerArray 				= explode("\r\n\r\n", $raw_header);
		$header 					= $headerArray[count($headerArray) - 1];
		
		//transaction status
		$elements = preg_split("/=/",substr($response, $header_size));
		$pesapal_response_data = $elements[1];
		
		return $pesapal_response_data;
	
	}

}
?>