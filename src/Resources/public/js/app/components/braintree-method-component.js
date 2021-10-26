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
                radio: '[data-choice]',
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
            this.$el = this.options._sourceElement;
            this.nonceInput = this.$el.find(this.options.selectors.nonceInputSelector);

            // Check if the component already exists outside of our block
            const existingElement = this.getContent().find(`[data-name="${this.options.paymentMethod}"]`);
            if (existingElement.length != 0) {
                mediator.trigger('braintree:update', options);
                this.$el.remove();
                this.dispose();
                return;
            }

            mediator.on('checkout:payment:before-transit', this.beforeTransit, this);
            mediator.on('checkout-content:initialized', this.onCheckoutInitialized, this);
            mediator.on('checkout:payment:method:changed', this.onPaymentMethodChanged, this);
            mediator.on('braintree:update', this.update, this);
        },

        onCheckoutInitialized: function () {
            // Move our container outside of the payment block so it is not refreshed
            this.$el.appendTo(this.getContent());
            // Add data name so it can uniquely be identified when a new component tries to initialize
            this.$el.attr('data-name', this.options.paymentMethod);
            debugger;

            //Hide the element if it is not selected on init
            if (this.getSelectedPaymentMethod() !== this.options.paymentMethod) {
                this.$el.addClass('hidden');
            }

            var dropinOptions = {
                authorization: this.options.authToken,
                selector: this.$el.find(this.options.selectors.dropInContainerSelector)[0],
                vaultManager: this.options.vaultMode
            };

            dropinOptions = $.extend(dropinOptions, this.options.paymentMethodSettings);
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

        /**
         * Hide when another payment method is selected
         * @param event
         */
        onPaymentMethodChanged: function (event) {
            if (event.paymentMethod === this.options.paymentMethod) {
                this.$el.removeClass('hidden');
            } else {
                this.$el.addClass('hidden');
            }
        },

        update: function (newOptions) {
            // @TODO: Add Update logic here
            // var dropinOptions = {
            //     authorization: newOptions.authToken,
            //     vaultManager: newOptions.vaultMode
            // };
            //
            // dropinOptions = $.extend(dropinOptions, newOptions.paymentMethodSettings);
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
                                mediator.execute('showFlashMessage', 'error', __('aligent.braintree.payment_nonce_error'));
                                return;
                            }

                            mediator.trigger('checkout:payment:additional-data:set', JSON.stringify({nonce: payload.nonce}));
                            event.resume();
                        }
                    );
                }

                return;
            }

            // Our payment method was not used so dispose of it here
            this.dispose();
        },

        /**
         * Request the nonce and store it
         * @param event
         */
        onPaymentMethodRequestable: function (event) {
            if (event.paymentMethodIsSelected) {
                this.instance.requestPaymentMethod(this.storeNonce.bind(this));
            }
        },

        /**
         * Clear out the nonce input when the payment method is unavailable
         * @param event
         */
        onNoPaymentMethodRequestable: function (event) {
            this.nonceInput.val('');
        },

        /**
         * Store the nonce in a hidden input or flash an error
         * @param err
         * @param payload
         */
        storeNonce: function (err, payload) {
            if (err) {
                console.error(err);
                this.instance.clearSelectedPaymentMethod();
                mediator.execute('showFlashMessage', 'error', __('aligent.braintree.payment_nonce_error'));
                return;
            }

            this.nonceInput.val(payload.nonce);
        },

        dispose: function () {
            debugger;
            if (this.disposed) {
                return;
            }

            mediator.off('checkout:payment:before-transit', this.beforeTransit, this);

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
         * Returns the checkout content element
         * @returns {jQuery|HTMLElement}
         */
        getContent: function () {
            return $(this.options.selectors.checkoutContent);
        },

        /**
         * Get the currently selected payment method
         * @returns {*}
         */
        getSelectedPaymentMethod: function() {
            const $checkedRadio = this.getContent().find(this.options.selectors.radio).filter(':checked');
            return $checkedRadio.val();
        }
    });

    return BraintreeComponent;
});