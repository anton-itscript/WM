
<div class="middlenarrow">
<h1>Import</h1>
<blockquote class="tip">
<p>You can import messages with the following format "@DDCP1201109181713.....$"<br/>
This import tool checks whether:<br/>
<b>1.</b> messages start with "@"<br/>
<b>2.</b> messages ends with "$"<br/>
<b>3.</b> messages length of CRC
</p>
</blockquote>

<blockquote class="note">
    <p><b>ATTENTION</b>:
    <?php if ($settings->overwrite_data_on_import) {?>
        Be careful! Any data you import - WILL OVERWRITE existing data in the same period.
    <?php } else {?>
        only new from your import will be added. To overwrite existing data with imported - set this configuration in Admin / Setup .
    <?php }?></p>
</blockquote>

You can import more than 1 message.  Start each message on a new line.<br/><br/>

<?php echo CHtml::beginForm($this->createUrl('admin/importmsg'), 'post'); ?>

    <?php echo CHtml::activeLabel($form, 'import_type')?>:
    <?php echo CHtml::activeDropDownList($form, 'source_type', $source_types)?>

    <br/>

    <?php echo CHtml::activeLabel($form, 'import_data')?>:<br/>
    <?php echo CHtml::activeTextarea($form, 'import_data', array('style' => 'width: 1000px; height: 100px;')) ?>
    <br/>
    <?php echo CHtml::submitButton('Add')?>
<?php echo CHtml::endForm(); ?>
</div>