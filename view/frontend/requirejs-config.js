var config = {
    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'Ordergroove_Subscription/js/configurable-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Ordergroove_Subscription/js/model/place-order-mixin': true
            },
            'Magento_Checkout/js/view/minicart': {
                'Ordergroove_Subscription/js/checkout/view/minicart': true
            },
            'Magento_Catalog/js/catalog-add-to-cart': {
                'Ordergroove_Subscription/js/catalog-add-to-cart-mixin': true
            },
        }
    },
    map: {
        '*': {
            'Magento_Checkout/template/minicart/item/default.html':
                'Ordergroove_Subscription/template/minicart/item/default.html',
            'Magento_Checkout/js/proceed-to-checkout':
                'Ordergroove_Subscription/js/proceed-to-checkout',
        }
    },
    deps: [
        "Ordergroove_Subscription/js/switch-subscription"
    ]
};
