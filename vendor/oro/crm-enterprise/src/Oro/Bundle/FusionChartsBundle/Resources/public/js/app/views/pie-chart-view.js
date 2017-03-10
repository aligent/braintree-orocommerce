define(function(require) {
    'use strict';

    var PieChartView;
    var _ = require('underscore');
    var AbstractChartView = require('./abstract-chart-view');

    PieChartView = AbstractChartView.extend({

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
            var dataSource = this.chartOptions.dataSource;
            dataSource.type = 'Pie3D';
            return dataSource;
        }
    });

    return PieChartView;
});
