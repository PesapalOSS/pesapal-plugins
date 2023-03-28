<?php
/**
  * Name:		Fargo
  * Type:		Shipping Controller
  * Built by:	verviant <www.verviant.com>
  * Date:		3-13-2014
  * Tested on:	Magento ver. 1.8.0.2
 */
class Fargo_Shipping_ShippingController extends Mage_Core_Controller_Front_Action {  
      //indexAction is the default Action for any controller  
    public function indexAction() {  
    echo "indexAction";  
    $helloworld = Mage::getModel("Fargo_Shipping_Model_Carrier_LocalDelivery");  
    $helloworld->helloworld("helloworld");  
  }
    public function getCustomerTownAction() {  
   $townid	= 	$_GET['param'];
    $model = Mage::getModel("Fargo_Shipping_Model_Carrier_LocalDelivery");  
    $model->getTownId($townid);  
  }
  public function saveTownsAction() {  
    $sitetoken		= 	Mage::getStoreConfig('carriers/fargoshipping/token');  
    $model = Mage::getModel("Fargo_Shipping_Model_Carrier_LocalDelivery");  
    $model->gettowns($sitetoken);
    echo "Fargo KE Towns Added........Now Select Your Store Town in the Fargo Shipping Configuration Settings and Save.";
  }
    public function createTownsTableAction() {		
        $sitetoken		= 	Mage::getStoreConfig('carriers/fargoshipping/token'); 
		$resource = Mage::getSingleton('core/resource');
		$connection = $resource->getConnection('core_read');
		$table = $resource->getTableName('directory_towns');
				
		
			$query			=
                        "DROP TABLE IF EXISTS ".$table."; CREATE TABLE ".$table." (town_id INT(11) NOT NULL , town_name VARCHAR(20) NULL, country_id VARCHAR(5) NULL ) ENGINE=MYISAM DEFAULT CHARSET=utf8";
   
	$connection->query($query);
			
			echo "Fargo Towns Table Created........";
	
	$table2 = $resource->getTableName('sales_flat_order');				 
		$query = 'Show columns from ' . $table2. ' like "consignment_id" ';
		$column = $connection->fetchAll($query);
		
		if(empty($column)){
			$trackingtable	=	$resource->getTableName('sales_flat_order');
			$query			=	"ALTER TABLE ".$trackingtable." ADD COLUMN consignment_id VARCHAR(50) NULL";
			$connection->query($query);
			
			echo "Fargo Shipping Tracking Column added to Sales Table  ........";
		}
		else{
			echo 'Fargo tracking column already exists...';
		}

    }
        
}  