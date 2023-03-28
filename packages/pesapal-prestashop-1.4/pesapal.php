<?php
	include('pesapal/OAuth.php');
	include("pesapal/xmlhttprequest.php");
	class pesapal extends PaymentModule{
	
		private $_html = '';
		private $_postErrors = array();
	  
		public function __construct(){
			$this->name = 'pesapal';
			$this->tab = 'payments_gateways';
			$this->version = '1.0';
			$this->author = 'Team Pesapal';
	
			parent::__construct();
	
			$this->displayName = 'Pesapal';
			$this->description = $this->l('Receive payment with Pesapal');
		}
	
		public function install(){
			// Install SQL
			include(dirname(__FILE__).'/sql-install.php');
			foreach ($sql as $s)
				if (!Db::getInstance()->Execute($s))
					return false;
	
			return (parent::install() AND $this->registerHook('orderConfirmation') AND $this->registerHook('payment'));
		}
	
		public function uninstall(){
			// Uninstall SQL
			include(dirname(__FILE__).'/sql-uninstall.php');
			foreach ($sql as $s)
				if (!Db::getInstance()->Execute($s))
					return false;
	
			Configuration::deleteByName('PESAPAL_MERCHANT_KEY');
			Configuration::deleteByName('PESAPAL_MERCHANT_SECRET');
			Configuration::deleteByName('PESAPAL_DESCRIPTION');
			Configuration::deleteByName('PESAPAL_REFERENCE_CODE');
			Configuration::deleteByName('PESAPAL_COMPLETED_MSG');
			Configuration::deleteByName('PESAPAL_PENDING_MSG');
			Configuration::deleteByName('PESAPAL_FAILED_MSG');
			Configuration::deleteByName('PESAPAL_INVALID_MSG');
	
			return parent::uninstall();
		}
	
		public function getContent()
		{
			$html="";
			if (Tools::isSubmit('submitModule'))
			{
				Configuration::updateValue('PESAPAL_MERCHANT_KEY', Tools::getvalue('pesapal_merchant_key'));
				Configuration::updateValue('PESAPAL_MERCHANT_SECRET', Tools::getvalue('pesapal_merchant_secret'));
				Configuration::updateValue('PESAPAL_DESCRIPTION', Tools::getvalue('pesapal_description'));
				Configuration::updateValue('PESAPAL_REFERENCE_CODE', Tools::getvalue('pesapal_reference_code'));
				Configuration::updateValue('PESAPAL_COMPLETED_MSG', Tools::getvalue('pesapal_completed_msg'));
				Configuration::updateValue('PESAPAL_PENDING_MSG', Tools::getvalue('pesapal_pending_msg'));
				Configuration::updateValue('PESAPAL_FAILED_MSG', Tools::getvalue('pesapal_failed_msg'));
				Configuration::updateValue('PESAPAL_INVALID_MSG', Tools::getvalue('pesapal_invalid_msg'));
	
				$this->_html=$this->displayConfirmation($this->l('Configuration updated'));
			}
			return $this->_html.'
			<h2>'.$this->displayName.'</h2>
			<fieldset><legend> '.$this->l('Help').'</legend>
				<a href="http://www.pesapal.com/" style="float: right;"><img src="../modules/'.$this->name.'/logo_pesapal.jpg" alt="Pesapal" /></a>
				<h3>'.$this->l('In your PrestaShop admin panel').'</h3>
				'.$this->l('1. Open an account online at <a href="http://www.pesapal.com/" style="float: right;">www.pesapal.com</a> as a merchant ').'
				'.$this->l('<blockquote>You will receive an email with a consumer_key and consumer_secret</blockquote>').'
				'.$this->l('2. Fill in the consumer key and consumer secret in the general settings below').'<br /><br />
				'.$this->l('3. Add a small decription about the transaction. Ideally a 5-10 letter description').'
				'.$this->l('<blockquote>Eg, Purchase on goods from ABC company').'<br />
				'.$this->l('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Donations to XYZ organization</blockquote>').'
				'.$this->l('4. Input a 3-4 letter code to be used as a reference for each transaction. <b>Note:</b> Each business MUST have a unique code').'
				'.$this->l('<blockquote>Eg, ABC - For organization ABC').'<br />
				'.$this->l('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ZACK - For the bring ZACK back home campaign etc</blockquote>').'<br /><br />
				'.$this->l('4. Key in the message that will be displayed to the client after a payment has been made.<br /> 
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pending message: </b>There is an ongoing communication between pesapal and an external entity hence the status is yet to be confirmed.<br />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Invalid message: </b>This system can not actually verify the status of the payment due to some reason. However, as a merchant you can check the actual status when you login to your merchants account.').'<br /><br />
				'.$this->l('5. Update your settings and test the pesapal payment plugin').'
				
			</fieldset><br />
			<ul id="menuTab">
					<li id="menuTab1" class="menuTabButton selected">1. '.$this->l('General Settings').'</li>
			</ul>
				<div id="tabList">
					<div id="menuTab1Sheet" class="tabItem selected">
					<form action="'.Tools::htmlentitiesutf8($_SERVER['REQUEST_URI']).'" method="post">
						<fieldset>
							<legend><img src="../img/admin/contact.gif" alt="" />'.$this->l('General Settings').'</legend>
							<span class="inputs">
								<label for="pesapal_merchant_key">'.$this->l('Consumer Key').'</label>
								<input type="text" size="61" id="pesapal_merchant_key" name="pesapal_merchant_key" value="'.Configuration::get('PESAPAL_MERCHANT_KEY').'" />
							</span>
							
							<span class="inputs">
								<label for="pesapal_merchant_secret">'.$this->l('Consumer Secret').'</label>
								<input type="text" size="61" id="pesapal_merchant_secret" name="pesapal_merchant_secret" value="'.Configuration::get('PESAPAL_MERCHANT_SECRET').'" />
							</span>
							
							<span class="inputs">
								<label for="pesapal_description">'.$this->l('Description').'</label>
								<input type="text" size="61" id="pesapal_description" name="pesapal_description" value="'.Configuration::get('PESAPAL_DESCRIPTION').'" />
							</span>
							
							<span class="inputs">
								<label for="pesapal_reference_code">'.$this->l('Reference Code').'</label>
								<input type="text" size="61" id="pesapal_reference_code" name="pesapal_reference_code" value="'.Configuration::get('PESAPAL_REFERENCE_CODE').'" />
							</span>
							
							<span class="inputs">
								<label for="pesapal_completed_msg">'.$this->l('Completed Message').'</label>
								<textarea rows="5" cols="57" id="pesapal_completed_msg" name="pesapal_completed_msg">'.Configuration::get("PESAPAL_COMPLETED_MSG").'</textarea>
							</span>
							
							<span class="inputs">
								<label for="pesapal_pending_msg">'.$this->l('Pending Message').'</label>
								<textarea rows="5" cols="57" id="pesapal_pending_msg" name="pesapal_pending_msg">'.Configuration::get("PESAPAL_PENDING_MSG").'</textarea>
							</span>
							
							<span class="inputs">
								<label for="pesapal_failed_msg">'.$this->l('Failed Message').'</label>
								<textarea rows="5" cols="57" id="pesapal_failed_msg" name="pesapal_failed_msg">'.Configuration::get("PESAPAL_FAILED_MSG").'</textarea>
							</span>
							
							<span class="inputs">
								<label for="pesapal_invalid_msg">'.$this->l('Invalid Message').'</label>
								<textarea rows="5" cols="57" id="pesapal_invalid_msg" name="pesapal_invalid_msg">'.Configuration::get("PESAPAL_INVALID_MSG").'</textarea>
							</span>
							
							
							<br /><center><input type="submit" name="submitModule" value="'.$this->l('Update settings').'" class="button" /></center>
						</fieldset>
					</form>
					</div>
			
				<br clear="left" />
				<br />
				<style>
					#menuTab { float: left; padding: 0; margin: 0; text-align: left; }
					#menuTab li { text-align: left; float: left; display: inline; padding: 5px; padding-right: 10px; background: #EFEFEF; font-weight: bold; cursor: pointer; border-left: 1px solid #EFEFEF; border-right: 1px solid #EFEFEF; border-top: 1px solid #EFEFEF; }
					#menuTab li.menuTabButton.selected { background: #FFF6D3; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; }
					#tabList { clear: left; }
					.tabItem { display: none; }
					.tabItem.selected { display: block; background: #FFFFF0; border: 1px solid #CCCCCC; padding: 10px; padding-top: 20px; }
					.inputs{ margin:5px; display:block}
					.inputs label{ float:left; text-align: left; width:130px; margin :0 5px;}
				</style>
				<script>
					$(".menuTabButton").click(function () {
					  $(".menuTabButton.selected").removeClass("selected");
					  $(this).addClass("selected");
					  $(".tabItem.selected").removeClass("selected");
					  $("#" + this.id + "Sheet").addClass("selected");
					});
				</script>
			';
		}

		/**
		* hookPayment($params)
		* Called in Front Office at Payment Screen - displays user this module as payment option
		*/
		function hookPayment($params){
			global $smarty,$cookie;
	
			if (!Configuration::get('PESAPAL_MERCHANT_KEY') || !Configuration::get('PESAPAL_MERCHANT_SECRET'))
				return false;
				
			$invoiceAddress = new Address((int)$params['cart']->id_address_invoice);
			$PESAPALParams = array();
			$PESAPALParams['invoice_num'] =  Configuration::get('PESAPAL_REFERENCE_CODE').'-'.(int)$params['cart']->id;
			$PESAPALParams['client_num'] = (int)$invoiceAddress->id_customer;
			$PESAPALParams['lastname'] = $invoiceAddress->lastname;
			$PESAPALParams['firstname'] = $invoiceAddress->firstname;
			$PESAPALParams['amount'] = number_format($params['cart']->getOrderTotal(true, 3), 2, '.', '');
			$PESAPALParams['address'] = $invoiceAddress->address1.' '.$invoiceAddress->address2;
			$PESAPALParams['city'] = $invoiceAddress->city;
			$PESAPALParams['state'] = State::getNameById((int)$invoiceAddress->id_state);
			$PESAPALParams['country'] = Country::getIsoById((int)$invoiceAddress->id_country);
			$PESAPALParams['phone'] = $invoiceAddress->phone;
			$PESAPALParams['zip'] = $invoiceAddress->postcode;
			$PESAPALParams['email'] = $cookie->email;
			$PESAPALParams['cartid'] = $params['cart']->id;
			$currency = Db::getInstance()->getValue('
				SELECT `iso_code` 
				FROM `'._DB_PREFIX_.'currency` 
				WHERE `id_currency` = '.$params["cart"]->id_currency);
			$PESAPALParams['currency'] = $currency;
			
			$smarty->assign('p', $PESAPALParams);
			
			return $this->display(__FILE__, 'payment.tpl');
		}
		
		
		public function checkStatus($trackingID, $referenceNO){ 

			$token = $params = NULL;
			$statusrequest = 'https://www.pesapal.com/api/querypaymentstatus';
			$consumer_key = Configuration::get('PESAPAL_MERCHANT_KEY');
			$consumer_secret = Configuration::get('PESAPAL_MERCHANT_SECRET');
			
			$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
			$consumer = new OAuthConsumer($consumer_key,$consumer_secret);
				
			//get transaction status
			$request_status = OAuthRequest::from_consumer_and_token($consumer, $token,"GET", $statusrequest, $params);
			$request_status->set_parameter("pesapal_merchant_reference", $referenceNO);
			$request_status->set_parameter("pesapal_transaction_tracking_id",$trackingID);
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
}
?>