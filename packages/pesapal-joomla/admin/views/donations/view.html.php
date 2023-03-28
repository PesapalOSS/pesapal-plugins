<?php
defined('_JEXEC') or die;

class PesapalViewDonations extends JViewLegacy
{
    protected $items;
    protected $state;

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $context = "pesapal.list.admin.pesapal";
        $this->items = $this->get('Items');
        $this->state = $this->get('State');
        $this->pagination		= $this->get('Pagination');
        $this->filter_order 	= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'date_created', 'cmd');
        $this->filter_order_Dir = $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', 'asc', 'cmd');
        $this->filterForm    	= $this->get('FilterForm');
        $this->activeFilters 	= $this->get('ActiveFilters');
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }
        $this->addToolbar();
        parent::display($tpl);


    }

    protected function addToolbar()
    {
        JToolbarHelper::title(JText::_('COM_PESAPAL_MANAGER_DONATIONS'), '');
        JToolbarHelper::preferences('com_pesapal');
        JHtmlSidebar::addEntry('Donation List','index.php?option=com_pesapal',1);
        JHtmlSidebar::addEntry('Donation Trends','index.php?option=com_pesapal&view=trends',0);

    }
    protected function getSortFields()
    {
        return array(
            'a.firstname' => JText::_('COM_PESAPAL_FULLNAME'),
            'a.status' => JText::_('COM_PESAPAL_STATUS'),
            'a.date_created' => JText::_('COM_PESAPAL_DATE'),
            'a.amount' => JText::_('COM_PESAPAL_AMOUNT'),
            'a.id' => JText::_('JGRID_HEADING_ID')
        );
    }

}