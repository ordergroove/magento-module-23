define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    $,
    wrapper,
    fullScreenLoader
) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {

            if (window.OG && checkoutConfig.isCustomerLoggedIn) {
                const productIds = window.OG.getOptins(checkoutConfig.ordergroove_product_ids);
                if (productIds.length > 0) {
                    if (paymentData['additional_data']) {
                        paymentData['additional_data']['og_optins'] = JSON.stringify(productIds);
                    } else {
                        paymentData['additional_data'] = {
                            'og_optins': JSON.stringify(productIds)
                        }
                    }
                }
            }

            return originalAction(paymentData, messageContainer).fail(
                function () {
                    fullScreenLoader.stopLoader();
                }
            );
        });
    };
});
