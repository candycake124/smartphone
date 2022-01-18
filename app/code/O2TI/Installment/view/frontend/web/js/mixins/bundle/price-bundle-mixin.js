define([
    'jquery',
    'mage/utils/wrapper',
    'installment',
    'ko'
], function ($, wrapper, installment, ko) {
    'use strict';
    return function(targetModule){
       

        var _onBundleOptionChanged = targetModule.prototype._onBundleOptionChanged;
        
        var _onBundleOptionChanged = wrapper.wrap(_onBundleOptionChanged, function(original){
            return original();
        });
        targetModule.prototype._onBundleOptionChanged = _onBundleOptionChanged;
        return targetModule;
    };
});