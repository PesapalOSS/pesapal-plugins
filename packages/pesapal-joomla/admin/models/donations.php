<?php
defined('_JEXEC') or die;

class PesapalModelDonations extends JModelList
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'id',
                'firstname',
                'lastname',
                'email',
                'amount',
                'status',
                'method',
                'reference',
                'date_created',
            );
        }
        parent::__construct($config);
    }
    protected function populateState($ordering = null, $direction =null)
    {
        parent::populateState('id', 'desc');
    }
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__pesapaldonations'));

        // Filter: like / search
        $search = trim($this->getState('filter.search'));
        $from = trim($this->getState('filter.from'));
        $to = trim($this->getState('filter.to'));
        $status= trim($this->getState('filter.status'));

        if (!empty($search))
        {
            $like = $db->quote('%' . $search . '%');
            $num=$db->quote($search);
            $query->where('firstname LIKE ' . $like.' OR lastname LIKE'.$like.' OR amount = '.$num);
        }

        if(!empty($to)){
            $to = $db->quote($to);
            $query->where('date_created < '.$to);
        }
        if(!empty($from)){
            $from = $db->quote($from);
            $query->where('date_created > '.$from);
        }
        if(!empty($status)){
            $q = $db->quote($status );
            if(!empty($from) || !empty($to) || !empty($search)) {
                $query->andWhere('status = ' . $q);
            }
            else{
                $query->where('status = ' . $q);
            }
        }

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'id');
        $orderDirn 	= $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        return $query;
    }
}