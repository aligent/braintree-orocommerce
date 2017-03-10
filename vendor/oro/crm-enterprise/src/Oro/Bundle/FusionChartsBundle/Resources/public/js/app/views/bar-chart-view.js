define(function(require) {
    'use strict';

    var BarChartView;
    var _ = require('underscore');
    var DataHandler = require('orofusioncharts/js/fusion-data-handler');
    var AbstractChartView = require('./abstract-chart-view');

    BarChartView = AbstractChartView.extend({
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
            var lineStep;
            var topLineValue;
            var handler = this.getDataHandler();
            var dataSource = handler.getDataSource();
            // * 1.12 to leave space for label with value above
            // or 5 to have round values on axis
            var maxValue = Math.ceil(handler.getMaxValue() * 1.12) || 5;
            var exponent = Math.floor(Math.log10(maxValue));
            var multiplier = Math.pow(10, exponent);
            var significand = maxValue / multiplier;
            if (significand < 2) {
                lineStep = multiplier / 5;
            } else if (significand < 4) {
                lineStep = multiplier / 2;
            } else if (significand < 8) {
                lineStep = multiplier;
            } else {
                lineStep = 2 * multiplier;
            }
            topLineValue = Math.ceil(maxValue / lineStep) * lineStep;
            dataSource.chart.yAxisMaxValue = topLineValue;
            dataSource.chart.numDivLines = topLineValue / lineStep - 1;
            return dataSource;
        }
    });

    return BarChartView;
});
