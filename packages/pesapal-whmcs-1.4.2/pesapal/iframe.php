<?php
// *************************************************************************
// *                                                                       *
// * WHMCS PesaPal payment Gateway                                         *
// * Copyright (c) WHMCS Ltd. All Rights Reserved,                         *
// * Tested on WHMCS Version: 7.1.1                                        *
// * Release Date: 9th May 2018                                             *
// * V1.4.2                                                                     *
// *************************************************************************
// *                                                                       *
// * Author:  Lazaro Ong'ele | PesaPal Dev Team                            *
// * Email:   developer@pesapal.com                                        *
// * Website: http://developer.pesapal.com | http://www.pesapal.com        *
// *                                                                       *
// *************************************************************************


require('OAuth.php'); 
define("CLIENTAREA",true);
//define("FORCESSL",true); // Uncomment to force the page to use https://
require("../../../init.php");
include("../../../includes/gatewayfunctions.php");
include("../../../invoicefunctions.php");
    
global $CONFIG;
    
$gatewaymodule  	= "pesapal"; 
$gateway        	= getGatewayVariables($gatewaymodule);
$systemurl 		= ($CONFIG['SystemSSLURL']) ? $CONFIG['SystemSSLURL'].'/' : $CONFIG['SystemURL'].'/';

$baseURL = $gateway['basedomainurl'] ? $gateway['basedomainurl'] : $systemurl;

    
# Checks gateway module is active before accepting callback
if (!$gateway["type"]) die("PesaPal Module Not Activated");
    
$order          = base64_decode($_POST["order"]);

$orderDetails   = array();
$orderDetails   = unserialize($order);
    
$invoiceid= checkCbInvoiceID($orderDetails['invoiceid'],$gateway["name"]);
    
if (!$invoiceid) die("Invalid order used");
      
if($gateway['testmode'])
    $gatewayapi     = "https://demo.pesapal.com";
else
    $gatewayapi     = "https://www.pesapal.com";

$token 		    = $params 	= NULL;
$iframelink 	    = $gatewayapi.'/api/PostPesapalDirectOrderV4';
$iframelink_mobile  = $gatewayapi.'/api/PostPesapalDirectOrderMobile';
$consumer_key 	    = $gateway['consumerkey'];
$consumer_secret    = $gateway['consumersecret'];
$signature_method   = new OAuthSignatureMethod_HMAC_SHA1();
$consumer 	    = new OAuthConsumer($consumer_key, $consumer_secret);
$amount             = $orderDetails['amount'];
$amount 	    = number_format($amount, 2);//format amount to 2 decimal places
$desc 		    = $orderDetails['description'];
$type 		    = 'MERCHANT';	
$first_name 	    = $orderDetails['clientdetails']['firstname'];
$last_name 	    = $orderDetails['clientdetails']['lastname'];
$email 		    = $orderDetails['clientdetails']['email'];
$phonenumber	    = $orderDetails['clientdetails']['phonenumber'];
$currency 	    = $orderDetails['currency'];
$reference 	    = $orderDetails['invoiceid'];
$callback_url 	    = $baseURL.'modules/gateways/callback/pesapal.php';
$post_xml	    = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                        <PesapalDirectOrderInfo 
                            xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" 
			    xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" 
			    Currency=\"".$currency."\" 
			    Amount=\"".$amount."\" 
			    Description=\"".$desc."\" 
			    Type=\"".$type."\" 
			    Reference=\"".$reference."\" 
			    FirstName=\"".$first_name."\" 
			    LastName=\"".$last_name."\" 
			    Email=\"".$email."\" 
			    PhoneNumber=\"".$phonenumber."\" 
			xmlns=\"http://www.pesapal.com\" />";
$post_xml = htmlentities($post_xml);
	

//post transaction to pesapal
$iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframelink, $params);
$iframe_src->set_parameter("oauth_callback", $callback_url);
$iframe_src->set_parameter("pesapal_request_data", $post_xml);
$iframe_src->sign_request($signature_method, $consumer, $token);

$ca = new WHMCS_ClientArea();
$ca->setPageTitle("Secure Online Payments | PesaPal");
$ca->addToBreadCrumb('index.php',$orderDetails['globalsystemname']);
$ca->initPage();
$ca->assign('iframe_src', $iframe_src);

$ca->setTemplate('pesapal_iframe'); 

$ca->output();
?>