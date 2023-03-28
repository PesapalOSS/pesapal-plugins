<?php

// Avoid direct access to the file
if (!defined('_PS_VERSION_'))
	exit;

class fargo_courier extends CarrierModule
{
	public  $id_carrier;

	private $_html = '';
	private $_moduleName = 'fargo_courier';


	/*
	** Construct Method
	**
	*/

	public function __construct()
	{
		$this->name = 'fargo_courier';
		$this->tab = 'shipping_logistics';
		$this->version = '1.0';
		$this->author = 'Rowlings Odhiambo rowlings@pesapal.com';
		$this->bootstrap = true;

		parent::__construct ();

		$this->displayName = $this->l('Fargo Courier');
		$this->description = $this->l('Our Business is delivering your business');

		if (self::isInstalled($this->name))
		{
			// Getting carrier list
			global $cookie;
			$carriers = Carrier::getCarriers($cookie->id_lang, true, false, false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);

			// Saving id carrier list
			$id_carrier_list = array();
			foreach($carriers as $carrier)
				$id_carrier_list[] .= $carrier['id_carrier'];

			$warning = array();
			if (!in_array((int)(Configuration::get('FARGO_COURIER_ID')), $id_carrier_list))
				$warning[] .= $this->l('"Fargo Courier"').' ';
			if (!Configuration::get('FARGO_COURIER_MERCHANT_TOKEN'))
				$warning[] .= $this->l('"Merchant Token"').' ';
			if (!Configuration::get('FARGO_COURIER_FIRSTNAME'))
				$warning[] .= $this->l('"First Name"').' ';
			if (!Configuration::get('FARGO_COURIER_LASTNAME'))
				$warning[] .= $this->l('"Last Name"').' ';
			if (!Configuration::get('FARGO_COURIER_EMAIL'))
				$warning[] .= $this->l('"Email Address"').' ';
			if (!Configuration::get('FARGO_COURIER_TELNUMBER'))
				$warning[] .= $this->l('"Telephone Number"').' ';
			if (!Configuration::get('id_towns'))
				$warning[] .= $this->l('"Town"').' ';
			if (!Configuration::get('FARGO_COURIER_ADDRESS'))
				$warning[] .= $this->l('"Address"').' ';
			if (count($warning))
				$this->warning .= implode(' , ',$warning).$this->l('must be configured to use this module correctly').' ';
		}
	}


	/*
	** Install / Uninstall Methods
	**
	*/

	public function install()
	{
		$carrierConfig = array('name' => 'Fargo Courier',
				'id_tax_rules_group' => 0,
				'active' => true,
				'deleted' => 0,
				'shipping_handling' => false,
				'range_behavior' => 0,
				'delay' => array('fr' => 'Our business is delivering your business', 'en' => 'Our business is delivering your business', Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) => 'Our business is delivering your business'),
				'id_zone' => 1,
				'is_module' => true,
				'shipping_external' => true,
				'external_module_name' => 'fargo_courier',
				'need_range' => true
		);

		$this->installDB();

		$id_carrier = $this->installExternalCarrier($carrierConfig);
		Configuration::updateValue('FARGO_COURIER_ID', (int)$id_carrier);
		if (!parent::install() ||
		    !Configuration::updateValue('FARGO_COURIER_MERCHANT_TOKEN', '') ||
		    !Configuration::updateValue('FARGO_COURIER_FIRSTNAME', '') ||
		    !Configuration::updateValue('FARGO_COURIER_LASTNAME', '') ||
		    !Configuration::updateValue('FARGO_COURIER_EMAIL', '') ||
		    !Configuration::updateValue('FARGO_COURIER_TELNUMBER', '') ||
		    !Configuration::updateValue('id_towns', '') ||
		    !Configuration::updateValue('FARGO_COURIER_ADDRESS', '') ||
		    !$this->registerHook('updateCarrier'))
			return false;
		return true;
	}

	const INSTALL_SQL_FILE = 'install.mysql.utf8.sql';

	public function installDB()
	{


		if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
    		return false;
    	else if (!$sql = file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
    		return false;
    	$sql = str_replace(array('PREFIX_', 'ENGINE_TYPE'), array(_DB_PREFIX_, _MYSQL_ENGINE_), $sql);
    	$sql = preg_split("/;\s*[\r\n]+/", trim($sql));
 
    	foreach ($sql as $query)
    		if (!Db::getInstance()->execute(trim($query)))
    		return false;
	}

	
	public function uninstall()
	{
		// Uninstall
		if (!parent::uninstall() ||
		    !Configuration::updateValue('FARGO_COURIER_MERCHANT_TOKEN', '') ||
		    !Configuration::updateValue('FARGO_COURIER_FIRSTNAME', '') ||
		    !Configuration::updateValue('FARGO_COURIER_LASTNAME', '') ||
		    !Configuration::updateValue('FARGO_COURIER_EMAIL', '') ||
		    !Configuration::updateValue('FARGO_COURIER_TELNUMBER', '') ||
		    !Configuration::updateValue('id_towns', '') ||
		    !Configuration::updateValue('FARGO_COURIER_ADDRESS', '') ||
		    !$this->unregisterHook('updateCarrier'))
			return false;

		//drop table towns
		$this->uninstallDB();
		
		// Delete External Carrier
		$Fargocourier = new Carrier((int)(Configuration::get('FARGO_COURIER_ID')));

		// If fargo courier is default set other one as default
		if (Configuration::get('PS_CARRIER_DEFAULT') == (int)($Fargocourier->id))
		{
			global $cookie;
			$carriersD = Carrier::getCarriers($cookie->id_lang, true, false, false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
			foreach($carriersD as $carrierD)
				if ($carrierD['active'] AND !$carrierD['deleted'] AND ($carrierD['name'] != $this->_config['name']))
					Configuration::updateValue('PS_CARRIER_DEFAULT', $carrierD['id_carrier']);
		}

		// Then delete Carrier
		$Fargocourier->deleted = 1;

		if (!$Fargocourier->update())
			return false;

		return true;
	}

	public function uninstallDB()
	{

		return Db::getInstance()->execute('
			DROP TABLE IF EXISTS
			`'._DB_PREFIX_.'fargo_courier_towns`');
	}

	public static function installExternalCarrier($config)
	{
		$carrier = new Carrier();
		$carrier->name = $config['name'];
		$carrier->id_tax_rules_group = $config['id_tax_rules_group'];
		$carrier->id_zone = $config['id_zone'];
		$carrier->active = $config['active'];
		$carrier->deleted = $config['deleted'];
		$carrier->delay = $config['delay'];
		$carrier->shipping_handling = $config['shipping_handling'];
		$carrier->range_behavior = $config['range_behavior'];
		$carrier->is_module = $config['is_module'];
		$carrier->shipping_external = $config['shipping_external'];
		$carrier->external_module_name = $config['external_module_name'];
		$carrier->need_range = $config['need_range'];

		$languages = Language::getLanguages(true);
		foreach ($languages as $language)
		{
			if ($language['iso_code'] == 'fr')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			if ($language['iso_code'] == 'en')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')))
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
		}

		if ($carrier->add())
		{
			$groups = Group::getGroups(true);
			foreach ($groups as $group)
				Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier_group', array('id_carrier' => (int)($carrier->id), 'id_group' => (int)($group['id_group'])), 'INSERT');

			$rangePrice = new RangePrice();
			$rangePrice->id_carrier = $carrier->id;
			$rangePrice->delimiter1 = '0';
			$rangePrice->delimiter2 = '10000';
			$rangePrice->add();

			$rangeWeight = new RangeWeight();
			$rangeWeight->id_carrier = $carrier->id;
			$rangeWeight->delimiter1 = '0';
			$rangeWeight->delimiter2 = '10000';
			$rangeWeight->add();

			$zones = Zone::getZones(true);
			foreach ($zones as $zone)
			{
				Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier_zone', array('id_carrier' => (int)($carrier->id), 'id_zone' => (int)($zone['id_zone'])), 'INSERT');
				Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.'delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => (int)($rangePrice->id), 'id_range_weight' => NULL, 'id_zone' => (int)($zone['id_zone']), 'price' => '0'), 'INSERT');
				Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.'delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => NULL, 'id_range_weight' => (int)($rangeWeight->id), 'id_zone' => (int)($zone['id_zone']), 'price' => '0'), 'INSERT');
			}

			// Copy Logo
			if (!copy(dirname(__FILE__).'/carrier.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg'))
				return false;

			// Return ID Carrier
			return (int)($carrier->id);
		}

		return false;
	}




	/*
	** Form Config Methods
	**
	*/

	public function getContent()
	{
		$this->_html .= '<script src="../modules/abc/abc.js" type="text/javascript" ></script>';
		$this->_html .= '<h2>' . $this->l('Fargo Courier').'</h2>';
		if (Tools::isSubmit('submitFargo'))
		{
				$this->_postProcess();
		}
		//$this->_displayForm();

		$this->_html .= $this->renderForm();

		return $this->_html;
	}

	public function hookHeader($params)
{
     $this->controller->addJS(($this->_path).'abc.js');
}

	public function renderForm()
	{
			$warning = array();
			if (!Configuration::get('FARGO_COURIER_MERCHANT_TOKEN'))
				$warning[] .= $this->l('"Merchant Token"').' ';
			if (!Configuration::get('FARGO_COURIER_FIRSTNAME'))
				$warning[] .= $this->l('"First Name"').' ';
			if (!Configuration::get('FARGO_COURIER_LASTNAME'))
				$warning[] .= $this->l('"Last Name"').' ';
			if (!Configuration::get('FARGO_COURIER_EMAIL'))
				$warning[] .= $this->l('"Email Address"').' ';
			if (!Configuration::get('FARGO_COURIER_TELNUMBER'))
				$warning[] .= $this->l('"Telephone Number"').' ';
			if (!Configuration::get('id_towns'))
				$warning[] .= $this->l('"Town"').' ';
			if (!Configuration::get('FARGO_COURIER_ADDRESS'))
				$warning[] .= $this->l('"Address"').' ';
			if (count($warning)){
				$this->_html .= '<img src="'._PS_IMG_.'admin/warn2.png" /><strong>'.$this->l('Fargo Courier is not configured yet.:').'</strong><br />';
				$this->_html .= $this->l('Please configure the following: ').implode(' , ',$warning);
			}else{
				$this->_html .= '<img src="'._PS_IMG_.'admin/module_install.png" /><strong>'.$this->l('Fargo Courier is configured and online!').'</strong>';
			}

		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Fargo Courier Module Status'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					
					array(
						'type' => 'text',
						'label' => $this->l('Merchant Token'),
						'name' => 'FARGO_COURIER_MERCHANT_TOKEN'
					),
					array(
						'type' => 'text',
						'label' => $this->l('First Name'),
						'name' => 'FARGO_COURIER_FIRSTNAME'
					),
					array(
						'type' => 'text',
						'label' => $this->l('Last Name'),
						'name' => 'FARGO_COURIER_LASTNAME'
					),
					array(
						'type' => 'text',
						'label' => $this->l('Email'),
						'name' => 'FARGO_COURIER_EMAIL'
					),
					array(
						'type' => 'text',
						'label' => $this->l('Telephone Number'),
						'name' => 'FARGO_COURIER_TELNUMBER'
					),
					array(
						'type' => 'select',
						'label' => $this->l('Town :'),
						'name' => 'id_towns',
						'options' => array(
							'default' => array('value' => 0, 'label' => $this->l('Choose town')),
							'query' => $this->townDropDown(),
							'id' => 'id_towns',
							'name' => 'towns'
						),
			),
					array(
						'type' => 'text',
						'label' => $this->l('Address'),
						'name' => 'FARGO_COURIER_ADDRESS'
					)
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitFargo';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		return array(
			//'id_towns' => Tools::getValue('id_towns'),
			'FARGO_COURIER_MERCHANT_TOKEN' => Tools::getValue('FARGO_COURIER_MERCHANT_TOKEN', Configuration::get('FARGO_COURIER_MERCHANT_TOKEN')),
			'FARGO_COURIER_FIRSTNAME' => Tools::getValue('FARGO_COURIER_FIRSTNAME', Configuration::get('FARGO_COURIER_FIRSTNAME')),
			'FARGO_COURIER_LASTNAME' => Tools::getValue('FARGO_COURIER_LASTNAME', Configuration::get('FARGO_COURIER_LASTNAME')),
			'FARGO_COURIER_EMAIL' => Tools::getValue('FARGO_COURIER_EMAIL', Configuration::get('FARGO_COURIER_EMAIL')),
			'FARGO_COURIER_TELNUMBER' => Tools::getValue('FARGO_COURIER_TELNUMBER', Configuration::get('FARGO_COURIER_TELNUMBER')),
			'id_towns' => Tools::getValue('id_towns', Configuration::get('id_towns')),
			'FARGO_COURIER_ADDRESS' => Tools::getValue('FARGO_COURIER_ADDRESS', Configuration::get('FARGO_COURIER_ADDRESS')),
		);
	}

	/* Check to see if json file exists(containing cities json) if so read the contents
		If not, generate the file and read its contents
		prevents numerous calls to the Fargo Courier API
	*/
	function checkJsonFile()
	{
		if (file_exists('../modules/fargo_courier/fargo_cities.json')){

			//read contents
		    $data = file_get_contents ('../modules/fargo_courier/fargo_cities.json');
		    $json = json_decode($data, TRUE);

		    return $json;

		}else{
			//generate file from the api's json response
			$json = file_get_contents('http://api.bookacourier.co.ke/index.php?option=com_fargo_shipping&task=shippingregions.getRegions&token='.Configuration::get('FARGO_COURIER_MERCHANT_TOKEN'));
			$true = fopen("../modules/fargo_courier/fargo_cities.json", "w+");
		    fwrite($true, $json);

			//call the function recursively to generate the dropdown
			$this->checkJsonFile();
		}
	}

	function townDropDown()
	{
		
		$objs = $this->checkJsonFile();
		
		foreach ($objs['regions'] as $key => $value) {
			$objs['regions'][$key]['id_towns'] = $value['id'];
			$objs['regions'][$key]['towns'] = $value['title'];
		}

		return $objs['regions'];

	}

	private function _postProcess()
	{
		// Saving new configurations
		if (Configuration::updateValue('FARGO_COURIER_MERCHANT_TOKEN', Tools::getValue('FARGO_COURIER_MERCHANT_TOKEN')) &&
		    Configuration::updateValue('FARGO_COURIER_FIRSTNAME', Tools::getValue('FARGO_COURIER_FIRSTNAME')) &&
		    Configuration::updateValue('FARGO_COURIER_LASTNAME', Tools::getValue('FARGO_COURIER_LASTNAME')) &&
		    Configuration::updateValue('FARGO_COURIER_EMAIL', Tools::getValue('FARGO_COURIER_EMAIL')) &&
		    Configuration::updateValue('FARGO_COURIER_TELNUMBER', Tools::getValue('FARGO_COURIER_TELNUMBER')) &&
		    Configuration::updateValue('id_towns', Tools::getValue('id_towns')) &&
		    Configuration::updateValue('FARGO_COURIER_ADDRESS', Tools::getValue('FARGO_COURIER_ADDRESS')))
			$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
		else
			$this->_html .= $this->displayErrors($this->l('Settings failed'));
	}


	/*
	** Hook update carrier
	**
	*/

	public function hookupdateCarrier($params)
	{
		if ((int)($params['id_carrier']) == (int)(Configuration::get('FARGO_COURIER_ID')))
			Configuration::updateValue('FARGO_COURIER_ID', (int)($params['carrier']->id));
	}




	/*
	** Front Methods
	**
	** If you set need_range at true when you created your carrier (in install method), the method called by the cart will be getOrderShippingCost
	** If not, the method called will be getOrderShippingCostExternal
	**
	** $params var contains the cart, the customer, the address
	** $shipping_cost var contains the price calculated by the range in carrier tab
	**
	*/
	
	public function getOrderShippingCost($params, $shipping_cost)
	{
		return $this->getFargoShippingPrice($params);
	}

	public function getOrderShippingCostExternal($params)
	{
		// This example returns the overcost directly, but you can call a webservice or calculate what you want before returning the final value to the Cart
		if ($this->id_carrier == (int)(Configuration::get('FARGO_COURIER_ID')) )
			return $this->getFargoShippingPrice($params);

		// If the carrier is not known, you can return false, the carrier won't appear in the order process
		return false;
	}

	function getFargoShippingPrice($params){

		global $cookie;
		$customer = new Customer((int)$cookie->id_customer);
		$customerInfo = $customer->getAddresses((int)$cookie->id_lang);

		$cartitems = $params->getProducts();


			foreach ($cartitems as $cartitem) {
				$dataitem=array();

			 // $dataitem1['radius']=0;
			  	$dataitem1['quantity']=$cartitem['quantity'];
			  	$dataitem1['height']=$cartitem['height'];
			  	$dataitem1['width']=$cartitem['width'];
			  	$dataitem1['length']=$cartitem['depth'];
			  	$dataitem1['weight']=$cartitem['weight'];
			  	$dataitem1['dropoff']=$customerInfo[0]['state_iso'];
			  	$dataitem1['pickup']=Configuration::get('id_towns');
			  	$dataitem1['product_sku']= $cartitem['unique_id'];

			  	$dataitem1['to_street']=$customerInfo[0]['address1'];
	            $dataitem1['firstname']=$customerInfo[0]['firstname'];
	            $dataitem1['lastname']=$customerInfo[0]['lastname'];
	            $dataitem1['telephone']=$customerInfo[0]['phone'];
	            $dataitem1['email']=$this->context->customer->email;
			  
				$dataitem1['from_firstname']=Configuration::get('FARGO_COURIER_FIRSTNAME');
	            $dataitem1['from_lastname']=Configuration::get('FARGO_COURIER_LASTNAME');
	            $dataitem1['from_email']=Configuration::get('FARGO_COURIER_EMAIL');
	           // $dataitem1['from_building']=$method->building_name;
	            $dataitem1['from_street']=Configuration::get('FARGO_COURIER_ADDRESS');
	           // $dataitem1['from_suburb']=$method->suburb;
	           // $dataitem1['from_additionalinfo']=$method->additional_information;
	            $dataitem1['from_telephone']=Configuration::get('FARGO_COURIER_TELNUMBER');
  
  		 $data[]=$dataitem1;
  		}
           
         $token = Configuration::get('FARGO_COURIER_MERCHANT_TOKEN');
		
        $ch = curl_init();

        $shippingrequest= base64_encode (json_encode( $data ));//echo  $shippingrequest;exit;
        $url="http://api.bookacourier.co.keindex.php?option=com_fargo_shipping&task=shippingregions.getItemsShippingMatrixValue&token=$token&shippingrequest=$shippingrequest";
        //set the url, number of POST vars, POST data
        
       
        $timeout = NULL;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        $resp=json_decode($data);
        
        return $resp->shipping_cost;

	}
	
}