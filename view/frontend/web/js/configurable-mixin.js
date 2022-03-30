define([
    'jquery',
    'mage/translate'
], function ($) {

    return function (widget) {
        $.widget('ordergroove.configurable', widget, {
            /**
             * {@inheritDoc}
             */
            _configureElement: function (element) {
                this.simpleProduct = this._getSimpleProductId(element);

                if (element.value) {
                    this.options.state[element.config.id] = element.value;

                    if (element.nextSetting) {
                        element.nextSetting.disabled = false;
                        this._fillSelect(element.nextSetting);
                        this._resetChildren(element.nextSetting);
                    } else {
                        let ogOffer = $("og-offer[location='pdp']");
                        if (!!document.documentMode) { //eslint-disable-line
                            let value = element.options[element.selectedIndex].config.allowedProducts[0];
                            this.inputSimpleProduct.val(value);
                            if(ogOffer.length){
                                ogOffer.attr("product",value);
                            }
                        } else {
                            let value = element.selectedOptions[0].config.allowedProducts[0];
                            this.inputSimpleProduct.val(value);
                            if(ogOffer.length){
                                ogOffer.attr("product",value);
                            }
                        }
                    }
                } else {
                    this._resetChildren(element);
                }

                this._reloadPrice();
                this._displayRegularPriceBlock(this.simpleProduct);
                this._displayTierPriceBlock(this.simpleProduct);
                this._displayNormalPriceLabel();
                this._changeProductImage();
            },
        });
        return $.ordergroove.configurable;
    };
});
