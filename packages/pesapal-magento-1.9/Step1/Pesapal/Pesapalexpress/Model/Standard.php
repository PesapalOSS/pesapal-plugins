<?php
/**
  * Name:		Pesapalexpress 
  * Type:		Payment Controller
  * Built by:	verviant <www.verviant.com>
  * Date:		3-13-2013
  * Tested on:	Magento ver. 1.7.0.2
 */
 
class Pesapal_Pesapalexpress_Model_Standard extends Mage_Payment_Model_Method_Abstract {
	protected $_code = 'pesapalexpress';
	
	protected $_isInitializeNeeded      = true;
	protected $_canUseInternal          = true;
	protected $_canUseForMultishipping  = false;
	
	public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl('pesapalexpress/payment/redirect', array('_secure' => true));
	}
}
?>