define(function(require) {
    'use strict';

    const mediator = require('oroui/js/mediator');
    const BaseView = require('orocheckout/js/app/views/single-page-checkout-form-view');

    const SinglePageCheckoutFormView = BaseView.extend({

        /**
         * @inheritDoc
         */
        constructor: function SinglePageCheckoutFormView(options) {
            SinglePageCheckoutFormView.__super__.constructor.call(this, options);
        },
        /**
         * @param {jQuery.Event} event
         */
        onChange: function (event) {
            SinglePageCheckoutFormView.__super__.onChange.call(this, event);

            // Do not execute logic when hidden element (form) is refreshed
            if (!$(event.target).is(':visible')) {
                return;
            }

            mediator.trigger('checkout:after-change', event);
        },

        /**
         * @param {jQuery.Event} event
         */
        onForceChange: function (event) {
            SinglePageCheckoutFormView.__super__.onForceChange.call(this, event);
            mediator.trigger('checkout:after-change', event);
        }
    });

    return SinglePageCheckoutFormView;
});
