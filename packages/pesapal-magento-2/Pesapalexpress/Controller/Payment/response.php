<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Pesapal\Pesapalexpress\Controller\Payment;
use Pesapal\Pesapalexpress\Helper\Data;

 
/**
 * DirectPost Payment Controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Response extends \Magento\Framework\App\Action\Action
{
    protected $datafunctions;
    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        Data $datafunctions
    ) {
       
        $this->resource = $resource;
        $this->datafunctions = $datafunctions;

        parent::__construct($context);
    }
    public function execute()
{       $orderId 	= 	$_GET['pesapal_merchant_reference'];
        $trackingId	= 	$_GET['pesapal_transaction_tracking_id'];
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        if($orderId && $trackingId) {

            //Add pesapal tracking id to order
            
            $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            $trackingtable	=	$this->resource->getTableName('sales_order');
             $query		=	"UPDATE ".$trackingtable." SET `pesapal_transaction_tracking_id` = '".$trackingId."' WHERE `increment_id` = '".$orderId."' ";
            $connection->rawQuery($query);

            /** update the order's state
             * send order email and move to the success page
             */
            $this->datafunctions->updateOrder($orderId, $trackingId, 'processingorder');

             $this->_redirect('checkout/onepage/success');

        }
        else {
            // There is a problem in the response we got
            $this->datafunctions->cancelAction();
            $this->_redirect('checkout/onepage/failure');

        }
 }
 
}
