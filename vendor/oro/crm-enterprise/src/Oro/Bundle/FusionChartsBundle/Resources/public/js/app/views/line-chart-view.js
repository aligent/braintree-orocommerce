define(function(require) {
    'use strict';

    var LineChartView;
    var _ = require('underscore');
    var DataHandler = require('orofusioncharts/js/fusion-data-handler');
    var AbstractChartView = require('./abstract-chart-view');

    LineChartView = AbstractChartView.extend({
        /**
         * @inheritDoc
         */
        DataHandler: DataHandler,

        /**
         * @property {Object}
         */
        defaultChartOptions: _.defaults({
            height: 300
        }, AbstractChartView.prototype.defaultChartOptions),

        /**
         * @inheritDoc
         */
        prepareDataSource: function() {
            var handler = this.getDataHandler();
            var dataSource = handler.getDataSource();
            var max = handler.getMaxValue();
            var min = handler.getMinValue();
            var delta = (max - min) / 10;
            var noNegativeValues = min >= 0;
            delta = (delta === 0) ? 1 : delta;
            max += delta;
            min -= delta;
            dataSource.chart.yAxisMaxValue = Math.round(max);
            if (!noNegativeValues || min > 0) {
                dataSource.chart.yAxisMinValue = Math.floor(min);
            }
            dataSource.type = 'Line';
            return dataSource;
        }
    });

    return LineChartView;
});
