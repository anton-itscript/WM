    <?php if (!$validated) {?>
            <blockquote class="note">
            <p>
                <b>Check fields marked with red!</b>
            </p>
            </blockquote>
    <?php }?>

    <input type="hidden" name="station_id" value="<?php echo $station->station_id?>" />
    <input type="hidden" name="sensor_id" value="<?php echo $sensor->station_sensor_id?>" />
    <input type="hidden" name="handler_id" value="<?php echo $handler_db->handler_id?>" />
    
    <table cellpadding="5" cellspacing="0" class="tableform">
    <tr>
        <th><?php echo CHtml::activeLabel($sensor, 'handler_id')?>:</th>
        <td><b><u><?php echo $handler_db->display_name?></u></b></td>
        <td>&nbsp;</td>
        <th><?php echo CHtml::activeLabel($sensor, 'sensor_id_code')?>:</th>
        <td>
            <?php echo   CHtml::activeDropDownList($sensor, 'sensor_id_code', $possible_code_id, array('style'=>'width: 70px;'))?>
            <?php echo CHtml::error($sensor,'sensor_id_code'); ?>
        </td>
        <td>&nbsp;</td>
        <th><?php echo CHtml::activeLabel($sensor, 'display_name')?>:</th>
        <td>
            <?php echo CHtml::activeTextField($sensor, 'display_name', array('style' => 'width: 250px;')) ?>
            <?php echo CHtml::error($sensor,'display_name'); ?>
        </td>
        
    </tr>
    </table>

        
    
        <?php if ($handler_db->handler_id) {?>
            <blockquote class="tip">
            <p>
                Handler "<b><?php echo $handler_db->display_name?></b>" : <br/>
                <?php echo $handler_db->description?>
            </p>
            </blockquote>
        <?php } else {?>
            <blockquote class="note">
            <p>
                <b>Please choose proper handler for your Sensor.</b> <br/>
                Handler - is a module that knows all features for particular sensor, can load important fields to this form. <br/>
                Also Handler knows how to process sensor's information from messages coming from RG/AWS station.
            </p>
            </blockquote>
        <?php }?>
    
    
    
    <?php if ($sensor_features) {?>
        
        <?php foreach ($sensor_features as $key => $value) {?>
            <table class="tableform">
            <tr>
                <th style="width: 100px;"><b><?php if ($value->is_constant) {?>Feature<?php }else{?>Measurement<?php }?>:</b></th>
                <td nowrap>
                    
                    <?php echo  $value->feature_display_name ?>:
                    
                    <?php if ($value->is_constant) {?>
                        <?php echo CHtml::activeTextField($value, '['.$key.']feature_constant_value', array('style' => 'width: 70px;')) ?>
                    <?php }?>
                    
                    <?php if ($value->metrics_list) {?>
                        <?php echo CHtml::activeDropDownList($value, '['.$key.']metric_id', $value->metrics_list, array('encode' => false)); ?>                    
                    <?php }?>
                </td>
                <td>
                    <?php echo CHtml::error($value,'metric_id'); ?>
                    <?php echo CHtml::error($value,'feature_constant_value'); ?>
                </td>
            </tr>   
            </table>
            
            <?php  if ($value->has_filter_min || $value->has_filter_max || $value->has_filter_diff) {?>
                <table class="tableform">
                <tr>
                    <th style="width: 100px;"><b>Filters &amp; alerts:</b></th>
                    <?php if ($value->has_filter_min) {?>
                    <td style="padding-right:5px;"><?php echo CHtml::activeLabel($value, 'filter_min')?></td>
                    <td style="padding-right:20px;"><?php echo CHtml::activeTextField($value, '['.$key.']filter_min', array('style' => 'width: 70px;')) ?></td>
                    <?php }?>
                    <?php if ($value->has_filter_max) {?>
                    <td style="padding-right:5px;"><?php echo CHtml::activeLabel($value, 'filter_max')?></td>
                    <td style="padding-right:20px;"><?php echo CHtml::activeTextField($value, '['.$key.']filter_max', array('style' => 'width: 70px;')) ?></td>
                    <?php }?>
                    <?php if ($value->has_filter_diff) {?>
                    <td style="padding-right:5px;"><?php echo CHtml::activeLabel($value, 'filter_diff')?></td>
                    <td style="padding-right:20px;"><?php echo CHtml::activeTextField($value, '['.$key.']filter_diff', array('style' => 'width: 70px;')) ?></td>
                    <?php }?>        
                    <td style="width: 460px;">
                        <blockquote class="tip">
                            <?php if ($value->is_cumulative) {?>
                            <p>Set  values for a <b>1 HOUR</b> period. If we receive rain for a different period -will calculate the appropriate filter-value.<br/><span style="font-size: 10px;">E.g. if we receive a value for 120 minutes instead of 60 minutes, the observed value will be compared with alert value * 2.</span></p>
                            <?php } else {?>
                            <p>Doesn't depend on period of measurement. Is used to filter incoming value.</p>
                            <?php }?>
                        </blockquote>
                    </td>
                </tr>
                </table>
            <?php }?>
    
            <div style="border-top: 1px solid #ccc; margin-top: 10px; margin-bottom:10px;"></div>    
        <?php }?>
    <?php  } ?>
            
    <?php if ($sensor_extra_features) {?>
            <?php foreach ($sensor_extra_features as $key => $value) {?>
                <table class="tableform">
                <tr>
                    <th style="width: 100px;"><b><?php echo  $value->feature_display_name ?>:</b></th>
                    
                    <td nowrap>
                        
                        <?php if ($value->metrics_list) {?>
                            <?php echo CHtml::activeDropDownList($value, 'metric_id', $value->metrics_list, array('encode' => false, 'style' => 'width: 100px;')); ?>                    
                        <?php }?>
                    </td>
                </tr>   
                </table>
            <?php }?>
    <?php }?>

            

<script type="text/javascript">        
$(document).ready(function() {
    <?php if ($handler_db->handler_id) {?>
        $('#submit_sensor_data').show();
    <?php } else {?>
        $('#submit_sensor_data').hide();    
    <?php  } ?>

    <?php if ($saved) {?>
        document.location.href = "<?php echo Yii::app()->createUrl('admin/sensors', array('station_id' => $station->station_id))?>";
    <?php }?>
});        
</script>
