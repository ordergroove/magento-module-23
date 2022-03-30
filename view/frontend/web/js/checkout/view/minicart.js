define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'mage/cookies',
    'domReady!'
], function ($, customerData, urlBuilder, cookie) {
    'use strict';

    return function (Component) {
        return Component.extend({
            initSidebar: function () {
                $('#top-cart-btn-checkout').click(function (event) {
                    var customer = customerData.get('customer');
                    event.preventDefault();

                    var productIdsInCart = [];
                    var cart = customerData.get('cart');

                    // Get cart items for guest user
                    var getCartItems = cart._latestValue.items;
                    for (var i = 0; i < getCartItems.length; i++) {
                        var productId = getCartItems[i].simple_product_id ? getCartItems[i].simple_product_id : getCartItems[i].product_id;
                        productIdsInCart.push(productId);

                        // Need to set the cookie for already existing cart items for logged in customers without cookie
                        if (window.OG && customer().firstname) {
                            var isItSubscriptionProduct = window.OG.getOptins(productId);
                            if (isItSubscriptionProduct.length > 0) {
                                $.cookie('product_subscribed_' + productId, true);
                            } else {
                                $.cookie('product_subscribed_' + productId, false);
                            }
                        }
                    }

                    // Redirect for guest users with subscription products
                    if (window.OG) {
                        var isSubscriptionProduct = window.OG.getOptins(productIdsInCart);
                        if (!customer().firstname && isSubscriptionProduct.length > 0) {
                            location.href = urlBuilder.build("ordergroove/checksubscription/index");
                            return false;
                        }
                    }
                });
            },
        });
    }
});
