define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Ordergroove_Subscription/js/model/checkbox-validator'
], function (Component, additionalValidators, checkboxValidator) {
    'use strict';

    additionalValidators.registerValidator(checkboxValidator);

    return Component.extend({});
});
