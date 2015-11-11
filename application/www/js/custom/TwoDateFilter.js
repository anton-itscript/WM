$(document).ready(function(){

    if (typeof(block_path)=='undefined')
        block_path='';

    if (typeof(date_from_name)=='undefined')
        date_from_name='';

    if (typeof(date_to_name)=='undefined')
        date_to_name='';

    $(block_path + ' .date-pick').datePicker({startDate:'01/01/1996', clickInput:true, imgCreateButton: true});
    $(block_path + ' input[name="' + date_from_name + '"]').bind(
        'dpClosed',
        function(e, selectedDates)
        {
            var d = selectedDates[0];
            if (d) {
                d = new Date(d);
                $(block_path + ' input[name="' + date_to_name + '"]').dpSetStartDate(d.addDays(1).asString());
            }
        }
    );
    $(block_path + ' input[name="' + date_to_name + '"]').bind(
        'dpClosed',
        function(e, selectedDates)
        {
            var d = selectedDates[0];
            if (d) {
                d = new Date(d);
                $(block_path + ' input[name="' + date_from_name + '"]').dpSetEndDate(d.addDays(-1).asString());
            }
        }
    );
});