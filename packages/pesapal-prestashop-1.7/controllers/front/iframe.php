<?php
/*
* 2007-2015 PrestaShop
/**
 * @since 1.5.0
 */
class PesapalIframeModuleFrontController extends ModuleFrontController {

    /**
     * @see FrontController::initContent()
     */
    
    public function initContent()
    {
        parent::initContent();

        global $smarty,$cookie;

        $order_id = (int)Tools::getValue('id_order');
        $cart_id = (int)Tools::getValue('id_cart');
        $module_id = (int)Tools::getValue('id_module');
        
        $oldCart = new Cart($cart_id);
        $order = new Order($order_id);
        
        
        //this is another way of getting cart information
        //$cart = $this->context->cart;
        
        $consumer_key = Configuration::get('PESAPAL_MERCHANT_KEY');
        $consumer_secret = Configuration::get('PESAPAL_MERCHANT_SECRET');

        $iframelink = $this->module->api.'/PostPesapalDirectOrderV4';
        
        
        $signature_method = new OAuthSignatureMethod_HMAC_SHA1(); 
        $consumer = new OAuthConsumer($consumer_key, $consumer_secret);

        $invoiceAddress = new Address((int)$oldCart->id_address_invoice);
        
        /*format the amount keeping 2 places of decimal
        *like the way prestashop formats their figures.
        */
        $amount = number_format($oldCart->getOrderTotal(),2);

        $desc = "Prestashop-".(int)$order->id;
        $type = "MERCHANT";
        $reference =  (int)$order->id;
        $first_name = $invoiceAddress->lastname;
        $last_name = $invoiceAddress->firstname;
        $email =   $cookie->email;
        $phonenumber = $invoiceAddress->phone;

        $currency = Db::getInstance()->getValue('

                    SELECT `iso_code` 
                    FROM `'._DB_PREFIX_.'currency` 
                    WHERE `id_currency` = '.$oldCart->id_currency

                    );
        
        //$pageURL = Configuration::get('PESAPAL_CALLBACK_URL');
        $pageURL = $this->module->getebaseURL();
        $customer = new Customer($oldCart->id_customer);
        
        $callback_url = '/index.php?controller=order-confirmation&step=1&id_cart='.
        $cart_id.'&id_module='.$module_id.'&id_order='.$order_id.'&key='.$customer->secure_key;

        $callback_url = $pageURL.$callback_url;

        $post_xml = '<?xml version="1.0" encoding="utf-8"?>
                        <PesapalDirectOrderInfo 
                                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                                xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
                                Currency="'.$currency.'" 
                                Amount="'.$amount.'" 
                                Description="'.$desc.'" 
                                Type="'.$type.'" 
                                Reference="'.$reference.'" 
                                FirstName="'.$first_name.'" 
                                LastName="'.$last_name.'" 
                                Email="'.$email.'" 
                                PhoneNumber="'.$phonenumber.'" 
                                xmlns="http://www.pesapal.com" />';

        $post_xml = htmlentities($post_xml);
        
        $token = $params = NULL; 
        $iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframelink, $params); 
        $iframe_src->set_parameter('oauth_callback', $callback_url); 
        $iframe_src->set_parameter('pesapal_request_data', $post_xml); 
        $iframe_src->sign_request($signature_method, $consumer, $token); 

        $product_details = $oldCart->getProducts();
        $ordered_items = 0;

        foreach ($product_details as $product_detail) {
            # code...
            $ordered_items += $product_detail['cart_quantity'];
        }

        $ordered_items =  $ordered_items;
        $order_currency = $this->context->currency->sign;
        $ordered_items_total =  $order->total_products_wt;
        $order_shipping = $order->total_shipping_tax_excl;
        
        $smarty->assign(

            array(
                'url'=>$iframe_src,
                'ordered_items'=>$ordered_items,
                'order_currency'=>$order_currency,
                'ordered_items_total'=>$ordered_items_total,           
                'order_shipping'=>$order_shipping,           
            )
        );
        $this->setTemplate('module:pesapal/views/templates/front/iframe.tpl');
    }

    

}
