<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
    exit;

include('pesapal/OAuth.php');
include("pesapal/xmlhttprequest.php");
include("pesapal/checkStatus.php");

class Pesapal extends PaymentModule
{
	protected $_html = '';
	protected $_postErrors = array();

//	public $details;
//	public $owner;
//	public $address;
//	public $extra_mail_vars;
        
        public $merchant_keys;
        public $merchant_secret;
        public $pesapa_cart;
        public $is_live;
        public $api;
        
	public function __construct()
	{
		$this->name = 'pesapal';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'Team Pesapal';
		$this->controllers = array('payment','validation','ipnReturn','iframe');
		$this->is_eu_compatible = 1;
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		$this->bootstrap = true;		
		$this->displayName = $this->l('Pesapal');
		$this->description = $this->l('Accept payments for your products via Pesapal.');
		$this->confirmUninstall = $this->l('Are you sure about removing Pesapal payment module details?');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
                
                parent::__construct();
                
//                $config = Configuration::getMultiple(array('BANK_WIRE_DETAILS', 'BANK_WIRE_OWNER', 'BANK_WIRE_ADDRESS'));
//                
//		if (!empty($config['BANK_WIRE_OWNER']))
//			$this->owner = $config['BANK_WIRE_OWNER'];
//		if (!empty($config['BANK_WIRE_DETAILS']))
//			$this->details = $config['BANK_WIRE_DETAILS'];
//		if (!empty($config['BANK_WIRE_ADDRESS']))
//			$this->address = $config['BANK_WIRE_ADDRESS'];
//		if (!isset($this->owner) || !isset($this->details) || !isset($this->address))
//			$this->warning = $this->l('Account owner and account details must be configured before using this module.');
//		if (!count(Currency::checkPaymentCurrencies($this->id)))
//			$this->warning = $this->l('No currency has been set for this module.');
//
//		$this->extra_mail_vars = array('{pesapal_owner}' => Configuration::get('BANK_WIRE_OWNER'),
//						'{pesapal_details}' => nl2br(Configuration::get('BANK_WIRE_DETAILS')),
//						'{pesapal_address}' => nl2br(Configuration::get('BANK_WIRE_ADDRESS'))
//					      );
                
                /*
                *isLive is true is the admin sets pesapal to use live customer(merchant) account and false when otherwise
                */

                $this->is_live = Configuration::get('PESAPAL_MERCHANT_IS_LIVE');

                if($this->is_live)
                    $this->api = 'https://www.pesapal.com/api';
                else
                    $this->api = 'https://demo.pesapal.com/api';
	}
        
        public function addPesapalOrderStatuses($name,$invoice,$send_email,$template,$color,$unremovable,
                                            $hidden,$logable,$delivery,$shipped,$paid,$pdf_invoice,$pdf_delivery,$deleted){
                $OrderState = new OrderState();

                $OrderState->name = array_fill(0,2,$name);
                $OrderState->invoice = $invoice;
                $OrderState->send_email = $send_email;
                $OrderState->module_name = "pesapal";
                $OrderState->template = $template;
                $OrderState->color = $color;
                $OrderState->unremovable = $unremovable;
                $OrderState->hidden = $hidden;
                $OrderState->logable = $logable;
                $OrderState->delivery = $delivery;
                $OrderState->shipped = $shipped;
                $OrderState->paid = $paid;
                $OrderState->pdf_invoice = $pdf_invoice;
                $OrderState->pdf_delivery = $pdf_delivery;
                $OrderState->deleted = $deleted;
                $OrderState->add();

                return $OrderState;

        }
        
        public function deletePesapalOrderStates($state_id,$state_name){
                $sql_a = "DELETE FROM ".pSQL(_DB_PREFIX_)."order_state_lang "
                        . "WHERE name = '$state_name'";
                Db::getInstance()->Execute($sql_a);

                $sql_b = "DELETE FROM ".pSQL(_DB_PREFIX_)."order_state "
                        . "WHERE id_order_state ='$state_id' "
                        . "AND module_name = 'pesapal'";
                Db::getInstance()->Execute($sql_b);

                return true;

        }


	public function install()
	{
                //add pesapal order statuses
                $placed    =  $this->addPesapalOrderStatuses("PLACED",1,0,     "preparation","#4169e1",1,0,1,0,0,0,0,0,0);
                $pending   = $this->addPesapalOrderStatuses("PENDING",1,0,     "payment","#ff5722",1,0,1,0,0,0,0,0,0);
                $failed    = $this->addPesapalOrderStatuses("FAILED",1,1,      "payment","#f44336",1,0,1,0,0,0,0,0,0);
                $completed = $this->addPesapalOrderStatuses("COMPLETED",1,1,   "payment","#4caf50",1,0,1,0,0,1,1,0,0);

                //set configuration for pesapal payment statuses
                Configuration::updateValue('PESAPAL_PLACED_STATUS',$placed->id);
                Configuration::updateValue('PESAPAL_PENDING_STATUS',$pending->id);
                Configuration::updateValue('PESAPAL_FAILED_STATUS',$failed->id);
                Configuration::updateValue('PESAPAL_COMPLETED_STATUS',$completed->id);


               if ( !parent::install() ||
                        !$this->registerHook('paymentReturn') ||
                        !$this->registerHook('paymentOptions')
                ) {
                    return false;
                }
                
		return true;
	}

	public function uninstall()
	{
            
                $deletedStateData = true;
                if(!$this->deletePesapalOrderStates(Configuration::get('PESAPAL_PLACED_STATUS'),"PLACED") ||
                   !$this->deletePesapalOrderStates(Configuration::get('PESAPAL_PENDING_STATUS'),"PENDING") ||
                   !$this->deletePesapalOrderStates(Configuration::get('PESAPAL_FAILED_STATUS'),"FAILED") ||
                   !$this->deletePesapalOrderStates(Configuration::get('PESAPAL_COMPLETED_STATUS'),"COMPLETED")
                  ){
                    $deletedStateData = false;
                    return $deletedStateData;
                }
                
                if (($deletedStateData == false)
                        || !Configuration::deleteByName('PESAPAL_MERCHANT_IS_LIVE')
                        || !Configuration::deleteByName('PESAPAL_MERCHANT_KEY')
                        || !Configuration::deleteByName('PESAPAL_MERCHANT_SECRET')
                        || !Configuration::deleteByName('PESAPAL_DESCRIPTION')
                        || !Configuration::deleteByName('PESAPAL_REFERENCE_CODE')
                        || !Configuration::deleteByName('PESAPAL_COMPLETED_MSG')
                        || !Configuration::deleteByName('PESAPAL_PENDING_MSG')
                        || !Configuration::deleteByName('PESAPAL_FAILED_MSG')
                        || !Configuration::deleteByName('PESAPAL_INVALID_MSG')

                        || !Configuration::deleteByName('PESAPAL_PLACED_STATUS')
                        || !Configuration::deleteByName('PESAPAL_PENDING_STATUS')
                        || !Configuration::deleteByName('PESAPAL_FAILED_STATUS')
                        || !Configuration::deleteByName('PESAPAL_COMPLETED_STATUS')

                        || !Configuration::deleteByName('PESAPAL_CALLBACK_URL')

                        || !parent::uninstall()) {
                    return false;
                }
                
//		if (!Configuration::deleteByName('BANK_WIRE_DETAILS')
//                    || !Configuration::deleteByName('BANK_WIRE_OWNER')
//                    || !Configuration::deleteByName('BANK_WIRE_ADDRESS')
//                    || !parent::uninstall())
//			return false;
                
		return true;
	}
        
	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}

	protected function _postValidation()
        {
            if (Tools::isSubmit('btnSubmit')) {

                if (!Tools::getValue('pesapal_merchant_key')) {
                    $this->_postErrors[] = $this->l('Pesapal merchant key is required.');
                } elseif (!Tools::getValue('pesapal_merchant_secret')) {
                    $this->_postErrors[] = $this->l('Pesapal merchant secret is required.');
                }
            }
        }
        
        protected function _postProcess()
        {
            if (Tools::isSubmit('btnSubmit')) {

                Configuration::updateValue('PESAPAL_MERCHANT_IS_LIVE', Tools::getvalue('pesapal_merchant_is_live'));
                Configuration::updateValue('PESAPAL_MERCHANT_KEY', Tools::getvalue('pesapal_merchant_key'));
                Configuration::updateValue('PESAPAL_MERCHANT_SECRET', Tools::getvalue('pesapal_merchant_secret'));
                Configuration::updateValue('PESAPAL_DESCRIPTION', Tools::getvalue('pesapal_description'));
                Configuration::updateValue('PESAPAL_REFERENCE_CODE', Tools::getvalue('pesapal_reference_code'));
                Configuration::updateValue('PESAPAL_COMPLETED_MSG', Tools::getvalue('pesapal_completed_msg'));
                Configuration::updateValue('PESAPAL_PENDING_MSG', Tools::getvalue('pesapal_pending_msg'));
                Configuration::updateValue('PESAPAL_FAILED_MSG', Tools::getvalue('pesapal_failed_msg'));
                Configuration::updateValue('PESAPAL_INVALID_MSG', Tools::getvalue('pesapal_invalid_msg'));


                // $custom_text = array();
                // $languages = Language::getLanguages(false);
                // foreach ($languages as $lang) {
                //     if (Tools::getIsset('PESAPAL_CUSTOM_TEXT_'.$lang['id_lang'])) {
                //         $custom_text[$lang['id_lang']] = Tools::getValue('PESAPAL_CUSTOM_TEXT_'.$lang['id_lang']);
                //     }
                // }
                //Configuration::updateValue('PESAPAL_CUSTOM_TEXT', $custom_text);
            }
            $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
        }
        
        public function getebaseURL(){
                global $cookie;

               /* To consider language iso code in the url,
                * check if the number of active languages available in the admin side exceed one.
                * Then check for iso_code and append it to the base url.
                */

               $activeLanguages = Language::getLanguages(true);
                /*
                * (true) means get only the active languages,
                * (false)means get all languages both active and inactive.
                */

               $base_url = _PS_BASE_URL_;//. __PS_BASE_URI__;

               if ($_SERVER["HTTPS"] == "on") {
                   $base_url = _PS_BASE_URL_SSL_;
               }

               $base_url = $base_url.__PS_BASE_URI__;


               if(count($activeLanguages) > 1){
                   $iso_code = Language::getIsoById( (int)$cookie->id_lang );

                   $base_url .= "$iso_code/";
               }
               //echo $base_url;exit();
               return $base_url;
        }

	protected function _displayPesaPal()
        {
            global $smarty;

            $ipn_url =  $this->getebaseURL()."index.php?fc=module&module=pesapal&controller=ipnReturn";

            $smarty->assign(
                    array(
                      'my_module_name' =>$this->name,
                      'my_module_display_name' =>$this->displayName,
                      'my_module_link' => $this->context->link->getModuleLink('pesapal', 'display'),
                      'ipn_url'=>$ipn_url,
                    )
                );

            return $this->display(__FILE__, 'infos.tpl');
        }

	public function getContent()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= $this->displayError($err);
		}
		else
			$this->_html .= '<br />';

		$this->_html .= $this->_displayPesapal();
		$this->_html .= $this->renderForm();

		return $this->_html;
	}

        public function hookPayment($params)
	{
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;

		$this->smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_bw' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		return $this->display(__FILE__, 'pesapal_intro.tpl');
	}
        
        public function hookPaymentOptions($params)
        {
            global $smarty,$cookie;

            if (!$this->active) {
                return;
            }

            if (!$this->checkCurrency($params['cart'])) {
                return;
            }

            if (!count(Currency::checkPaymentCurrencies($this->id))) {
                $this->warning = $this->l('No currency has been set for this module.');
            }

            $consumer_key = Configuration::get('PESAPAL_MERCHANT_KEY');
            $consumer_secret = Configuration::get('PESAPAL_MERCHANT_SECRET');

            if(empty($consumer_key) && empty($consumer_secret)){
                    $this->warning = $this->l('Please configure Customer Key and Customer Secret for Pesapal Payments to be Successful.');
            }

//            $newOption = new PaymentOption();
//            $newOption->setModuleName($this->name)
//                    ->setCallToActionText($this->l('Pay with Pesapal'))
//                    ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
//                    ->setAdditionalInformation($this->context->smarty->fetch('module:pesapal/views/templates/hook/pesapal_intro.tpl'));
//
//            $payment_options = [
//                $newOption,
//            ];
            
            $payment_options = array(
			'cta_text' => $this->l('Pay with Pesapal'),
			'logo' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/pesapal.jpg'),
			'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
		);

            return $payment_options;
        }

        public function updateOrderPaymentMethod($reference,$payment_method){
            $sql_a = "UPDATE ".pSQL(_DB_PREFIX_)."orders "
                    . "SET payment = 'Pesapal - $payment_method' "
                    . "WHERE reference = '$reference'";
            Db::getInstance()->Execute($sql_a);

            $sql_b = "UPDATE ".pSQL(_DB_PREFIX_)."order_payment "
                    . "SET payment_method = 'Pesapal - $payment_method' "
                    . "WHERE order_reference = '$reference'";

            Db::getInstance()->Execute($sql_b);

        }

        public function updateOrderPaymentStatus($order,$status){

            switch ($status) {
                case 'PENDING':
                    $updated_status_code = (Configuration::get('PESAPAL_PENDING_STATUS'));
                    break;
                case 'COMPLETED':
                    $updated_status_code = (Configuration::get('PESAPAL_COMPLETED_STATUS'));
                    break;
                case 'FAILED':
                    $updated_status_code = (Configuration::get('PESAPAL_FAILED_STATUS'));
                    break;
                case 'PLACED':
                    $updated_status_code = (Configuration::get('PESAPAL_PLACED_STATUS'));
                    break;
                default:
                    $updated_status_code = (Configuration::get('PESAPAL_PENDING_STATUS'));
                    break;
            }

            //prepare variables to update order status
            $order_id = (int)$order->id;
            $history = new OrderHistory();
            $history->id_order = $order_id;
            $history->changeIdOrderState((int)$updated_status_code, $order_id);

        }

        public function hookPaymentReturn($params)
        {
            global $smarty,$cookie;
            $order = $params['order'];
            //$cart = $this->context->cart;

            if (!$this->active) {
                return;
            }
            //var_dump($this);
            //var_dump($cookie);
            //var_dump($params['order']);

            //filter_input sanitizes the form data
            $transaction_tracking_id = Tools::getValue('pesapal_transaction_tracking_id');
            $invoice = Tools::getValue('pesapal_merchant_reference');

            $customer_lastname = $cookie->customer_lastname;
            $customer_firstname = $cookie->customer_firstname;

            $options = array();
            $options['customer_key']    = Configuration::get('PESAPAL_MERCHANT_KEY');
            $options['customer_secret'] = Configuration::get('PESAPAL_MERCHANT_SECRET');
            $options['is_live']         = Configuration::get('PESAPAL_MERCHANT_IS_LIVE');

            $pesapal        = new pesapalCheckStatus($options);

            $paymentDetails = $pesapal->getTransactionDetails($invoice, $transaction_tracking_id);
            $status         = $paymentDetails['status'];
            $payment_method = $paymentDetails['payment_method'];

            $buyer          = ucwords($customer_firstname." ".$customer_lastname);

            //update order status
            $this->updateOrderPaymentStatus($order,$status);
            //update payment method to the one used by user to make payments (MTN Mobile Money, Airtel Mobile Money, Credit Card) etc
            $this->updateOrderPaymentMethod($order->reference,$payment_method);


            $smarty->assign(
                array(
                'status' => $status,
                'payment_method' => $payment_method,
                'buyer' => $buyer,
                )
            );

            return $this->fetch('module:pesapal/views/templates/hook/payment_return.tpl');
        }
    
        public function renderForm()
        {
            $fields_form_customization = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('General Settings'),
                        'icon' => 'icon-cogs'
                    ),
                    'input' => array(

                            array(
                                    'type'      => 'radio',
                                    'label'     => $this->l('Use Live Pesapal Account?'), //label for radio group
                                    'name'      => 'pesapal_merchant_is_live',  			
                                    'class'     => 't',
                                    'values'    => array(         			// $values contains the data itself.
                                        array(
                                            'id'    => 'active_on',
                                            'value' => 1,                   // The content of the 'value' attribute of the <input> tag.
                                            'label' => $this->l('Live')     // The <label> for this radio button.
                                        ),
                                        array(
                                            'id'    => 'active_off',
                                            'value' => 0,
                                            'label' => $this->l('Demo')
                                        )
                                    ),
                                ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('Customer Key'),
                            'name' => 'pesapal_merchant_key',
                            'required' => true
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('Customer Secret'),
                            'name' => 'pesapal_merchant_secret',
                            'required' => true
                        ),
                        array(
                            'type' => 'hidden',
                            'label' => $this->l('Description'),
                            'name' => 'pesapal_description',
                            'required' => false
                        ),
                        array(
                            'type' => 'hidden',
                            'label' => $this->l('Reference Code'),
                            'name' => 'pesapal_reference_code',
                            'required' => false
                        ),
                        array(
                            'type' => 'hidden',
                            'label' => $this->l('Completed Message'),
                            'name' => 'pesapal_completed_msg',
                            'required' => false
                        ),
                        array(
                            'type' => 'hidden',
                            'label' => $this->l('Pending Message'),
                            'name' => 'pesapal_pending_msg',
                            'required' => false
                        ),
                        array(
                            'type' => 'hidden',
                            'label' => $this->l('Failed Message'),
                            'name' => 'pesapal_failed_msg',
                            'required' => false
                        ),
                        array(
                            'type' => 'hidden',
                            'label' => $this->l('Invalid Message'),
                            'name' => 'pesapal_invalid_msg',
                            'required' => false
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save Settings'),
                    )
                ),
            );


            $helper = new HelperForm();
            $helper->module = $this;
            $helper->name_controller = $this->name;
            $helper->show_toolbar = false;
            $helper->table = $this->table;
            $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
            $helper->default_form_language = $lang->id;
            $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? : 0;
            $this->fields_form = array();
            $helper->id = (int)Tools::getValue('id_carrier');
            $helper->identifier = $this->identifier;
            $helper->submit_action = 'btnSubmit';
            $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='
                .$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
            $helper->token = Tools::getAdminTokenLite('AdminModules');
            $helper->tpl_vars = array(
                'fields_value' => $this->getConfigFieldsValues(),
                'languages' => $this->context->controller->getLanguages(),
                'id_language' => $this->context->language->id
            );

            return $helper->generateForm(array($fields_form_customization));
        }

        public function getConfigFieldsValues()
        {
            // $custom_text = array();
            // $languages = Language::getLanguages(false);

            // foreach ($languages as $lang) {
            //     $custom_text[$lang['id_lang']] = Tools::getValue(
            //         'PESAPAL_CUSTOM_TEXT_'.$lang['id_lang'],
            //         Configuration::get('PESAPAL_CUSTOM_TEXT', $lang['id_lang'])
            //     );
            // }

            return array(
                'pesapal_merchant_is_live' => Tools::getValue('pesapal_merchant_is_live', Configuration::get('PESAPAL_MERCHANT_IS_LIVE')),
                'pesapal_merchant_key' => Tools::getValue('pesapal_merchant_key', Configuration::get('PESAPAL_MERCHANT_KEY')),
                'pesapal_merchant_secret' => Tools::getValue('pesapal_merchant_secret', Configuration::get('PESAPAL_MERCHANT_SECRET')),
                'pesapal_description' => Tools::getValue('pesapal_description', Configuration::get('PESAPAL_DESCRIPTION')),
                'pesapal_reference_code' => Tools::getValue('pesapal_reference_code', Configuration::get('PESAPAL_REFERENCE_CODE')),
                'pesapal_completed_msg' => Tools::getValue('pesapal_completed_msg', Configuration::get('PESAPAL_COMPLETED_MSG')),
                'pesapal_pending_msg' => Tools::getValue('pesapal_pending_msg', Configuration::get('PESAPAL_PENDING_MSG')),
                'pesapal_failed_msg' => Tools::getValue('pesapal_failed_msg', Configuration::get('PESAPAL_FAILED_MSG')),
                'pesapal_invalid_msg' => Tools::getValue('pesapal_invalid_msg', Configuration::get('PESAPAL_INVALID_MSG')),
            );
        }

}
