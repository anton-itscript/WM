<?php
/**
 * @var SMSCommandGenerateMessageForm $form
 */
?>
<h2><?php echo SMSCommand::getSMSCommandsCode()[$form->sms_command_code] ?></h2>

<?php echo CHtml::beginForm('','post',['id' => 'generate_command']) ?>

<?php echo CHtml::activeHiddenField($form,'station_id')?>
<?php echo CHtml::activeHiddenField($form,'sms_command_code')?>

<?php if ($form->getParamsList()): ?>
    <?php foreach($form->getParamsList() as $id => $label) {?>
        <?php echo CHtml::activeHiddenField($form,"sms_command_params[$id]") ?>

        <?php echo CHtml::label($label,"SMSCommandGenerateMessageForm[sms_command_params][$id]",
            ['class' => $form->hasErrors("sms_command_params[$id]") ? CHtml::$errorCss : '']) ?>

        <?php echo CHtml::activeTextField($form, "sms_command_params[$id]", ['style' => 'width: 296px;']) ?>
        <?php echo CHtml::error($form,"sms_command_params[$id]") ?>
    <?php }?>
<?php endif ?>

<?php echo CHtml::endForm() ?>
<script>
    $("#generate_command").keypress(function(e) {
        if (e.which == 13) {
            return false;
        }
    });
</script>
