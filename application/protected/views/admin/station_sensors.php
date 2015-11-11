<?php
/**
 * @var Station $station
 * @var array|CalculationDBHandler[] $calculations
 */
?>

<?php $this->renderPartial('../_tmpls/admin_station_submenu', array(), false, true ); ?>

<div class="middlewide">
    <div class="middlenarrow">
        <div class="breadcrumbs">
            <?php echo CHtml::link('Stations', array('admin/stations')); ?> &gt;
			<?php echo CHtml::link($station->station_id_code .' - '. $station->display_name, array('admin/StationSave', 'station_id' => $station->station_id)); ?> &gt;
            <?php echo CHtml::link('Sensors', array('admin/sensors', 'station_id' => $station->station_id)); ?>
        </div>
    </div>
</div>

<div class="middlenarrow">
<h1>Sensors <?php echo $station->station_id_code; ?></h1>

    <?php echo CHtml::dropDownList('handler_id', 0, CHtml::listData(SensorDBHandler::getHandlers($station->station_type), 'handler_id', 'display_name'), array('id' => 'select_handler_id')); ?>
    <?php echo CHtml::button('Add', array('onclick' => 'openAddForm($("#select_handler_id").val())')); ?>

		<?php
			if (in_array($station->station_type, array('aws', 'awos')) && is_array($calculations)){
				echo '&nbsp;&nbsp;| Add/ Edit Calculations Parameters:';

				foreach ($calculations as $calculation) {?>
                    &nbsp;&nbsp;
                    <input type="button" value="<?php echo $calculation->display_name; ?>" onclick="document.location.href='<?php echo $this->createUrl('admin/CalculationSave', array('station_id' => $station->station_id, 'handler_id' => $calculation->handler_id)); ?>'" />
        <?php
				}
			}
		?>
    <br/><br/>

    <?php
		if (count($station->sensors) == 0){
			echo 'No any sensors registered for this station.';
		} else {
	?>
        <table class="tablelist">
        <tr>
            <th rowspan="2">No.</th>
            <th rowspan="2">Device ID</th>
            <th rowspan="2">Sensor Name</th>
            <th colspan="2">Main Feature</th>
            <th colspan="3">Filters</th>
            <th rowspan="2">Dew Point?</th>
            <th rowspan="2">MSL?</th>
            <th rowspan="2">Tools</th>
        </tr>
        <tr>
            <th>Name</th>
            <th>Unit</th>
            <th>Min</th>
            <th>Max</th>
            <th>Diff</th>
        </tr>

        <?php foreach ($station->sensors as $key => $sensor) { ?>
            <?php $main_feature = $sensor->main_feature ?>
            <?php $metric = RefbookMeasurementType::model()->with('metricMain')->findByAttributes(['code' => $main_feature->measurement_type_code]); ?>
            <tr class="<?php echo ($key % 2 == 0 ? 'c' : ''); ?>" id="station_sensor_<?php echo $sensor->station_sensor_id ?>">
                <td><?php echo ($key+1); ?>.</td>
                <td><?php echo $sensor->sensor_id_code; ?></td>
                <td><?php echo $sensor->display_name; ?></td>

                <?php if ($main_feature):?>
                    <td><?php echo $main_feature->feature_display_name ?></td>
                    <td><?php echo $metric->metricMain->metric->html_code ?></td>
                    <td><?php echo $main_feature->filter_min ?></td>
                    <td><?php echo $main_feature->filter_max ?></td>
                    <td><?php echo $main_feature->filter_diff ?></td>
                <?php else : ?>
                    <td colspan="5"></td>
                 <?php endif ?>

                <td><?php echo (($sensor->hasCalculation('DewPoint')) ? 'Yes' : 'No'); ?></td>
                <td><?php echo (($sensor->hasCalculation('PressureSeaLevel')) ? 'Yes' : 'No'); ?></td>
                <td>
                    <?php echo CHtml::link('Edit', null, ['onclick' => 'openEditForm(' . $sensor->station_sensor_id . ')']); ?>
                    <?php echo CHtml::link('Delete', array('admin/deleteSensor', 'sensor_id' => $sensor->station_sensor_id), array('onclick' => "return confirm('Are you sure you want to delete sensor ". $sensor->sensor_id_code ."?')")); ?>
                </td>
            </tr>
        <?php } ?>
        </table>

    <?php }?>
</div>
<div id="modal_form">
    <div id="modal_button">
        <?php echo CHtml::button('Save Sensor',['onclick' => 'saveEditForm()']); ?>
        <?php echo CHtml::button('Cancel',['onclick' => 'closeModal()']); ?>
    </div>
</div>
<div id="overlay"></div>

<script type="text/javascript">
    function openAddForm(handler_id) {
        document.location.href='<?php echo $this->createUrl('admin/sensor'); ?>?station_id=<?php echo $station->station_id; ?>&handler_id='+handler_id;
    }

    // For modal
    var model_form = $('#modal_form');

    function showModal(data) {
        if (data.form) {
            $('#overlay').fadeIn(200, function(){
                $(model_form).find('form, h2').remove();
                $(model_form)
                    .prepend(data.form)
                    .css('display', 'block')
                    .animate({opacity: 1, top: '50%'}, 200);
            });
        } else {
            closeModal();
        }
    }
    function closeModal() {
        $(model_form)
            .animate({opacity: 0, top: '30%'}, 200,
                function() {
                    $(this)
                        .css('display', 'none')
                        .css('top', '90%')
                        .find('form, h2').remove();
                    $('#overlay').fadeOut(200);
                }
            )
    }

    function saveEditForm() {
        var msg = $(model_form).find('form').serialize();
        $.ajax({
            type: 'POST',
            url: BaseUrl + '/admin/editsensor/',
            data: msg,
            success: function(json_data) {
                var data = JSON.parse(json_data);
                if (data && data.status == 'save') {
                    $('#station_sensor_' + data.sensor_id).find('td:eq(2)').html(data.sensor_name);
                    closeModal();
                } else {
                    showModal(data);
                }
            },
            error:  function(xhr, str){
            }
        });
    }

    function openEditForm(sensor_id) {
        var ajax_param = {
            type : "POST",
            url  : BaseUrl + '/admin/editsensor/',
            data : { sensor_id: sensor_id }
        };
        $.ajax(ajax_param)
            .done(function(json_data){
                var data = JSON.parse(json_data);
                showModal(data);
            });

    }

    $('#overlay').click( function(){
        closeModal();
    });

    <?php if (It::getMem('sensor_id')): ?>
        openEditForm(<?php echo It::extractMem('sensor_id'); ?>);
    <?php endif; ?>
</script>