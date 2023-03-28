<?php
namespace Pesapal\Pesapalexpress\Helper;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\Store;
use Pesapal\Pesapalexpress\Helper\pesapalCheckStatus;
use \Magento\Sales\Model\ResourceModel\Order;
 
// optional use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Checkout default helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
public function __construct(
        \Magento\Framework\App\Helper\Context $context,
         \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order $salesOrderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Config\Source\Order\Status $statuses,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        pesapalCheckStatus $pesapal,
        Order $Order
        
     ) {
         $this->_checkoutSession = $checkoutSession;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->scopeConfig = $scopeConfig;
        $this->statuses = $statuses;
        $this->directory_list = $directory_list;
        $this->pesapal = $pesapal;
        $this->invoiceSender = $invoiceSender;
        $this->Order = $Order;

      // $this->orderSender= $orderSender;
        parent::__construct($context);
        
    }

public function updateOrder($orderId, $trackingId, $action){
        $order   = $this->salesOrderFactory->loadByIncrementId($orderId);
        $results=$this->detailedCheckStatus($trackingId,$orderId);
        //Get order status
        $status	= $results['status'];
        if($status == 'INVALID'){
            $status	=	$this->simpleCheckStatus($orderId, $trackingId);
        }

        /** Update the order status if is new order
         * or
         * if action is cron, the new status is not pending
         */
         if($action == 'neworder' || $status != 'PENDING'){
            if($status == 'COMPLETED'){
                 
            if ($order->getStatus()!=="complete") {
           
              $order->setState(\Magento\Sales\Model\Order::STATE_COMPLETE);
              
              $order->setStatus('complete');   
           
          // Create invoice for this order
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $invoice = $objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($order);
            
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
           
            $invoice->register();
           
            // Save the invoice to the order
            $transaction = $objectManager->create('Magento\Framework\DB\Transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
        
            $transaction->save();
        
            // Magento\Sales\Model\Order\Email\Sender\InvoiceSender
            $this->invoiceSender->send($invoice);
            
            $order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId())
            )
                ->setIsCustomerNotified(true);
               // ->save();
            }
            }
            else if($status == 'PENDING')
                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
            else if($status == 'FAILED')
                $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
            else if($status == 'INVALID')
                $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
        }

        /** Send mail if is a new order
         * or
         * If action is cron, send mail only if status changes to COMPLETED or FAILED
         */
            if($action == 'neworder' || $status == 'COMPLETED' || $status == 'FAILED'){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
             }
 
        $this->Order->save($order);

        return $status;
    }

     
   public function detailedCheckStatus($pesapalTrackingId,$order_id){
 
        $results=$this->pesapal->detailedcheckStatus($pesapalTrackingId,$order_id);

        return $results;
    }
    public function simpleCheckStatus($pesapalTrackingId,$order_id){

        $status=$this->pesapal->simplecheckStatus($pesapalTrackingId,$order_id);

        return $status;
    }

     public function pesapalIframe($order, $redirect=false){

        $iframe=$this->pesapal->loadIframe($order,$redirect);
         return $iframe;
    }
    
    public function cancelAction() {
        if ($this->_checkoutSession->getLastOrderId()) {
            $order = $this->salesOrderFactory->load($this->_checkoutSession->getLastOrderId());
            if($order->getId()) {
                // Flag the order as 'cancelled' and save it
                $order->cancel()->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true, 'Pesapal Gateway has declined the payment.')->save();
            }
        }
    }
}