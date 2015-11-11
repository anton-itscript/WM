<?php $importAdminsSettings->result_success===true ? It::memStatus('admin_imports_occurred_successfully'):""?>
<?php $importAdminsSettings->result_success===false ? It::memStatus('admin_import_was_fail'):"";?>

<div class="middlenarrow" style="padding-top: -100px">

    <?php echo CHtml::errorSummary($importAdminsSettings); ?>

    <h1>Export settings</h1>
    <?php echo CHtml::beginForm($this->createUrl('superadmin/exportadminssettings'), 'post', array('id' => 'formexportadminssettings')); ?>
    <table class="">
        <tr>
            <td><?php echo CHtml::activeCheckBox($exportAdminsSettings,'user_settings')?> <?php echo CHtml::activeLabel($exportAdminsSettings, 'user_settings')?></td>
        </tr>
    </table>
    <br/><br/>
    <?php echo CHtml::hiddenField('type','export')?>
    <?php echo CHtml::submitButton('Get Export')?>
    <?php echo CHtml::endForm(); ?>
    <h1>Import settings</h1>


    <?php echo CHtml::beginForm($this->createUrl('superadmin/exportadminssettings'), 'post', array('id' => 'formimportadminssettings','enctype'=>'multipart/form-data')); ?>
    <?php echo CHtml::hiddenField('type','import')?>
    <table class="">
        <tr>
            <th><?php echo CHtml::activeLabel($importAdminsSettings, 'imported_file')?>:</th>
            <td><?php echo CHtml::activeFileField($importAdminsSettings, 'imported_file')?></td>
        </tr>
    </table>
    <br/><br/>
    <?php  echo CHtml::submitButton('Import')?>
    <?php echo CHtml::endForm(); ?>

</div>