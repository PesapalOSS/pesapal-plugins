<?php
/*
* 2007-2015 PrestaShop
/**
 * @since 1.5.0
 */
class PesapalIpnReturnModuleFrontController extends ModuleFrontController {

    /**
     * @see FrontController::initContent()
     */
    
    public function initContent()
    {
        parent::initContent();

        global $smarty;

        
        $invoice = (int)Tools::getValue('pesapal_merchant_reference');
        $transaction_tracking_id = (string)Tools::getValue('pesapal_transaction_tracking_id');
        $payment_notification = Tools::getValue('pesapal_notification_type');
        
        $order = new Order($invoice);
        
        $ipn_resp = $this->ipn_return($order, $invoice, $transaction_tracking_id, $payment_notification);
        ob_start();
        echo $ipn_resp;
        ob_flush();
        exit();
        
        /*
         * $smarty->assign(
         
             array(
                 'ipn_resp'=>$ipn_resp   
             )
         );
        $this->setTemplate('module:pesapal/views/templates/front/ipn_return.tpl')
        */
    }
    
    
    
    public function ipn_return($order,$invoice,$transaction_tracking_id,$payment_notification) {

        $consumer_key = Configuration::get('PESAPAL_MERCHANT_KEY');
        $consumer_secret = Configuration::get('PESAPAL_MERCHANT_SECRET');
        $statusrequestAPI = $this->api. "/querypaymentstatus";
        $status = 'PLACED';
        
        if (!empty($transaction_tracking_id) && $payment_notification == "CHANGE") {

                $token = $params = NULL;
                $consumer = new OAuthConsumer($consumer_key, $consumer_secret);
                $signature_method = new OAuthSignatureMethod_HMAC_SHA1();

                //get transaction status
                $request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $statusrequestAPI, $params);
                $request_status->set_parameter("pesapal_merchant_reference", $invoice);
                $request_status->set_parameter("pesapal_transaction_tracking_id", $transaction_tracking_id);
                $request_status->sign_request($signature_method, $consumer, $token);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $request_status);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                if (defined('CURL_PROXY_REQUIRED'))
                        if (CURL_PROXY_REQUIRED == 'True') {
                                $proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
                                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
                                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                                curl_setopt($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
                        }

                $response = curl_exec($ch);

                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $raw_header = substr($response, 0, $header_size - 4);
                $headerArray = explode("\r\n\r\n", $raw_header);
                $header = $headerArray[count($headerArray) - 1];
                
                /*
                *options array is an argument for pesapalCheckStatus class located in $root/modules/pesapal/pesapal folder.
                */

                $options = array();
                $options['customer_key'] = Configuration::get('PESAPAL_MERCHANT_KEY');
                $options['customer_secret'] = Configuration::get('PESAPAL_MERCHANT_SECRET');
                $options['is_live'] = Configuration::get('PESAPAL_MERCHANT_IS_LIVE');
                
                $pesapal = new pesapalCheckStatus($options);

                $paymentDetails = $pesapal->getTransactionDetails($invoice, $transaction_tracking_id);
                $status = $paymentDetails['status'];
                $payment_method = $paymentDetails['payment_method']; 
                
                //update order status
                $this->module->updateOrderPaymentStatus($order,$status);
                //update payment method to the one used by user to make payments (MTN Mobile Money, Airtel Mobile Money, Credit Card) etc
                $this->module->updateOrderPaymentMethod($order->reference,$payment_method);
               
                $ipn_resp = "";
                if($status != 'PENDING'){
                    $ipn_resp = "pesapal_notification_type=$payment_notification&pesapal_transaction_tracking_id=$transaction_tracking_id&pesapal_merchant_reference=$invoice";           
                }
                return $ipn_resp;

        }
        
        
    }
    
}
