function init(){
    if (typeof dataJs != 'undefined') {
        renderGraph();
    }
    $("select").selectBox();

    $('#aws_single_boxes_new .list_left, #aws_single_boxes_new .list_right').on('click', function()
        {
            var direction = $(this).attr('class');
            var parent    = $(this).closest('.content');

            if (direction == 'list_right')
            {
                var nextt = $('table:visible', parent).next('table');

                if (nextt.length)
                {
                    $('table:visible', parent).hide();
                    $(nextt).show();
                }
            }

            if (direction == 'list_left')
            {
                var prevt = $('table:visible', parent).prev('table');

                if (prevt.length)
                {
                    $('table:visible', parent).hide();
                    $(prevt).show();
                }
            }

            var nextt = $('table:visible', parent).next('table');

            if (!nextt.length)
            {
                $('a.list_right', parent).addClass('disabled');
            }
            else
            {
                $('a.list_right', parent).removeClass('disabled');
            }

            var prevt = $('table:visible', parent).prev('table');

            if (!prevt.length)
            {
                $('a.list_left', parent).addClass('disabled');
            }
            else
            {
                $('a.list_left', parent).removeClass('disabled');
            }
        }
    );
}

$(document).ready(init());
$(document).on('reloadData', function(e){
    init();
});

function renderGraph()
{
    var names = dataJs['names'],
        data = dataJs['data'];
    var seriesOptions = [],
        seriesCounter = 1;

    $.each(names, function (i, name) {
        seriesOptions[i] = {
            name: names[i],
            data: data[i]
        };

        if (seriesCounter === names.length) {
            createChartAWSSingle(seriesOptions,$("#rose"));
        } else {
            seriesCounter += 1;
        }
    });
}

function createChartAWSSingle(seriesOptions,graph)
{
    var credits = { enabled: false },
        wind_label = ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'];

    graph.highcharts({
        title: {
            text: ''
        },

        chart: {
            polar: true,
            type: 'column',
            width: 360
        },

        xAxis: {
            min: 0,
            max: 360,

            tickInterval: 22.5,
            labels: {
                formatter: function () {

                    return wind_label[this.value/22.5];
                }
            },
            pointInterval: 0
        },

        yAxis: {
            min: -1,
//            startOnTick: true,

//            tickPositioner: function () {
//                var positions = [0],
//                    tick = 2,
//                    increment = 2;
//
//                for (tick; tick - 1 <= this.dataMax; tick += increment) {
//                    positions.push(tick);
//                }
//                return positions;
//            },
            labels: {
                formatter: function () {
                    if (this.value >= 0) {
                        return this.value;
                    } else {
                        return '';
                    }
                }
            }
        },

        tooltip: {
            formatter: function () {

                return '<span style="color:{series.color}">' + this.x + '°</span>' + (this.y ? ': <b>' + this.y + '</b>m/s' : '')
            },
            valueDecimals: 1
        },

        plotOptions: {
            series: {
                groupPadding: 0.1,
                minPointLength: 90
            },
            column: {
                pointWidth: 0.3
            }
        },

        credits: credits,
        series: seriesOptions
    });
}
