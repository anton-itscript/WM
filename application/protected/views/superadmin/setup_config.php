<?php
/** @var $form SuperAdminConfigForm */
?>

<div class="middlenarrow">
    <h1>Config</h1>

<?php echo CHtml::beginForm($this->createUrl('superadmin/config'), 'post'); ?>

<?php echo CHtml::errorSummary($form); ?>

<table class="formtable">
    <?php foreach($form->getConfig() as $config):  ?>
    <tr>
        <th><?php echo CHtml::activeLabel($form, "config[{$config->key}]")?></th>
        <td><?php echo CHtml::activeTextField($form, "config[{$config->key}]")?></td>
        <td><?php echo CHtml::error($form, "config[{$config->key}]"); ?></td>
    </tr>
    <?php endforeach; ?>
    <tr>
        <th></th>
        <td>
            <?php echo CHtml::submitButton('Save config', array('name' => '__save'))?>
        </td>
    </tr>
</table>

</div>