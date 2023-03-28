<?php
// *************************************************************************
// *                                                                       *
// * WHMCS PesaPal payment Gateway                                         *
// * Copyright (c) WHMCS Ltd. All Rights Reserved,                         *
// * Tested on WHMCS Version: 7.1.2                                        *
// * Release Date: 9th May 2018                                             *
// * V1.4.2                                                                    *
// *************************************************************************
// *                                                                       *
// * Author:  Lazaro Ong'ele | PesaPal Dev Team                            *
// * Email:   developer@pesapal.com                                        *
// * Website: http://developer.pesapal.com | http://www.pesapal.com        *
// *                                                                       *
// *************************************************************************

if (isset($_GET['demo'])){
exit('working');   
}

//include("../../../dbconnect.php");
require_once __DIR__ . '/../../../init.php';
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");
include("checkStatus.php");

$pesapalTrackingId 		= null;
$pesapalNotification 		= null;
$pesapalMerchantReference	= null;
$amount				= null;
$fee				= null;
$dbUpdateSuccessful 		= false;
$checkStatus 			= new pesapalCheckStatus();
$gatewaymodule  		= $checkStatus->gatewaymodule;
$gateway        		= $checkStatus->gateway;

# Checks gateway module is active before accepting callback
if (!$gateway["type"]) die("PesaPal Module Not Activated");
	
if(isset($_GET['pesapal_merchant_reference']))
	$pesapalMerchantReference = $invoiceid = $_GET['pesapal_merchant_reference'];
		
if(isset($_GET['pesapal_transaction_tracking_id']))
	$pesapalTrackingId = $transid = $_GET['pesapal_transaction_tracking_id'];
		
if(isset($_GET['pesapal_notification_type']))
	$pesapalNotification=$_GET['pesapal_notification_type'];

/** 
  *check status of the transaction made
  *  -getTransactionDetails() - returns status only. 
  *  -getMoreDetails() - returns status, payment method, merchant reference and pesapal tracking id
*/
	
//$status 		= $checkStatus->checkTransactionStatus($pesapalMerchantReference);
//$status		= $checkStatus->checkTransactionStatus($pesapalMerchantReference,$pesapalTrackingId);
$transactionDetails	= $checkStatus->getTransactionDetails($pesapalMerchantReference,$pesapalTrackingId);
$status			= $transactionDetails['status'];


if($status=="COMPLETED")
	$values["status"]	= "Paid";
	
elseif($status=="FAILED")
	$values["status"]   	= "Failed";
	
elseif($status=="INVALID")
	$values["status"]   	= "Unpaid";
	
//Update your database
if($status != 'PENDING'){
	$command                    = "updateinvoice";
	$adminuser                  = $gateway["adminuser"];
	$values["invoiceid"]        = $invoiceid;
	$values["paymentmethod"]    = $gateway["name"];
	
	$results = localAPI($command,$values,$adminuser);
        
	logTransaction($gateway["name"],$_GET,$results);
}
	
if($results['result'] == 'success')
	$dbUpdateSuccessful = true;
	
/*test if IPN runs on status change
$to      = '';
$subject = 'IPN: '.$pesapalNotification;
$message = '<b>Merchant Reference: </b>'.$pesapalMerchantReference.'<br> ';
$message .= '<b>Tracking ID: </b>'.$pesapalTrackingId.'<br> ';
$message .= '<b>Payment Method: </b>'.$transactionDetails['payment_method'].'<br> ';
$message .= '<b>Status: </b>'.$status.'<br> ';
$message .= '<b>Database update: </b>'.$results['result'].'<br> ';
$headers = 'From: ipntester@pesapal.com' . "\r\n";
$headers .= 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
'Reply-To: no-reply@noreplyx.com' . "\r\n" .
'X-Mailer: PHP/' . phpversion();
	
mail($to, $subject, $message, $headers);*/
		
//If there was a status change and you updated your db successfully && the change is not to a Pending state	


if($pesapalNotification=="CHANGE" && $dbUpdateSuccessful && $status != "PENDING"){
	
	/*/Notify me when the IPN for this transaction is killed
	$to      = '';
	$subject = 'IPN Killer';
	$message = '<b>Merchant Reference: </b>'.$pesapalMerchantReference.'<br> ';
	$message .= '<b>Tracking ID: </b>'.$pesapalTrackingId.'<br> ';
	$message .= '<b>Status: </b>'.$status.'<br> ';
	$headers = 'From: ipntester@pesapal.com' . "\r\n";
	$headers .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n".
	'Reply-To: no-reply@noreplyx.com' . "\r\n" .
	'X-Mailer: PHP/' . phpversion();
	
	mail($to, $subject, $message, $headers);*/
	
	$resp	= "pesapal_notification_type=$pesapalNotification".		
			  "&pesapal_transaction_tracking_id=$pesapalTrackingId".
			  "&pesapal_merchant_reference=$pesapalMerchantReference";
			  
	ob_start();
	echo $resp;
	ob_flush();
	exit;
}else{
    var_dump($status);exit();
}
?>