<?php
/**
 * @var SMSCommand $sms
 */
?>

<?php if ($sms && !$sms->isNewRecord) : ?>
    <div id="sms_status">
        <h1> SMS status </h1>

        <table class="formtable">
            <tr>
                <th style="min-width: 100px;"><?php echo $sms->getAttributeLabel('sms_command_id'); ?></th>
                <td><?php echo $sms->sms_command_id; ?></td>
            </tr>
            <tr>
                <th><?php echo $sms->getAttributeLabel('sms_command_code'); ?></th>
                <td><?php echo $sms->sms_command_code; ?></td>
            </tr>
            <tr>
                <th><?php echo $sms->getAttributeLabel('sms_command_status'); ?></th>
                <td><?php echo $sms->sms_command_status; ?></td>
            </tr>
            <tr>
                <th><?php echo $sms->getAttributeLabel('station_id'); ?></th>
                <td><?php echo $sms->station_id; ?></td>
            </tr>
            <tr>
                <th><?php echo $sms->getAttributeLabel('sms_command_message'); ?></th>
                <td><?php echo $sms->sms_command_message; ?></td>
            </tr>
            <tr>
                <th><?php echo $sms->getAttributeLabel('sms_command_response'); ?></th>
                <td><?php echo $sms->sms_command_response; ?></td>
            </tr>
            <tr>
                <th><?php echo $sms->getAttributeLabel('updated'); ?></th>
                <td><?php echo $sms->updated; ?></td>
            </tr>
            <tr>
                <th><?php echo $sms->getAttributeLabel('created'); ?></th>
                <td><?php echo $sms->created; ?></td>
            </tr>
        </table>
    </div>
    <?php if (!isset($_GET['sms_command_id'])): ?>
        <?php echo CHtml::ajaxButton('Update', $this->createUrl('admin/sendsmscommand',['sms_command_id' => $sms->sms_command_id]), ['success'=>'js:function(string){ $("#sms_status").html(string); }']); ?>
    <?php endif; ?>
<?php endif; ?>