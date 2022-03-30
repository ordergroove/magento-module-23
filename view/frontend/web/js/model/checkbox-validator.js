define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/model/messageList',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/checkout-data'
], function ($, $t, messageList, customer, checkout) {
    'use strict';

    return {
        /**
         * Validate Subscription checkbox
         *
         * @returns {Boolean}
         */
        validate: function () {
            var isCheckboxChecked = false;
            var isCheckboxAvailable = $('#subscribecheckbox').length;
            var isBoxChecked = $('#subscribecheckbox').is(':checked');
            var checkIfBraintree = checkout.getSelectedPaymentMethod();
            var ordergrooveProductIds = window.checkoutConfig.ordergroove_product_ids;

            if (!window.checkoutConfig.isOrdergrooveModuleEnabled) {
                isCheckboxChecked = true;
                return isCheckboxChecked;
            }

            var isSubscriptionProduct = window.OG.getOptins(ordergrooveProductIds);

            if (isSubscriptionProduct.length == 0 ||
                !customer.isLoggedIn() ||
                !(checkIfBraintree === 'braintree')) {
                isCheckboxChecked = true;
                return isCheckboxChecked;
            }

            /**
             * Update checkbox state based on the availability of element on DOM
             */
            if (isCheckboxAvailable > 0) {
                isCheckboxChecked = !!isBoxChecked;
            }

            /**
             *  Show error message on failed validation
             */
            if (!isCheckboxChecked) {
                messageList.addErrorMessage({
                    "message": 'We can not continue with a subscription order until you check the box.'
                });
            }

            return isCheckboxChecked;
        }
    }
});

