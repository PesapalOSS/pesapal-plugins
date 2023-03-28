<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Pesapal\Pesapalexpress\Controller\Payment;

 
/**
 * DirectPost Payment Controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Ipn extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Pesapal\Pesapalexpress\Helper\Data $datafunctions
    ) {
        $this->datafunctions = $datafunctions;

        parent::__construct($context);
    }
    public function execute()
    { 
      $orderId 	= 	$_GET['pesapal_merchant_reference'];
        $trackingId	= 	$_GET['pesapal_transaction_tracking_id'];
        $notificationType	= 	$_GET['pesapal_notification_type'];

        if($orderId && $trackingId) {

            //Add pesapal tracking id to order
            
            /** update the order's state
             * send order email and move to the success page
             */
            $this->datafunctions->updateOrder($orderId, $trackingId, 'completeorder');
            
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
  
}
