<?php
defined('_JEXEC') or die;

class PesapalViewDonation extends JViewLegacy
{
    protected $item;
    protected $form;

    public function display($tpl = null)
    {
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);
        JToolbarHelper::title(JText::_('COM_PESAPAL_MANAGER_DONATION'), '');
        JToolbarHelper::save('donation.save');
        if (empty($this->item->id)) {
            JToolbarHelper::cancel('donation.cancel');
        } else {
            JToolbarHelper::cancel('donation.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}