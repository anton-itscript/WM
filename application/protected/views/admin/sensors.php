<div class="breadcrumbs">
<a href="<?php echo $this->createUrl('admin/stations')?>">Stations</a> &gt; <a href="<?php echo $this->createUrl('admin/StationSave', array('station_id' => $station->station_id))?>"><?php echo $station->station_id_code?> - <?php echo $station->display_name?></a> &gt; Sensors
</div>

<h1>Sensors <?php echo $station->station_id_code ?></h1>

    <?php if ($station->station_type == 'rain') {?>
        Rain Gauge can have only 1 sensor.
    <?php }?>

    <table class="rgtable">
    <tr>
        <th>Code</th>
        <th>Type</th>
        <th>Tools</th>
    </tr>
    </table>

    <?php foreach ($sensors as $key => $value) {?>
    <div class="form_box">
    <?php echo CHtml::beginForm($this->createUrl('admin/sensors', array('station_id' => $station->station_id)), 'post'); ?>
    <input type="hidden" name="station_sensor_id" value="<?php echo $value->station_sensor_id?>" />
    <table class="tableform">
    <tr><td colspan="4"><h1>Sensor General Info:</h1></td></tr>
    <tr>
        <th><?php echo CHtml::activeLabel($value, 'sensor_id_code')?></th>
        <th><?php echo CHtml::activeLabel($value, 'display_name')?></th>
        <th><?php echo CHtml::activeLabel($value, 'bucket_size')?></th>
        <th>&nbsp;</th>
    </tr>
    <tr>
        <td><?php echo CHtml::activeTextField($value, 'sensor_id_code') ?></td>
        <td><?php echo CHtml::activeTextField($value, 'display_name') ?></td>
        <td><?php echo CHtml::activeDropDownList($value, 'bucket_size', Yii::app()->params['bucket_sizes']) ?></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td><?php echo CHtml::error($value,'sensor_id_code'); ?></td>
        <td><?php echo CHtml::error($value,'display_name'); ?></td>
        <td><?php echo CHtml::error($value,'bucket_size'); ?></td>
        <td>&nbsp;</td>
    </tr>
    <tr><td colspan="4"><h1>Filters:</h1></td></tr>
    <tr>
        <td>
            <?php echo CHtml::activeLabel($value, 'filter_limit_min') ?><br/> <?php echo CHtml::activeTextField($value, 'filter_limit_min', array('style' => 'width: 100px;')) ?>
        </td>

        <td>
            <?php echo CHtml::activeLabel($value, 'filter_limit_max') ?> <br/> <?php echo CHtml::activeTextField($value, 'filter_limit_max', array('style' => 'width: 100px;')) ?>
        </td>
        <td>
            <?php echo CHtml::activeLabel($value, 'filter_limit_diff') ?> <br/> <?php echo CHtml::activeTextField($value, 'filter_limit_diff', array('style' => 'width: 100px;')) ?>
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td colspan="4"><?php echo CHtml::submitButton($value->station_sensor_id ? 'Update' : 'Add')?></td>
    </tr>

    </table>

    <?php echo CHtml::endForm(); ?>
    </div>
    <?php }?>