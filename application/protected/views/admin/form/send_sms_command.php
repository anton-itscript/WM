<?php
/**
 * @var SMSCommandSendForm $form
 */
?>


<?php echo CHtml::beginForm($this->createUrl('admin/sendsmscommand'), 'post') ?>

<table class="formtable">
    <tr>
        <th colspan="2">
            <?php if(Yii::app()->user->hasFlash('SendSMSCommandForm_success')): ?>

                <div class="status_success">
                    <?php echo Yii::app()->user->getFlash('SendSMSCommandForm_success'); ?>
                </div>

            <?php endif; ?>
        </th>
    </tr>
    <tr>
        <th style="min-width: 150px;"><?php echo CHtml::activeLabel($form, 'station_id') ?></th>
        <td><?php echo CHtml::activeDropDownList($form, 'station_id', $form->getStations(), ['prompt' => 'Select Station', 'style' => 'width: 200px;'])?></td>
    </tr>
    <tr><td colspan="2"><?php echo CHtml::error($form,'station_id') ?></td></tr>
    <tr>
        <th><?php echo CHtml::activeLabel($form, 'sms_command_code') ?></th>
        <td><?php echo CHtml::activeDropDownList($form, 'sms_command_code', $form->getCommands(), ['onchange' => "$('#SendSMSCommandForm_sms_command_message').val($('#SendSMSCommandForm_sms_command_code').val());", 'prompt' => 'Select Command', 'style' => 'width: 200px;'])?></td>
    </tr>
    <tr><td colspan="2"><?php echo CHtml::error($form,'sms_command_code') ?></td></tr>
    <tr>
        <th colspan="2" style="text-align: right;">
            <?php echo CHtml::button('Next',['name' => 'generate', 'onclick'=>'openEditForm($(\'#SMSCommandSendForm_station_id\').val(),$(\'#SMSCommandSendForm_sms_command_code\').val())']); ?>
        </th>
    </tr>
    <tr>
        <th><?php echo CHtml::activeLabel($form, 'sms_command_message') ?></th>
        <td><?php echo CHtml::activeTextField($form, 'sms_command_message',['style' => 'width: 238px;'])?></td>
    </tr>
    <tr><td colspan="2"><?php echo CHtml::error($form,'sms_command_message') ?></td></tr>
    <tr>
        <th colspan="2" style="text-align: right;">
            <?php echo CHtml::submitButton('Send',['name' => 'send', 'success'=>'js:function(string){ $(".middlenarrow").append(string); }']); ?>
        </th>
    </tr>
</table>
<?php echo CHtml::endForm() ?>

<div id="modal_form">
    <div id="modal_button">
        <?php echo CHtml::button('Generate',['onclick' => 'saveEditForm()']); ?>
        <?php echo CHtml::button('Cancel',['onclick' => 'closeModal()']); ?>
    </div>
</div>
<div id="overlay"></div>

<script type="text/javascript">
    // For modal
    var model_form = $('#modal_form');

    function showModal(data) {
        if (data.form) {
            $('#overlay').fadeIn(200, function(){
                $(model_form).find('form, h2').remove();
                $(model_form)
                    .prepend(data.form)
                    .css('display', 'block')
                    .animate({opacity: 1, top: '50%'}, 200);
            });
        } else if (data.status == 'success') {
            $('[name *= sms_command_message]').val(data.message);
            closeModal();
        } else {
            closeModal();
        }
    }

    function closeModal() {
        $(model_form)
            .animate({opacity: 0, top: '30%'}, 200,
            function() {
                $(this)
                    .css('display', 'none')
                    .css('top', '90%')
                    .find('form, h2').remove();
                $('#overlay').fadeOut(200);
            }
        )
    }

    function saveEditForm() {
        var msg = $(model_form).find('form').serialize();
        $.ajax({
            type: 'POST',
            url: BaseUrl + '/admin/generatesmscommand/',
            data: msg,
            success: function(json_data) {
                showModal(JSON.parse(json_data));
            },
            error:  function(xhr, str){
            }
        });
    }

    function openEditForm(station_id, sms_command_code) {
        if (station_id == '' || sms_command_code == '') {
            alert('Please select station and command');
            return;
        }
        var data = {SMSCommandGenerateMessageForm: { station_id: station_id, sms_command_code: sms_command_code }, open: true};
        var ajax_param = {
            type : "POST",
            url  : BaseUrl + '/admin/generatesmscommand/',
            data : data
        };
        $.ajax(ajax_param)
            .done(function(json_data){
                var data = JSON.parse(json_data);
                showModal(data);
            });

    }

    $('#overlay').click( function(){
        closeModal();
    });

</script>