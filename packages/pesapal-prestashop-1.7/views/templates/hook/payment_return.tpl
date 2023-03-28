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

<div id="center_column">
    <h1>Order confirmation</h1>
    
    <p>Pesapal payment method is {$payment_method}</p>
    {*<p>IPN....{$ipn_resp}</p>*}
        
    {if $status == "COMPLETED"}
        {assign var="transaction_response_style" value="alert-success"}

        {assign var="transaction_response" 
        value="<h4>Payment Completed</h4>
        Thank you <b>{$buyer}</b>, your payment has been processed successfully."
        }
      
    {*$pesapal_completed_msg*}

    {elseif $status == "PENDING"}
        {assign var="transaction_response_style" value="alert-info"}

        {assign var="transaction_response" 
        value="<h4>Payment Pending</h4>
        Thank you <b>{$buyer}</b>, Your payment is being processed.<br/>
        Once confirmed, You will receive an Email/SMS notification, and your payment settled instantly"
        }

      
    {*$pesapal_pending_msg*}

    {elseif $status == "FAILED"}
        {assign var="transaction_response_style" value="alert-warning"}
        {assign var="transaction_response" value="<h4>Payment Failed</h4>
        Sorry <b>{$buyer}</b>, Your payment has failed. This could be because of several reasons:
        <br/>
        <ol style='margin:0 0 5px 30px;'>
        <li>The card details you entered are incorrect.</li>
        <li>Your bank may have blocked online payments.</li>
        <li>You have insufficient funds in the card/mobile money account you are attempting to use.</li> 
        <li>Your bank may have declined this transaction, kindly check with your bank.</li>
        </ol>
        <br>
        Kindly try again or contact support at support@pesapal.com for assistance"
        }

     
    {*$pesapal_failed_msg*}

    {else}
        {assign var="transaction_response_style" value="alert-danger"}
        {assign var="transaction_response" value="Your payment was Invalid. Kindly try again or contact support at support@pesapal.com for assistance"}

    {/if}
    <div class="{$transaction_response_style}" style="border-left:5px solid; padding:5px 10px;">
        {if $ipn_resp}
            <div style="margin:10px 0 20px 0;"><h3>IPN Return Response:</h3>{$ipn_resp}</div>
        {/if}
        {$transaction_response nofilter} {*nofilter enables smarty to output HTML*}
    </div>
    
</div>