define(function(require) {
    'use strict';

    var console = window.console;
    var CurrencyGridSView;
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var BaseView = require('oroui/js/app/views/base/view');
    var CurrencyModel = require('oromulticurrency/js/app/models/currency-model');
    var BaseCollection = require('oroui/js/app/models/base/collection');
    var Select2View = require('oroform/js/app/views/select2-view');
    var currencyGridItemTpl = require('tpl!oromulticurrency/templates/currency-grid-item.html');
    var DeleteConfirmation = require('oroui/js/delete-confirmation');
    var StandardConfirmation = require('oroui/js/standart-confirmation');
    var layout = require('oroui/js/layout');
    require('oroui/js/items-manager/table');
    CurrencyGridSView = BaseView.extend({

        validationMessages: {
            'NotBlank': __('oro.multi.currency.system_configuration.currency_grid.rate.blank'),
            'Number': __('oro.multi.currency.system_configuration.currency_grid.rate.number'),
            'BiggerThenZero': __('oro.multi.currency.system_configuration.currency_grid.rate.greaterThanZero')
        },

        /**
         * @property {Object} List of all available currencies
         */
        currencies: null,

        /**
         * @property {String} Code of current default currency
         */
        defaultCurrency: null,

        defaultCurrencyOptionTemplate: _.template(
            '<option value="<%- code %>"<%if (isDefault) { %> selected<% } %>><%- name %></option>'
        ),

        /**
         * @property {Array} Array of codes of currently selected currencies
         */
        allowedCurrencies: null,

        domCache: null,

        useParentElements: null,

        offSubmitHandler: null,

        events: {
            'click [data-name="currency-add"]': 'onAddCurrency',
            'change [data-name="default-currency"]': 'onDefaultCurrencyChange',
            'change .currency-rate [data-name^="rate"]': 'onRateChange'
        },

        /**
         * @constructor
         */
        initialize: function(options) {
            CurrencyGridSView.__super__.initialize.apply(this, arguments);
            this.currencies = _.result(options, 'currencies');
            this._initCollection();
            if (this._isEnabledResettable()) {
                this._checkUseParentElements();
            }
            /**
             * Submit listener must attached at the last
             * to have possibility prevent form submit
             */
            this.initFormSubmitListener();
        },

        _setElement: function(el) {
            CurrencyGridSView.__super__._setElement.call(this, el);
            this.createDomCache();
        },

        _isEnabledResettable: function() {
            var disabledElements = [];
            var useParentElementsLength = this.useParentElements.length;
            _.each(this.useParentElements, function(element) {
                if (0 === element.length || element.length && '0' === element.val()) {
                    disabledElements.push(element);
                }
            });

            if (0 === disabledElements.length) {
                return true;
            }

            if (useParentElementsLength === disabledElements.length) {
                return false;
            }

            if (console && console.warn) {
                console.warn(
                    'Unrecognized state of "resettable" functionality, next dom elements missed:',
                    disabledElements
                );
            }

            return true;
        },

        /**
         * Check that use_parent_scope in all binding elements have the same value
         * and synchronize them if needed
         *
         * @private
         */
        _checkUseParentElements: function() {
            var checked = [];
            var unchecked = [];
            var useParentElementsLength = this.useParentElements.length;
            _.each(this.useParentElements, function(element) {
                if (element.prop('checked')) {
                    checked.push(element);
                } else {
                    unchecked.push(element);
                }
            });

            if (useParentElementsLength === checked.length || useParentElementsLength === unchecked.length) {
                return true;
            }

            /**
             * Make checkbox synchronization
             */
            _.each(checked, function(element) {
                element.prop('checked', false).trigger('change');
            });
        },

        initFormSubmitListener: function() {
            var form = this.$el.closest('form');
            var submitHandler = _.bind(this.submitHandler, this);
            form.bindFirst('submit', submitHandler);

            this.offSubmitHandler = function() {
                form.off('submit', submitHandler);
            };
        },

        submitHandler: function(e) {
            if (!this.isAllRatesValid()) {
                this.collection.trigger('reset');
                e.stopImmediatePropagation();
                e.preventDefault();
            }
        },

        delegateEvents: function(events) {
            CurrencyGridSView.__super__.delegateEvents.call(this, events);
            this.domCache.$allowedCurrenciesUseParentScope
                .on('change' + this.eventNamespace(), _.bind(this.onUseParentScopeChange, this));
            return this;
        },

        undelegateEvents: function() {
            CurrencyGridSView.__super__.undelegateEvents.call(this);
            if (this.$el) {
                this.domCache.$allowedCurrenciesUseParentScope.off(this.eventNamespace());
                if (this.offSubmitHandler) {
                    this.offSubmitHandler();
                }
            }
            return this;
        },

        createDomCache: function() {
            var $fieldset = this.$el.closest('fieldset');
            this.domCache = {
                $defaultCurrencyInput: $fieldset.find('[name$="default_currency][value]"]'),
                $defaultCurrencyUseParentScope: $fieldset.find('[name$="default_currency][use_parent_scope_value]"]'),
                $allowedCurrenciesInput: this.$('[name$="allowed_currencies][value]"]'),
                $allowedCurrenciesUseParentScope:
                    $fieldset.find('[name$="allowed_currencies][use_parent_scope_value]"]'),
                $availableCurrenciesSelect: this.$('[data-name="currency-select"]'),
                $currencyRates: $fieldset.find('[name$="currency_rates][value]"]'),
                $currencyRatesUseParentScope: $fieldset.find('[name$="currency_rates][use_parent_scope_value]"]'),
                $currencyTable: this.$('[data-name="currency-table-body"]')
            };

            this.useParentElements = [
                this.domCache.$defaultCurrencyUseParentScope,
                this.domCache.$allowedCurrenciesUseParentScope,
                this.domCache.$currencyRatesUseParentScope
            ];
        },

        _initCollection: function() {
            var defaultCurrency = this.getDefaultCurrency();
            var allowedCurrencies = this.getAllowedCurrencies();
            var currencyRates = this.getCurrencyRates();
            var CurrencyCollection = BaseCollection.extend({
                model: CurrencyModel
            });
            this.collection = new CurrencyCollection(_.map(allowedCurrencies, _.bind(function(key) {
                return _.extend(
                    _.extend(currencyRates[key], {isDefault: defaultCurrency === key}),
                    this.currencies[key]
                );
            }, this)));
            this.listenTo(this.collection, 'sort', this.hidePopoverDialog);
            this.listenTo(this.collection, 'add remove change reset sort', this.onCollectionChange);
        },

        isAllRatesValid: function() {
            return _.isUndefined(this.collection.find(function(model) {
                return model.getRatesValidationStatus() === false;
            }));
        },

        updateCurrencyRates: function() {
            var ratesData = {};
            this.collection.each(function(model) {
                _.extend(ratesData, model.getRatesData());
            });
            this.domCache.$currencyRates.val(JSON.stringify(ratesData));
        },

        getCurrencyRates: function() {
            return JSON.parse(this.domCache.$currencyRates.val());
        },

        onRateChange: function(e) {
            var cid = this.$(e.currentTarget).closest('tr').data('cid');
            var propertyName = this.$(e.currentTarget).data('name');
            var propertyValue = this.$(e.currentTarget).val();
            this.collection.get({cid: cid}).set(propertyName, propertyValue);
        },

        onDefaultCurrencyChange: function(e) {
            var $selectedRadioinput = this.$(e.currentTarget);
            var confirmation = new StandardConfirmation({
                content: __('oro.multi.currency.system_configuration.currency_grid.change_default_confirmation')
            });

            confirmation.on('ok', _.bind(function() {
                this.setDefaultCurrency($selectedRadioinput.closest('tr').data('cid'));
                this.initPopover();
            }, this));
            confirmation.on('cancel', function() {
                $selectedRadioinput.prop('checked', false);
            });

            confirmation.open();
        },

        hidePopoverDialog: function() {
            this.domCache.$currencyTable
                .find('[data-name="default-currency"]:checked')
                .closest('tr')
                .find('[data-toggle="popover"]')
                .popover('hide');
        },

        onCollectionChange: function() {
            this.setAllowedCurrencies(this.collection.pluck('code'));
            this.domCache.$availableCurrenciesSelect.select2({
                'data': this.getAvailableCurrencies(),
                'placeholder': __('oro.multi.currency.system_configuration.currency_grid.placeholder')
            });
            this.updateDefaultCurrencyInput();
            this.updateCurrencyRates();
        },

        onAddCurrency: function(e) {
            e.preventDefault();
            var value = this.domCache.$availableCurrenciesSelect.inputWidget('val');
            var defaultCurrency = this.getDefaultCurrency();
            if (value) {
                this.domCache.$availableCurrenciesSelect.inputWidget('val', '');
                var model = new this.collection.model(_.extend(
                    {
                        isDefault: defaultCurrency === value,
                        onlyAdded: true
                    },
                    this.currencies[value]
                ));
                /**
                 * Switch off parameter 'onlyAdded'
                 */
                this.collection.once('add', function(model) {
                    model.set({onlyAdded: false}, {silent: true});
                });
                this.collection.unshift(model);
            }
        },

        onUseParentScopeChange: function() {
            var state = this.domCache.$allowedCurrenciesUseParentScope.prop('checked');
            this.domCache.$defaultCurrencyUseParentScope.prop('checked', state).trigger('change');
            this.domCache.$currencyRatesUseParentScope.prop('checked', state).trigger('change');
            this.$el.toggleClass('disabled', state);
            this.domCache.$availableCurrenciesSelect.select2({
                'data': this.getAvailableCurrencies(),
                'placeholder': __('oro.multi.currency.system_configuration.currency_grid.placeholder')
            });
            // Restore disabled state after use parent scope was checked off
            if (state === false) {
                this.$el
                    .find('[data-name="default-currency"]:checked')
                    .closest('tr')
                    .find('input[data-name^="rate"]')
                    .attr('disabled', 'disabled');
            }
        },

        getAvailableCurrencies: function() {
            var results = [];
            var allowedCurrencies = this.getAllowedCurrencies();
            _.each(this.currencies, _.bind(function(value, key) {
                if (!_.contains(allowedCurrencies, key)) {
                    results.push({
                        'id': key,
                        'text': value.name + ' (' + value.code + ')'
                    });
                }
            }, this));
            return results;
        },

        getDefaultCurrency: function() {
            return this.domCache.$defaultCurrencyInput.val();
        },

        setDefaultCurrency: function(cid) {
            var newDefaultCurrencyGridModel = this.collection.get({cid: cid});
            var ratesRatio = newDefaultCurrencyGridModel.getRatesRatio();
            this.collection.each(function(model) {
                var data = {
                    isDefault: model.cid === cid
                };

                var recalculatedRates = model.getRecalculatedRates(ratesRatio);
                if (recalculatedRates) {
                    data = _.extend(data, recalculatedRates);
                }

                model.set(data);
            });
            this.domCache.$defaultCurrencyInput.val(newDefaultCurrencyGridModel.get('code')).trigger('change');
        },

        updateDefaultCurrencyInput: function() {
            var options = this.collection.map(_.bind(function(model) {
                return this.defaultCurrencyOptionTemplate(model.toJSON());
            }, this));
            this.domCache.$defaultCurrencyInput.html(options.join('')).trigger('change');
        },

        getAllowedCurrencies: function() {
            return JSON.parse(this.domCache.$allowedCurrenciesInput.val());
        },

        setAllowedCurrencies: function(currencies) {
            this.domCache.$allowedCurrenciesInput.val(JSON.stringify(currencies));
        },

        removeCurrency: function(model) {
            if (!model.get('isDefault')) {
                var deleteConfirmation = new DeleteConfirmation({
                    content: __('oro.multi.currency.system_configuration.currency_grid.delete_confirmation')
                });

                deleteConfirmation.on('ok', _.bind(function() {
                    this.collection.remove(model);
                }, this));

                deleteConfirmation.open();
            }
        },

        initPopover: function() {
            var popovers = this.domCache.$currencyTable.find('[data-toggle="popover"]');
            layout.initPopoverForElements(popovers, {
                container: '.currency-grid tbody'
            }, true);
        },

        render: function() {
            var getErrorMessage = _.bind(function(errorCode, fieldName) {
                if (this.validationMessages[errorCode]) {
                    var tmpl = _.template(this.validationMessages[errorCode]);
                    return tmpl({fieldName: fieldName});
                }
                throw new Error('Not supported message for validation error code - ' + errorCode);
            }, this);
            /**
             * Select2 subview requires right value in property "disabled" of $availableCurrenciesSelect
             * that's why we set property "disabled" before Select2 will be initialized
             */
            if (this.domCache.$allowedCurrenciesInput.is(':disabled')) {
                this.$(':input').prop('disabled', 'disabled');
                this.$el.addClass('disabled');
            }
            this.subview('available-currencies-select-view', new Select2View({
                el: this.domCache.$availableCurrenciesSelect,
                select2Config: {
                    data: this.getAvailableCurrencies(),
                    'placeholder': __('oro.multi.currency.system_configuration.currency_grid.placeholder')
                }
            }));
            this.domCache.$currencyTable.itemsManagerTable({
                collection: this.collection,
                itemTemplate: currencyGridItemTpl,
                deleteHandler: _.bind(this.removeCurrency, this),
                itemRender: function(tmpl, data) {
                    var currencyModel = new CurrencyModel(data);
                    var validationResultByRates;
                    var formattedRates = {};
                    if (!data.onlyAdded) {
                        formattedRates = currencyModel.formatRates();
                        validationResultByRates = currencyModel.getValidationResultByRates();
                    } else {
                        validationResultByRates = currencyModel.getDefaultValidationResultByRates();
                    }
                    return tmpl(
                        _.extend(
                            currencyModel.toJSON(),
                            formattedRates,
                            {
                                validationResultByRates: validationResultByRates,
                                getErrorMessage: getErrorMessage
                            }
                        )
                    );
                }
            }).enableSelection(); // Fixed issue on FF with can't make active rate element on click

            /**
             * Popover can be init only after "itemsManagerTable" did render of elements
             */
            this.initPopover();
            this.listenTo(this.collection, 'sort', this.initPopover);
        }
    });

    return CurrencyGridSView;
});
