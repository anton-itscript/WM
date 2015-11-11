<div class="middlenarrow">
    <h1><?php echo It::t('menu_label', 'superadmin_LongDbTask'); ?></h1>
    <?php echo CHtml::beginForm($this->createUrl('superadmin/longdbtask'), 'post'); ?>

    <?php echo CHtml::errorSummary($conf_form); ?>

    <table class="formtable">
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'sync_id')?></th>
            <td><b><?php echo $conf_form->sync_id ;?></b></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'sync_interval')?></th>
            <td>
                <?php echo CHtml::activeTextField($conf_form, 'sync_interval',array('style' => 'width: 50px;'))?>
                <?php echo CHtml::activeDropDownList($conf_form, 'sync_periodicity', $periodicity, array('style' => 'width: 70px;')); ?>
            </td>
            <td><?php echo CHtml::error($conf_form,'sync_interval'); ?></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'sync_startTime')?></th>
            <td><?php echo CHtml::activeTextField($conf_form, 'sync_startTime',array('style' => 'width: 50px;'))?>
                <?php echo CHtml::activeLabel($conf_form,'sync_startTime_desc'); ?></td>
            <td><?php echo CHtml::error($conf_form,'sync_startTime'); ?></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'sync_delete_period')?></th>
            <td>
                <?php echo CHtml::activeTextField($conf_form, 'sync_delete_period',array('style' => 'width: 50px;'))?>
                <?php echo CHtml::activeDropDownList($conf_form, 'sync_delete_periodicity', $const, array('style' => 'width: 70px;'))?>
            </td>
            <td><?php echo CHtml::error($conf_form,'sync_delete_period'); ?></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'sync_max_row')?></th>
            <td><?php echo CHtml::activeTextField($conf_form, 'sync_max_row',array('style' => 'width: 50px;'))?>
                <?php echo CHtml::activeLabel($conf_form,'sync_max_row_desc'); ?></td>
            <td><?php echo CHtml::error($conf_form,'sync_max_row'); ?></td>
        </tr>
        <tr>
            <th></th>
            <td>
                <?php echo CHtml::submitButton(TaskManager::check($conf_form->sync_id)?'Update':'Start', array('name' => 'save_db_sync'))?>
                <?php if (TaskManager::check($conf_form->sync_id)) echo CHtml::submitButton('Stop', array('name' => 'delete_db_sync'))?>
            </td>
        </tr>
    </table>


</div>