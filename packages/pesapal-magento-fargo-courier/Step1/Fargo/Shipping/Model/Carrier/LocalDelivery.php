<?php
class Fargo_Shipping_Model_Carrier_LocalDelivery extends Mage_Shipping_Model_Carrier_Abstract
{
   
    /**
* Our test shipping method module adapter
*/

  /**
   * unique internal shipping method identifier
   *
   * @var string [a-z0-9_]
   */
  protected $_code = 'fargoshipping';
  public function toOptionArray()
   {
    $resource = Mage::getSingleton('core/resource');
     
    /**
     * Retrieve the read connection
     */
    $readConnection = $resource->getConnection('core_read');
     
    $query = 'SELECT town_id,town_name FROM ' . $resource->getTableName('directory_towns');
     
    /**
     * Execute the query and store the results in $results
     */
    $results = $readConnection->fetchAll($query);
    
    $towns =array();
    foreach($results as $town)
    {
       $thetown =  array('value' =>$town['town_id'],
                          'label' =>   str_replace("'","\'",ucwords(strtolower($town['town_name']))),
           
       );
       $towns[]= $thetown;
    }
 
       return $towns;
   }

  
  function helloworld($arg) {
    echo "<br>Hello World! My argument is : " . $arg;  
  } 
  public function gettowns($token)
    {
       
        $ch = curl_init();
         
		//$url="http://localhost/merchants/index.php?option=com_fargo_shipping&task=shippingregions.getRegions&token=$token";
		$url="http://api.bookacourier.co.ke/index.php?option=com_fargo_shipping&task=shippingregions.getRegions&token=$token";
 
		//set the url, number of POST vars, POST data
		$timeout = NULL;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
                $data = curl_exec($ch);
                curl_close($ch);
                $resp=json_decode($data);
               // $obj = new ObjectAndXML();
               $regions=$resp->regions;
	       $resource = Mage::getSingleton('core/resource');
	     $table = $resource->getTableName('directory_country_region');
        $table2 = $resource->getTableName('directory_towns');
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $values=array();
	if(count($regions)>0)
	{
        $sql1="DELETE FROM ".$table." WHERE country_id='KE'";
        $write->query($sql1);
	    foreach($regions as $region)
	    {
		
               $town= Mage::getSingleton('core/resource')->getConnection('default_write')->quote($region->title);
               $values[]="($region->id,$town)";
	    
               Mage::getSingleton('core/resource')->getConnection('default_write')->quote($region->title);
          $sql="REPLACE INTO ".$table2." (town_id,town_name,country_id) VALUES($region->id,$town,'KE')";
	    $write->query($sql);

	  $sql2="REPLACE INTO ".$table." (region_id,country_id,code,default_name) VALUES($region->id,'KE',$region->id,$town);";
	   $write->query($sql2);
	    }
         
        } 
		 
    }
    public function getTownId($townid){
      $customertown=$townid;
      echo $customertown;
      
    }
  public function getShippingCost($data)
    {
       
        $ch = curl_init();
        $token="Z20LyuV2Mxhj9lcsGhtw4XMVwPXX7rcr";
        
        
		//$url="http://localhost/merchants/index.php?option=com_fargo_shipping&task=shippingregions.getItemsShippingValue&token=$token&shippingrequest=";
		$url="http://api.bookacourier.co.ke/index.php?option=com_fargo_shipping&task=shippingregions.getItemsShippingValue&token=$token&shippingrequest=".$data;

		//set the url, number of POST vars, POST data
		$timeout = NULL;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
                $data = curl_exec($ch);
                curl_close($ch);
                $resp=json_decode($data);
		//close connection
		
                //return array($orderid,$resp->consignmentid,$resp->shipping_cost,$resp->itemized);
                return $resp->shipping_cost;
              
   
    }
  
  /**
   * Collect rates for this shipping method based on information in $request
   *
   * @param Mage_Shipping_Model_Rate_Request $data
   * @return Mage_Shipping_Model_Rate_Result
   */
  
  
  
  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {  
         	$token=Mage::getStoreConfig('carriers/fargoshipping/token'); 
          $storetown = Mage::getStoreConfig('carriers/'.$this->_code.'/town');

               // skip if not enabled
        if (!Mage::getStoreConfig('carriers/'.$this->_code.'/active'))
            return false;
        
         $quote = Mage::getSingleton('checkout/session')->getQuote();
         $billingaddress = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
        $shippingAddress = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
       
  $townto=$shippingAddress->getData("region_id");
   if($townto==""){$townto=$billingaddress->getData("region_id");}
   else { $townto=$shippingAddress->getData("region_id");}
  
  $towntoaddress=$shippingAddress->getData("postcode");
   if($towntoaddress==""){$$towntoaddress=$billingaddress->getData("postcode");}
   else { $towntoaddress=$shippingAddress->getData("postcode");}
   
 $customer = Mage::getSingleton('customer/session')->getCustomer();

  
        $cartItems = $quote->getAllVisibleItems();
        foreach ($cartItems as $item)
        {
            $productId = $item->getProductId();
            $product = Mage::getModel('catalog/product')->load($productId);
                        // Do something
                 $productName = $item->getProduct()->getName();
                 $productweight = $item->getWeight();
                 $itemtype = $product->getSku();
                 $quantity= $item->getQty();
                 $itemheight = $product->getData('itemheight');
                 $itemwidth = $product->getData('itemwidth');
                 $itemlength = $product->getData('itemlength');
                //echo $townto=$this->getTownId($customertown);exit;
        
                $dataitem[]=array();
                $dataitem['quantity']=$quantity;
 		$dataitem['radius']=$itemwidth;
 		$dataitem['height']=$itemheight;
 		$dataitem['width']=$itemwidth;
 		$dataitem['length']=$itemlength;
 		$dataitem['weight']=$productweight;
                $dataitem['dropoff']=(int)$townto;
                 $dataitem['pickup']=(int)$storetown;
 
                $dataitem['product_sku']=$itemtype;
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$dataitem['to_building']=""; 
                $dataitem['to_street']=$shippingAddress->getData("street");;
                $dataitem['to_suburb']=$shippingAddress->getData("city");;
                $dataitem['to_additionalinfo']="";
                $dataitem['firstname']=Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getFirstname();
                $dataitem['lastname']=Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getLastname();
                $dataitem['telephone']=Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getTelephone();
                $dataitem['email']=Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getEmail();


                  $dataitem['from_firstname']=Mage::getStoreConfig('general/store_information/name');
                $dataitem['from_lastname']="";
                $dataitem['from_email']=Mage::getStoreConfig('trans_email/ident_general/email');
                $dataitem['from_building']="";
                $dataitem['from_street']="";
                $dataitem['from_suburb']="";
                $dataitem['from_additionalinfo']=Mage::getStoreConfig('general/store_information/address');
                $dataitem['from_telephone']=Mage::getStoreConfig('general/store_information/phone');;
                 $data[]=$dataitem;
                 
                 }
          
               $shippingdata=$data;  
                $shippingrequest= base64_encode (json_encode( $shippingdata ));  // var_dump( $shippingdata);exit;
             $ch = curl_init();
        
       
        
		$url="http://api.bookacourier.co.ke/index.php?option=com_fargo_shipping&task=shippingregions.getItemsShippingValue&token=$token&shippingrequest=$shippingrequest";
		 //echo $url;
		//set the url, number of POST vars, POST data
		
		$timeout = NULL;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
                $newdata = curl_exec($ch);
                curl_close($ch);
                $resp=json_decode($newdata); 
              
 	       $shippingcost=$resp->shipping_cost;
               // array($resp->consignmentid,$resp->shipping_cost,$resp->itemized);
           
           
//           
                             

        
      
        $result = Mage::getModel('shipping/rate_result');
         
       
      
      
        
       
        $method = Mage::getModel('shipping/rate_result_method');
 
        $method->setCarrier($this->_code);
        $method->setCarrierTitle(Mage::getStoreConfig('carriers/'.$this->_code.'/title'));
        /* Use method name */
        $method->setMethod('delivery');
        $method->setMethodTitle(Mage::getStoreConfig('carriers/'.$this->_code.'/methodtitle'));
        $method->setCost($shippingcost);
        $method->setPrice($shippingcost);
        $result->append($method);
        return $result;
     
    }
  public function getAllowedMethods() {
    return array($this->_code => $this->getConfigData('name'));
  }
  
}
