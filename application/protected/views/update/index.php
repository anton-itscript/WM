<div class="middlenarrow">
    <h1>Update Version</h1>
    <?php if (php_uname('s') == 'Linux') {?>
        <blockquote class="note">
            <p>Attention!
            Current server's OS is Linux. Please don't use the feature "UPGRADE" because it is not tested and can cause problems in work of the application
            </p>
        </blockquote>
    <?php } ?>
        
    Your current version is <?php echo  getConfigValue('version_name');?>
    
    <br/><br/>
    Load <b>update_x_x_x.zip</b> - to update your current app version. You can get update_x_x_x.zip via email from Delairco company.
    <br/><br/>
    
    
    <?php echo CHtml::beginForm($this->createUrl('update/index'), 'post', array('enctype' => 'multipart/form-data')); ?>
    
    <?php echo CHtml::activeFileField($form, 'update_zip');?>
    <?php echo CHtml::submitButton('Upload')?>
    <?php echo CHtml::error($form,'update_zip'); ?>
    
    <?php echo CHtml::endForm(); ?>
    
    <?php if ($history) {?>
        <br/><br/>
        <b>History of changes (starting from v 0.4.2)</b>
        <div id="changes_history">
            <?php echo nl2br($history);?>
        </div>    
    <?php }?>
</div>
