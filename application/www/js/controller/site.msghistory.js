$(document).ready(function(){
    $('.tablelist.messageslog input[name="check_all"]').click(function(){

        if ($(this).hasClass('checked')) {

            var inputs =  $('.tablelist.messageslog input[name="log_id[]"]')
            for (var i=0;i<inputs.length;i++) {
                $(inputs[i]).removeAttr('checked');
                inputs[i].checked = false;
            }
            $(this).removeClass('checked')
            $(this).attr('checked','')
        } else {

            var inputs =  $('.tablelist.messageslog input[name="log_id[]"]')
            for (var i=0;i<inputs.length;i++) {
                $(inputs[i]).attr('checked','checked');
                inputs[i].checked = true;
            }
            $(this).attr('checked','checked')
            $(this).addClass('checked')

        }
    })


    $('#delete_checked').click()



    if (typeof(block_path)=='undefined')
        block_path='';

    if (typeof(date_from_name)=='undefined')
        date_from_name='';

    if (typeof(date_to_name)=='undefined')
        date_to_name='';

   // Date.format = 'dd/mm/yyyy';

    $(block_path + ' .date-pickmh').datePicker({
        startDate:'01/01/1996',
        clickInput:true,
        imgCreateButton: true,
        });



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