<?php

// *************************************************************************
// *                                                                       *
// * WHMCS PesaPal payment Gateway                                         *
// * Copyright (c) WHMCS Ltd. All Rights Reserved,                         *
// * Tested on WHMCS Version: 7.1.1                                        *
// * Release Date: 9th May 2018                                             *
// * V1.4.2                                                                       *
// *************************************************************************
// *                                                                       *
// * Author:  Lazaro Ong'ele | PesaPal Dev Team                            *
// * Email:   developer@pesapal.com                                        *
// * Website: http://developer.pesapal.com | http://www.pesapal.com        *
// *                                                                       *
// *************************************************************************


//include("../../../dbconnect.php");
require_once __DIR__ . '/../../../init.php';
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");
include("checkStatus.php");

global $CONFIG;
$ca = new WHMCS_ClientArea();
$ca->initPage();
$ca->requireLogin();

$pesapal = new pesapalCheckStatus();
$gatewaymodule = $pesapal->gatewaymodule;
$gateway = $pesapal->gateway;
$systemurl = ($CONFIG['SystemSSLURL']) ? $CONFIG['SystemSSLURL'] . '/' : $CONFIG['SystemURL'] . '/';

# Checks gateway module is active before accepting callback
if (!$gateway["type"])
    die("PesaPal Module Not Activated");

$data = $_GET['pid'];
$data = base64_decode($data);
$data = unserialize($data);

$pesapalTrackingId = $data['transactionid'];
$pesapalMerchantReference = $data['invoiceid'];

$status = $pesapal->checkTransactionStatus($pesapalMerchantReference, $pesapalTrackingId);
$transid = $pesapalTrackingId;
$amount = NULL;
$fee = NULL;


# Checks invoice ID is a valid invoice number or ends processing
$invoiceid = checkCbInvoiceID($pesapalMerchantReference, $gateway["name"]);

# Checks transaction number isn't already in the database and ends processing if it does
//checkCbTransID($transid);

$pesapalTransactionDetails = $pesapal->getTransactionDetails($pesapalMerchantReference, $pesapalTrackingId);


if ($status == "COMPLETED") {
    addInvoicePayment($invoiceid, $transid, $amount, $fee, $gatewaymodule);
    logTransaction($gateway["name"], $_GET, "Completed");

    #redirect to invoice page
    $invoice_url = $systemurl . 'viewinvoice.php?id=' . $invoiceid;
    //header("Location: $invoice_url");
    //exit;
} elseif ($status == "FAILED")
    $values["status"] = "Failed";
else
    $values["status"] = "Unpaid";

$command = "UpdateInvoice";
$adminuser = $gateway["adminuser"];
$values["invoiceid"] = $invoiceid;
$values["paymentmethod"] = $gateway["name"];

$results = localAPI($command, $values, $adminuser);
logTransaction($gateway["name"], $_GET, $results);

//Redirect to callback page
$ca->setPageTitle("Pesapal | Payment Summary");
$ca->addToBreadCrumb('index.php', 'Payment Summary');
$ca->assign('status', $status);
$ca->assign('invoiceid', $invoiceid);
$ca->assign('pesapalTrackingId', $pesapalTrackingId);
$ca->setTemplate('pesapal_callback');
$ca->output();
?>