{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}


	{if isset($my_module_display_name) && $my_module_display_name}
		<h2>{$my_module_display_name}</h2>
	{else}
        <h2>Pesapal</h2>
	{/if}
	<div class ="alert alert-info">
	<fieldset>
	<legend> {l s=' Help'} </legend>
		<a href="http://www.pesapal.com/" style="float: right;">
			<img src="../modules/{$my_module_name}/logo_pesapal.jpg" alt="Pesapal" />
		</a>
		
		{*<p>{l s='In your PrestaShop admin panel' }</p>*}
		{l s='1. Open an account online at'}
		<a href="http://www.pesapal.com/">www.pesapal.com</a> as a merchant.
		<br><br>
		<p>
		{l s='Pesapal requires Full names and email/phone number.'} 
		{assign var="ipn_setting" value="PesaPal requires Full names and email/phone number.<br/>To handle APN return requests, please set IPN Listener URL field to:<br><b>{$ipn_url}</b> on your pesapal account settings"}
		{$ipn_setting nofilter}
		</p>
		<br/>
		<blockquote>You will receive an email with a consumer_key and consumer_secret</blockquote>
		{l s='2. Fill in the consumer key and consumer secret in the general settings below'}
		<br><br>
		{l s='3. Add a small decription about the transaction. Ideally a 5-10 letter description'}
		<blockquote>Eg. Purchase on goods from ABC company<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Donations to XYZ organization</blockquote>
		{l s='4. Input a 3-4 letter code to be used as a reference for each transaction '}
		<b>Note:</b> Each business MUST have a unique code 
		<blockquote>Eg. ABC - For organization ABC <br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ZACK - For the bring ZACK back home campaign etc</blockquote>
		<br><br>
		{l s='5. Key in the message that will be displayed to the client after a payment has been made.'}
		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pending message: </b>There is an ongoing communication between pesapal and an external entity hence the status is yet to be confirmed.<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Invalid message: </b>This system can not actually verify the status of the payment due to some reason. <br /> However, as a merchant you can check the actual status when you login to your merchants account.
		<br /><br />
		<p>{l s='6. Update your settings and test the pesapal payment plugin'}</p>
		
	</fieldset>
	</div>
	