define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";

    function reloadOgOffers(config) {
        var CurrentProduct = config.CurrentProduct;
        var checkAvailableElement = $("input[id^='bundle-option-']").length;
        if (checkAvailableElement) {
            $("[id^='bundle-option-']").on("change", function (e) {
                var allOptionValues = [];
                var allRadioValues = [];
                var allSelectOptionValues = [];
                var allMultiSelectOptionValues = [];
                $("input[id^='bundle-option-']:checked").each(function () {
                    var ID = $(this).attr('id');
                    var InputForQty = ID.substr(0, ID.lastIndexOf('-'));
                    var Qty = $('#' + InputForQty + '-qty-input').val();
                    allOptionValues.push({"optionID": ID, "selectionQTY": Qty});
                });

                $("input:hidden:checked").each(function () {
                    var ID = $(this).attr('id');
                    var InputForQty = ID.substr(0, ID.lastIndexOf('-'));
                    var Qty = $('#' + InputForQty + '-qty-input').val();
                    allRadioValues.push({"optionID": ID, "selectionQTY": Qty});
                });

                $("select[id^='bundle-option-']").each(function () {
                    var ID = $(this).attr('id');
                    var OptionID = $('#' + ID).val();
                    if (Array.isArray(OptionID)) {
                        OptionID.forEach(function (option) {
                            allSelectOptionValues.push({"optionID": ID + "-" + option, "selectionQTY": "1"});
                        });
                    } else {
                        allSelectOptionValues.push({
                            "optionID": ID + "-" + OptionID,
                            "selectionQTY": $('#' + ID + '-qty-input').val()
                        });
                    }
                });

                var allValues = allOptionValues.concat(allRadioValues).concat(allSelectOptionValues).concat(allMultiSelectOptionValues);
                var updatedBundleComponents = config.updatedBundleComponents;
                var multipleIds = [];
                var singleIds = [];

                allValues.forEach(function (item) {
                    var optionID = item["optionID"];
                    var selectionId = optionID.substr(optionID.lastIndexOf('-') + 1);
                    var selectionQty = item["selectionQTY"];
                    var productId = updatedBundleComponents[selectionId];
                    if (selectionQty > 1) {
                        for (var count = 0; count < selectionQty; ++count) {
                            multipleIds.push(String(productId));
                        }
                    } else {
                        singleIds.push(String(productId));
                    }
                });

                var finalComponents = multipleIds.concat(singleIds);
                $("#changed_og_offers").empty();
                e.preventDefault();
                $("#default_og_offers").remove();
                var og = $('<og-offer location="pdp">').attr('product', CurrentProduct).attr('product-components', JSON.stringify(finalComponents));
                $('#changed_og_offers').append(og).show();
                $(".changed_og_offers:not(:first)").remove();
                return true;
            });
        }
    }

    return reloadOgOffers;
});
