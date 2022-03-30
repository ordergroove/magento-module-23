define([
        'ko',
        'jquery',
        'uiComponent',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data'
    ],
    function (ko, $, Component, customer, checkout) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Ordergroove_Subscription/checkout/subscription-checkbox'
            },

            /**
             * Observe the checkbox value
             */
            initObservable: function () {
                this._super()
                    .observe({
                        CheckVal: ko.observable(false)
                    });
                return this;
            },

            /**
             * Checks if customer is logged in
             */
            isCustomerLoggedIn: customer.isLoggedIn,

            /**
             * Compare all conditions to pass result to template
             * @returns {boolean}
             */
            aggregateResult: function () {
                return !!(isCustomerLoggedIn && this.isOrderContainSubscriptionProduct() && this.isBraintreePaymentMethod());
            },

            /**
             * Checks if cart items contains ordergroove subscription products
             * @returns {boolean}
             */
            isOrderContainSubscriptionProduct: function () {
                if (!window.checkoutConfig.isOrdergrooveModuleEnabled){
                    return false;
                }
                var ordergrooveProductIds = window.checkoutConfig.ordergroove_product_ids;
                var isSubscriptionProduct = window.OG.getOptins(ordergrooveProductIds);
                return isSubscriptionProduct.length > 0;
            },

            /**
             * Checks allowed paymentMethods
             * @returns {boolean}
             */
            isBraintreePaymentMethod: function () {
                var checkIfBraintree = checkout.getSelectedPaymentMethod();
                return !!(isCustomerLoggedIn && checkIfBraintree === 'braintree');
            }
        });
    });
