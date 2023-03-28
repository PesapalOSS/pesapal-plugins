<?php

namespace Pesapal\Pesapalexpress\Helper;
use Pesapal\Pesapalexpress\Helper\OAuthUtil;
use Pesapal\Pesapalexpress\Helper\OAuthSignatureMethod;

class OAuthSignatureMethodHMAC extends OAuthSignatureMethod {
  public function __construct(
	//\Magento\Framework\App\Helper\Context $context,
	  OAuthUtil $OAuthUtil
  )
  {
 	$this->OAuthUtil = $OAuthUtil;
	// parent::__construct($context);
  }
  function get_name() {
    return "HMAC-SHA1";
  }

  public function build_signature($request, $consumer, $token) {
    $base_string = $request->get_signature_base_string();
    $request->base_string = $base_string;
    $key_parts = array(
      $consumer->secret,
      ($token) ? $token->secret : ""
    );

    $key_parts = $this->OAuthUtil->urlencode_rfc3986($key_parts);
    $key = implode('&', $key_parts);

    return base64_encode(hash_hmac('sha1', $base_string, $key, true));
  }

 
}
  ?>