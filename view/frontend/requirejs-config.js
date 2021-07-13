var config = {
    map: {
        '*': {
            'Tada_Shopback/global-tracking': 'Tada_Shopback/js/global-tracking'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'Tada_Shopback/js/model/place-order-mixin': true
            },
        }
    }
};
