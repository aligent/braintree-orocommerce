define(function(require) {
    'use strict';

    var CreditCardComponent;
    var _ = require('underscore');
    var $ = require('jquery');
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');
    var client = require('braintree/js/braintree/braintree-client');
    var hostedFields = require('braintree/js/braintree/braintree-hosted-fields');

    CreditCardComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            paymentMethod: null,
            allowedCreditCards: [],
            selectors: {
                payment_method_nonce: 'input[name="payment_method_nonce"]',
                form: '[data-credit-card-form]',
                saveForLater: '[data-save-for-later]',
                creditCardsSaved: '[data-credit-cards-saved]',
                credit_card_value: 'input[name="credit_card_value"]',
                braintree_client_token: 'input[name="braintree_client_token"]',
            }
        },

        /**
         * @property {Boolean}
         */
        paymentValidationRequiredComponentState: true,

        /**
         * @property {jQuery}
         */
        $el: null,

        /**
         * @property {jQuery}
         */
        $form: null,

        /**
         * @property {Boolean}
         */
        disposable: true,

        hostedFieldsInstance: null,
        
        tokenizationPayload: null,
        
        tokenizationError: null,
        
        isTokenized: false,
        
        isFormValid: false,
        
        valueCreditCard: "newCreditCard",
        
        isCreditCardSaved: false,
        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options);

            this.$el = this.options._sourceElement;

            this.$form = this.$el.find(this.options.selectors.form);

            this.$el
                .on('change', this.options.selectors.saveForLater, $.proxy(this.onSaveForLaterChange, this))
            	.on('change', this.options.selectors.creditCardsSaved, $.proxy(this.onCreditCardsSavedChange, this));

            mediator.on('checkout:place-order:response', this.handleSubmit, this);
            mediator.on('checkout:payment:method:changed', this.onPaymentMethodChanged, this);
            mediator.on('checkout:payment:before-transit', this.beforeTransit, this);
            mediator.on('checkout:payment:before-hide-filled-form', this.beforeHideFilledForm, this);
            mediator.on('checkout:payment:before-restore-filled-form', this.beforeRestoreFilledForm, this);
            mediator.on('checkout:payment:remove-filled-form', this.removeFilledForm, this);
            mediator.on('checkout-content:initialized', this.refreshPaymentMethod, this);
            
            var component = this;
            
            this.setCreditCardsSavedValue (this.$el);
            
        	client.create({
        		authorization: component.$el.find(component.options.selectors.braintree_client_token).val()
        		}, function (err, clientInstance) {
        			if (err) {
        				console.log(err);
        				return;
        			}

        		hostedFields.create({
        			client: clientInstance,
        			fields: {
        		    	number: {
        		    		selector: '#card-number',
        		    		placeholder: '1111 1111 1111 1111'
        		    	},
        		    	cvv: {
        		    		selector: '#cvv',
        		    		placeholder: '123'
        		    	},
        		    	expirationDate: {
        		    		selector: '#expiration-date',
        		    		placeholder: '10 / ' + ((new Date).getFullYear() + 2)
        		    	}
        		    }
        		  	}, function (err, hostedFieldsInst) {
        		  		component.hostedFieldsInstance = hostedFieldsInst;
        		  		if (err) {
        		  			console.log(err);
        		  			return;
        		  		}
        		  		
        		  		component.hostedFieldsInstance.on('validityChange', function (event) {
        		  			  
	        		  	    var field = event.fields[event.emittedBy];
	
	        		        if (field.isValid) {
	        		        	$(field.container).removeClass('error');
	        		        	if (event.emittedBy === 'expirationDate') {
	        		        		if (!event.fields.expirationDate.isValid ) {
	        		        			return;
	        		        		}
	        		        	}
	        		        } else if (field.isPotentiallyValid) {
	        		        	$(field.container).removeClass('error');
	        		        } else {
	        		        	$(field.container).addClass('error');
	        		        }
	        		        
	        		        var formValid = Object.keys(event.fields).every(function (key) {
	        		  	        return event.fields[key].isValid;
	        		  	    });
	        		        component.isFormValid = formValid;
        		  	    });
        		  	});
            });
        },

        setCreditCardsSavedValue: function(form) {
            var $el = form;
            var $value = $el.prop('value'); // es el id de la transaccion o newCreditCard que siginifica que quiere ingresar un nuevo valor
            var saveFLater = this.$form.find(this.options.selectors.saveForLater);
            if ($value == null || $value == "newCreditCard"){
            	$('#braintree-custom-cc-form').show();
            	$('#save_for_later_field_row').show();
            } else {
            	$('#braintree-custom-cc-form').hide();
            	$('#save_for_later_field_row').hide();
            }
            
           /* var component = this;
	    	var creditsCardsSaved1 = component.$el.find(component.options.selectors.creditCardsSaved);
	    	creditsCardsSaved1.val($value);
	    	$("[name='oro_workflow_transition']").append(creditsCardsSaved1[0]);  
            $("[name='oro_workflow_transition']").submit();
            */
            //alert('Hey');
    		var credit_card_value = this.$el.find(this.options.selectors.credit_card_value);
    		var payment_method_nonce = this.$el.find(this.options.selectors.payment_method_nonce);
    		credit_card_value.val($value);
    		//$("[name='oro_workflow_transition']").append(credit_card_value[0]); 
    		//document.querySelector('input[name="credit_card_value"]').value = $value;
            this.valueCreditCard = $value;
            if (this.valueCreditCard != null && this.valueCreditCard != "newCreditCard"){
            	this.isTokenized = false; // Esto porque selecciono una de las que ya estaban guardadas
            	this.isCreditCardSaved = true;
            }
            else{
            	this.isTokenized = false; // Esto porque selecciono una de las que ya estaban guardadas
            	this.isCreditCardSaved = false;            	
            }
            //$("[name='oro_workflow_transition']").submit();

            //mediator.trigger('checkout:payment:save-for-later:change', $el.prop('checked'));
        },       
        
        
        refreshPaymentMethod: function() {
            mediator.trigger('checkout:payment:method:refresh');
        },

        /**
         * @param {Object} eventData
         */
        handleSubmit: function(eventData) {
        	if (eventData.responseData.paymentMethod === this.options.paymentMethod) {
                eventData.stopped = true;
                var resolvedEventData = _.extend(
                    {
                        'SECURETOKEN': false,
                        'SECURETOKENID': false,
                        'returnUrl': '',
                        'errorUrl': '',
                        'formAction': '',
                        'paymentMethodSupportsValidation': true
                    },
                    eventData.responseData
                );

         
                    mediator.execute('redirectTo', {url: resolvedEventData.returnUrl}, {redirect: true});
                    return;
            }
        },

        dispose: function() {
            if (this.disposed || !this.disposable) {
                return;
            }

            this.$el.off();

            mediator.off('checkout-content:initialized', this.refreshPaymentMethod, this);
            mediator.off('checkout:place-order:response', this.handleSubmit, this);
            mediator.off('checkout:payment:method:changed', this.onPaymentMethodChanged, this);
            mediator.off('checkout:payment:before-transit', this.beforeTransit, this);
            mediator.off('checkout:payment:before-hide-filled-form', this.beforeHideFilledForm, this);
            mediator.off('checkout:payment:before-restore-filled-form', this.beforeRestoreFilledForm, this);
            mediator.off('checkout:payment:remove-filled-form', this.removeFilledForm, this);

            CreditCardComponent.__super__.dispose.call(this);
        },

        /**
         * @param {String} elementSelector
         */
        validate: function() {
            return this.isFormValid;
        },
        
        /**
         * @param {Boolean} state
         */
        setGlobalPaymentValidate: function(state) {
            this.paymentValidationRequiredComponentState = state;
            mediator.trigger('checkout:payment:validate:change', state);
        },
        

        /**
         * @returns {jQuery}
         */
        getSaveForLaterElement: function() {
            if (!this.hasOwnProperty('$saveForLaterElement')) {
                this.$saveForLaterElement = this.$form.find(this.options.selectors.saveForLater);
            }

            return this.$saveForLaterElement;
        },

        /**
         * @returns {Boolean}
         */
        getSaveForLaterState: function() {
            return this.getSaveForLaterElement().prop('checked');
        },

        setSaveForLaterBasedOnForm: function() {
            mediator.trigger('checkout:payment:save-for-later:change', this.getSaveForLaterState());
        },

        /**
         * @param {Object} eventData
         */
        onPaymentMethodChanged: function(eventData) {
            if (eventData.paymentMethod === this.options.paymentMethod) {
                this.onCurrentPaymentMethodSelected();
            }
        },

        onCurrentPaymentMethodSelected: function() {
            this.setGlobalPaymentValidate(this.paymentValidationRequiredComponentState);
            this.setSaveForLaterBasedOnForm();
        },

        /**
         * @param {Object} e
         */
        onSaveForLaterChange: function(e) {
            var $el = $(e.target);
            mediator.trigger('checkout:payment:save-for-later:change', $el.prop('checked'));
        },
        
        /**
         * @param {Object} e
         */
        onCreditCardsSavedChange: function(e) {
            var $el = $(e.target);
            var $value = $el.prop('value'); // es el id de la transaccion o newCreditCard que siginifica que quiere ingresar un nuevo valor
            var saveFLater = this.$form.find(this.options.selectors.saveForLater);
            if ($value == null || $value == "newCreditCard"){
            	$('#braintree-custom-cc-form').show();
            	$('#save_for_later_field_row').show();
            } else {
            	$('#braintree-custom-cc-form').hide();
            	$('#save_for_later_field_row').hide();
            }
            
           /* var component = this;
	    	var creditsCardsSaved1 = component.$el.find(component.options.selectors.creditCardsSaved);
	    	creditsCardsSaved1.val($value);
	    	$("[name='oro_workflow_transition']").append(creditsCardsSaved1[0]);  
            $("[name='oro_workflow_transition']").submit();
            */
            //alert('Hey');
    		var credit_card_value = this.$el.find(this.options.selectors.credit_card_value);
    		var payment_method_nonce = this.$el.find(this.options.selectors.payment_method_nonce);
    		credit_card_value.val($value);
    		//$("[name='oro_workflow_transition']").append(credit_card_value[0]); 
    		//document.querySelector('input[name="credit_card_value"]').value = $value;
            this.valueCreditCard = $value;
            if (this.valueCreditCard != null && this.valueCreditCard != "newCreditCard"){
            	this.isTokenized = false; // Esto porque selecciono una de las que ya estaban guardadas
            	this.isCreditCardSaved = true;
            }
            else{
            	this.isTokenized = false; // Esto porque selecciono una de las que ya estaban guardadas
            	this.isCreditCardSaved = false;            	
            }
            //$("[name='oro_workflow_transition']").submit();

            //mediator.trigger('checkout:payment:save-for-later:change', $el.prop('checked'));
        },       
        
        /**
         * @param {Object} eventData
         */
        beforeTransit: function(eventData) {
        	//
        	if (this.isTokenized) {
           		this.isTokenized = false;        		
        		var component = this;
        		
           		var payment_method_nonce = this.$el.find(this.options.selectors.payment_method_nonce);
           		if (!this.isCreditCardSaved){
           			payment_method_nonce.val(this.tokenizationPayload.nonce); 
        		}
           		else{
           			payment_method_nonce.val("noValue"); 
           		}

           		eventData.stopped = false;
        		
        	} else {
        		eventData.stopped = true;
        		
        		var component = this;
            	this.tokenizationPayload = null;
            	this.tokenizationError = null;
            	
            	var deferred = $.Deferred();
            	var tokenizationCallback = function (error, payload) {
            	    if (error && !component.isCreditCardSaved) {
            			deferred.reject({error});
            		} else {
    	        		deferred.resolve({payload});
            		}
            	};

            	var getPaymentNonce = function () {
            		component.hostedFieldsInstance.tokenize(tokenizationCallback);
            	    return deferred.promise();
            	};

            	getPaymentNonce().then(
            	    function (payload) {
            	    	component.tokenizationPayload = payload.payload;
            	    	
            	    	component.isTokenized = true;
            	    	if (!component.isCreditCardSaved){
                	    	var payment_method_nonce = component.$el.find(component.options.selectors.payment_method_nonce);
                	    	payment_method_nonce.val(component.tokenizationPayload.nonce);
                	    	$("[name='oro_workflow_transition']").append(payment_method_nonce[0]);               	    		
            	    	}
            	    	else{
                	    	var payment_method_nonce = component.$el.find(component.options.selectors.payment_method_nonce);
                	    	payment_method_nonce.val("noValue");
                	    	$("[name='oro_workflow_transition']").append(payment_method_nonce[0]); 
            	    		
            	    	}
            	    	
                        document.querySelector('input[name="credit_card_value"]').value = component.valueCreditCard;
                		var credit_card_value = component.$el.find(component.options.selectors.credit_card_value);
                		credit_card_value.val(component.valueCreditCard);
                		$("[name='oro_workflow_transition']").append(credit_card_value[0]);
            	    	$("[name='oro_workflow_transition']").submit();
            	    }, 
            	    function (error) {
            	    	component.tokenizationError = error.error;
            	    }
            	);
        	}
        	
            if (eventData.data.paymentMethod === this.options.paymentMethod) {
                //eventData.stopped = !this.validate();
            }
            
        },

        beforeHideFilledForm: function() {
            this.disposable = false;
        },

        beforeRestoreFilledForm: function() {
            if (this.disposable) {
                this.dispose();
            }
        },

        removeFilledForm: function() {
            // Remove hidden form js component
            if (!this.disposable) {
                this.disposable = true;
                this.dispose();
            }
        }
    });

    return CreditCardComponent;
});
