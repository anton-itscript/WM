
<div class="middlenarrow">
<h1>Import XML</h1>
<blockquote class="tip">
    <p>
        You can upload XML files with the structure &lt;AWA&gt;...&lt;/AWA&gt;<br/>
        The file will be placed into the "<?php echo Yii::app()->user->getSetting('xml_messages_path')?>" folder. You can change the destination in "Admin/ Settings/ Other"<br/>
        A background script will check this folder every <?php echo Yii::app()->params['xml_check_frequency'][Yii::app()->user->getSetting('xml_check_frequency')]?>.
    </p>
</blockquote>


<?php echo CHtml::beginForm($this->createUrl('admin/importxml'), 'post', array('enctype'=>"multipart/form-data")); ?>

    <?php echo CHtml::activeFileField($form, 'xml_file', array())?>
    <?php echo CHtml::submitButton('Upload')?>
<?php echo CHtml::endForm(); ?>
</div>