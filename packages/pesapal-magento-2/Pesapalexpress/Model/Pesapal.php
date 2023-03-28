<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Pesapal\Pesapalexpress\Model;
 
 /**
 * Pay In Store payment method model
 */
class Pesapal extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'pesapal';

    /**
     * Availability option
     *
     * @var bool
     */
   protected $_isOffline = true;
   
   protected $_isInitializeNeeded      = true;
   
   protected $_canUseInternal          = true;
   
	 

}
