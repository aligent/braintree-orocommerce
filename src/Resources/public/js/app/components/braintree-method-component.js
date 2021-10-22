define(function (require) {
    'use strict';

    var BraintreeComponent;
    var $ = require('jquery');
    const _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');
    var dropin = require('braintree-web-drop-in');

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
                dropInContainerSelector: '#dropin-container',
                checkoutContent: '[data-role="checkout-content"]',
            },
            paymentMethodSettings: {}
        },

        initialized: false,

        /**
         * @property {Object}
         */
        instance: null,

        /**
         * @property {Object}
         */
        nonceInput: null,

        initialize: function (options) {
            this.options = $.extend({}, this.options, options);

            debugger;

            const existingElement = this.getContent().find(`[data-name="${this.options.paymentMethod}"]`);

            if (existingElement.length != 0) {
                const parent = this.options._sourceElement.parent();
                existingElement.appendTo(parent);
                this.options._sourceElement.remove();
                existingElement.trigger('braintree:update', this.options);
                this.dispose();
                return;
            }

            this.$el = this.options._sourceElement;
            this.nonceInput = this.$el.find(this.options.selectors.nonceInputSelector);

            mediator.on('checkout:payment:before-transit', this.beforeTransit, this);
            mediator.on('checkout-content:initialized', this.checkoutContentInit, this);
            mediator.on('checkout:after-change', this.afterChange, this);
            this.$el.on('braintree:update', _.bind(this.update, this));

            if (this.initialized) {
                console.log('Already initialized we should update dropin options here instead.')
            } else {
                var dropinOptions = {
                    authorization:  this.options.authToken,
                    selector: this.$el.find(this.options.selectors.dropInContainerSelector)[0],
                    vaultManager: this.options.vaultMode
                };

                dropinOptions = $.extend(dropinOptions, this.options.paymentMethodSettings);
                this.initializeDropIn(dropinOptions);
            }
        },

        /**
         * Initialize the drop UI component
         * @param dropinOptions
         */
        initializeDropIn: function (dropinOptions) {
            const component = this;
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

        update: function (dropinOptions) {
            console.log(dropinOptions);
            this.$el.removeClass('hidden');
            debugger;
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
                    debugger;
                    mediator.trigger('checkout:payment:additional-data:set', JSON.stringify({nonce: this.nonceInput.val()}));
                    event.resume();
                } else {
                    // Else request the nonce, set the additional data and then continue the event
                    var self = this;
                    this.instance.requestPaymentMethod(
                        function (err, payload) {
                            debugger;
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

        afterChange: function(event) {
            // Do not execute for our own braintree field changes
            if ($(event.target).is("[id^=braintree__]")) {
                return;
            }

            // Hide this element
            this.$el.addClass('hidden');

            // Add the payment id so we can find it later
            this.$el.attr('data-name', this.options.paymentMethod);

            // Move iframe outside of block being updated
            this.$el.appendTo(this.getContent());
        },

        dispose: function () {
            debugger;
            if (this.disposed) {
                return;
            }

            mediator.off('checkout:payment:before-transit', this.beforeTransit, this);
            mediator.off('checkout:before-change', this.afterChange, this);

            if (this.instance) {
                this.instance.teardown(function (data) {
                    if (data) {
                        console.error(data);
                    }
                });
            }

            BraintreeComponent.__super__.dispose.call(this);
        },

        /**
         * @returns {jQuery|HTMLElement}
         */
        getContent: function() {
            return $(this.options.selectors.checkoutContent);
        },
    });

    return BraintreeComponent;
});