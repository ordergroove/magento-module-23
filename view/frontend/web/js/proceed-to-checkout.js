/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'mage/cookies',
    'domReady!'
], function ($, authenticationPopup, customerData, urlBuilder) {
    'use strict';

    return function (config, element) {
        $(element).click(function (event) {

            var cart = customerData.get('cart'),
                customer = customerData.get('customer');
            event.preventDefault();

            if (window.checkoutConfig.isOrdergrooveModuleEnabled) {
                var ordergrooveProductIds = window.checkoutConfig.ordergroove_product_ids;
                if (window.OG) {
                    var isSubscriptionProduct = window.OG.getOptins(ordergrooveProductIds);
                    if (!customer().firstname && isSubscriptionProduct.length > 0) {
                        location.href = urlBuilder.build("ordergroove/checksubscription/index");
                        return false;
                    }
                }
            }

            if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                authenticationPopup.showModal();

                return false;
            }
            $(element).attr('disabled', true);
            location.href = config.checkoutUrl;
        });

    };
});
