<?php
defined('_JEXEC') or die('Restricted access');

class PesapalControllerTrends extends JControllerLegacy
{

    public function getDonationStats(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select( array('DATE_FORMAT(`date_created`, \'%e %b %Y\') AS \'date_formatted\'', 'SUM(amount) as total') )
            ->from($db->quoteName('#__pesapaldonations'))
            ->group($db->quoteName('date_formatted'));
        $db->setQuery($query);

        $results = $db->loadObjectList();
        $chart=new stdClass();
        $series_array= array();
        $series=array();

        $categories=array();
        foreach ($results as $result){
            array_push($series_array,(int)$result->total);
            array_push($categories,$result->date_formatted);
        }

        $serie = new stdClass();
        $serie->name="All Donations";
        $serie->data=$series_array;

        array_push($series,$serie);


        @$chart->series=$series;
        @$chart->categories=$categories;
        echo new JResponseJson($chart);
        JFactory::getApplication()->close();

    }
    public function getStatusStats(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select( array('status', 'SUM(amount) as total') )
            ->from($db->quoteName('#__pesapaldonations'))
            ->group($db->quoteName('status'));
        $db->setQuery($query);
        $results = $db->loadObjectList();
        $json = json_encode($results);

        echo($json);
        JFactory::getApplication()->close();
    }
}

