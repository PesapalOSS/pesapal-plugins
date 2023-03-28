<?php
/**
  * Name:		Pesapalexpress 
  * Type:		Payment Controller
  * Built by:	verviant <www.verviant.com>
  * Date:		3-13-2013
  * Tested on:	Magento ver. 1.7.0.2
 */

// Edit this page and create an installer script that perfoms an equivalent of the commented code below
	/*
	$resource = Mage::getSingleton('core/resource');
	$connection = $resource->getConnection('core_read');
	$table	=	$resource->getTableName('sales_flat_order');
	$query			=	"ALTER TABLE ".$table." ADD COLUMN pesapal_transaction_tracking_id VARCHAR(50) NULL";
	$connection->query($query);
	*/
//END OF CODE THAT YOU SHOULD CRON

 

$installer = $this;
/* @var $installer Pesapal_Pesapalexpress_Model_Resource_Setup */
$installer->startSetup();
$installer->run("");
$installer->endSetup();
