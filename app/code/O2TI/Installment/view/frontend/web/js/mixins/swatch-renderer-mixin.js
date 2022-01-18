define([
    'installment',
    'ko',
    'jquery',
    'mage/utils/wrapper',
    
], function (installment, ko, $, wrapper,) {
    'use strict';
    return function(targetModule){
        var updatePrice = targetModule.prototype._UpdatePrice;
        var updatePriceWrapper = wrapper.wrap(updatePrice, function(original){
            if($(".catalog-product-view").length){
                var productIdBase = $('#product_addtocart_form > input[name=product]').val();
            } else {
                var productIdBase = this.element.parents(this.options.selectorProduct).context.className.replace ( /[^\d.]/g, '' );
            }
            var products = this._CalcProducts();
            if(products.slice().shift()){
                var productId = products.slice().shift();
                var price = this.options.jsonConfig.optionPrices[productId];  
                $('[data-installment-for-product-id="'+productIdBase+'"]').first().attr("data-installment-for-price", price.finalPrice.amount);
            }
            var installmentName = 'installment-'+productIdBase;
            var installmentBlockName = '.block-installment-'+productIdBase;
          
            installment.installmentprice = ko.observable(price.finalPrice.amount);
            return original();
        });
        targetModule.prototype._UpdatePrice = updatePriceWrapper;
        return targetModule;
    };
});