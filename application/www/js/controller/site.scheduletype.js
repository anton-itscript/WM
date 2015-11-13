

function checkReportType()
{
    if ($('#ScheduleReport_report_type').val() == 'bufr')
    {
        $("#ScheduleReport_report_format").selectBox('options', {'bufr': 'bufr'});
        $('#ScheduleReport_report_format').selectBox('value', 'bufr');
    }
    else if ($('#ScheduleReport_report_type').val() == 'synop')
    {
        $("#ScheduleReport_report_format").selectBox('options', {'txt': 'txt', 'csv' : 'csv'});
        $('#ScheduleReport_report_format').selectBox('value', report_format);
    }
    else if ($('#ScheduleReport_report_type').val() == 'data_export')
    {
        $("#ScheduleReport_report_format").selectBox('options', {'csv' : 'csv'});
        $('#ScheduleReport_report_format').selectBox('value', report_format);
    }
    else if ($('#ScheduleReport_report_type').val() == 'metar')
    {
        $("#ScheduleReport_report_format").selectBox('options', {'txt': 'txt'});
        $('#ScheduleReport_report_format').selectBox('value', 'txt');
    }
    else if ($('#ScheduleReport_report_type').val() == 'speci')
    {
        $("#ScheduleReport_report_format").selectBox('options', {'txt': 'txt'});
        $('#ScheduleReport_report_format').selectBox('value', 'txt');
    }

    if ($('#ScheduleReport_report_type').val() == 'speci')
    {
        $('#ScheduleReport_period').next('a.selectBox-dropdown').hide();
    }
    else
    {
        $('#ScheduleReport_period').next('a.selectBox-dropdown').show();
    }
}

function addDestination(type)
{
    var num = $('#destinations_container .destination_block').length;
    num++;

    dest_key++;
    if (type == 'mail') {

        var dest_name  = dest_name_mail;
        var dest_email = dest_email_mail;

        var html = '<div class="destination_block">'+
            '<input name="ScheduleTypeReportDestination['+dest_key+'][ex_schedule_destination_id]" id="ScheduleTypeReportDestination_'+dest_key+'_schedule_destination_id" value="" type="hidden">'+
            '<input name="ScheduleTypeReportDestination['+dest_key+'][method]" id="ScheduleTypeReportDestination_'+dest_key+'_method" value="mail" type="hidden">'+
            '<b>'+num+'. '+dest_name+'<\/b>'+
            '&nbsp;&nbsp;[ <a href="#" class="delete_destination">'+do_delete+'<\/a> ]'+
            '<table class="formtable">'+
            '<tbody><tr>'+
            '<td><label for="ScheduleTypeReportDestination_'+dest_key+'_destination_email">'+dest_email+'<\/label>:<\/td>'+
            '<td><input style="width: 300px;" name="ScheduleTypeReportDestination['+dest_key+'][destination_email]" id="ScheduleTypeReportDestination_'+dest_key+'_destination_email" value="" type="text"><\/td>'+
            '<\/tr>'+
            '<\/tbody><\/table><\/div>';
        $('#destinations_container').append(html);
    } else if (type == 'ftp') {

        var dest_name   = dest_name_ftp;
        var dest_ip     = dest_ip_ftp;
        var dest_port   = dest_port_ftp;
        var dest_folder = dest_folder_ftp;
        var dest_user   = dest_user_ftp;
        var dest_pwd    = dest_pwd_ftp;

        var html = '<div class="destination_block">'+
            '<input name="ScheduleTypeReportDestination['+dest_key+'][ex_schedule_destination_id]" id="ScheduleTypeReportDestination_'+dest_key+'_schedule_destination_id" value="" type="hidden">'+
            '<input name="ScheduleTypeReportDestination['+dest_key+'][method]" id="ScheduleTypeReportDestination_'+dest_key+'_method" value="ftp" type="hidden">'+
            '<b>'+num+'. '+dest_name+'<\/b>'+
            '&nbsp;&nbsp;[ <a href="#" class="delete_destination">'+do_delete+'<\/a> ]'+
            '<table class="formtable">'+
            '<tbody><tr>'+
            '<td><label for="ScheduleTypeReportDestination_'+dest_key+'_destination_ip">'+dest_ip+'<\/label>:<\/td>'+
            '<td><input style="width: 110px;" name="ScheduleTypeReportDestination['+dest_key+'][destination_ip]" id="ScheduleTypeReportDestination_'+dest_key+'_destination_ip" maxlength="15" value="" type="text"><\/td>'+
            '<td><label for="ScheduleTypeReportDestination_'+dest_key+'_destination_ip_port">'+dest_port+'<\/label>:<\/td>'+
            '<td><input style="width: 50px;" name="ScheduleTypeReportDestination['+dest_key+'][destination_ip_port]" id="ScheduleTypeReportDestination_'+dest_key+'_destination_ip_port" value="21" type="text"><\/td>'+
            '<td><label for="ScheduleTypeReportDestination_'+dest_key+'_destination_ip_folder">'+dest_folder+'<\/label>:<\/td>'+
            '<td><input name="ScheduleTypeReportDestination['+dest_key+'][destination_ip_folder]" id="ScheduleTypeReportDestination_'+dest_key+'_destination_ip_folder" value="/" type="text"><\/td>'+
            '<td><label for="ScheduleTypeReportDestination_'+dest_key+'_destination_ip_user">'+dest_user+'<\/label>:<\/td>'+
            '<td><input style="width: 110px;" name="ScheduleTypeReportDestination['+dest_key+'][destination_ip_user]" id="ScheduleTypeReportDestination_'+dest_key+'_destination_ip_user" value="" type="text"><\/td>'+
            '<td><label for="ScheduleTypeReportDestination_'+dest_key+'_destination_ip_password">'+dest_pwd+'<\/label>:<\/td>'+
            '<td><input style="width: 110px;" name="ScheduleTypeReportDestination['+dest_key+'][destination_ip_password]" id="ScheduleTypeReportDestination_'+dest_key+'_destination_ip_password" value="" type="text"><\/td>'+
            '<td colspan="2"><\/td>'+
            '<\/tr>'+
            '<\/tbody><\/table><\/div>';
        $('#destinations_container').append(html) ;

    } else if (type == 'local_folder') {
        var dest_name = dest_name_local;
        var dest_fld  = dest_fld_local;
        var dest_note = dest_note_local;
        var html = '<div class="destination_block">'+
            '<input name="ScheduleTypeReportDestination['+dest_key+'][ex_schedule_destination_id]" id="ScheduleTypeReportDestination_'+dest_key+'_schedule_destination_id" value="" type="hidden">'+
            '<input name="ScheduleTypeReportDestination['+dest_key+'][method]" id="ScheduleTypeReportDestination_'+dest_key+'_method" value="local_folder" type="hidden">'+
            '<b>'+num+'. '+dest_name+'<\/b>'+
            '&nbsp;&nbsp;[ <a href="#" class="delete_destination">'+do_delete+'<\/a> ]'+
            '<table class="formtable">'+
            '<tbody><tr>'+
            '<td><label for="ScheduleTypeReportDestination_'+dest_key+'_destination_local_folder">'+dest_fld+'<\/label>:<\/td>'+
            '<td>'+
            '<b>' + scheduled_reports_path + '<\/b>'+
            '<input style="width: 300px;" name="ScheduleTypeReportDestination['+dest_key+'][destination_local_folder]" id="ScheduleTypeReportDestination_'+dest_key+'_destination_local_folder" maxlength="255" value="" type="text"> '+
            dest_note+
            '<\/td><td><\/td>'+
            '<\/tr>'+
        '<\/tbody><\/table><\/div>';
        $('#destinations_container').append(html) ;
    }
    return false;
}


$(document).ready(function(){
    checkReportType();
    $('#ScheduleReport_report_type').change(function(){
        checkReportType();
    });


    $('form').on('click','#destinations_container .destination_block a.delete_destination', function(){
        if (!confirm('Are you sure you want to delete destination?')) {
            return false;
        }
        var parent = $(this).parents('.destination_block').get(0);
        var id = $('input[type=hidden]', parent).val();
        if (id > 0) {
            $.getJSON(
                BaseUrl+'/ajax/DeleteScheduleTypeDestination',
                {sid: ex_schedule_id, did: id},
                function(data){
                    if (data.ok == 1) {
                        $(parent).remove();
                    }
                });
        } else {
            $(parent).remove();
        }
        return false;
    });
});

$(document).ready(function(){

    $('#ScheduleTypeReport_start_date').datePicker({ clickInput:true, imgCreateButton: true});
    $('#ScheduleTypeReport_start_date').bind(
        'dpClosed',
        function(e, selectedDates)
        {
            var d = selectedDates[0];
            if (d) {
                d = new Date(d);
                $('#filterparams input[name="RgTableForm[date_to]"]').dpSetStartDate(d.addDays(1).asString());
            }
        }
    );

    $.mask.definitions['H'] = "[0-2]";
    $.mask.definitions['m'] = "[0-5]";
    $('#ScheduleTypeReport_start_time').mask('H9/m9');

});
 