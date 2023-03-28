<?php
/**
 * Name:		Pesapalexpress
 * Type:		Payment Controller
 * Built by:	verviant <www.verviant.com>
 * Date:		3-13-2013
 * Tested on:	Magento ver. 1.7.0.2
 */

class Pesapal_Pesapalexpress_PaymentController extends Mage_Core_Controller_Front_Action {
    // The redirect action is triggered when someone places an order
    public function redirectAction() {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','pesapalexpress',array('template' => 'pesapalexpress/paynow.phtml'));
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    // The response action is triggered when your gateway sends back a response after processing the customer's payment
    public function responseAction() {
        $orderId 	= 	$_GET['pesapal_merchant_reference'];
        $trackingId	= 	$_GET['pesapal_transaction_tracking_id'];


        if($orderId && $trackingId) {

            //Add pesapal tracking id to order
            $resource 		= 	Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $trackingtable	=	$resource->getTableName('sales_flat_order');
            $query			=	"UPDATE ".$trackingtable." SET `pesapal_transaction_tracking_id` = '".$trackingId."' WHERE `increment_id` = '".$orderId."' ";
            $writeConnection->query($query);

            /** update the order's state
             * send order email and move to the success page
             */
            $this->updateOrder($orderId, $trackingId, 'neworder');

            Mage::getSingleton('checkout/session')->unsQuoteId();
            Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=>true));
        }
        else {
            // There is a problem in the response we got
            $this->cancelAction();
            Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure'=>true));
        }
    }
    public function ipnAction() {
        $orderId 	= 	$_GET['pesapal_merchant_reference'];
        $trackingId	= 	$_GET['pesapal_transaction_tracking_id'];
        $notificationType	= 	$_GET['pesapal_notification_type'];


        if($orderId && $trackingId) {

            //Add pesapal tracking id to order
            $resource 		= 	Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $trackingtable	=	$resource->getTableName('sales_flat_order');

            /** update the order's state
             * send order email and move to the success page
             */
            $this->updateOrder($orderId, $trackingId, 'completeorder');
            if($notificationType=="CHANGE" && $trackingId!=''){
            $resp="pesapal_notification_type=".$notificationType."&pesapal_transaction_tracking_id=".$trackingId."&pesapal_merchant_reference=".$orderId;

                ob_start();

                echo $resp;

                ob_flush();

                exit;
            }
        }
        else {
            echo "There is a problem in the response we got";
        }
		exit;
    }

    // The cancel action is triggered when an order is to be cancelled
    public function cancelAction() {
        if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
            if($order->getId()) {
                // Flag the order as 'cancelled' and save it
                $order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Gateway has declined the payment.')->save();
            }
        }
    }

    public function cronAction() {
        require_once (Mage::getBaseDir('base') .DS. 'app'.DS.'code'.DS.'local'.DS.'Pesapal'.DS.'Pesapalexpress'.DS . 'Helper' . DS .'OAuth.php');
        require_once (Mage::getBaseDir('base') .DS. 'app'.DS.'code'.DS.'local'.DS.'Pesapal'.DS.'Pesapalexpress'.DS . 'Helper' . DS .'xmlhttprequest.php');

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('sales_flat_order');
        $query = "SELECT increment_id, pesapal_transaction_tracking_id FROM ".$table." WHERE status = 'pending' AND pesapal_transaction_tracking_id <>'' ORDER BY increment_id DESC LIMIT 10";
        $pending_orders = $readConnection->fetchAll($query);

        foreach($pending_orders as $pending_order){
            $status	= $this->updateOrder($pending_order['increment_id'], $pending_order['pesapal_transaction_tracking_id'], 'cron');

            echo '<b>Order: </b>'.$pending_order['increment_id'].' ----------------------- <b>Status:</b> '.$status;

            if($status != "PENDING")
                echo ' - <i>Updated</i>';
            echo '<br />';
        }
    }

    public function getPesapalOrdersAction() {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('sales_flat_order');
        $query = "SELECT increment_id, pesapal_transaction_tracking_id, status FROM ".$table." WHERE pesapal_transaction_tracking_id <>'' ORDER BY increment_id";
        $pesapal_orders = $readConnection->fetchAll($query);

        foreach($pesapal_orders as $pesapal_order){
            echo '<b>Order: </b>'.$pesapal_order['increment_id'].' ----------------------- <b>Tracking ID:</b> '.$pesapal_order['pesapal_transaction_tracking_id'].' ----------------------- <b>Site Status:</b> '.$pesapal_order['status'].'<br />';
        }
    }

    public function updateOrder($orderId, $trackingId, $action){
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($orderId);
        $results=$this->detailedCheckStatus($trackingId,$orderId);
        //Get order status
        $status	= $results['status'];
        if($status == 'INVALID'){
            $status	=	$this->simpleCheckStatus($orderId, $trackingId);
        }

        /** Update the order status if is new order
         * or
         * if action is cron, the new status is not pending
         */

        if($action == 'neworder' || $status != 'PENDING'){
            if($status == 'COMPLETED'){
                $order->setState('', true, 'Pesapal has authorized the payment. Status: COMPLETED');
                $order->setStatus('complete');
            }
            else if($status == 'PENDING')
                $order->setState(Mage_Sales_Model_Order::STATE_NEW, true, 'Pesapal has authorized the payment. Status: PENDING.');
            else if($status == 'FAILED')
                $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Pesapal has rejected this payment. Status: FAILED');
            else if($status == 'INVALID')
                $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, 'Pesapal could not verify the status of the payment');
        }

        /** Send mail if is a new order
         * or
         * If action is cron, send mail only if status changes to COMPLETED or FAILED
         */
        if($action == 'neworder' || $status == 'COMPLETED' || $status == 'FAILED'){
            $order->sendNewOrderEmail();
            $order->setEmailSent(true);
        }

        $order->save();

        return $status;
    }

    public function checkStatusByMerchantRefAndTrackingId($transaction_id,$trackingID){
        require_once (Mage::getBaseDir('base') .DS. 'app'.DS.'code'.DS.'local'.DS.'Pesapal'.DS.'Pesapalexpress'.DS . 'Helper' . DS .'OAuth.php');
        require_once (Mage::getBaseDir('base') .DS. 'app'.DS.'code'.DS.'local'.DS.'Pesapal'.DS.'Pesapalexpress'.DS . 'Helper' . DS .'xmlhttprequest.php');

        $token 				= 	$params = NULL;
        $consumer_key 		=	Mage::getStoreConfig('payment/pesapalexpress/consumer_key');
        $consumer_secret 	= 	Mage::getStoreConfig('payment/pesapalexpress/consumer_secret');
        $consumer 			= 	new OAuthConsumer($consumer_key, $consumer_secret);
        $signature_method	= 	new OAuthSignatureMethod_HMAC_SHA1();
        $sandbox			= 	Mage::getStoreConfig('payment/pesapalexpress/test_api');
        if($sandbox)
            $statusrequest	=	'http://demo.pesapal.com/api/querypaymentstatus';
        else
            $statusrequest	= 	'https://www.pesapal.com/api/querypaymentstatus';

        //get transaction status
        $request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $statusrequest, $params);
        $request_status->set_parameter("pesapal_merchant_reference", $trackingID);
        $request_status->set_parameter("pesapal_transaction_tracking_id", $transaction_id);
        $request_status->sign_request($signature_method, $consumer, $token);

        //curl request
        $ajax_req = new XMLHttpRequest();
        $ajax_req->open("GET",$request_status);
        $ajax_req->send();

        //if curl request successful
        if($ajax_req->status == 200){
            $values = array();
            $elements = preg_split("/=/",$ajax_req->responseText);
            $values[$elements[0]] = $elements[1];
        }if($_GET['type']=='check'){
            //var_dump($ajax_req);exit;
        }
        //transaction status
        $status = $values['pesapal_response_data'];
       // echo $status;
        return $status;
    }
    public function detailedCheckStatus($pesapalTrackingId,$order_id){

        require_once (Mage::getBaseDir('base') .DS. 'app'.DS.'code'.DS.'local'.DS.'Pesapal'.DS.'Pesapalexpress'.DS . 'Helper' . DS .'pesapalCheckStatus.php');

        $consumer_key 		=	Mage::getStoreConfig('payment/pesapalexpress/consumer_key');
        $consumer_secret 	= 	Mage::getStoreConfig('payment/pesapalexpress/consumer_secret');
        $sandbox			= 	Mage::getStoreConfig('payment/pesapalexpress/test_api');

        if($sandbox)
            $true	=true;//	'http://demo.pesapal.com/api/QueryPaymentStatusByMerchantRef';
        else
            $true	=false;//'https://www.pesapal.com/api/QueryPaymentStatusByMerchantRef';

        $pesapal=new pesapalCheckStatus($consumer_key,$consumer_secret,$true);
        //$pesapal=new pesapalCheckStatus("WpK6ReF5GFAJLErYIc94HQd2BX3djZWC","0rPpE9sBIno6Wop1fvtB/27iCZ4=",false);

        $results=$pesapal->detailedcheckStatus($pesapalTrackingId,$order_id);

        return $results;
    }
    public function simpleCheckStatus($pesapalTrackingId,$order_id){

        require_once (Mage::getBaseDir('base') .DS. 'app'.DS.'code'.DS.'local'.DS.'Pesapal'.DS.'Pesapalexpress'.DS . 'Helper' . DS .'pesapalCheckStatus.php');

          $consumer_key 		=	Mage::getStoreConfig('payment/pesapalexpress/consumer_key');
        $consumer_secret 	= 	Mage::getStoreConfig('payment/pesapalexpress/consumer_secret');
        $sandbox			= 	Mage::getStoreConfig('payment/pesapalexpress/test_api');

        if($sandbox)
            $true	=true;//	'http://demo.pesapal.com/api/QueryPaymentStatusByMerchantRef';
        else
            $true	=false;//'https://www.pesapal.com/api/QueryPaymentStatusByMerchantRef';

        $pesapal=new pesapalCheckStatus($consumer_key,$consumer_secret,$true);
        //$pesapal=new pesapalCheckStatus("WpK6ReF5GFAJLErYIc94HQd2BX3djZWC","0rPpE9sBIno6Wop1fvtB/27iCZ4=",false);

        $status=$pesapal->simplecheckStatus($pesapalTrackingId,$order_id);

        return $status;
    }

    public function checkStatusByMerchantRef($merchant_reference){
        require_once (Mage::getBaseDir('base') .DS. 'app'.DS.'code'.DS.'local'.DS.'Pesapal'.DS.'Pesapalexpress'.DS . 'Helper' . DS .'OAuth.php');
        require_once (Mage::getBaseDir('base') .DS. 'app'.DS.'code'.DS.'local'.DS.'Pesapal'.DS.'Pesapalexpress'.DS . 'Helper' . DS .'xmlhttprequest.php');

        $token 				= 	$params = NULL;
        $consumer_key 		=	Mage::getStoreConfig('payment/pesapalexpress/consumer_key');
        $consumer_secret 	= 	Mage::getStoreConfig('payment/pesapalexpress/consumer_secret');
        $consumer 			= 	new OAuthConsumer($consumer_key, $consumer_secret);
        $signature_method	= 	new OAuthSignatureMethod_HMAC_SHA1();
        $sandbox			= 	Mage::getStoreConfig('payment/pesapalexpress/test_api');
        if($sandbox)
            $statusrequest	=	'http://demo.pesapal.com/api/QueryPaymentStatusByMerchantRef';
        else
            $statusrequest	= 	'https://www.pesapal.com/api/QueryPaymentStatusByMerchantRef';

        //get transaction status
        $request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $statusrequest, $params);
        $request_status->set_parameter("pesapal_merchant_reference", $merchant_reference);
        $request_status->sign_request($signature_method, $consumer, $token);

        //curl request
        $ajax_req =  new XMLHttpRequest();
        $ajax_req->open("GET",$request_status);
        $ajax_req->send();

        //if curl request successful
        if($ajax_req->status == 200){
            $values = array();
            $elements = preg_split("/=/",$ajax_req->responseText);
            $values[$elements[0]] = $elements[1];
        }
        //transaction status
        $status = $values['pesapal_response_data'];

        return $status;
    }

    //Run this script once to create pesapal tracking table
    public function createPesapalTableAction() {

        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $table = $resource->getTableName('sales_flat_order');
        $query = 'Show columns from ' . $table. ' like "pesapal_transaction_tracking_id" ';
        $column = $connection->fetchAll($query);

        if(empty($column)){
            $trackingtable	=	$resource->getTableName('sales_flat_order');
            $query			=	"ALTER TABLE ".$trackingtable." ADD COLUMN pesapal_transaction_tracking_id VARCHAR(50) NULL";
            $connection->query($query);

            echo "Table Added........";
        }
        else{
            echo 'Pesapal tracking id column already exists...';
        }
    }
}