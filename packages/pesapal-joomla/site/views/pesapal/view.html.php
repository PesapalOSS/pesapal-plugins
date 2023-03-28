<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the HelloWorld Component
 *
 * @since  0.0.1
 */
class PesapalViewPesapal extends JViewLegacy
{
    var $address_fields;
    var $pay_type;
    /**
     * Display the Hello World view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    function display($tpl = null)
    {
        $dconfig = JComponentHelper::getParams('com_pesapal');
        $this->address_fields = $dconfig->get('show_address_fields');
        $this->pay_type = $dconfig->get('payment_type');
        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_pesapal/css/bootstrap.min.css');
        $document->addStyleSheet('components/com_pesapal/css/custom.css');
        $document->addScript('components/com_pesapal/scripts/react.min.js');
        $document->addScript('components/com_pesapal/scripts/react-dom.min.js');
        $document->addScript('components/com_pesapal/scripts/browser.min.js');
        $document->addScript('components/com_pesapal/scripts/jquery.min.js');
        $document->addScript('components/com_pesapal/scripts/ajaxPostReact.js');
        $document->addScript('components/com_pesapal/scripts/textToLink.js');
        $document->addCustomTag('<script type="text/babel" src="components/com_pesapal/scripts/app.js" defer></script>');

        // Assign data to the view
        // Display the view
        parent::display($tpl);
    }
}