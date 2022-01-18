define([
    'jquery',
    'mage/utils/wrapper',
    'installment',
    'ko'
], function ($, wrapper, installment, ko) {
    'use strict';
    return function(targetModule){
       
        var _reloadPrice = targetModule.prototype._reloadPrice;
        var customreloadPriceWrapper = wrapper.wrap(_reloadPrice, function(original){
            var productIdBase = $('#product_addtocart_form > input[name=product]').val();
            var newPrices = this.options.spConfig.optionPrices[this.simpleProduct];
            if(newPrices){
                $('[data-installment-for-product-id="'+productIdBase+'"]').attr("data-installment-for-price", newPrices.finalPrice.amount);
            }
            return original();
        });
        targetModule.prototype._reloadPrice = customreloadPriceWrapper;
        return targetModule;
    };
});