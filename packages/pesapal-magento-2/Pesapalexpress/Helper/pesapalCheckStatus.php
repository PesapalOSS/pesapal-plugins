<?php
 
//include_once('oauth.php');
  namespace Pesapal\Pesapalexpress\Helper;
 use Pesapal\Pesapalexpress\Helper\OAuthSignatureMethodHMAC;
 use Pesapal\Pesapalexpress\Helper\OAuthConsumer;



class pesapalCheckStatus extends \Magento\Framework\App\Helper\AbstractHelper{

	var $token;
	var $params;
	var $consumer_key; // merchant key
	var $consumer_secret;//  merchant secret
	var $signature_method;//
	var $iframelink;
	var $statusrequest;
	var $detailedstatusrequest;
	public function __construct(
	\Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
         OAuthSignatureMethodHMAC $hmac,
	  OAuthConsumer $OAuthConsumer,
 	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
	{
		// PHPMailer has an issue using the relative path for it's language files
		
		$this->token = $this->params = NULL;
                $this->directory_list = $directory_list;
                $this->hmac = $hmac;
		$this->OAuthConsumer=$OAuthConsumer;
 		$this->scopeConfig = $scopeConfig;
		$this->consumer_key 		= 	$this->scopeConfig->getValue('payment/pesapal/consumer_key');
	        $this->consumer_secret 		= 	$this->scopeConfig->getValue('payment/pesapal/consumer_secret');
	        $this->sandbox			= 	$this->scopeConfig->getValue('payment/pesapal/test_api');

		if($this->sandbox){
			$this->iframelink = 'http://demo.pesapal.com/api/PostPesapalDirectOrderV4';
			$this->statusrequest = 'https://demo.pesapal.com/api/querypaymentstatus';
			$this->detailedstatusrequest = 'https://demo.pesapal.com/API/QueryPaymentDetails';
		}else{
			$this->iframelink = 'https://www.pesapal.com/api/PostPesapalDirectOrderV4';
			$this->statusrequest = 'https://www.pesapal.com/api/querypaymentstatus';
			$this->detailedstatusrequest = 'https://www.pesapal.com/API/QueryPaymentDetails';
		}
	
	 parent::__construct($context);
	}
	

	public function simplecheckStatus($pesapal_tracking_id,$reference){
 
		$token = $params = NULL;
		$this->signature_method = $this->hmac;

		$consumer = $this->OAuthConsumer;
		
		//get transaction status
		$request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $this->statusrequest, $params);
		$request_status->set_parameter("pesapal_merchant_reference", $reference);
		$request_status->set_parameter("pesapal_transaction_tracking_id",$pesapal_tracking_id);
		$request_status->sign_request($this->signature_method, $consumer, $token);
		
		$status = $this->curlRequest($request_status); 
		
		return $status;
	}
		
	public function detailedcheckStatus($pesapal_tracking_id,$reference){
 
		$token = $params = NULL;
	       $this->signature_method =    $this->hmac;

		$consumer = $this->OAuthConsumer;
		
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
	public function loadIframe($orderDetails=array(),$redirect=false){

	if(count($orderDetails)){
	$amount 		=	$orderDetails['grand_total'];
	$amount 		= 	number_format($amount, 2);
	$desc 			= 	$orderDetails["desc"];
	$type 			=  	"MERCHANT";
	$reference 		=  	$orderDetails['increment_id']; 
	$first_name 		= 	$orderDetails['customer_firstname'];
	$last_name 		= 	$orderDetails['customer_lastname'];
	$email 			=  	$orderDetails['customer_email'];
	$currency 		=  	$orderDetails['order_currency_code'];
	$phonenumber 		= 	'';
	$callback_url 		= 	$orderDetails['callback_url'];

        $this->signature_method =    $this->hmac;
	
	$consumer = $this->OAuthConsumer;		

	$post_xml 		= 	"<?xml version=\"1.0\" encoding=\"utf-8\"?>
							<PesapalDirectOrderInfo xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" 
								Currency=\"".$currency."\" 
								Amount=\"".$amount."\" 
								Description=\"".$desc."\" 
								Type=\"".$type."\" 
								Reference=\"".$reference."\" 
								FirstName=\"".$first_name."\" 
								LastName=\"".$last_name."\" 
								Email=\"".$email."\" 
								PhoneNumber=\"".$phonenumber."\" 
								xmlns=\"http://www.pesapal.com\" 
							/>";
	$post_xml = htmlentities($post_xml);
	$iframe_src 	= 	OAuthRequest::from_consumer_and_token($consumer, $this->token, "GET", $this->iframelink, $this->params);
	$iframe_src->set_parameter("oauth_callback", $callback_url);
	$iframe_src->set_parameter("pesapal_request_data", $post_xml);
	$iframe_src->sign_request($this->signature_method, $consumer, $this->token);
			
	
	 $iframe='<iframe src="'. $iframe_src.'" width="100%" height="700px"  scrolling="no" frameBorder="0">
            <p>Browser unable to load iFrame</p>
        </iframe>';
	if(!$redirect) return $iframe;
	else return $iframe_src;

	}

	
	}
	public function curlRequest($request_status){
		
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