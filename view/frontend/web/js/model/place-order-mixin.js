define([
    'jquery',
    'mage/utils/wrapper',
    'Tada_Shopback/js/model/shopback-code-assigner'
], function ($, wrapper, shopbackCodeAssigner) {
    'use strict';

    return function (placeOrderAction) {

        /** Override default place order action and add shopBack params to request */
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            shopbackCodeAssigner.setData(paymentData);

            var result = originalAction(paymentData, messageContainer);

            shopbackCodeAssigner.clearShopBackCookie();

            return result;
        });
    };
});
