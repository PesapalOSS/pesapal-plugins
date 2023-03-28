/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url'        
    ],
    function (
              Component,
              placeOrderAction,
              selectPaymentMethodAction,
              customerData,
              checkoutData,
              additionalValidators,
              url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Pesapal_Pesapalexpress/payment/pesapal'
            },

            
             placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self = this,
                    placeOrder;
                
                    this.isPlaceOrderActionAllowed(false);
 
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                    jQuery.when(placeOrder).then(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;
                
             },

            

             afterPlaceOrder: function () {
            
            console.log('orderplaced');

            window.location.replace(url.build('pesapalexpress/payment/index'));
            //jQuery( location ).attr("href", '/pesapalexpress/payment/index');
            console.log('redirected');

        },/** Returns send check to info */
            getMailingAddress: function() {
                
            return window.checkoutConfig.payment.checkmo.mailingAddress;
        }

           
        });
    }
);
