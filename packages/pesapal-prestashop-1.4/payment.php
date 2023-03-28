<?php 
	include(dirname(__FILE__).'/../../config/config.inc.php');
	include(dirname(__FILE__).'/../../header.php');
	include('pesapal/OAuth.php');
	include("pesapal/xmlhttprequest.php");
	 
	if (!$cookie->isLogged())
		Tools::redirect('authentication.php?back=order.php');

	$token = $params = NULL; 
	$consumer_key = Configuration::get('PESAPAL_MERCHANT_KEY');
	$consumer_secret = Configuration::get('PESAPAL_MERCHANT_SECRET');
	$iframelink = 'https://www.pesapal.com/api/PostPesapalDirectOrderV4';
	
	$signature_method = new OAuthSignatureMethod_HMAC_SHA1(); 
	$consumer = new OAuthConsumer($consumer_key, $consumer_secret); 
		
	$amount = $_POST['amount']; 
	$amount = number_format($amount, 2);
	$currency =  $_POST['currency']; 
	$desc = Configuration::get('PESAPAL_DESCRIPTION'); 
	$type = "MERCHANT";
	$reference = $_POST['invoice_num'];
	$first_name = $_POST['firstname'];
	$last_name = $_POST['lastname']; 
	$email = $_POST['email'];
	$phonenumber = '';
	if($_POST['phone'])
		$phonenumber = $_POST['phone'];
	
	
	//Create callback URL
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on")
		$pageURL .= "s";
		
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80")
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	else
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		 
	$callback_url = str_replace('payment.php','paymentReturn.php',$pageURL);
	$post_xml = '<?xml version="1.0" encoding="utf-8"?><PesapalDirectOrderInfo xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" Currency="'.$currency.'" Amount="'.$amount.'" Description="'.$desc.'" Type="'.$type.'" Reference="'.$reference.'" FirstName="'.$first_name.'" LastName="'.$last_name.'" Email="'.$email.'" PhoneNumber="'.$phonenumber.'" xmlns="http://www.pesapal.com" />';
	$post_xml = htmlentities($post_xml);
	//var_dump($post_xml);exit;
	$iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframelink, $params); 
	$iframe_src->set_parameter('oauth_callback', $callback_url); 
	$iframe_src->set_parameter('pesapal_request_data', $post_xml); 
	$iframe_src->sign_request($signature_method, $consumer, $token); 
	
		echo "<iframe src='$iframe_src' width='100%' height='620px' scrolling='no' frameBorder='0'><p>Browser unable to load iFrame</p></iframe>"; 
	include_once(dirname(__FILE__).'/../../footer.php'); 
?>