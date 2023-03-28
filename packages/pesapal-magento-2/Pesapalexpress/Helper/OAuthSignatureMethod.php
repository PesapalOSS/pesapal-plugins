<?php

namespace Pesapal\Pesapalexpress\Helper;

 
class OAuthSignatureMethod extends \Magento\Framework\App\Helper\AbstractHelper
{
  public function check_signature(&$request, $consumer, $token, $signature) {
    $built = $this->build_signature($request, $consumer, $token);
    return $built == $signature;
  }
}
  ?>