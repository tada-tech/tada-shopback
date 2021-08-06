define([
    'jquery',
    'mage/cookies'
], function ($) {
    'use strict';

    return function (config, element) {
        var cookieTTL = config.cookieTTL || 86400; // 1 day of lifetime as default
        var shopbackUrl = config.shopbackUrl;

        var COOKIE_NAME = "shopback_affiliate_code";
        var REF_FLAG = '_med';
        var REF_VALUE = {
            affiliate: 'affiliate',
            refer: 'refer'
        };

        var options = {
            lifetime: cookieTTL
        };

        var createCookieValue = function (partner, partner_parameter){
            return {
                partner: partner,
                partner_parameter: partner_parameter
            };
        }

        var urlParamsObject = new URLSearchParams(window.location.search);

        var partner = urlParamsObject.get('partner');
        var partner_parameter = urlParamsObject.get('partner_parameter');

        if (partner && partner_parameter) {
            var cookieValue = createCookieValue(partner, partner_parameter);
            $.mage.cookies.set(COOKIE_NAME, JSON.stringify(cookieValue), options);
        }

        var referrer = document.referrer;

        var prevReferrer = $.mage.cookies.get(REF_FLAG);

        if (prevReferrer === null) {
            $.mage.cookies.set(REF_FLAG, REF_VALUE.refer, options);
        }

        if (referrer.includes(shopbackUrl)) {
            if (prevReferrer != REF_VALUE.affiliate) {
                $.mage.cookies.set(REF_FLAG, REF_VALUE.affiliate, options);
            }
        }
        else if(referrer !== "") {
            var noIncludeBaseUrl = !referrer.includes(window.BASE_URL);
            if (prevReferrer != REF_VALUE.refer && noIncludeBaseUrl) {
                $.mage.cookies.set(REF_FLAG, REF_VALUE.refer, options);
            }
        }
    };
});
