/**
 * autoloader
 */
var ctx = window['Context'];

/**
 * auto refresh
 */
var needMinuteRefresh = null;
var need5MinuteRefresh = null;

var refeshMinuteId = null;
var refesh5MinutesId = null;


$(document).ready(function()
{
	$("select").selectBox();

    $('#jclock1').jclock({utc: true, utcOffset: 0});
    $('#jclock2').jclock({utc: true, utcOffset: CurrentTZOffset});

	needMinuteRefresh = $('#autorefreshedPpage').length;
	need5MinuteRefresh = $('#autorefreshedPpage_5min').length;

	refreshStart();
});

function refreshPage(container)
{
    $('#' + container).load(document.location.href, function(response, status, xhr)
	{
        $(document).trigger('reloadData');
		if (status == "error" && xhr.status)
		{
			var msg = "Sorry but there was an error: ";

            $("#autorefreshedPageError").html(msg + xhr.status + " " + xhr.statusText);
            clearInterval(refeshIntervalId);
		}
		else
		{
			$("#autorefreshedPageError").html('');
		}
    });
}

function refreshStop()
{
	if (refeshMinuteId != null)
	{
		clearInterval(refeshMinuteId);
		
		refeshMinuteId = null;
	}
	
	if (refesh5MinutesId != null)
	{
		clearInterval(refesh5MinutesId);
		
		refesh5MinutesId = null;
	}
}

function refreshStart()
{
	if (needMinuteRefresh)
	{
        refeshMinuteId = setInterval("refreshPage('autorefreshedPpage')", 60000); // 1 minute.
    }
	
    if (need5MinuteRefresh) 
	{
        refesh5MinutesId = setInterval("refreshPage('autorefreshedPpage_5min')", 300000); // 5 minutes.
    }
}

function refreshRestart()
{
	refreshStop();
	refreshStart();
}

function showCancelButton()
{
	var cancelButton = $('#cancel-button');
			
	if (cancelButton)
	{
		cancelButton.show();
	}
}

function hideCancelButton()
{
	var cancelButton = $('#cancel-button');
			
	if (cancelButton)
	{
		cancelButton.hide();
	}
}