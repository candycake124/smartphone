define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'mage/utils/wrapper',
    'installment',
    'ko'
], function ($, utils, wrapper, installment, ko) {
    'use strict';
    return function(targetModule){
       

        var updatePrice = targetModule.prototype.updatePrice;
        
        var updatePrice = wrapper.wrap(updatePrice, function(original){
            var newPrices = Array.prototype.slice.call(arguments)[1];
            var prices = this.cache.displayPrices,
                additionalPrice = {},
                pricesCode = [],
                priceValue, origin, finalPrice;

            this.cache.additionalPriceObject = this.cache.additionalPriceObject || {};

            if (newPrices) {
                $.extend(this.cache.additionalPriceObject, newPrices);
            }

            if (!_.isEmpty(additionalPrice)) {
                pricesCode = _.keys(additionalPrice);
            } else if (!_.isEmpty(prices)) {
                pricesCode = _.keys(prices);
            }

            _.each(this.cache.additionalPriceObject, function (additional) {
                if (additional && !_.isEmpty(additional)) {
                    pricesCode = _.keys(additional);
                }
                _.each(pricesCode, function (priceCode) {
                    priceValue = additional[priceCode] || {};
                    priceValue.amount = +priceValue.amount || 0;
                    priceValue.adjustments = priceValue.adjustments || {};

                    additionalPrice[priceCode] = additionalPrice[priceCode] || {
                            'amount': 0,
                            'adjustments': {}
                        };
                    additionalPrice[priceCode].amount =  0 + (additionalPrice[priceCode].amount || 0) +
                        priceValue.amount;
                    _.each(priceValue.adjustments, function (adValue, adCode) {
                        additionalPrice[priceCode].adjustments[adCode] = 0 +
                            (additionalPrice[priceCode].adjustments[adCode] || 0) + adValue;
                    });
                });
            });

            if (_.isEmpty(additionalPrice)) {
                this.cache.displayPrices = utils.deepClone(this.options.prices);
            } else {
                _.each(additionalPrice, function (option, priceCode) {
                    origin = this.options.prices[priceCode] || {};
                    finalPrice = prices[priceCode] || {};
                    option.amount = option.amount || 0;
                    origin.amount = origin.amount || 0;
                    origin.adjustments = origin.adjustments || {};
                    finalPrice.adjustments = finalPrice.adjustments || {};

                    finalPrice.amount = 0 + origin.amount + option.amount;
                    console.log(finalPrice.amount);
                    var box = this.element,
                        reloadProductId,
                        reloadInstallmentProductId,
                        priceHolders = $('[data-price-type]', box);
                    reloadProductId = box.data('productId');
                    reloadInstallmentProductId = $("[data-role='block-installment" + reloadProductId + "']");
                    reloadInstallmentProductId.attr("data-installment-for-price", finalPrice.amount);
                    if(!ko.dataFor($(reloadInstallmentProductId)[0])) {
                        $(reloadInstallmentProductId).applyBindings( new installment({
                            "component": 1,
                            "index": "installment"+reloadProductId+""
                        }));
                    }
                    _.each(option.adjustments, function (pa, paCode) {
                        finalPrice.adjustments[paCode] = 0 + (origin.adjustments[paCode] || 0) + pa;
                    });
                }, this);
            }
             
                    

            return original();
        });
        targetModule.prototype.updatePrice = updatePrice;
        return targetModule;
    };
});