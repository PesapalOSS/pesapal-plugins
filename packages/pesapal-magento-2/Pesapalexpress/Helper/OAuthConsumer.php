<?php

namespace Pesapal\Pesapalexpress\Helper;

 
 class OAuthConsumer extends \Magento\Framework\App\Helper\AbstractHelper  {
  public $key;
  public $secret;

  function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {
   $this->scopeConfig = $scopeConfig;
  $this->key 		= 	$this->scopeConfig->getValue('payment/pesapal/consumer_key');
  $this->secret 		= 	$this->scopeConfig->getValue('payment/pesapal/consumer_secret');

    $this->callback_url = NULL;
  }

  function __toString() {
    return "OAuthConsumer[key=$this->key,secret=$this->secret]";
  }
}

  ?>