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
class Index extends \Magento\Framework\App\Action\Action
{
    protected $datafunctions;
        
    protected $data;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\Order $salesOrderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        Data $datafunctions,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        
        $this->salesOrderFactory = $salesOrderFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->datafunctions = $datafunctions;
 	    $this->scopeConfig = $scopeConfig;
        $this->data=array(); 

        parent::__construct($context);
    }
    
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
       
        $orderid=$this->_checkoutSession->getLastRealOrder()->getIncrementId();
       
        $order   = $this->salesOrderFactory->loadByIncrementId($orderid);
        
        $orderDetails= $order->getData();
        
       
        $redirect = $this->scopeConfig->getValue('payment/pesapal/redirect');
    
       
        $orderDetails["callback_url"]=$storeManager->getStore()->getBaseUrl()."pesapalexpress/payment/response";
        
        $orderDetails["desc"] 	= 	"Payments for order no.".$orderDetails['increment_id']." Amounting to ".$orderDetails['order_currency_code']." ".$orderDetails['grand_total']." bought from ".$storeManager->getStore()->getName();
        
        $iframe=$this->datafunctions->pesapalIframe($orderDetails,$redirect);
        
        if($redirect){
            
         $this->_redirect($iframe);
         
        }else{
            
        $this->_view->loadLayout();
        
        $this->_view->getLayout()->initMessages();
       
        $this->_view->getLayout()->getBlock('pesapalexpress')->setData("iframe",$iframe);
        
        $this->_view->getLayout()->getBlock('pesapalexpress')->setName("Pesapal Payment");
        
        $this->_view->renderLayout();
        }
    }

}
