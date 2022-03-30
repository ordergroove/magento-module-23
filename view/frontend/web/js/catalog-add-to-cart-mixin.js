define([
    'jquery',
    'mage/translate',
    'jquery/ui',
    'Magento_Catalog/js/product/view/product-ids-resolver',
    'jquery-ui-modules/widget',
    'domReady!'
], function ($, $t, alert, idsResolver) {
    'use strict';
    return function (widget) {
        $.widget('mage.catalogAddToCart', widget, {
            submitForm: function (form) {
                var configurableSelectedSimpleProductId = $("input[name=selected_configurable_option]").val();
                if (configurableSelectedSimpleProductId !== "") {
                    this.setSubscriptionCookie(configurableSelectedSimpleProductId);
                } else {
                    var productId = idsResolver(form);
                    this.setSubscriptionCookie(productId);
                }
                this._super(form);
            },
            setSubscriptionCookie: function (productId) {
                if (window.OG) {
                    var configurableSelectedSimpleProductId = $("input[name=selected_configurable_option]").val();
                    var isSubscriptionProduct = window.OG.getOptins(productId);
                    if (isSubscriptionProduct.length > 0) {
                        $.cookie('product_subscribed_' + productId, true);
                    } else {
                        $.cookie('product_subscribed_' + productId, false);
                    }
                }
            }
        });

        return $.mage.catalogAddToCart;
    }
});
