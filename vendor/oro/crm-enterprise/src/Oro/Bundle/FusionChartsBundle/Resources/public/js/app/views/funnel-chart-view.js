define(function(require) {
    'use strict';

    var FunnelChartView;
    var _ = require('underscore');
    var AbstractChartView = require('./abstract-chart-view');

    FunnelChartView = AbstractChartView.extend({

        /**
         * @property {Object}
         */
        defaultChartOptions: _.defaults({
            width: '75%',
            height: 300
        }, AbstractChartView.prototype.defaultChartOptions),

        /**
         * inheritDoc
         */
        prepareChartOptions: function() {
            var chartOptions = FunnelChartView.__super__.prepareChartOptions.call(this);
            if (_.isMobile()) {
                chartOptions.height = 300 + this.chartOptions.dataSource.data.length * 12;
            }
            return chartOptions;
        },

        /**
         * @inheritDoc
         */
        prepareDataSource: function() {
            var dataSource = this.chartOptions.dataSource;
            dataSource.type = 'Funnel';
            return dataSource;
        }
    });

    return FunnelChartView;
});
