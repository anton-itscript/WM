function init() {
    $("select").selectBox();
    $('#filterparams .date-pick').datePicker({startDate:'01/01/1996', clickInput:true, imgCreateButton: true});
    $('#filterparams input[name="RgTableForm[date_from]"]').bind(
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
    $('#filterparams input[name="RgTableForm[date_to]"]').bind(
        'dpClosed',
        function(e, selectedDates)
        {
            var d = selectedDates[0];
            if (d) {
                d = new Date(d);
                $('#filterparams input[name="RgTableForm[date_from]"]').dpSetEndDate(d.addDays(-1).asString());
            }
        }
    );
}

$(document).ready(function(){
    init();
});