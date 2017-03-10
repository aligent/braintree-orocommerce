define(function(require) {
    'use strict';

    var AbstractChartView;
    var _ = require('underscore');
    var BaseView = require('oroui/js/app/views/base/view');
    var FusionCharts = require('orofusioncharts/lib/FusionCharts');

    AbstractChartView = BaseView.extend({
        chart: null,

        /**
         * @property {Function} constructor one of data handler
         */
        DataHandler: null,

        /**
         * @property {Object}
         */
        defaultChartOptions: {
            dataFormat: 'json',
            width: '100%',
            height: 400
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            _.extend(this, _.pick(options, ['chartOptions']));
            AbstractChartView.__super__.initialize.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }
            this.chart.dispose();
            AbstractChartView.__super__.dispose.call(this);
        },

        /**
         * @inheritDoc
         */
        render: function() {
            var chartOptions;
            if (!this.chart) {
                chartOptions = this.prepareChartOptions();
                this.chart = new FusionCharts(chartOptions);
            }
            this.chart.render();
            return this;
        },

        /**
         * @return {Object}
         */
        prepareChartOptions: function() {
            var dataSource = this.prepareDataSource();
            return _.extend({
                type: dataSource.type,
                dataSource: dataSource,
                id: this.chartOptions.containerId,
                renderAt: this.$el.attr('id')
            }, this.defaultChartOptions);
        },

        /**
         * @return {Object}
         */
        prepareDataSource: function() {
            return this.getDataHandler().getDataSource();
        },

        /**
         * @return {DataHandler}
         */
        getDataHandler: function() {
            var options = this.chartOptions;
            return new this.DataHandler(
                options.dataSource,
                options.options,
                options.isCurrencyPrepend
            );
        }
    });

    return AbstractChartView;
});
