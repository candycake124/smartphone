var config = {
    map: {
        '*': {
            'installment': 'O2TI_Installment/js/view/installment'
        }
    },
    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'O2TI_Installment/js/mixins/configurable-mixin': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'O2TI_Installment/js/mixins/swatch-renderer-mixin': true
            },
            'Magento_Catalog/js/price-box': {
                'O2TI_Installment/js/mixins/price-box-mixin': true
            },
            'Magento_Bundle/js/price-bundle': {
                'O2TI_Installment/js/mixins/bundle/price-bundle-mixin': true
            }
        }
    }

};