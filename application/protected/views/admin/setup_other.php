
<div class="middlenarrow">
    <h1>Other Settings</h1>

    <?php $timezones = TimezoneWork::prepareList();?>
    <?php echo  CHtml::beginForm($this->createUrl('admin/setupother'), 'post'); ?>

        <table class="formtable">
        <tr>
            <td><?php echo CHtml::activeCheckbox($settings, 'overwrite_data_on_import') ?></td>
            <td><?php echo CHtml::activeLabel($settings, 'overwrite_data_on_import')?></td>
        </tr>
        <tr>
            <td><?php echo CHtml::activeCheckbox($settings, 'overwrite_data_on_listening') ?></td>
            <td><?php echo CHtml::activeLabel($settings, 'overwrite_data_on_listening')?></td>
        </tr>
        </table>
        
        <table class="formtable">
        <tr>
            <td><?php echo CHtml::activeLabel($settings, 'current_company_name')?></td>
            <td>
                <?php echo CHtml::activeTextField($settings, 'current_company_name', array('style' => 'width: 350px;'))?>
                 <?php echo CHtml::error($settings,'current_company_name'); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo CHtml::activeLabel($settings, 'scheduled_reports_path')?></td>
            <td>
                <?php
                if (DOCKER) {
                    echo CHtml::activeTextField($settings, 'scheduled_reports_path', array('style' => 'width: 350px;','disabled'=>'disabled'));
                } else {
                    echo CHtml::activeTextField($settings, 'scheduled_reports_path', array('style' => 'width: 350px;'));
                }
                ?>

                <?php echo CHtml::error($settings,'scheduled_reports_path'); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo CHtml::activeLabel($settings, 'local_timezone_id')?></td>
            <td>
                <?php echo CHtml::activeDropDownList($settings, 'local_timezone_id', $timezones, array('style' => 'width: 320px;'))?> Set the local time.
                <?php echo CHtml::error($settings,'local_timezone_id'); ?>
            </td>
        </tr>
        
        <tr><th colspan="2"><br/>XML Messages Settings:</th></tr>
        <tr>
            <td><?php echo CHtml::activeLabel($settings, 'xml_messages_path')?></td>
            <td>
                <?php
                if (DOCKER) {
                    echo CHtml::activeTextField($settings, 'xml_messages_path', array('style' => 'width: 350px;','disabled'=>'disabled'));
                } else {
                    echo CHtml::activeTextField($settings, 'xml_messages_path', array('style' => 'width: 350px;'));
                }
                ?> <br>Collect incoming XML messages in this folder.
                <?php echo CHtml::error($settings,'xml_messages_path'); ?>
            </td>
        </tr>   
        <tr>
            <td><?php echo CHtml::activeLabel($settings, 'xml_check_frequency')?></td>
            <td>
                <?php echo CHtml::activeDropDownList($settings, 'xml_check_frequency', Yii::app()->params['xml_check_frequency'], array('style' => 'width: 60px;'))?> 
                (How often script should check XML folder?)
                <?php echo CHtml::error($settings,'xml_check_frequency'); ?>
            </td>
        </tr>        
        </table>    
        <?php echo CHtml::submitButton('Save')?>

    <?php echo  CHtml::endForm(); ?>
    
    
</div>
<script type="text/javascript">
    $( document ).ready(function() {
        $('.selectBox-options').css({ maxHeight: "200px"});
    });
</script>