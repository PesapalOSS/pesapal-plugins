<?php
class Fargo_Shipping_Model_Observer extends Varien_Event_Observer
{
  
    public function updatefargo(Varien_Event_Observer $observer) {
        $order = $observer->getEvent()->getOrder();
        $orderdetails = Mage::getModel('sales/order')->load($order->getId());        
   
        $token=Mage::getStoreConfig('carriers/fargoshipping/token');
        $storetown = Mage::getStoreConfig('carriers/fargoshipping/town');

                
        
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $billingaddress = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
        $shippingAddress = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
                
        $townto=$shippingAddress->getData("region_id");
        if($townto==""){$townto=$billingaddress->getData("region_id");}
        else { $townto=$shippingAddress->getData("region_id");}
           
        $towntoaddress=$shippingAddress->getData("postcode");
        if($towntoaddress==""){$towntoaddress=$billingaddress->getData("postcode");}
        else { $towntoaddress=$shippingAddress->getData("postcode");}
            
        $customer = Mage::getSingleton('customer/session')->getCustomer();

  
        $cartItems = $quote->getAllVisibleItems();
        if(!empty($cartItems)){
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
         
                $dataitem[]=array();
               
                $dataitem['quantity']=$quantity;
 		$dataitem['radius']=$itemwidth;
 		$dataitem['height']=$itemheight;
 		$dataitem['width']=$itemwidth;
 		$dataitem['length']=$itemlength;
 		$dataitem['weight']=$productweight;
                $dataitem['dropoff']=(int)$townto;
                $dataitem['pickup']=(int)$storetown;
                $dataitem['orderid']=$order->getId();
 
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
                $shippingrequest= base64_encode (json_encode( $shippingdata ));
                $ch = curl_init();
        
		$url="http://api.bookacourier.co.ke/index.php?option=com_fargo_shipping&task=shippingregions.getItemsShippingMatrixValue&token=$token&shippingrequest=$shippingrequest";
		 //echo $url;
		//set the url, number of POST vars, POST data
		
		$timeout = NULL;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
                $newdata = curl_exec($ch);
                curl_close($ch);
                $resp=json_decode($newdata); 
 
 	        $shippingconsignment=$resp->consignmentid;
 
                $write = Mage::getSingleton('core/resource')->getConnection('core_write');
                
                $resource = Mage::getSingleton('core/resource');
               
                $table2 = $resource->getTableName('sales_flat_order');	
               
                $sql="UPDATE  ".$table2." SET consignment_id=".$shippingconsignment." WHERE entity_id=".$order->getId();
	       
                $write->query($sql);
        }
            else{
              $this->notifydelivery( $observer);

            }
           
                
      
    }
    public function notifydelivery(Varien_Event_Observer $observer) {
        $order = $observer->getEvent()->getOrder();
        $quote  = $observer->getEvent()->getQuote();
        $orderdetails = Mage::getModel('sales/order')->load($order->getId());
        $token=Mage::getStoreConfig('carriers/fargoshipping/token');                
                $resource = Mage::getSingleton('core/resource');
                		
                $readConnection = $resource->getConnection('core_read');
               
                $table2 = $resource->getTableName('sales_flat_order');	
               
                //Mage::getSingleton('core/resource')->getConnection('default_write')->quote($region->title);

                $sql="SELECT  consignment_id FROM  ".$table2." WHERE entity_id=".$order->getId();
	       
                $result= $readConnection->fetchAll($sql);

                $consignmentid=(int)$result[0]['consignment_id'];

                $sql2="SELECT  status FROM  ".$table2." WHERE entity_id=".$order->getId();

                $result= $readConnection->fetchAll($sql2);

                $status=$result[0]['status'];
        
        if($consignmentid>0 && $status=="complete")
		{
			$ch = curl_init();   
        
			$url="http://api.bookacourier.co.ke/index.php?option=com_fargo_shipping&task=shippingregions.updatePaymetStatus&token=$token&consignmentid=$consignmentid&status=4";
			//set the url, number of POST vars, POST data

			$timeout = NULL;
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			//$resp=json_decode($data);
			
            //var_dump($resp);exit;

		}
     }
    

}
?>