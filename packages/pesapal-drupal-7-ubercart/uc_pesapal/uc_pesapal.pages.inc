<?php

	/*
	 * Finalizes pesapal transaction.
	 */
 	include_once('pesapal/OAuth.php');
 	include_once('pesapal/checkStatus.php');
 
	function uc_pesapal_makepayment(){
		$token = $params = NULL;
	
		$consumer_key = variable_get('uc_pesapal_consumer_key');
		$consumer_secret = variable_get('uc_pesapal_consumer_secret');
		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		
		$uc_pesapal_account = variable_get('uc_pesapal_account');
		
		#echo $uc_pesapal_account;
		#var_dump( $uc_pesapal_account );
		
		if($uc_pesapal_account == 'live')
		    $iframelink = 'https://www.pesapal.com/';
		else
		    $iframelink = 'https://demo.pesapal.com/';
			
		$iframelink .= 'api/PostPesapalDirectOrderV4';
		#var_dump( $iframelink );
		
		//get form details
		$amount = $_SESSION['cart']['total'];
		$amount = number_format($amount, 2);
		$currency = $_SESSION['cart']['currency'];
		$reference = variable_get('uc_pesapal_reference_code').$_POST['cart_order_id'];
		$desc = variable_get('uc_pesapal_desc');
		$type =  variable_get('uc_pesapal_policy');
		$first_name = $_POST['first_name'];
		$last_name = $_POST['last_name'];
		$email = $_POST['email'];
		$phonenumber = $_POST['phone'];
			
		$callback_url =  $_SESSION['cart']['callback_url'];
		
		$post_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?><PesapalDirectOrderInfo xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" Currency=\"".$currency."\" Amount=\"".$amount."\" Description=\"".$desc."\" Type=\"".$type."\" Reference=\"".$reference."\" FirstName=\"".$first_name."\" LastName=\"".$last_name."\" Email=\"".$email."\" PhoneNumber=\"".$phonenumber."\" xmlns=\"http://www.pesapal.com\" />";
		$post_xml = htmlentities($post_xml);
		
		$consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		
		//post transaction to pesapal
		$iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframelink, $params);
		$iframe_src->set_parameter("oauth_callback", $callback_url);
		$iframe_src->set_parameter("pesapal_request_data", $post_xml);
		$iframe_src->sign_request($signature_method, $consumer, $token);
		
		//display pesapal - iframe and pass iframe_src
		
		$pesapal_iframe = '<iframe src="'.$iframe_src.'" width="100%" height="620px"  scrolling="no" frameBorder="0"><p>Browser unable to load iFrame</p></iframe>';
	 
		 return $pesapal_iframe;
	 }
	 
	function uc_pesapal_complete($cart_id = 0){	
		//Check payment status from pesapal
		$trackingId = $_GET['pesapal_transaction_tracking_id'];
		$referenceNo = $_GET['pesapal_merchant_reference'];
		
		//$pesapal_payment_status = checkStatus($trackingId, $referenceNo);
		$pesapal_payment_status = "COMPLETED";
		
		//strip off the reference code so that we can load the order()
		$refcode = variable_get('uc_pesapal_reference_code');
		$orderid = str_replace($refcode, "", $referenceNo);
		$order = uc_order_load($orderid);
	
		if (!$order) {
			return t('An error has occurred during payment. Please contact us to ensure your order has submitted.');
		}
	
		if($pesapal_payment_status == 'COMPLETED'){
			$order->order_status = "pesapal_completed";
			drupal_set_message(t(variable_get('uc_pesapal_completed_msg')));
		}
		else if($pesapal_payment_status == 'PENDING'){
			$order->order_status = 'pesapal_pending';
			drupal_set_message(t(variable_get('uc_pesapal_pending_msg')));
		}
		else if($pesapal_payment_status == 'FAILED'){
			$order->order_status = 'pesapal_failed';
			uc_order_comment_save($order->order_id, 0, t('Payment has failed..'), 'admin');
			drupal_set_message(t(variable_get('uc_pesapal_failed_msg')), 'error');
		}
		else{
			$order->order_status = 'pesapal_invalid';
			uc_order_comment_save($order->order_id, 0, t('Confirm actual status from your pesapal merchant account dashboard'), 'admin');
			drupal_set_message(t(variable_get('uc_pesapal_invalid_msg')));
		}
			
		//save order
		uc_order_save($order);  
		
		//Empty that cart...
		uc_cart_empty($cart_id);
		
		//delete session data
	    unset($_SESSION['cart']);
	
		$build = uc_cart_complete_sale($order);
		
		return $build;
	}
