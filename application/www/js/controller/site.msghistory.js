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



});