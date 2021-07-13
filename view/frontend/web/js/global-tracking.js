define([
    'jquery',
    'mage/cookies'
], function ($) {
    'use strict';

    return function (config, element) {
        var cookieTTL = config.cookieTTL || 86400; // 1 day of lifetime as default

        var COOKIE_NAME = "shopback_affiliate_code";
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
    };
});
