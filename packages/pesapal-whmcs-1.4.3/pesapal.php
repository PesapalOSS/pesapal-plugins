<?php

// *************************************************************************
// *                                                                       *
// * WHMCS PesaPal payment Gateway                                         *
// * Copyright (c) WHMCS Ltd. All Rights Reserved,                         *
// * Tested on WHMCS Version: 8.5.2                                        *
// * Release Date: 9th May 2018                                             *
// * V1.4.2                                                                      *
// *************************************************************************
// *                                                                       *
// * Author:  Lazaro Ong'ele | PesaPal Dev Team                            *
// * Email:   developer@pesapal.com                                        *
// * Website: http://developer.pesapal.com | http://www.pesapal.com        *
// *                                                                       *
// *************************************************************************

if (isset($_GET['demo'])) {
    exit('is home');
}

function pesapal_config() {
    global $CONFIG;
    
    copyPesapalTemplates(); //tries to safely copy templates to respective folder
    $root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
    $ipnurl = $root.'modules/gateways/pesapal/ipn.php';

    $configarray = array(
        "FriendlyName" => array("Type" => "System", "Value" => "pesapal"),
        "adminuser" => array("FriendlyName" => "WHMCS Admin username", "Type" => "text", "Size" => "50",),
        "orderprefix" => array("FriendlyName" => "Order prefix", "Type" => "text", "Size" => "20", "Description" => "This is the prefix appended to all order to ensure you do not have duplicate pesapal merchant references generated by other systems connected to Pesapal."),
        "consumerkey" => array("FriendlyName" => "Consumer Key", "Type" => "text", "Size" => "50",),
        "consumersecret" => array("FriendlyName" => "Consumer Secret", "Type" => "text", "Size" => "50",),
        "testmode" => array("FriendlyName" => "Test Mode", "Type" => "yesno", "Description" => "Tick this to use demo/test mode. Ensure consumer/secret key pair used above belong to test credentials found here <strong><a href='https://developer.pesapal.com/api3-demo-keys.txt' target='_blank'>https://developer.pesapal.com/api3-demo-keys.txt</a></strong>.",),
        "basedomainurl" => array("FriendlyName" => "Base Domain", "Type" => "text", "Size" => "100", "Description" => "Base Domain URL e.g " . $systemurl),
    );
    
    return $configarray;
}

function pesapal_link($data) {
    global $CONFIG;

    $systemurl = ($CONFIG['SystemSSLURL']) ? $CONFIG['SystemSSLURL'] . '/' : $CONFIG['SystemURL'] . '/';
    $siteURL = $data['systemurl'];
    $data = serialize($data);
    $data = base64_encode($data);

    $gatewayConfigs = getGatewayVariables("pesapal");
    $baseUrl = ($gatewayConfigs['basedomainurl']) ? $gatewayConfigs['basedomainurl'] : $systemurl; //.'modules/gateways/pesapal/iframe.php';

    $iframelink = $baseUrl . 'modules/gateways/pesapal/iframe.php';

    $debug = new stdClass();
    $debug->domain = $CONFIG['Domain'];
    $debug->SystemURL = $CONFIG['SystemURL'];
    $debug->BaseDomainUrl = $gatewayConfigs['basedomainurl'];
    $debug->data = $data;
    $debugData = json_encode($debug, JSON_UNESCAPED_SLASHES);

    $code = '<form method="POST" action="' . $iframelink . '">
		<input type="hidden" name="order" value="' . $data . '" />
		<input type="submit" value="Pay Now" />
                
	    </form><span style="display:none;"><pre>' . $debugData . '</pre></span>';

    return $code;
}

function getBaseSystemUrl($url = NULL) {
    $pathInfo = pathinfo($url);
    $root = $pathInfo['dirname'];

    if (isset($_GET['test_baseurl']) == TRUE) {
        var_dump($pathInfo);
        var_dump(parse_url($url));
        exit();
    }

    return $root;
}

function copyPesapalTemplates() {
    global $CONFIG;

    try {
        if (!isset($CONFIG['Template']))
            throw new Exception("Template not set. Cannot discover Template Name", 1);

        $templateName = $CONFIG['Template'];

        $templatePath = __DIR__ . '/../../templates/' . $templateName . '/';

        if (!file_exists($templatePath) || !$templateName) {
            throw new Exception("Template \"$templateName\" doesn't exist.", 1);
        }

        $pesapalFinalTemplatePaths = array(
            __DIR__ . '/../../templates/' . $templateName . '/pesapal_iframe.tpl',
            __DIR__ . '/../../templates/' . $templateName . '/pesapal_callback.tpl'
        );

        $sourceDirectory = __DIR__ . '/pesapal/';
        $destinationDirectory = __DIR__ . '/../../templates/' . $templateName . '/';
        $templatesToCopy = array(
            'pesapal_iframe.tpl',
            'pesapal_callback.tpl'
        );

        foreach ($templatesToCopy AS $templateToCopy) {
            $sourceTemplatePath = $sourceDirectory . $templateToCopy;
            $destinationTemplatePath = $destinationDirectory . $templateToCopy;
            if (!file_exists($destinationTemplatePath)) {

                if (!file_exists($sourceTemplatePath)) {
                    throw new Exception("Pesapal Source Template ('$templateToCopy') Not Found. Please ensure to copy all the files correctly", 2);
                }

                $copySuccess = copy($sourceTemplatePath, $destinationTemplatePath);

                if (!$copySuccess) {
                    throw new Exception("Copy $sourceTemplatePath to $destinationTemplatePath Failed", 3);
                }
                logData("Copied Template: $destinationTemplatePath");
            }
        }
    } catch (Exception $ex) {
        logData("An exception Occured: " . $ex->getMessage());
    }
}

function logData($data = NULL, $logFile = 'event') {

    $output = print_r($data, TRUE);

    $logPath = __DIR__ . '/pesapal/' . $logFile . '.log';

    if (!file_exists($logPath)) {
        fopen($logPath, "w") or die('Cannot open file:  ' . $logPath);
    }

    error_log(date('m/d/Y H:i:s', time()) . "----- " . $output . "\n", 3, $logPath);
}

function pesapal_clientarea() {
    exit('test');
}