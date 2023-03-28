<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Pesapal\Pesapalexpress\Block;

/**
  */
class Pesapalexpress extends \Magento\Framework\View\Element\Template
{
    private $messageManager;
     
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Order $salesOrderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Catalog\Model\Session $catalogSession, 

        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->directory_list = $directory_list;
        $this->getCatalogSession = $catalogSession;

        

        $this->_isScopePrivate =true;
        parent::__construct($context, $data);
    }


    
    public function _prepareLayout()
    {
    
    return parent::_prepareLayout();
    }
    /**
    * Retrieve current order
    *
    * @return \Magento\Sales\Model\Order
    */
        public function getOrder()
        {
           $orderId = $this->_checkoutSession->getLastOrderId();
           $order   = $this->salesOrderFactory->load($orderId);
           return $order->getData(); // you can access various order details from here. 
        }
}