<?php
/** @var $form SyncSettings */
?>

<div class="middlenarrow">
    <h1>Setting up synchronization</h1>

<?=CHtml::beginForm($this->createUrl('superadmin/syncsettings'), 'post'); ?>

<?=CHtml::errorSummary($form); ?>


<table class="formtable">

    <tr>
        <th><?php echo CHtml::Label( "Control Server IP", '1')?></th>
        <td><?=$_SERVER['SERVER_ADDR']?></td>

    </tr>
    <tr>
        <th><?php echo CHtml::activeLabel($form, "server_ip")?></th>
        <td><?php echo CHtml::activeTextField($form, "server_ip", array('disabled'=>$disabled))?></td>
        <td><?php echo CHtml::error($form, "server_ip"); ?></td>

        <th><?php echo CHtml::activeLabel($form, "remote_server_ip")?></th>
        <td><?php echo CHtml::activeTextField($form, "remote_server_ip", array('disabled'=>$disabled))?></td>
        <td><?php echo CHtml::error($form, "remote_server_ip"); ?></td>
    </tr>
    <tr>
        <th><?php echo CHtml::activeLabel($form, "server_port")?></th>
        <td><?php echo CHtml::activeTextField($form, "server_port", array('disabled'=>$disabled))?></td>
        <td><?php echo CHtml::error($form, "server_port"); ?></td>

        <th><?php echo CHtml::activeLabel($form, "remote_server_port")?></th>
        <td><?php echo CHtml::activeTextField($form, "remote_server_port", array('disabled'=>$disabled))?></td>
        <td><?php echo CHtml::error($form, "remote_server_port"); ?></td>
    </tr>

    <tr>
        <th><?php echo CHtml::activeLabel($form, "forwarding_messages_port")?></th>
        <td><?php echo CHtml::activeTextField($form, "forwarding_messages_port", array('disabled'=>$disabled))?></td>
        <td><?php echo CHtml::error($form, "forwarding_messages_port"); ?></td>

        <th><?php echo CHtml::activeLabel($form, "for_send_messages_port")?></th>
        <td><?php echo CHtml::activeTextField($form, "for_send_messages_port", array('disabled'=>$disabled))?></td>
        <td><?php echo CHtml::error($form, "for_send_messages_port"); ?></td>
    </tr>

    <tr>
        <th><?php echo CHtml::activeLabel($form, "tcp_server_command_port")?></th>
        <td><?php echo CHtml::activeTextField($form, "tcp_server_command_port", array('disabled'=>$disabled))?></td>
        <td><?php echo CHtml::error($form, "tcp_server_command_port"); ?></td>

        <th><?php echo CHtml::activeLabel($form, "tcp_client_command_port")?></th>
        <td><?php echo CHtml::activeTextField($form, "tcp_client_command_port", array('disabled'=>$disabled))?></td>
        <td><?php echo CHtml::error($form, "tcp_client_command_port"); ?></td>

    </tr>

    <tr>
        <th><?php echo CHtml::activeLabel($form, "identificator")?></th>
        <td><?php echo CHtml::activeTextField($form, "identificator", array('disabled'=>$disabled))?></td>
        <td><?php echo CHtml::error($form, "identificator"); ?></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <th><?php echo CHtml::activeLabel($form, "switch_variant")?></th>
        <td><?php echo CHtml::activeDropDownList($form,"switch_variant",SynchronizationForm::getSwitchVariants(), array('disabled'=>$disabled))?></td>
        <td><?php echo CHtml::error($form, "switch_variant"); ?></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <th><?php echo CHtml::activeLabel($form, "flexibility_role")?></th>
        <td><?php echo CHtml::activeDropDownList($form,"flexibility_role",SynchronizationForm::getFlexibilityRole(), array('disabled'=>$disabled))?></td>
        <td><?php echo CHtml::error($form, "flexibility_role"); ?></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <th><?php echo CHtml::activeLabel($form, "main_role")?></th>
        <td><?php echo CHtml::activeDropDownList($form,"main_role",SynchronizationForm::getFlexibilityRole(), array('disabled'=>$disabled))?></td>
        <td><?php echo CHtml::error($form, "main_role"); ?></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <th></th>
        <td>
            <?php echo CHtml::submitButton('Save server sunc', array('name' => '__save','disabled'=>$disabled))?>
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
</table>
    <?=CHtml::endForm(); ?>

    <?=CHtml::beginForm($this->createUrl('superadmin/syncsettings'), 'post'); ?>
        <?=CHtml::submitButton(($synchronization->process_status=='stopped' or $synchronization->process_status=='')  ?    'Start process' : 'Stop process'  , array('name' => 'process_start'))?>
    <?=CHtml::endForm(); ?>
</div>