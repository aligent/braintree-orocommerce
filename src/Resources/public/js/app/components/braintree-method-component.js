define(function (require) {
    'use strict';

    var BraintreeComponent;
    var $ = require('jquery');
    var __ = require('orotranslation/js/translator');
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');

    var braintree = {};
    braintree.dropin = require('aligentbraintree/js/braintree/braintree-drop-in-ui');
    braintree.dataCollector = require('aligentbraintree/js/braintree/braintree-data-collector');

    // Needed as the dropin script uses window internally
    // Version 4.1 and up of this library will use the proper NPM dependencies so this will be removed.
    window.braintree = braintree;

    BraintreeComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            paymentMethod: null,
            authToken: null,
            vaultMode: false,
            selectors: {
                nonceInputSelector: '#nonce-input',
                deviceDataInputSelector: '#device-data-input'
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

        /**
         * @property {Object}
         */
        deviceDataInput: null,

        initialize: function (options) {
            this.options = _.extend({}, this.options, options);
            this.$el = this.options._sourceElement;
            this.nonceInput = $(this.options.selectors.nonceInputSelector);
            this.deviceDataInput = $(this.options.selectors.deviceDataInputSelector);
            var component = this;

            mediator.on('checkout:payment:before-transit', this.beforeTransit, this);

            //Options keys from the backend can be seen in \Aligent\BraintreeBundle\Method\View\BraintreeView.php
            //options are passed through from twig widget: _payment_methods_aligent_braintree_widget
            var dropinOptions = {
                authorization:  this.options.authToken,
                selector: this.$el[0],
                vaultManager: this.options.vaultMode,
                dataCollector: this.options.fraudProtectionAdvanced
            };

            dropinOptions = _.extend(dropinOptions, this.options.paymentMethodSettings);

            braintree.dropin.create(
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
                        component.instance.requestPaymentMethod(component.storeAdditionalData.bind(component));
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
                    var additionalData = {nonce: this.nonceInput.val()};

                    if (this.deviceDataInput.val()) {
                        additionalData.deviceData = this.deviceDataInput.val();
                    }

                    mediator.trigger('checkout:payment:additional-data:set', JSON.stringify(additionalData));
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

                            var additionalData = {nonce: payload.nonce};

                            if (payload.deviceData) {
                                additionalData.deviceData = payload.deviceData;
                            }

                            mediator.trigger('checkout:payment:additional-data:set', JSON.stringify(additionalData));
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
                this.instance.requestPaymentMethod(this.storeAdditionalData.bind(this));
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
        storeAdditionalData: function(err, payload) {
            if (err) {
                console.error(err);
                this.instance.clearSelectedPaymentMethod();
                mediator.execute('showFlashMessage', 'error', __('aligent.braintree.payment_nonce_error'));
                return;
            }

            this.nonceInput.val(payload.nonce);

            if (payload.deviceData) {
                this.deviceDataInput.val(payload.deviceData);
            }
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
