<?php
defined('_JEXEC') or die;

class PesapalViewTrends extends JViewLegacy
{
    protected $items;

    public function display($tpl = null)
    {
        ;
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }
        $document = JFactory::getDocument();
        $document->addScript('components/com_pesapal/scripts/jquery.min.js');
        $document->addScript('components/com_pesapal/scripts/highcharts.js');
        $document->addScript('components/com_pesapal/scripts/donations.js');
        $this->addToolbar();

        parent::display($tpl);


    }

    protected function addToolbar()
    {
        JToolbarHelper::title(JText::_('COM_PESAPAL_TRENDS'), '');
        JToolbarHelper::preferences('com_pesapal');

        JHtmlSidebar::addEntry('Donation List', 'index.php?option=com_pesapal', 0);
        JHtmlSidebar::addEntry('Donation Trends', 'index.php?option=com_pesapal&view=trends', 1);

    }

}