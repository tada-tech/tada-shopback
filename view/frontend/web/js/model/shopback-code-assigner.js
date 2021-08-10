define([
    'jquery',
    'mage/cookies'
], function ($) {
    'use strict';

    return {
        setData: function (paymentData) {
            //Set Affiliate name & transaction Id
            if (
                paymentData['additional_data'] === undefined
                || paymentData['additional_data'] === null
                || paymentData['additional_data'] === ""
            ) {
                paymentData['additional_data'] = {};
            }

            if ($.mage.cookies.get('shopback_affiliate_code') !== null && $.mage.cookies.get('_med') === 'affiliate') {
                if (typeof paymentData['additional_data'] === 'object' && paymentData['additional_data'] !== null) {

                    var partnerModel = JSON.parse($.mage.cookies.get('shopback_affiliate_code'));

                    paymentData['additional_data']['partner'] = partnerModel.partner;
                    paymentData['additional_data']['partner_parameter'] = partnerModel.partner_parameter;
                }
            }

        },
        clearShopBackCookie: function () {
            $.mage.cookies.clear('shopback_affiliate_code');
            $.mage.cookies.clear('_med');
        }
    }
});
