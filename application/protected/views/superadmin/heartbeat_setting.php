
<div class="middlenarrow">
    <h1>
        <?php
        echo "Heartbeat Report Setting";
        ?>
    </h1>


    <?php echo CHtml::beginForm($this->createUrl('superadmin/heartbeatreport'), 'post'); ?>


    <table class="formtable" style="float: left; width: 550px;" >
        <tr>
            <th style="width: 140px;"><?php echo CHtml::activeLabel($form, 'status')?></th>
            <td class ="<?php echo $form->status? 'EnableTD':'DisableTD'; ?>"> <?php echo $form->status?'Enable':'Disable'?></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($form, 'period')?></th>
            <td><?php echo CHtml::activeDropDownList($form, 'period', HeartbeatReportForm::$periodArray, array('style' => 'width: 70px;')); ?></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($form, 'clientName')?></th>
            <td>
                <?php echo CHtml::activeTextField($form, 'clientName', array('style' => 'width: 200px;resize:none;', 'rows' => '3', 'cols' => '20')); ?>
                <?php echo CHtml::error($form,'clientName'); ?>
            </td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($form, 'email')?></th>
            <td>
                <?php if ($form->email) foreach($form->email as $email_id => $email)
                    echo $email.' '.CHtml::link('X', array('superadmin/heartbeatreport', 'email_id' => $email_id)).'<br>';?>
            </td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($form, 'newEMail')?></th>
            <td>
                <?php echo CHtml::activeTextField($form, 'newEMail', array('style' => 'width: 200px;resize:none;', 'rows' => '3', 'cols' => '20')); ?>
                <?php echo CHtml::submitButton('Add', array('name' => 'scenario'))?>
                <?php echo CHtml::error($form,'newEMail'); ?>
            </td>
        </tr>

        <tr>
            <th><?php echo CHtml::activeLabel($form, 'ftp')?></th>
            <td>
                <?php echo CHtml::activeTextField($form, 'ftp', array('style' => 'width: 200px;resize:none;', 'rows' => '3', 'cols' => '20')); ?>
                <?php echo CHtml::error($form,'ftp'); ?>
            </td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($form, 'ftpPort')?></th>
            <td>
                <?php echo CHtml::activeTextField($form, 'ftpPort', array('style' => 'width: 200px;resize:none;', 'rows' => '3', 'cols' => '20')); ?>
                <?php echo CHtml::error($form,'ftpPort'); ?>
            </td>
        </tr>

        <tr>
            <th><?php echo CHtml::activeLabel($form, 'ftpDir')?></th>
            <td>
                <?php echo CHtml::activeTextField($form, 'ftpDir', array('style' => 'width: 200px;resize:none;', 'rows' => '3', 'cols' => '20')); ?>
                <?php echo CHtml::error($form,'ftpDir'); ?>
            </td>
        </tr>

        <tr>
            <th><?php echo CHtml::activeLabel($form, 'ftpUser')?></th>
            <td>
                <?php echo CHtml::activeTextField($form, 'ftpUser', array('style' => 'width: 200px;resize:none;', 'rows' => '3', 'cols' => '20')); ?>
                <?php echo CHtml::error($form,'ftpUser'); ?>
            </td>
        </tr>

        <tr>
            <th><?php echo CHtml::activeLabel($form, 'ftpPassword')?></th>
            <td>
                <?php echo CHtml::activePasswordField($form, 'ftpPassword', array('style' => 'width: 200px;resize:none;', 'rows' => '3', 'cols' => '20')); ?>
                <?php echo CHtml::error($form,'ftpPassword'); ?>
            </td>
        </tr>
        <tr>
            <th></th>
            <td>
                <?php echo CHtml::submitButton('Save', array('name' => 'scenario'))?>
                <?php echo CHtml::submitButton($form->status? 'Stop': 'Start', array('name' => 'scenario'))?>
            </td>
        </tr>
    </table>

    <?php echo CHtml::endForm(); ?>
</div>