define(function(require) {
    'use strict';

    var console = window.console;
    var CurrencyModel;
    var BaseModel = require('oroui/js/app/models/base/model');
    var NumberFormatter = require('orolocale/js/formatter/number');
    var _ = require('underscore');
    var $ = require('jquery');

    /**
     * @export  oromulticurrency/js/app/models/currency-model
     */
    CurrencyModel = BaseModel.extend({

        fractionDigits: {
            min: 6,
            max: 10
        },

        rateAttrs: [
            'rateTo',
            'rateFrom'
        ],

        validationRules: {
            'NotBlank': function(value) {
                return $.trim(value).length === 0;
            },
            'Number': function(value) {
                return isNaN(Number(value)) || !isFinite(Number(value));
            },
            'BiggerThenZero': function(value) {
                return Number(value) <= 0;
            }
        },

        defaultValidationResult: {
            isValid: true,
            validationCode: null
        },

        initialize: function() {
            this.on('change:isDefault', this.onDefaultChange, this);
            this.on('change:rateFrom', this.onChangeRate, this);
            this.on('change:rateTo', this.onChangeRate, this);
        },

        onChangeRate: function(model) {
            this.validateRate(model);
            this.truncateRatePrecision(model);
            this.fixOppositeRate(model);
        },

        truncateRatePrecision: function(model) {
            var changedKey = _.first(_.keys(model.changed));
            if (_.indexOf(this.rateAttrs, changedKey) === -1) {
                return false;
            }

            var validationResult = this.validateAttribute(changedKey);
            if (validationResult.isValid) {
                var value = this.get(changedKey);
                var fractionalDigits = this.getFractionalDigits(value);
                if (fractionalDigits > this.fractionDigits.max) {
                    this.set(changedKey, Number(value.toFixed(this.fractionDigits.max)));
                }
            }
        },

        fixOppositeRate: function(model) {
            var changedKey = _.first(_.keys(model.changed));
            if (_.indexOf(this.rateAttrs, changedKey) === -1) {
                return false;
            }

            var oppositeRateKey = _.first(_.without(this.rateAttrs, changedKey));
            if (this.get(oppositeRateKey) !== '') {
                return false;
            }

            var validationResult = this.validateAttribute(changedKey);
            if (validationResult.isValid) {
                var oppositeValue = this.get(changedKey);
                var fractionalDigits = this.getFractionalDigits(oppositeValue);
                var oppositeRateValue = 1 / oppositeValue;
                this.set(oppositeRateKey, Number(oppositeRateValue.toFixed(fractionalDigits)));
            }
        },

        getFractionalDigits: function(value) {
            var precision = this.getPrecisionByValue(value);
            return precision < this.fractionDigits.min ? this.fractionDigits.min : precision;
        },

        getPrecisionByValue: function(value) {
            var valueParts = value
                .toString()
                .split('.');

            if (valueParts.length === 1) {
                return 0;
            }

            if (valueParts.length === 2) {
                return valueParts[1].length;
            }

            console.warn(
                'Incorrect value format:',
                value
            );

            return false;
        },

        validateRate: function(model) {
            var changedKey = _.first(_.keys(model.changed));
            if (_.indexOf(this.rateAttrs, changedKey) === -1) {
                return false;
            }

            var unformattedValue = NumberFormatter.unformatStrict(model.changed[changedKey]);
            var validationResult = this.validateValue(unformattedValue);
            if (validationResult.isValid) {
                this.set(changedKey, unformattedValue, {silent: true});
            }
        },

        onDefaultChange: function() {
            if (this.get('isDefault')) {
                this.set({
                    rateFrom: 1,
                    rateTo: 1
                }, {silent: true});
            }
        },

        defaults: {
            code: '',
            name: '',
            symbol: '',
            isDefault: false,
            rateFrom: '',
            rateTo: '',
            onlyAdded: false
        },

        formatRates: function() {
            var formattedRates = {};
            _.each(this.rateAttrs, function(attrName) {
                var validationResult = this.validateAttribute(attrName);
                if (validationResult.isValid) {
                    formattedRates[attrName] = NumberFormatter.formatDecimal(
                        this.get(attrName),
                        {
                            'max_fraction_digits': this.fractionDigits.max
                        }
                    );
                }
            }, this);
            return formattedRates;
        },

        getValidationResultByRates: function() {
            var validationResults = {};
            _.each(this.rateAttrs, function(attrName) {
                validationResults[attrName] = this.validateAttribute(attrName);
            }, this);

            return validationResults;
        },

        _getValidRates: function() {
            var rates = {};
            _.each(this.rateAttrs, function(attrName) {
                var validateResult = this.validateAttribute(attrName);
                if (validateResult.isValid) {
                    rates[attrName] = this.get(attrName);
                }
            }, this);
            return rates;
        },

        getRatesRatio: function() {
            var rates = this._getValidRates();
            var rateKeys = _.keys(rates);

            if (0 === rateKeys.length) {
                return false;
            }

            /**
             * Try to guess missed rate
             */
            if (1 === rateKeys.length) {
                var existedRateKey = _.first(rateKeys);
                var missedRateKey =  _.first(_.without(this.rateAttrs, rateKeys));
                var missedRateValue = 1 / rates[existedRateKey];
                var fractionalDigits = this.getFractionalDigits(rates[existedRateKey]);
                rates[missedRateKey] = Number(missedRateValue.toFixed(fractionalDigits));
            }

            /**
             * Exchange rate
             */
            return {
                'rateFrom': rates.rateTo,
                'rateTo': rates.rateFrom
            };
        },

        getRecalculatedRates: function(ratesRatio) {

            if (false === ratesRatio) {
                return false;
            }

            var rates = {};
            _.each(this.rateAttrs, function(attrName) {
                var validateResult = this.validateAttribute(attrName);
                if (validateResult.isValid) {
                    rates[attrName] = Number(
                        (this.get(attrName) * ratesRatio[attrName]).toFixed(this.fractionDigits.max)
                    );
                }
            }, this);

            if (_.keys(rates).length) {
                return rates;
            }

            return false;
        },

        getDefaultValidationResultByRates: function() {
            var validationResults = {};
            _.each(this.rateAttrs, function(attrName) {
                validationResults[attrName] = _.clone(this.defaultValidationResult);
            }, this);

            return validationResults;
        },

        getRatesValidationStatus: function() {
            return _.every(this.rateAttrs, function(attrName) {
                return this.validateAttribute(attrName).isValid;
            }, this);
        },

        validateValue: function(value) {
            var validationResult = _.clone(this.defaultValidationResult);

            _.every(this.validationRules, function(ruleFunc, validationCode) {
                if (ruleFunc(value)) {
                    validationResult.isValid = false;
                    validationResult.validationCode = validationCode;
                    return false;
                }
                return true;
            }, this);

            return validationResult;
        },

        validateAttribute: function(attrName) {
            return this.validateValue(this.get(attrName));
        },

        getRatesData: function() {
            var data = [];
            data[this.get('code')] = {'rateFrom': this.get('rateFrom'), 'rateTo': this.get('rateTo')};
            return data;
        }
    });

    return CurrencyModel;
});
