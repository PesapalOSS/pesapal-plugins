* INSTALLATION INSTRUCTIONS PESAPAL WHMCS V1.4.3 *

The PesaPal gateway module requires the use of modified templates that are provided with this distribution. 

Please follow the instructions below to setup the PesaPal gateway module. 



The templates provided are based on WHMCS 8.6.X and the default theme.

* 

Upload pesapal.php AND the pesapal folder to your whmcs's modules/gateways folder.

* 

Upload the callback/pesapal.php file to your whmcs's modules/gateways/callback folder.

* 

<ignore>Copy the pesapal_callback.tpl and pesapal_iframe.tpl inside the template folder to your WHMCS's template directory. e.g root/templates/six/pesapal_iframe.tpl</ignore>

These templates are based off of the default template in 8.6.1

Enable the PesaPal module in the WHMCS admin area by going to Apps & Integrations->Payments Apps->View All->under Additional apps, locate Pesapal->click on it to activate then manage and paste in your administrator username, Consumer Key and Consumer Secret. 


Enter your base domain url (include the trailing slash) e.g https://mywebsite.com/ . If this setting is ignored, the module will automatically use your systemURL 

To get your consumer Key and Consumer Secret, Open a business account on www.pesapal.com or Pesapal test credentials. 

If you opened an account on www.pesapal.com(live account), the key and secret have been sent to the email address you registered with.

Find test credentials here, https://developer.pesapal.com/api3-demo-keys.txt

* 


Ensure when you are done testing the plugin using the demo/sandbox account you switch to the live API.

*

Save configurations.



NB:// Do not change display name in the configuration





/*************/
PesaPal IPN setup.
IPN has been automated the this release. You don't ant extra setup.

Some transaction may not be verified immediately where a client completes the payment. 

IPN helps notify your WHMCS setup that a payment has COMPLETED successfully. 

If you have any questions, recommendations or need installation assistance, please send us an email to developer@pesapal.com
    
