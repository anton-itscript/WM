<div class="breadcrumbs">
    <a href="<?=$this->createUrl('admin/stations')?>">Stations</a> &gt; 
    <a href="<?=$this->createUrl('admin/StationSave', array('station_id' => $station->station_id))?>"><?=$station->station_id_code?> - <?=$station->display_name?></a> &gt; 
    <a href="<?=$this->createUrl('admin/sensors', array('station_id' => $station->station_id))?>">Sensors</a> &gt; 
    <?if ($sensor->station_sensor_id) { ?>
        Edit
    <?} else {?>
        Add
    <?}?>
</div>

<? $form=$this->beginWidget('CActiveForm', array(
        'id'                   => 'sensor-form',
        'enableAjaxValidation' => true,
        'action'               => Yii::app()->createUrl('admin/sensor'),
    )); ?>
<div id="sensor_subform" style="margin-top: 10px;">
    

    <?php $this->renderPartial(
            'station_sensor_subform', 
            array(
                'station' => $station,
                'sensor'  => $sensor,
                'sensor_features' => $sensor_features,
                'validated' => $validated,
                'saved' => $saved,
                'handler_db' => $handler_db,
                'possible_code_id' => $possible_code_id
            ),
            false, true
    ); ?>
</div>

<?= CHtml::ajaxSubmitButton(
    "Save sensor", 
    CController::createUrl('admin/sensor'), 
    array('update' => '#sensor_subform', 'data' => 'js:"submit_action=1&"+jQuery(this).parents("form").serialize()'), 
    array('name' => 'submit_sensor_data')
);
?>
<?= CHtml::button('Cancel', array('onclick' => 'document.location.href="'.$this->createUrl('admin/sensors', array('station_id' => $station->station_id)).'"'))?>


<?php $this->endWidget(); ?>


<script type="text/javascript">
    function loadSensorForm() {
        $.ajax({
          type: 'POST',
          url: "<?=Yii::app()->createUrl('admin/sensor')?>",
          data: $('#sensor-form').serialize(),
          success: function(response){$('#sensor_subform').html(response)}
        });        
        
    }
</script>