<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();


class  PesapalModelDonate extends JModelLegacy{

    function makeDonation(){
        include_once(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/donate-iframe.php');
    }

    function getPrefs(){

    }
    function checkStatus(){

        $reference_no = $_POST['pesapal_merchant_reference'];
        $tracking_id = $_POST['pesapal_transaction_tracking_id'];
        include_once(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/checkStatus.php');
        $status = checkStatus($tracking_id,$reference_no);
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('status') . ' = '.$db->quote($status['status']),
            $db->quoteName('tracking_id') . ' = '.$db->quote($tracking_id),
            $db->quoteName('method') . ' = '.$db->quote($status['method'])
        );

        $conditions = array(
            $db->quoteName('reference') . ' = ' . $db->quote($reference_no)
        );

        $query->update($db->quoteName('#__pesapaldonations'))->set($fields)->where($conditions);

        $db->setQuery($query);
        $result = $db->execute();

        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__pesapaldonations'));
        $query->where('reference = ' . $db->quote($reference_no));
        $query->setLimit('1');
        $db->setQuery($query);
        $results = $db->loadObject();
        $res= new stdClass();
        $res->result=$results;
        $res->status=$status;
        return $res;
    }
    function checkStatusIpn($tracking_id,$reference_no){
        include_once(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/checkStatus.php');
        $status = checkStatus($tracking_id,$reference_no);
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('status') . ' = '.$db->quote($status['status']),
            $db->quoteName('tracking_id') . ' = '.$db->quote($tracking_id),
            $db->quoteName('method') . ' = '.$db->quote($status['method'])
        );

        $conditions = array(
            $db->quoteName('reference') . ' = ' . $db->quote($reference_no)
        );

        $query->update($db->quoteName('#__pesapaldonations'))->set($fields)->where($conditions);

        $db->setQuery($query);
        $db_status=true;
        try {
            $result = $db->execute();
        }
        catch (Exception $e){
            $db_status=false;
        }
        $result = new stdClass();
        $result->status = $status['status'];
        $result->db = $db_status;

        return $result;
    }


}
