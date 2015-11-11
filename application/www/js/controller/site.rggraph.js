function renderGraph(){
    var seriesOptions = [],
        seriesCounter = 1,
        graph = $('#graph');

    $.each(names, function (i, name) {
        seriesOptions[i] = {
            name: names[i],
            data: data[i]
        };

        if (seriesCounter === names.length) {
            createChart(graph, seriesOptions);
        } else {
            seriesCounter += 1;
        }
    });
}
$(document).ready(renderGraph());

$(document).on('reloadData', function(e){
    renderGraph();
});

var credits = {
    href: window['Context']['domain'],
    text: 'Delairco'
};

function createChart(graph, seriesOptions)
{
    graph.highcharts("StockChart", {
        legend: {
            enabled: true
        },
        rangeSelector: {
            allButtonsEnabled: false,
            selected: 0
        },
        tooltip: {
            pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
            valueDecimals: 1
        },
        plotOptions: {
            series: {
                turboThreshold: 0
            }
        },
        credits: credits,
        series: seriesOptions
    });
}