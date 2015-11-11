$(document).ready(function(){
    init();
});



function init()
{
    function updateButton(self)
    {
        if ($(self).is('[name *= station_id]') || !self) {
            var count = $('#station_select').next().find('ul').find("input:checked:not([id *= all])").length;
            if (count > 0) {
                $("#station_select").val("Selected: " + count + " Stations");
            } else {
                $("#station_select").val("Select");
            }

            var stationInpus = $("#station-select-list").find('input');

            for (var i=0;i<stationInpus.length;i++) {

                if(stationInpus[i].checked!=true) {

                    var container = $("#handler-select-list").find('.'+$(stationInpus[i]).val()+'-station');
                        container.hide();
                        container.addClass('hide');
                }
            }

            for (var i=0;i<stationInpus.length;i++) {

                if(stationInpus[i].checked==true) {

                    var container = $("#handler-select-list").find('.'+$(stationInpus[i]).val()+'-station');
                        container.show();
                    if ($(container).hasClass('hide')) {
                        $(container).removeClass('hide');
                    }
                }
            }


            for (var i=0;i<stationInpus.length;i++) {

                if(stationInpus[i].checked!=true) {

                    var container = $("#handler-select-list").find('.'+$(stationInpus[i]).val()+'-station');

                    for (var k=0;k<container.length; k++) {
                        if ($(container[k]).hasClass('hide')) {
                            var inputs = $(container[k]).find('input');
                            for (var j=0; j<inputs.length; j++) {
                                if (inputs[j].checked == true) {
                                    inputs[j].checked = false;
                                    $(inputs[j]).removeAttr('checked');
                                }
                            }
                        }
                    }

                }
            }

            var li = $('#handler-select-list>ul').children();

            var display = true;
            for(var i=0;i<li.length;i++) {
                if(!$(li[i]).hasClass('hide')) {
                    display = false;

                }
            }
            if(display){
                $('#station-feature-attention').show();
            } else {
                $('#station-feature-attention').hide();
            }


        }

        if ($(self).is('[name *= sensor_feature_code]') || !self) {
            var selected = $("#feature_select").next().find('ul').find("input:checked:not([id *= all])"),
                show_accumulate = false;

            count = selected.length;

            if (count) {
                $("#feature_select").val("Selected: " + count + " Features");

                selected.each(function(){
                    if ($.inArray($(this).val(), ['rain_in_period', 'solar_radiation_in_period', 'sun_duration_in_period']) > -1) {
                        show_accumulate = true;
                    }
                });

                if (show_accumulate) {
                    $('#accumulation_select').show();
                    $('[for = AWSGraphForm_accumulation_period]').show();
                } else {
                    $('#AWSGraphForm_accumulation_period_0').prop("checked", true).trigger('click').trigger("change");
                    $('#accumulation_select').hide();
                    $('[for = AWSGraphForm_accumulation_period]').hide();
                }
            } else {
                $("#feature_select").val("Select");
                $('#AWSGraphForm_accumulation_period_0').prop("checked", true).trigger('click').trigger("change");
                $('#accumulation_select').hide();
                $('[for = AWSGraphForm_accumulation_period]').hide();
            }
        }
        if ($(self).is('[name *= accumulation_period]') || !self) {
            var period = "";
            if (!self) {
                period = $("[id *= accumulation_period]").find("input:checked").next().html();
            } else {
                period = $(self).next().html()
            }

            if (period) {
                $("#accumulation_select").val(period);
            } else {
                $("#accumulation_select").val("Select");
            }
        }

    }

    // Remove group inputs
    var select = $(".select-list .select-ul");

    select.on("change", "input", function(e){
        var self = this;
        setTimeout(function(){
            updateButton($(self));
        }, 50);
    });

    $("#filterparams").on("mouseover mouseout", ".select-list", function(e){
        if (e.type == "mouseover") {
            $(this).find(".select-option").show();
        } else {
            $(this).find(".select-option").hide();
        }
    });

    updateButton();

    jQuery('#AWSGraphForm_sensor_feature_code_Temperature_all').unbind('click').click(function() {
        jQuery("input[name *= 'AWSGraphForm\[sensor_feature_code\]\[Temperature']:checkbox").prop('checked', this.checked);
    });
    jQuery("input[name *= 'AWSGraphForm\[sensor_feature_code\]\[Temperature' ]:checkbox").unbind('click').click(function() {
        jQuery('#AWSGraphForm_sensor_feature_code_Temperature_all').prop('checked', !jQuery("input[name *= 'AWSGraphForm\[sensor_feature_code\]\[Temperature']:not(:checked):checkbox").length);
    });
    jQuery('#AWSGraphForm_sensor_feature_code_Temperature_all').prop('checked', !jQuery("input[name *= 'AWSGraphForm\[sensor_feature_code\]\[Temperature']:not(:checked):checkbox").length);


    if (typeof dataJs != 'undefined') {
        initChart();
    }
}

function initChart()
{
    var graph = $('#graph');

    if (feature_code == 'custom') {
        var to_array = $.map(dataJs, function(value, index) {
            return [value];
        });
        createWindRose(graph,to_array);
    } else if (['SolarRadiation', 'RainAws', 'SunshineDuration'].indexOf(feature_code) != -1) {
        var seriesOptions = [],
            seriesCounter = 1,
            names         = dataJs['series_names'],
            data          = dataJs['series_data'];
        $.each(names, function (i, name) {
            seriesOptions[i] = {
                //type: 'column',
                color:names[i]['params']['color'],
                name: names[i]['name'],
                data: data[i]
            };

            if (seriesCounter == Object.keys(names).length) {
                $(document).ready(function(){
                    createChart(graph, seriesOptions);
                });
            } else {
                seriesCounter += 1;
            }
        });
    } else {
        var seriesOptions = [],
            seriesCounter = 1,
            names         = dataJs['series_names'],
            data          = dataJs['series_data'];
        $.each(names, function (i, name) {
            seriesOptions[i] = {
                color:names[i]['params']['color'],
                name: names[i]['name'],
                data: data[i]
            };

            if (seriesCounter == Object.keys(names).length) {
                $(document).ready(function(){
                    createChart(graph, seriesOptions);
                });
            } else {
                seriesCounter += 1;
            }
        });
    }
}

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
            selected: 0,
            inputEnabled: false
        },
        tooltip: {
            pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
            valueDecimals: 1
        },
        xAxis: {
            gapGridLineWidth: 0
        },

        plotOptions: {
            line: {
                gapSize: config['SITE_AWSGRAPH_GAPSIZE']['value']
            },
            series: {
                turboThreshold: 0
            }
        },


        credits: credits,
        series: seriesOptions
    });
}

function createWindRose(graph, seriesOptions)
{
    var wind_label = ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'],
        metric = "m/s";

    graph.highcharts({
        title: {
            text: 'Wind Direction'
        },

        chart: {
            polar: true,
            type: 'column'
        },

        xAxis: {
            tickmarkPlacement: 'on',
            labels: {
                formatter: function () {
                    return wind_label[this.value];
                }
            },
            pointInterval: 0
        },
        yAxis: {
            min: 0,
            endOnTick: false,
            labels: {
                enabled: false
            },
            reversedStacks: false
        },

        tooltip: {
            formatter: function() {
                return '<b>' + wind_label[this.x] + '</b> (' + this.series.name + metric + '): <b>' + this.y + '%</b>';
            }
        },

        plotOptions: {
            series: {
                stacking: 'normal',
                shadow: false,
                groupPadding: 0,
                pointPlacement: 'on',
                turboThreshold: 0
            }
        },
        legend: {
            align: 'right',
            verticalAlign: 'top',
            y: 100,
            layout: 'vertical',
            labelFormat: '{name} ' + metric
        },
        credits: credits,
        series: seriesOptions
    });
}