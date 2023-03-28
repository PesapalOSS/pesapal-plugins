<?php
/**
 * @file
 * Contains \Drupal\uc_pesapal\Controller\PesapalController.
 */
namespace Drupal\uc_pesapal\Controller;

require_once(drupal_get_path('module', 'uc_pesapal').'/src/Controller/OAuth.php');
require_once(drupal_get_path('module', 'uc_pesapal').'/src/Controller/xmlhttprequest.php');
//require_once(drupal_get_path('module', 'uc_pesapal').'/src/Controller/xmlhttprequest.php');
// Available in Drupal\uc_pesapal\Controller\PesapalCheckStatus class.

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
use Drupal\uc_pesapal\Controller\OAuthSignatureMethod_HMAC_SHA1;
use Drupal\uc_pesapal\Controller\PesapalCheckStatus;
//use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
//use Drupal\Core\Url;
use Drupal\uc_cart\CartManagerInterface;
use Drupal\uc_order\Entity\Order;

class PesapalController extends ControllerBase {
    
    
    /**
    * The cart manager.
    *
    * @var \Drupal\uc_cart\CartManager
    */
    protected $cartManager;
    
    /**
    * Variables to use often.
    */
    var $is_live;
    var $consumer_key;
    var $consumer_secret;
    var $module_config;
    var $api;

    /**
    * Constructs a TwoCheckoutController.
    *
    * @param \Drupal\uc_cart\CartManagerInterface $cart_manager
    *   The cart manager.
    */
    public function __construct(CartManagerInterface $cart_manager) {
        $this->cartManager = $cart_manager;
        $this->module_config = $this->config('uc_pesapal.settings');
        
        $this->is_live =  $this->module_config->get('uc_pesapal_is_live');
        $this->consumer_key =  $this->module_config->get('uc_pesapal_consumer_key');
        $this->consumer_secret =  $this->module_config->get('uc_pesapal_consumer_secret');
		
		if($this->is_live){
            $this->api = 'https://www.pesapal.com/api';
        }else{
            $this->api = 'https://demo.pesapal.com/api';
        }
    }

    /**
    * {@inheritdoc}
    */
    public static function create(ContainerInterface $container) {
    // @todo: Also need to inject logger
        return new static(
            $container->get('uc_cart.manager')
        );
    }
	
    public function makepayment(Request $request){
        $token = $params = NULL;
        
        $order = Order::load($request->request->get('merchant_order_id'));
        
        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
	    $consumer = new OAuthConsumer($this->consumer_key, $this->consumer_secret);
        
        
        $iframelink = $this->api.'/PostPesapalDirectOrderV4';
        //echo $iframelink."<br>";
        
        //get form details
        $amount = $_SESSION['cart']['total'];
        $amount = number_format($amount, 2);
        $currency = $_SESSION['cart']['currency'];
        $reference = $request->request->get('cart_order_id');
        $desc = "Ubercart-".(int)$order->id();
        $type = "MERCHANT";
        $first_name = $request->request->get('first_name');
        $last_name = $request->request->get('last_name');
        $email = $request->request->get('email');
        $phonenumber = $request->request->get('phone');

        $callback_url =  $_SESSION['cart']['callback_url'];

        $post_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>"
                . "<PesapalDirectOrderInfo xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" "
                . "xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" "
                . "Currency=\"".$currency."\" "
                . "Amount=\"".$amount."\" "
                . "Description=\"".$desc."\" "
                . "Type=\"".$type."\" "
                . "Reference=\"".$reference."\" "
                . "FirstName=\"".$first_name."\" "
                . "LastName=\"".$last_name."\" "
                . "Email=\"".$email."\" "
                . "PhoneNumber=\"".$phonenumber."\" "
                . "xmlns=\"http://www.pesapal.com\" />";
        
        $post_xml = htmlentities($post_xml);
        
        //post transaction to pesapal
        $iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframelink, $params);
        $iframe_src->set_parameter("oauth_callback", $callback_url);
        $iframe_src->set_parameter("pesapal_request_data", $post_xml);
        $iframe_src->sign_request($signature_method, $consumer, $token);

        //display pesapal - iframe and pass iframe_src

        $pesapal_iframe = '<iframe src="'.$iframe_src.'" '
                . 'width="100%" '
                . 'height="620px"'
                . '  scrolling="no" '
                . 'frameBorder="0">'
                . '     <p>Browser unable to load iFrame</p>'
                . '</iframe>';
        
        //return $this->t($pesapal_iframe);
        
        $output = [
            '#type' => 'markup',
            '#markup' => $this->t($pesapal_iframe),
        ];
        
        return $output;
    }
     
    public function complete($cart_id = 0,Request $request){	
        
		$ipn_resp = $order_status = "";
        $transaction_tracking_id = \Drupal::request()->query->get('pesapal_transaction_tracking_id');
        $reference = \Drupal::request()->query->get('pesapal_merchant_reference');
        $payment_notification = \Drupal::request()->query->get('pesapal_notification_type');
        
        $order = uc_order_load($reference); 
        
        $options = array();
        $options['customer_key'] = $this->consumer_key;
        $options['customer_secret'] = $this->consumer_secret;
        $options['is_live'] = $this->is_live;
        
        $pesapal = new PesapalCheckStatus($options);
        
        $paymentDetails = $pesapal->getTransactionDetails($reference, $transaction_tracking_id);
        $status = $paymentDetails['status'];
        $payment_method = $paymentDetails['payment_method'];
        
        $buyer = Unicode::substr($order->getAddress('billing')->first_name . ' ' . $order->getAddress('billing')->last_name, 0, 128);
        $buyer = ucwords($buyer);
        
        //echo $buyer;
        
        /* update order status to the current one
         * update payment method to the one used by user to make payments 
         * (MTN Mobile Money, Airtel Mobile Money, Credit Card) etc
         */
        
        $payment_method = "Pesapal - $payment_method";
		
		/*
		*IPN return section
		*/
		
		if (!empty($transaction_tracking_id) && $payment_notification == "CHANGE") {
			
			switch ($status) {
				case 'PENDING':
					$order_status = 'pesapal_pending';
					break;
				case 'COMPLETED':
					$order_status = "pesapal_completed";
					break;
				case 'FAILED':
					$status = 'pesapal_failed';
					break;
				case 'PLACED':
					$order_status = 'pesapal_placed';
					break;
				default:
					$order_status = 'pesapal_pending';
					break;
			}
			
			// Update the order with pesapal details  
            $this->updateOrder($order_status,$payment_method,$reference);
				
			if($status != 'PENDING'){
				$ipn_resp = "pesapal_notification_type=$payment_notification&pesapal_transaction_tracking_id=$transaction_tracking_id&pesapal_merchant_reference=$reference";  				
			}
			
			ob_start();
			echo $ipn_resp;
			ob_flush();
			exit(); 
			
		}//end of IPN section
		
		
        $msg = "";
        if (!$order) {
            return $this->t('An error has occurred during payment. Please contact us to ensure your order has submitted.');
        }
        
        //This function lets you log a comment to an order, either in the order comments 
        //section or admin comments section. 
        //Order comments are displayed to customers when they come by your site to check their order status. 
        //Admin comments are purely for administrative record keeping, so you can log information about a customer 
        //or order that you wouldn't want showing up on their screen.
        if (Unicode::strtolower($request->request->get('email')) !== Unicode::strtolower($order->getEmail())) {
            uc_order_comment_save($reference, 0, $this->t('Customer used a different e-mail address during payment: @email', ['@email' => SafeMarkup::checkPlain($request->request->get('email'))]), 'admin');
        }
        
        if (Unicode::strtolower($request->request->get('phone')) !== Unicode::strtolower($order->getAddress('billing')->phone)) {
            uc_order_comment_save($reference, 0, $this->t('Customer used a different Phone number during payment: @phone', ['@phone' => SafeMarkup::checkPlain($request->request->get('phone'))]), 'admin');
        }
        
        switch ($status) {
            case 'PENDING':
                $order_status = 'pesapal_pending';
                
                $msg = "<p>Pesapal payment method is $payment_method</p>";
                $msg .= "<h4><b>Payment Pending</b></h4>";
                $msg .= "Thank you <b>$buyer</b>, Your payment is being processed.<br/>";
                $msg .= "Once confirmed, You will receive an Email/SMS notification, and your payment settled instantly";
                
                // show the message after updating status and payment method
                drupal_set_message($this->t($msg));
                
                break;
            
            case 'COMPLETED':
                $order_status = "pesapal_completed";
                
                $msg = "<p>Pesapal payment method is $payment_method</p>";
                $msg .= "<h4><b>Payment Completed</b></h4>";
                $msg .= "Thank you <b>$buyer</b>, your payment has been processed successfully.";
                
                // show the message after updating status and payment method
                drupal_set_message($this->t($msg));
                
                break;
            
            case 'FAILED':
                $order_status = 'pesapal_failed';
                
                $msg = "<p>Pesapal payment method is $payment_method</p>";
                $msg .= "<h4><b>Payment Failed</b></h4>";
                $msg .= "Sorry <b>$buyer</b>, Your payment has failed. This could be because of several reasons:
                <br/>
                <ol style='margin:0 0 5px 30px;'>
                    <li>The card details you entered are incorrect.</li>
                    <li>Your bank may have blocked online payments.</li>
                    <li>You have insufficient funds in the card/mobile money account you are attempting to use.</li> 
                    <li>Your bank may have declined this transaction, kindly check with your bank.</li>
                </ol>
                <br>
                Kindly try again or contact support at support@pesapal.com for assistance.";
                
                // show the message after updating status and payment method
                drupal_set_message($this->t($msg), 'error');
                
                break;
            
            case 'PLACED':
                $order_status = 'pesapal_placed';
                $msg = "Your payment was placed. Kindly try again or contact support at support@pesapal.com for assistance.";
                
                // show the message after updating status and payment method
                drupal_set_message($this->t($msg));
                
                break;
            
            default:
                $order_status = 'pesapal_invalid';
                $msg = "Your payment was Invalid. Kindly try again or contact support at support@pesapal.com for assistance.";
                
                // show the message after updating status and payment method
                drupal_set_message($this->t($msg), 'error');
                break;
        }
          
        $cart = $this->cartManager->get($cart_id);
        $cart->emptyCart();

        // Add a comment to let sales team know this came in through the site.
        uc_order_comment_save($order->id(), 0, $this->t('Order created through website.'), 'admin');

        //updates the order status and returns a response to the user
        $build = $this->cartManager->completeSale($order); 
        
        $header = [
          'id' => $this->t('Item #'),
          'item' => $this->t('Item'),
          'price' => $this->t('Unit Price'),
          'qnty' => $this->t('Quantity'),
          'total' => $this->t('Total'),
        ];
        
        //var_dump($order->line_items);
        //exit;
        
        $items = [];
        $i = 0;
                
        foreach ($order->products as $product) {
            
            $items[$i]['id'] = $i+1;
            $items[$i]['item'] = $product->title->value; // @todo: HTML escape and limit to 128 chars
            $items[$i]['price'] = uc_currency_format($product->price->value, FALSE, FALSE, '.');
            $items[$i]['qnty'] = $product->qty->value;
            $items[$i]['total'] = uc_currency_format( ( ($product->price->value) * ($product->qty->value) ), FALSE, FALSE, '.' );
            $i++;
            
        }
        
        $subtotal = uc_currency_format( $order->line_items[0]['amount'], FALSE, FALSE, '.' );
        $shipping = uc_currency_format( $order->line_items[1]['amount'], FALSE, FALSE, '.' );  
        $currier = $order->line_items[1]['title'];
        
        $i++;
        $items[$i]['id'] = 'Subtotal';
        $items[$i]['item'] = '';
        $items[$i]['price'] = '';
        $items[$i]['qnty'] = '';
        $items[$i]['total'] = $subtotal;
        
        $i++;
        $items[$i]['id'] = "Shipping ($currier)";
        $items[$i]['item'] = '';
        $items[$i]['price'] = '';
        $items[$i]['qnty'] = '';
        $items[$i]['total'] = $shipping;
        
        $i++;
        $items[$i]['id'] = 'Total';
        $items[$i]['item'] = '';
        $items[$i]['price'] = '';
        $items[$i]['qnty'] = '';
        $items[$i]['total'] = uc_currency_format( ($subtotal + $shipping), FALSE, FALSE, '.' );
        
        //var_dump($items);
        //exit;
       
        $form['products'] = [
          '#type' => 'table',
          '#caption' => $this->t('Order Details'),
          '#header' => $header,
          '#rows' => $items,
          '#empty' => t('No product details found'),
        ];
        
        $output['admin_filtered_string'] = [
            '#markup' => $build['#message']."<br /><br />",
        ];
		
        $build['#type'] = 'markup';
        $build['#message'] = array_merge($form, $output);
        
        // Update the order with pesapal details  
        $this->updateOrder($order_status,$payment_method,$reference);
        
        //delete session data
        unset($_SESSION['cart']);
        
        return $build;
        
    }
    
    public function updateOrder($order_status,$payment_method,$reference){
         
        db_update('uc_orders')
        ->fields(array(
          'order_status' => $order_status,
          'payment_method' => $payment_method,
        ))
        ->condition('order_id', $reference, '=')
        ->execute();
        
        //return $affected_rows;
               
    }
      
}