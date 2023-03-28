* INSTALLATION INSTRUCTIONS PESAPAL WHMCS V1.4.* *

The PesaPal gateway module requires the use of modified templates that are provided with this distribution. 

Please follow the instructions below to setup the PesaPal gateway module. 



The templates provided are based on WHMCS 7.1.X and the default theme.

* 

Upload pesapal.php AND the pesapal folder to your whmcs's modules/gateways folder.

* 

Upload the callback/pesapal.php file to your whmcs's modules/gateways/callback folder.

* 

<ignore>Copy the pesapal_callback.tpl and pesapal_iframe.tpl inside the template folder to your WHMCS's template directory. e.g root/templates/six/pesapal_iframe.tpl</ignore>

These templates are based off of the default template in 7.1.2.* 

Enable the PesaPal module in the WHMCS admin area by going to Setup->Payments->Payment Gateways->Pesapal and paste in your administrator username, Consumer Key and Consumer Secret. 


Enter your base domain url (include the trailing slash) e.g https://mywebsite.com/ . If this setting is ignored, the module will automatically use your systemURL 

To get your consumer Key and Consumer Secret, Open a business account on www.pesapal.com or demo.pesapal.com(Sandbox Application). 

If you opened an account on www.pesapal.com(live account), the key and secret have been sent to the email address you registered with.

If you opened an account on demo.pesapal.com, login and check on your dashboard(check at the bottom of the page)

* 


Ensure when you are done testing the plugin using the demo/sandbox account you switch to the live API.

*

Save configurations.



NB:// Do not change display name in the configuration





/*************/
PesaPal IPN setup.


Some transaction may not be verified immediately a client completes the payment. 

IPN helps notify your WHMCS setup that a payment has COMPLETED successfully. 

You will need to:
- Login to your PesaPal Merchant/business account
- Click IPN setting
- 
Enter: 
    Website Domain: eg. www.mysite.com
    

Website IPN Listener Url: http://[website domain]/modules/gateways/pesapal/ipn.php
                              
eg http://www.mysite.com/modules/gateways/pesapal/ipn.php


* 

If you have any questions, recommendations or need installation assistance, please send us an email to developer@pesapal.com
    
