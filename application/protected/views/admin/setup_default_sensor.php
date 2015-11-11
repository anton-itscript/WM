
<div class="middlewide">
    <div class="middlenarrow">
        <div class="breadcrumbs">
			<?php echo CHtml::link('Default Sensors Parameters', array('admin/setupsensors')); ?> &gt;
			<?php echo CHtml::link($handler_db->display_name, array('admin/setupsensors', 'handler_id' => $handler_db->handler_id)); ?>
        </div>
    </div>
</div>

<div class="middlenarrow">
    <h1>Edit Default Sensor Params</h1>
    
    <?php echo CHtml::beginForm($this->createUrl('admin/setupsensor', array('handler_id' => $handler_db->handler_id)), 'post') ?>
    
	<?php if (!$validated) { ?>
        <blockquote class="note">
            <p><b>Check fields marked with red!</b></p>
        </blockquote>
    <?php } ?>

    <input type="hidden" name="handler_id" value="<?php echo $handler_db->handler_id; ?>" />
    
    <table class="formtable">
    <tr>
        <th>Handler Name:</th>
        <td><input type="text" value="<?php echo $handler_db->display_name; ?>" disabled /></td>
        <th>Sensor ID-Prefix:</th>
        <td><input type="text" value="<?php echo $handler_db->default_prefix; ?>" disabled style="width:40px;" /></td>
        <th>Sensor Default Name:</th>
        <td>
            <?php echo CHtml::activeTextField($handler_db,'handler_default_display_name'); ?>
            <?php echo CHtml::error($handler_db,'handler_default_display_name'); ?>
        </td>
        <td>&nbsp;</td>
        <?php if($arrh){?>
            <th>Start Time:</th>
            <td>
                <?php echo CHtml::activeDropDownList($handler_db, 'start_time', $arrh, array('style' => 'width: 25px;')); ?>
            </td>
        <?php } ?>
    </tr>

    </table>
    
    <blockquote class="tip">
    <p>
        Handler "<b><?php echo $handler_db->display_name; ?></b>" : <br/>
        <?php echo $handler_description; ?>
    </p>
    </blockquote>

    <?php if ($sensor_features) { ?>
        <?php foreach ($sensor_features as $key => $value) { ?>
            <table class="formtable">
            <tr>
                <th style="width: 100px;"><b><?php if ($value->is_constant) {?>Feature<?php }else{?>Measurement<?php }?>:</b></th>
                <th nowrap><?php echo  $value->feature_display_name ?>:</th>
                <td nowrap>
                    <?php if ($value->is_constant) {?>
                        <?php echo CHtml::activeTextField($value, '['.$key.']feature_constant_value', array('style' => 'width: 70px;')) ?>
                    <?php }?>
                    
                    <?php if ($value->metrics_list) {?>
                        <select disabled style="width: 70px"><option><?php echo $value->metrics_list; ?></option></select>
                    <?php } else {?>
                        This measurement has no metric.
                    <?php }?>
                        
                    <?php if ($value->comment) {?>
                        (<?php echo $value->comment ?>)
                    <?php }?>    
                </td>
                <td>
                    <?php echo CHtml::error($value,'metric_id'); ?>
                    <?php echo CHtml::error($value,'feature_constant_value'); ?>
                </td>
            </tr>   
            </table>
            
            <?php  if ($value->has_filter_min || $value->has_filter_max || $value->has_filter_diff) {?>
                <table class="formtable">
                <tr>
                    <th style="width: 100px;"><b>Filters &amp; Alerts:</b></th>
                    <?php if ($value->has_filter_min) {?>
                    <th nowrap><?php echo CHtml::activeLabel($value, 'filter_min')?></th>
                    <td style="padding-right:20px;"><?php echo CHtml::activeTextField($value, '['.$key.']filter_min', array('style' => 'width: 70px;')) ?></td>
                    <?php }?>
                    <?php if ($value->has_filter_max) {?>
                    <th nowrap><?php echo CHtml::activeLabel($value, 'filter_max')?></th>
                    <td style="padding-right:20px;"><?php echo CHtml::activeTextField($value, '['.$key.']filter_max', array('style' => 'width: 70px;')) ?></td>
                    <?php }?>
                    <?php if ($value->has_filter_diff) {?>
                    <th nowrap><?php echo CHtml::activeLabel($value, 'filter_diff')?></th>
                    <td style="padding-right:20px;"><?php echo CHtml::activeTextField($value, '['.$key.']filter_diff', array('style' => 'width: 70px;')) ?></td>
                    <?php }?>        
                    <td style="width: 460px;">
                        <blockquote class="tip">
                            <?php if ($value->is_cumulative) {?>
                                <p>Set  values for a <b>1 HOUR</b> period. If we receive rain for a different period -will calculate the appropriate filter-value.<br/><span style="font-size: 10px;">E.g. if we receive a value for 120 minutes instead of 60 minutes, the observed value will be compared with alert value * 2.</span></p>
                            <?php } else {?>
                            <p>Independent from the period of measurement. Filters incoming data.</p>
                            <?php }?>
                        </blockquote>
                    </td>
                </tr>
                </table>
            <?php }?>
    
            <div style="border-top: 1px solid #ccc; margin-top: 10px; margin-bottom:10px;"></div>    
        <?php }?>
    <?php  } ?>
            
    <?php if (isset($sensor_extra_features)) {?>
            <?php foreach ($sensor_extra_features as $key => $value) {?>
                <table class="tableform">
                <tr>
                    <th style="width: 100px;"><b><?php echo  $value->feature_display_name; ?>:</b></th>
                    
                    <td nowrap>
                        
                        <?php if ($value->metrics_list) {?>
                            <?php echo CHtml::activeDropDownList($value, 'metric_id', $value->metrics_list, array('encode' => false, 'style' => 'width: 70px;')); ?>                    
                        <?php }?>
                    </td>
                </tr>   
                </table>
            <?php }?>
    <?php }?>
    <?php echo CHtml::submitButton('Save')?>

<?php echo  CHtml::endForm(); ?>

</div>
