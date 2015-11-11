

<div class="middlewide">
    <div class="middlenarrow">
        <div class="breadcrumbs">
			<?php echo CHtml::link('Stations', array('admin/stations')); ?> &gt;
			<?php echo CHtml::link($station->station_id_code .' - '. $station->display_name, array('admin/StationSave', 'station_id' => $station->station_id)); ?> &gt;
            <?php echo CHtml::link('Sensors', array('admin/sensors', 'station_id' => $station->station_id)); ?> &gt;

            <?php if ($sensor->station_sensor_id) { ?>
                <a href="<?php echo $this->createUrl('admin/sensors', array('sensor_id' => $sensor->station_sensor_id)); ?>">Edit</a>
            <?php } else {?>
                <a href="<?php echo $this->createUrl('admin/sensors', array('station_id' => $station->station_id, 'handler_id' => $handler_db->handler_id)); ?>">Add</a>
            <?php }?>
        </div>
    </div><!--/div.middlenarrow -->
</div><!--/div.middlewide-->

<div class="middlenarrow">
    <h1><?php if ($sensor->station_sensor_id) { ?>Change Sensor Params<?php } else {?>Add New Sensor<?php }?></h1>
<?php echo CHtml::beginForm(Yii::app()->createUrl('admin/sensor', array('station_id' => $station->station_id)), 'post'); ?>

<div id="sensor_subform" style="margin-top: 10px;">

    <?php if (!$validated) : ?>
            <blockquote class="note">
            <p>
                <b>Check fields marked with red!</b>
            </p>
            </blockquote>
    <?php endif; ?>

    <input type="hidden" name="station_id" value="<?php echo $station->station_id?>" />
    <input type="hidden" name="sensor_id" value="<?php echo $sensor->station_sensor_id?>" />
    <input type="hidden" name="handler_id" value="<?php echo $handler_db->handler_id?>" />

    <table class="formtable">
    <tr>
        <th><?php echo CHtml::activeLabel($sensor, 'handler_id'); ?>:</th>
        <td><?php echo $handler_db->display_name?></td>
        <td>&nbsp;</td>
        <th><?php echo CHtml::activeLabel($sensor, 'sensor_id_code'); ?>:</th>
        <td>
            <?php echo CHtml::activeDropDownList($sensor, 'sensor_id_code', $possible_code_id, array('style'=>'width: 70px;')); ?>
            <?php echo CHtml::error($sensor,'sensor_id_code'); ?>
        </td>
        <td>&nbsp;</td>
        <th><?php echo CHtml::activeLabel($sensor, 'display_name'); ?>:</th>
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
                <?php echo $handler_description?>
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
            <table class="formtable">
            <tr>
                <th style="width: 100px;"><b><?php if ($value->is_constant) {?>Feature<?php }else{?>Measurement<?php }?>:</b></th>
                <th nowrap><?php echo  $value->feature_display_name ?>:</th>
                <td nowrap>
                    <?php if ($value->is_constant) {?>
                        <?php if ($value->possible_constant_values) { ?>
                            <?php echo CHtml::activeDropDownList($value, '['.$key.']feature_constant_value', $value->possible_constant_values, array('encode' => false)); ?>
                        <?php } else {?>
                            <?php echo CHtml::activeTextField($value, '['.$key.']feature_constant_value', array('style' => 'width: 70px;')) ?>
                        <?php }?>
                    <?php }?>

                    <?php if ($value->metrics_list) {?>
                        <?php echo CHtml::activeDropDownList($value, '['.$key.']metric_id', $value->metrics_list, array('encode' => false, 'style' => 'width: 70px;')); ?>
                    <?php } else {?>
                        This measurement is treated without metric.
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
                    <th style="width: 100px;"><b>Filters &amp; alerts:</b></th>
                    <?php if ($value->has_filter_min) {?>
                    <th nowrap ><?php echo CHtml::activeLabel($value, 'filter_min'); ?></th>
                    <td style="padding-right:20px;"><?php echo CHtml::activeTextField($value, '['.$key.']filter_min', array('style' => 'width: 70px;')) ?></td>
                    <?php }?>
                    <?php if ($value->has_filter_max) {?>
                    <th nowrap><?php echo CHtml::activeLabel($value, 'filter_max'); ?></th>
                    <td style="padding-right:20px;"><?php echo CHtml::activeTextField($value, '['.$key.']filter_max', array('style' => 'width: 70px;')) ?></td>
                    <?php }?>
                    <?php if ($value->has_filter_diff) {?>
                    <th nowrap><?php echo CHtml::activeLabel($value, 'filter_diff'); ?></th>
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

    <?php if (isset($sensor_extra_features)) {?>
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


</div>
<?php echo CHtml::submitButton('Save Sensor'); ?>

<?php echo  CHtml::button('Cancel', array('onclick' => 'document.location.href="'.$this->createUrl('admin/sensors', array('station_id' => $station->station_id)).'"')); ?>

 <?php echo CHtml::endForm(); ?>
</div>

<script type="text/javascript">
    function loadSensorForm() {
        $.ajax({
          type: 'POST',
          url: "<?php echo Yii::app()->createUrl('admin/sensor'); ?>",
          data: $('#sensor-form').serialize(),
          success: function(response){$('#sensor_subform').html(response)}
        });

    }
</script>