<?php

defined('_JEXEC') or die('Restricted access');

class PesapalController extends JControllerLegacy
{
    public function donate()
    {
        $model = $this->getModel('donate');
        $model->makeDonation();
    }

    public function checkStatus()
    {
        $model = $this->getModel('donate');
        echo json_encode($model->checkStatus());
    }

    public function ipnEndPoint()
    {
        $pesapalNotification = $_GET['pesapal_notification_type'];
        $reference_no = $_GET['pesapal_merchant_reference'];
        $tracking_id = $_GET['pesapal_transaction_tracking_id'];
        if ($pesapalNotification == "CHANGE" && $tracking_id != '') {

            $model = $this->getModel('donate');
            $res = $model->checkStatusIpn($tracking_id, $reference_no);
            if ($res->db && $res->status != "PENDING") {
                $resp = "pesapal_notification_type=$pesapalNotification&pesapal_transaction_tracking_id=$tracking_id&pesapal_merchant_reference=$reference_no";
                ob_start();
                echo $resp;
                ob_flush();
                exit;
            }else{
                echo 'not';
            }
        }
    }

}