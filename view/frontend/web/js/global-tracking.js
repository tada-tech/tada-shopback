define([
    'jquery',
    'mage/cookies'
], function ($) {
    'use strict';

    return function (config, element) {
        var cookieTTL = config.cookieTTL || 86400; // 1 day of lifetime as default
        var shopbackUrl = config.shopbackUrl;
        var shopbackTransactionParameter = config.shopbackTransactionParameter || 'shopback_id';

        var COOKIE_NAME = "shopback_affiliate_code";
        var REF_FLAG = '_med';
        var REF_VALUE = {
            affiliate: 'affiliate',
            refer: 'refer'
        };

        var options = {
            lifetime: cookieTTL
        };

        var createCookieValue = function (partner_parameter, partner = 'shopback') {
            return {
                partner: partner,
                partner_parameter: partner_parameter
            };
        }

        var urlParamsObject = new URLSearchParams(window.location.search);

        var partner = urlParamsObject.get('partner');
        var partner_parameter = urlParamsObject.get(shopbackTransactionParameter);

        if (partner_parameter) {
            var cookieValue = createCookieValue(partner_parameter, partner);
            $.mage.cookies.set(COOKIE_NAME, JSON.stringify(cookieValue), options);
        }

        var referrer = document.referrer;

        var prevReferrer = $.mage.cookies.get(REF_FLAG);

        if (prevReferrer === null) {
            $.mage.cookies.set(REF_FLAG, REF_VALUE.refer, options);
        }

        if (referrer.includes(shopbackUrl)) {
            var hasParameter = $.mage.cookies.get(COOKIE_NAME);
            if (prevReferrer != REF_VALUE.affiliate && hasParameter !== null) {
                $.mage.cookies.set(REF_FLAG, REF_VALUE.affiliate, options);
            }
        }
        else if(referrer !== "") {
            var noIncludeBaseUrl = !referrer.includes(window.BASE_URL);
            if (prevReferrer != REF_VALUE.refer && noIncludeBaseUrl) {
                $.mage.cookies.set(REF_FLAG, REF_VALUE.refer, options);
                $.mage.cookies.clear(COOKIE_NAME);
            }
        }
    };
});
