define(function(require) {
    'use strict';

    var MultilineChartView;
    var MultipleHandler = require('orofusioncharts/js/multiple-data-handler');
    var AbstractChartView = require('./abstract-chart-view');

    MultilineChartView = AbstractChartView.extend({
        /**
         * @inheritDoc
         */
        DataHandler: MultipleHandler,

        /**
         * @inheritDoc
         */
        prepareDataSource: function() {
            var dataSource = MultilineChartView.__super__.prepareDataSource.call(this);
            dataSource.type = 'MSLine';
            return dataSource;
        }
    });

    return MultilineChartView;
});
