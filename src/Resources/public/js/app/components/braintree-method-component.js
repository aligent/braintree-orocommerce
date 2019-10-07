define(function (require) {
    'use strict';

    var BraintreeComponent;
    var $ = require('jquery');
    var __ = require('orotranslation/js/translator');
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');
    var dropin = require('aligentbraintree/js/braintree/braintree-drop-in-ui');

    BraintreeComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            paymentMethod: null,
            authToken: null,
            vaultMode: false,
            selectors: {
                nonceInputSelector: '#nonce-input'
            },
            paymentMethodSettings: {}
        },

        /**
         * @property {Object}
         */
        instance: null,

        /**
         * @property {Object}
         */
        nonceInput: null,

        initialize: function (options) {
            this.options = _.extend({}, this.options, options);
            this.$el = this.options._sourceElement;
            this.nonceInput = $(this.options.selectors.nonceInputSelector);
            var component = this;

            mediator.on('checkout:payment:before-transit', this.beforeTransit, this);

            var dropinOptions = {
                authorization:  this.options.authToken,
                selector: this.$el[0],
                vaultManager: this.options.vaultMode
            };

            dropinOptions = _.extend(dropinOptions, this.options.paymentMethodSettings);

            dropin.create(
                dropinOptions,
                function (createErr, instance) {
                    if (createErr) {
                        console.error(createErr);
                        return;
                    }

                    component.instance = instance;

                    // Bind events to automatically request a payment nonce when a payment method becomes available
                    // and clear it out once it becomes unavailable
                    component.instance.on('paymentMethodRequestable', component.onPaymentMethodRequestable.bind(component));
                    component.instance.on('noPaymentMethodRequestable', component.onNoPaymentMethodRequestable.bind(component));

                    // if we have a saved payment method we can request a nonce for
                    // fetch and save it now to the hidden input field
                    if (component.instance.isPaymentMethodRequestable()) {
                        component.instance.requestPaymentMethod(component.storeNonce.bind(component));
                    }
                }
            );
        },

        /**
         * Before the checkout submits, add the nonce to the additional data set
         * @param event
         */
        beforeTransit: function (event) {
            if (event.data.paymentMethod === this.options.paymentMethod) {
                // Stop the checkout
                event.stopped = true;

                // if we already have the nonce set as additional data and continue
                if (this.nonceInput.val()) {
                    mediator.trigger('checkout:payment:additional-data:set', JSON.stringify({nonce: this.nonceInput.val()}));
                    event.resume();
                } else {
                    // Else request the nonce, set the additional data and then continue the event
                    var self = this;
                    this.instance.requestPaymentMethod(
                        function (err, payload) {
                            if (err) {
                                console.error(err);
                                self.instance.clearSelectedPaymentMethod();
                                mediator.execute('showFlashMessage', 'error',  __('aligent.braintree.payment_nonce_error'));
                                return;
                            }

                            mediator.trigger('checkout:payment:additional-data:set', JSON.stringify({nonce: payload.nonce}));
                            event.resume();
                        }
                    );

                }
            }
        },

        /**
         * Request the nonce and store it
         * @param event
         */
        onPaymentMethodRequestable: function(event) {
            if (event.paymentMethodIsSelected) {
                this.instance.requestPaymentMethod(this.storeNonce.bind(this));
            }
        },

        /**
         * Clear out the nonce input when the payment method is unavailable
         * @param event
         */
        onNoPaymentMethodRequestable: function(event) {
            this.nonceInput.val('');
        },

        /**
         * Store the nonce in a hidden input or flash an error
         * @param err
         * @param payload
         */
        storeNonce: function(err, payload) {
            if (err) {
                console.error(err);
                this.instance.clearSelectedPaymentMethod();
                mediator.execute('showFlashMessage', 'error', __('aligent.braintree.payment_nonce_error'));
                return;
            }

            this.nonceInput.val(payload.nonce);
        },

        dispose: function () {
            mediator.off('checkout:payment:before-form-serialization');

            if (this.instance) {
                this.instance.teardown(function (data) {
                    if (data) {
                        console.error(data);
                    }
                });
            }
        }
    });

    return BraintreeComponent;
});