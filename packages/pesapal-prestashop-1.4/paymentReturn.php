<?php
	include(dirname(__FILE__).'/../../config/config.inc.php');
	include(dirname(__FILE__).'/../../header.php');
	include(dirname(__FILE__).'/pesapal.php');
	
	$pageURL = 'http';
	 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	 	$pageURL .= "://";
	 if ($_SERVER["SERVER_PORT"] != "80") {
	  	$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];//.$_SERVER["REQUEST_URI"];
	 } else {
	  	$pageURL .= $_SERVER["SERVER_NAME"];//.$_SERVER["REQUEST_URI"];
	 }

	$pesapal = new pesapal();
	$total = $cart->getOrderTotal(true, Cart::BOTH);
	$customer = new Customer((int)$cart->id_customer);

	$tracking_id = $_GET['pesapal_transaction_tracking_id'];
	$reference_no = $_GET['pesapal_merchant_reference'];
	$status = $pesapal->checkStatus($tracking_id,$reference_no);
?>	
<div id="center_column">
    <h1>Order confirmation</h1>
    <p>Your order payment status is <span class="bold"><?php if($status == 'INVALID') echo 'Unknown.. A confirmation message will be sent later.'; else echo $status; ?></span></p>
    <p>
    <?php
	
        if($status == "COMPLETED"){
            echo Configuration::get('PESAPAL_COMPLETED_MSG');
			$pesapal_status = Configuration::get('PS_OS_PAYMENT');
		}
        else if($status == "PENDING"){ 
            echo Configuration::get('PESAPAL_PENDING_MSG');
			$pesapal_status = Configuration::get('PS_OS_PREPARATION');
		}
        else if($status == "FAILED") {
            echo Configuration::get('PESAPAL_FAILED_MSG');
			$pesapal_status = Configuration::get('PS_OS_ERROR');
		}
        else{
            echo Configuration::get('PESAPAL_INVALID_MSG');
			$pesapal_status = Configuration::get('PS_OS_PREPARATION');
		}

		$pesapal->validateOrder((int)$cart->id,$pesapal_status, $total, $pesapal->displayName, NULL, array(), NULL, false, $customer->secure_key);
    ?>
    </p>
</div>

<?php include_once(dirname(__FILE__).'/../../footer.php');  ?>