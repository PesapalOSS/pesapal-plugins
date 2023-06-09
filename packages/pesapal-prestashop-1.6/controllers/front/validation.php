<?php
/*
* 2007-2015 PrestaShop
/**
 * @since 1.5.0
 */
class PesapalValidationModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		$cart = $this->context->cart;
                
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		/*
		*Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		*/ 
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'pesapal')
			{
					$authorized = true;
					break;
			}

		if (!$authorized)
			die($this->module->getTranslator()->trans('This payment method is not available.', array(), 'Modules.Pesapal.Shop'));

		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);

		//creates an order from the shopping cart information PS_OS_PAYMENT
		$this->module->validateOrder((int)$cart->id,Configuration::get('PESAPAL_PLACED_STATUS'), $total, $this->module->displayName, NULL, array(), (int)$currency->id, false, $customer->secure_key);

		//Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
		//var_dump($this->module);
		//exit;
		//redirect to spaecific controller that loads the iframe 
		Tools::redirect('index.php?fc=module&module='.$this->module->name.'&controller=iframe&id_cart='.$cart->id.'&id_order='.$this->module->currentOrder.'&id_module='.$this->module->id.'&key='.$customer->secure_key);

	}

}
