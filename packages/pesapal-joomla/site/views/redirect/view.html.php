<?php

class PesapalViewRedirect extends JViewLegacy
{
    // Overwriting JView display method
    function display($tpl = null)
    {
        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_pesapal/css/bootstrap.min.css');
        $document->addStyleSheet('components/com_pesapal/css/custom.css');
        $document->addScript('components/com_pesapal/scripts/react.min.js');
        $document->addScript('components/com_pesapal/scripts/react-dom.min.js');
        $document->addScript('components/com_pesapal/scripts/browser.min.js');
        $document->addScript('components/com_pesapal/scripts/jquery.min.js');
        $document->addScript('components/com_pesapal/scripts/ajaxPostReact.js');
        $document->addCustomTag('<script type="text/babel" src="components/com_pesapal/scripts/redirect.js"></script>');
        parent::display($tpl);
    }
}
