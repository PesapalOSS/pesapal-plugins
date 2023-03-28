<?php
defined('_JEXEC') or die;

class PesapalModelDonation extends JModelAdmin
{
    protected $text_prefix = 'COM_PESAPAL';

    public function getTable($type = 'Pesapal', $prefix =
    'PesapalTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_pesapal.donation', 'donation',
            array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState('com_pesapal.edit.donation.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

}