function downloadTypeScheduledReport(action_button)
{
	var parent_form = $(action_button).parents('form');
    var ex_schedule_processed_id = $('input[name=ex_schedule_processed_id]', parent_form).val();
    
    document.location.href = BaseUrl + '/site/scheduleTypeDownload/?id=' + ex_schedule_processed_id;
}

function downloadScheduledReportWithRefreshContinue(action_button)
{
	resaveScheduledReportWithRefreshContinue(action_button);
	
	downloadScheduledReport(action_button);
}

function resendScheduledReport(action_button)
{
    var parent_form = $(action_button).parents('form');
    var schedule_processed_id = $('input[name=schedule_processed_id]', parent_form).val();

    $('.action_msg', parent_form).html('Please wait... Your request is processing...');

    $.getJSON(
        BaseUrl + '/ajax/ScheduleResendReport',
        { schedule_processed_id: schedule_processed_id },
        function(data)
		{
            if (data.ok)
			{
				$('.action_msg', parent_form).html('Report has been re-sent successfully.');
            } 
			else if(data.errors) 
			{
                var str = '';
                
				for (var i in data.errors)
				{
					str += (str ? "<br>" : "") + data.errors[i];
                }
				
                $('.action_msg', parent_form).html('Some errors were occured: ' + str);
            }
			
            setTimeout(function()
				{
					$('.action_msg', parent_form).html('');
				},
				2000);
        }
    );
}

function resendAllScheduledReport(action_button)
{
    var parent_form = $(action_button).closest('form');
    var method = $(action_button).closest('form').attr('method');
    var data = new FormData($(parent_form).get(0));

    $('.action_msg', parent_form).html('Please wait... Your request is processing...');


    $.ajax({
        type:method,
        url: BaseUrl + '/ajax/ScheduleResendReport',
        data: data,
        processData: false,
        contentType: false,
        success:function(dataResult){

            if (dataResult.ok)
            {
                $('.action_msg', parent_form).html('Report has been re-sent successfully.');
            }
            else if(dataResult.errors)
            {
                var str = '';

                for (var i in data.errors)
                {
                    str += (str ? "<br>" : "") + dataResult.errors[i];
                }

                $('.action_msg', parent_form).html('Some errors were occured: ' + str);
            }

            setTimeout(function()
                {
                    $('.action_msg', parent_form).html('');
                },
                2000);
        }

    });



}

function resaveScheduledReport(action_button)
{
    var parent_form = $(action_button).parents('form');
    
    $('.action_msg', parent_form).html('Please wait... Your request is processing...');

	var options = 
	{
		url: BaseUrl + '/ajax/ScheduleResaveReport',
		type: 'post',
		success: function(data)
		{
			if (data.errors) 
			{
				var str = '';

				for (var i in data.errors)
				{
					str += (str ? "<br>" : "") + data.errors[i];
				}

				$('.action_msg', parent_form).html('Some errors were occured: '+str);
			} 
			else if (data.ok)
			{
				$('.action_msg', parent_form).html('Report has been re-saved successfully.');
			}
		},
		dataType: 'json'
	};

	$(parent_form).ajaxSubmit(options);  
}

function resaveScheduledReportWithRefreshContinue(action_button)
{
	// Save report
	resaveScheduledReport(action_button);
	
	cancelScheduledReport(action_button);
}

function cancelScheduledReport(action_button)
{
	hideCancelButton();
	refreshRestart();
}

function regenerateScheduledReport(action_button)
{
    var parent_form = $(action_button).parents('form');
    var schedule_processed_id = $('input[name=schedule_processed_id]', parent_form).val();

    $('.action_msg', parent_form).html('Please wait... Your request is processing...');

    $.getJSON(
        BaseUrl + '/ajax/ScheduleRegenerateReport',
        { schedule_processed_id: schedule_processed_id },
        function(data)
		{
            if (data.ok) 
			{
                $('textarea', parent_form).val(data.report_string_initial);
                $('.action_msg', parent_form).html('Report has been re-generated successfully.');
            } 
			else if(data.errors)
			{
                var str = '';
                for (var i in data.errors) {
                    str += (str ? "<br>" : "") + data.errors[i];
                }
                $('.action_msg', parent_form).html('Some errors were occured: '+str);
            }
			
            setTimeout(function()
				{
					$('.action_msg', parent_form).html('');
				},
				2000);
        }
    );
}

function regenerateScheduledReportWithChangesStart(action_button)
{
	regenerateScheduledReport(action_button);
	
	cancelScheduledReport(action_button);
}