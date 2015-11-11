// for table
function setHeightTable(ind){
    // parameters
    if (!tabs_loaded[ind]) {
        return;
    }
    var scroll = 17,
        head = tabs_loaded[ind].station_id !=-1 ? 45 : 62;
    // clean height
    $('.awstable_block:eq('+ind+') .leftScroll').height('');
    $('.awstable_block:eq('+ind+') .mainScroll').height('');
    $('.awstable_block:eq('+ind+')').height('');
    //// set height
    //free height for awsTable
    var avsTableHeight = window.innerHeight//browser
        -(107+100+35+80);//top + menu + bottom + other
    //set
    if($('.awstable_block:eq('+ind+')').height() > avsTableHeight)
        $('.awstable_block:eq('+ind+')').height(avsTableHeight);

    $('.awstable_block:eq('+ind+') .leftScroll').height(
        $('.awstable_block:eq('+ind+')').height()-head-scroll
    );
    $('.awstable_block:eq('+ind+') .mainScroll').height(
        $('.awstable_block:eq('+ind+')').height()-head
    );
    ////scroll event
    $('.awstable_block:eq('+ind+') .mainScroll').scroll(function(){
        $('.awstable_block:eq('+ind+') .leftScroll').scrollTop(
            $('.awstable_block:eq('+ind+') .mainScroll').scrollTop());
        $('.awstable_block:eq('+ind+') .topScroll').scrollLeft(
            $('.awstable_block:eq('+ind+') .mainScroll').scrollLeft());
    });
    ////set width
    ///setWidthColumn(ind);
}
function setWidthColumn(ind){
    //parameters
    var bord = 1,//border cell
        pad = 10;//summ padding cell
    //
    var tableTop = document .getElementsByClassName('awstable_block')[ind]
            .getElementsByClassName("topScroll")[0]
            .getElementsByTagName("table")[0],
        cellsTop = tableTop.getElementsByTagName("tr")[1].getElementsByTagName("th"),
        cellsTopDesc = tableTop.getElementsByTagName("tr")[0].getElementsByTagName("th"),
        cellsLen = cellsTopDesc.length;

    var tableMain = document.getElementsByClassName('awstable_block')[ind]
            .getElementsByClassName("mainScroll")[0]
            .getElementsByTagName("table")[0],
        rowsMain = tableMain.getElementsByTagName("tr"),
        cellsMain = rowsMain[0].getElementsByTagName("td");

    var sumWidth= 0;
    if(!cellsTopDesc[0].offsetWidth)return;

    for(var i=0, len=0, j=0, widthCell= 0,e=0; j<cellsLen; j++){
        len+=cellsTopDesc[j].colSpan;
        if(len-i == 1){
            widthCell = bord + cellsTop[i].offsetWidth >= cellsMain[i].offsetWidth ? cellsTop[i].offsetWidth: cellsMain[i].offsetWidth;
            cellsTopDesc[j].style.width =
                cellsMain[i].style.width =
                    cellsTop[i].style.width = widthCell -bord -pad +'px';
            sumWidth+=widthCell;
            i++;
        } else {
            widthCell=0;
            for(var t=i;t<len;t++)
                widthCell+=bord + cellsTop[t].offsetWidth >= cellsMain[t].offsetWidth ? cellsTop[t].offsetWidth : cellsMain[t].offsetWidth;

            e=(bord + cellsTopDesc[j].offsetWidth)/widthCell;
            e=e > 1? e : 1;

            var sumWidthColSpan=0;
            for(;i<len;i++){
                widthCell = Math.ceil(e*(bord + cellsTop[i].offsetWidth >= cellsMain[i].offsetWidth ? cellsTop[i].offsetWidth : cellsMain[i].offsetWidth));
                cellsMain[i].style.width =
                    cellsTop[i].style.width = widthCell -bord -pad +'px';
                sumWidthColSpan+=widthCell;
            }
            cellsTopDesc[j].style.width = sumWidthColSpan -bord -pad +'px';
            sumWidth+=sumWidthColSpan;
        }
    }
    tableTop.style.width=
        tableMain.style.width=sumWidth +bord +'px';

}
function checkData(ind){
    var tableMain = document.getElementsByClassName('awstable_block')[ind]
        .getElementsByClassName("mainScroll")[0]
        .getElementsByTagName("table")[0]
        .getElementsByTagName("tr");
    return tableMain.length != 0;
}

$(document).ready( function() {
    init();
    //
    //if(checkData(0)){
    setHeightTable(0);
    setWidthColumn(0);//}
    //else
    //$('.awstable_block:eq(0)').html('No results');
    //
    $('.awstable_tabs div').click(function(){

        if (!$(this).hasClass('active')) {
            var ind = $('.awstable_tabs div').index(this);
            $('.awstable_tabs div').removeClass('active');
            $(this).addClass('active');

            $('.awstable_block').hide();

            if (tabs_loaded[ind].loaded == true) {
                $('.awstable_block:eq('+ind+')').show();
                setHeightTable(ind);
            } else {
                $('.awstable_block:eq('+ind+')').html('<img src="'+BaseUrl+'/img/loading.gif">');
                $('.awstable_block:eq('+ind+')').show();

                $.get(
                    BaseUrl+'/site/awstable',
                    {show_station: tabs_loaded[ind].station_id},
                    function(data){
                        $('.awstable_block:eq('+ind+')').html(data);
                        tabs_loaded[ind].loaded = true;})
                    .always(function() {
                        if(checkData(ind)){
                            setHeightTable(ind);
                            setWidthColumn(ind);}
                        else
                            $('.awstable_block:eq('+ind+')').html('No results');});
            }
        }
    });
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
                    console.log('false')
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
                    $('[for = AWSTableForm_accumulation_period]').show();
                } else {
                    $('#AWSTableForm_accumulation_period_0').prop("checked", true).trigger('click').trigger("change");
                    $('#accumulation_select').hide();
                    $('[for = AWSTableForm_accumulation_period]').hide();
                }
            } else {
                $("#feature_select").val("Select");
                $('#AWSTableForm_accumulation_period_0').prop("checked", true).trigger('click').trigger("change");
                $('#accumulation_select').hide();
                $('[for = AWSTableForm_accumulation_period]').hide();
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

    // Remove inputs
    var select = $(".select-list .select-ul");
    select.find("input[value *= group]").each(function(){
        $(this).parent("li").addClass("head");
        $(this).remove();
    });
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

    jQuery('#AWSTableForm_sensor_feature_code_Temperature_all').unbind('click').click(function() {
        jQuery("input[name *= 'AWSTableForm\[sensor_feature_code\]\[Temperature']:checkbox").prop('checked', this.checked);
    });
    jQuery("input[name *= 'AWSTableForm\[sensor_feature_code\]\[Temperature' ]:checkbox").unbind('click').click(function() {
        jQuery('#AWSTableForm_sensor_feature_code_Temperature_all').prop('checked', !jQuery("input[name *= 'AWSTableForm\[sensor_feature_code\]\[Temperature']:not(:checked):checkbox").length);
    });
    jQuery('#AWSTableForm_sensor_feature_code_Temperature_all').prop('checked', !jQuery("input[name *= 'AWSTableForm\[sensor_feature_code\]\[Temperature']:not(:checked):checkbox").length);

}