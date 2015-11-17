<?php
/** @var $form SuperAdminConfigForm */
?>

<div class="middlenarrow">
    <h1><?php echo It::t('menu_label', 'superadmin_aws_format'); ?></h1>

<?php echo CHtml::beginForm($this->createUrl('superadmin/awsformat'), 'post'); ?>

<?php  echo CHtml::errorSummary($form); ?>

<table class="formtable">
    <?php foreach($form->getConfig() as $config):  ?>
    <tr>
        <th><?php echo CHtml::activeLabel($form, "config[{$config->key}]")?></th>
        <td><?php  echo CHtml::activedropDownList($form, "config[{$config->key}]",AWSFormatConfigForm::listFormats())?></td>
        <td><?php echo CHtml::error($form, "config[{$config->key}]"); ?></td>
    </tr>
    <?php endforeach; ?>
    <tr>
        <th></th>
        <td>
            <?php echo CHtml::submitButton('Save AWS Format', array('name' => '__save'))?>
        </td>
    </tr>
</table>

</div>