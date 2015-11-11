

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
            '<input name="ScheduleReportDestination['+dest_key+'][schedule_destination_id]" id="ScheduleReportDestination_'+dest_key+'_schedule_destination_id" value="" type="hidden">'+
            '<input name="ScheduleReportDestination['+dest_key+'][method]" id="ScheduleReportDestination_'+dest_key+'_method" value="mail" type="hidden">'+
            '<b>'+num+'. '+dest_name+'<\/b>'+
            '&nbsp;&nbsp;[ <a href="#" class="delete_destination">'+do_delete+'<\/a> ]'+
            '<table class="formtable">'+
            '<tbody><tr>'+
            '<td><label for="ScheduleReportDestination_'+dest_key+'_destination_email">'+dest_email+'<\/label>:<\/td>'+
            '<td><input style="width: 300px;" name="ScheduleReportDestination['+dest_key+'][destination_email]" id="ScheduleReportDestination_'+dest_key+'_destination_email" value="" type="text"><\/td>'+
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
            '<input name="ScheduleReportDestination['+dest_key+'][schedule_destination_id]" id="ScheduleReportDestination_'+dest_key+'_schedule_destination_id" value="" type="hidden">'+
            '<input name="ScheduleReportDestination['+dest_key+'][method]" id="ScheduleReportDestination_'+dest_key+'_method" value="ftp" type="hidden">'+
            '<b>'+num+'. '+dest_name+'<\/b>'+
            '&nbsp;&nbsp;[ <a href="#" class="delete_destination">'+do_delete+'<\/a> ]'+
            '<table class="formtable">'+
            '<tbody><tr>'+
            '<td><label for="ScheduleReportDestination_'+dest_key+'_destination_ip">'+dest_ip+'<\/label>:<\/td>'+
            '<td><input style="width: 110px;" name="ScheduleReportDestination['+dest_key+'][destination_ip]" id="ScheduleReportDestination_'+dest_key+'_destination_ip" maxlength="15" value="" type="text"><\/td>'+
            '<td><label for="ScheduleReportDestination_'+dest_key+'_destination_ip_port">'+dest_port+'<\/label>:<\/td>'+
            '<td><input style="width: 50px;" name="ScheduleReportDestination['+dest_key+'][destination_ip_port]" id="ScheduleReportDestination_'+dest_key+'_destination_ip_port" value="21" type="text"><\/td>'+
            '<td><label for="ScheduleReportDestination_'+dest_key+'_destination_ip_folder">'+dest_folder+'<\/label>:<\/td>'+
            '<td><input name="ScheduleReportDestination['+dest_key+'][destination_ip_folder]" id="ScheduleReportDestination_'+dest_key+'_destination_ip_folder" value="/" type="text"><\/td>'+
            '<td><label for="ScheduleReportDestination_'+dest_key+'_destination_ip_user">'+dest_user+'<\/label>:<\/td>'+
            '<td><input style="width: 110px;" name="ScheduleReportDestination['+dest_key+'][destination_ip_user]" id="ScheduleReportDestination_'+dest_key+'_destination_ip_user" value="" type="text"><\/td>'+
            '<td><label for="ScheduleReportDestination_'+dest_key+'_destination_ip_password">'+dest_pwd+'<\/label>:<\/td>'+
            '<td><input style="width: 110px;" name="ScheduleReportDestination['+dest_key+'][destination_ip_password]" id="ScheduleReportDestination_'+dest_key+'_destination_ip_password" value="" type="text"><\/td>'+
            '<td colspan="2"><\/td>'+
            '<\/tr>'+
            '<\/tbody><\/table><\/div>';
        $('#destinations_container').append(html) ;

    } else if (type == 'local_folder') {
        var dest_name = dest_name_local;
        var dest_fld  = dest_fld_local;
        var dest_note = dest_note_local;
        var html = '<div class="destination_block">'+
            '<input name="ScheduleReportDestination['+dest_key+'][schedule_destination_id]" id="ScheduleReportDestination_'+dest_key+'_schedule_destination_id" value="" type="hidden">'+
            '<input name="ScheduleReportDestination['+dest_key+'][method]" id="ScheduleReportDestination_'+dest_key+'_method" value="local_folder" type="hidden">'+
            '<b>'+num+'. '+dest_name+'<\/b>'+
            '&nbsp;&nbsp;[ <a href="#" class="delete_destination">'+do_delete+'<\/a> ]'+
            '<table class="formtable">'+
            '<tbody><tr>'+
            '<td><label for="ScheduleReportDestination_'+dest_key+'_destination_local_folder">'+dest_fld+'<\/label>:<\/td>'+
            '<td>'+
            '<b>' + scheduled_reports_path + '<\/b>'+
            '<input style="width: 300px;" name="ScheduleReportDestination['+dest_key+'][destination_local_folder]" id="ScheduleReportDestination_'+dest_key+'_destination_local_folder" maxlength="255" value="" type="text"> '+
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
                BaseUrl+'/ajax/DeleteScheduleDestination',
                {sid: schedule_id, did: id},
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

    $('.add_button').click(function(){
		var count = $(this).attr("data-item-count");
			count++;
		$(this).attr("data-item-count",count);
	
	
        var tableContainer = $(this).closest('.middlenarrow').find('.station_table');
        var clonedTr = tableContainer.find('tr:first').clone(true);
		var html = clonedTr.html();

        tableContainer.append("<tr>"+html+"</tr>");
		tableContainer.find("tr:last").find("option").removeAttr("selected");
		tableContainer.find("tr:last").find("select").removeAttr("id");
		var selectName = tableContainer.find("tr:last").find("select").attr("name");
		selectName = selectName.replace(/\[\d\]/g,"["+ count+"]");
		tableContainer.find("tr:last").find("select").attr("name",selectName);
		tableContainer.find("tr:last").find("input").remove();
		tableContainer.find("tr:last").find("a:first").remove();
		tableContainer.find("tr:last").find(".remove_button").bind("click", removeStation);
		$('.selectBox').selectBox();
		
		

    });
	
	

	
	
	function removeStation (){
		
		var tableTr = $(this).closest('table').find('tr');
		if (tableTr.length>1) {
			var stationHiddenInput = $(this).closest('tr').find('input');
			if (stationHiddenInput.length) {
			var stationInputName = stationHiddenInput.attr('name');
			console.log(stationInputName);
				// stationInputName = stationInputName.toString()
				stationInputName = stationInputName.replace(/\[id\]/g,"[remove_id]");
				stationHiddenInput.attr('name',stationInputName);
			console.log(stationHiddenInput);
			$(this).closest('table').append(stationHiddenInput);
			}
			$(this).closest('tr').remove();
			
		}
       

    }
	
	$('.remove_button').click(removeStation);
});
 