define(function (require) {
    'use strict';

    var ReviewComponent;
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');

    ReviewComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            paymentMethod: null
        },

        initialize: function (options) {
            this.options = $.extend({}, this.options, options);
            this.$el = this.options._sourceElement;

            mediator.on('checkout:place-order:response', this.placeOrderResponse, this);
        },

        placeOrderResponse: function(eventData) {
            if (eventData.responseData.paymentMethod === this.options.paymentMethod) {
                eventData.stopped = true;
                if (eventData.responseData.successful) {
                    mediator.execute('redirectTo', {url: eventData.responseData.successUrl}, {redirect: true});
                } else {
                    mediator.execute('redirectTo', {url: eventData.responseData.errorUrl}, {redirect: true});
                }
            }
        },

        dispose: function () {
            mediator.off('checkout:place-order:response');
        }
    });

    return ReviewComponent;
});