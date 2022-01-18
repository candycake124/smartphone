/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ReviewReminder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
    "jquery",
    "underscore",
    "mage/translate"
], function ($, _, $t) {
    "use strict";
    return function (config) {
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawColColorsMpAbandonedCart);
    };

    function drawColColorsMpAbandonedCart() {
        var data = new google.visualization.DataTable(),
            reportData = window.reportData;

        data.addColumn('string', $t('Report Time'));
        data.addColumn('number', $t('Sent'));
        data.addColumn('number', $t('Error'));

        data.addRows(reportData);
        var options = {
            title: $t('Review Reminder Report'),
            colors: ['#60BD68', '#F55347'],
            hAxis: {
                title: $t('Report Time')
            }
        };
        var chart = new google.visualization.ColumnChart(document.getElementById('mp_rb_report_chart_div'));

        chart.draw(data, options);
    }
});
