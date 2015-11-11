

<div class="middlenarrow">
    <h1><?php echo It::t('menu_label', 'superadmin_LongDbSetup'); ?></h1>

    <?php echo CHtml::beginForm($this->createUrl('superadmin/longdbsetup'), 'post'); ?>

    <blockquote class="tip">
        <p>Attention! Be sure your DB Name - is name of new or not existed database. Script will create it again during installation.</p>
    </blockquote>

    <?php echo CHtml::errorSummary($conf_form); ?>

    <table class="formtable">
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'Status')?></th>
            <td class ="<?php echo $conf_form->status? 'EnableTD':'DisableTD'; ?>"> <?php echo $conf_form->status?'Enable':'Disable'?></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'host')?></th>
            <td><?php echo CHtml::activeTextField($conf_form, 'host')?></td>
            <td><?php echo CHtml::error($conf_form,'host'); ?></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'port')?></th>
            <td><?php echo CHtml::activeTextField($conf_form, 'port')?></td>
            <td><?php echo CHtml::error($conf_form,'port'); ?></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'user')?></th>
            <td><?php echo CHtml::activeTextField($conf_form, 'user')?></td>
            <td><?php echo CHtml::error($conf_form,'user'); ?></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'password')?></th>
            <td><?php echo CHtml::activePasswordField($conf_form, 'password')?></td>
            <td><?php echo CHtml::error($conf_form,'password'); ?></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'dbname')?></th>
            <td><?php echo CHtml::activeTextField($conf_form, 'dbname')?></td>
            <td><?php echo CHtml::error($conf_form,'dbname'); ?></td>
        </tr>
        <tr>
            <th></th>
            <td>
                <?php echo CHtml::submitButton('Save config', array('name' => 'save_db_config'))?> (Only change config file)
            </td>
        </tr>
        <tr>
            <th></th>
            <td>
                <?php echo CHtml::submitButton(($conf_form->status?'Recreate DB':'Create DB').' from config', array('name' => 'db_create'))?>
            </td>
        </tr>
    </table>

</div>