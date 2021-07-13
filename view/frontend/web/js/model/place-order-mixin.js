/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'Tada_Shopback/js/model/shopback-code-assigner'
], function ($, wrapper, shopbackCodeAssigner) {
    'use strict';

    return function (placeOrderAction) {

        /** Override default place order action and add agreement_ids to request */
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            shopbackCodeAssigner(paymentData);
            return originalAction(paymentData, messageContainer);
        });
    };
});
