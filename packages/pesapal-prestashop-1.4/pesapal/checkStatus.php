<?php
 	function checkStatus($trackingId, $referenceNo){
		
		$token = $params = NULL;
		$statusrequest = 'https://www.pesapal.com/api/querypaymentstatus';
		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		$consumer_key = PESAPAL_MERCHANT_KEY;
		$consumer_secret = PESAPAL_MERCHANT_SECRET;
					
		$consumer = new OAuthConsumer($consumer_key,$consumer_secret);
			
		//get transaction status
		$request_status = OAuthRequest::from_consumer_and_token($consumer, $token,"GET", $statusrequest, $params);
		$request_status->set_parameter("pesapal_merchant_reference", $referenceNo);
		$request_status->set_parameter("pesapal_transaction_tracking_id",$trackingId);
		$request_status->sign_request($signature_method, $consumer, $token);

		//curl request
		$ajax_req =  new XMLHttpRequest();
		$ajax_req->open("GET",$request_status);
		$ajax_req->send();
		//if curl request successful

		if($ajax_req->status == 200){
			$values = array();
			$elements = preg_split("/=/",$ajax_req->responseText);
			$values[$elements[0]] = $elements[1];
		}
		//transaction status
		$status = $values['pesapal_response_data'];
			
		return $status;
	}	

?>