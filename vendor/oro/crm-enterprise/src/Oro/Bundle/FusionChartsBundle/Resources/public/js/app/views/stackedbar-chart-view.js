define(function(require) {
    'use strict';

    var StackedBarChartView;
    var DataHandler = require('orofusioncharts/js/multiple-data-handler');
    var AbstractChartView = require('./abstract-chart-view');

    StackedBarChartView = AbstractChartView.extend({
        /**
         * @inheritDoc
         */
        DataHandler: DataHandler,

        /**
         * @inheritDoc
         */
        prepareDataSource: function() {
            var dataSource = StackedBarChartView.__super__.prepareDataSource.call(this);
            dataSource.type = 'scrollstackedcolumn2d';
            return dataSource;
        }
    });

    return StackedBarChartView;
});
