<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldIpn extends JFormField {

    protected $type = 'Ipn';

    // getLabel() left out

    public function getInput() {
        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        return 'To handle IPN updates, please copy this URL to IPN settings on your Pesapal Dashboard :<br/><strong>'.JRoute::_(JURI::root()).'index.php?option=com_pesapal&task=ipnEndPoint'.'</strong>';
    }
}