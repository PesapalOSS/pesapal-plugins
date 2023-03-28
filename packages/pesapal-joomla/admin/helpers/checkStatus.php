<?php
 	include_once('OAuth.php');
 	include_once('xmlhttprequest.php');

 	function checkStatus($tracking_id,$referenceNo){
		$token = $params = NULL;
		$statusrequest = 'https://www.pesapal.com/api/querypaymentstatusbymerchantref';
		if(!empty($tracking_id)){
            $statusrequest = 'https://www.pesapal.com/api/querypaymentdetails';
        }
		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		$dconfig = JComponentHelper::getParams('com_pesapal');
		$consumer_key = $dconfig->get('consumer_key');
        $mode = $dconfig->get('app_mode');
		$consumer_secret = $dconfig->get('consumer_secret');
		$consumer = new OAuthConsumer($consumer_key,$consumer_secret);

		if($mode=="demo"){
            $statusrequest ="https://demo.pesapal.com/api/querypaymentstatusbymerchantref";
            if(!empty($tracking_id)){
                $statusrequest ="https://demo.pesapal.com/api/querypaymentdetails";
            }
        }
			
		//get transaction status
		$request_status = OAuthRequest::from_consumer_and_token($consumer, $token,"GET", $statusrequest, $params);
		$request_status->set_parameter("pesapal_merchant_reference", $referenceNo);
        if(!empty($tracking_id)) {
            $request_status->set_parameter("pesapal_transaction_tracking_id", $tracking_id);
        }
		$request_status->sign_request($signature_method, $consumer, $token);

		$options = array(
				CURLOPT_RETURNTRANSFER => true,   // return web page
				CURLOPT_HEADER         => false,  // don't return headers
				CURLOPT_FOLLOWLOCATION => true,   // follow redirects
				CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
				CURLOPT_ENCODING       => "",     // handle compressed
				CURLOPT_USERAGENT      => "test", // name of client
				CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
				CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
				CURLOPT_TIMEOUT        => 120,    // time-out on response
		);

		$ch = curl_init($request_status);
		curl_setopt_array($ch, $options);
        $content=curl_exec($ch);
        curl_close($ch);

//		//curl request
//		$ajax_req =  new XMLHttpRequest();
//		$ajax_req->open("GET",$request_status);
//		$ajax_req->send();


//		//if curl request successful
//
//		if($ajax_req->status == 200){
//			$values = array();
//			$elements = preg_split("/=/",$ajax_req->responseText);
//			$values[$elements[0]] = $elements[1];
//		}
//		//transaction status
//		$status = $values['pesapal_response_data'];
		$values = array();
		$elements = preg_split("/=/",$content);
		$values[$elements[0]] = $elements[1];
		$res = explode( ',', $values['pesapal_response_data']);
		$array=array();
		if(count($res)==1){
		    $array['status']=$res[0];
        } else{
            $array['tracking_id']=$res[0];
            $array['method']=$res[1];
            $array['status']=$res[2];
            $array['reference']=$res[3];
        }

		return $array;
	}	

?>