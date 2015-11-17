

<div class="middlenarrow" id="stations-overview">
<h1><?php echo It::t('menu_label', 'admin_station_overview'); ?></h1>



<?php if (count($stations) > 0) :?>
    <table class="tablelist" style="width: 500px;">

    <?php
	/**
	 * @var $station Station
	 */
		foreach ($stations as $key => $station) 
		{
	?>
		<tr>
			<th>Station ID</th>
			<th>Color</th>
			<th>Type</th>
			<th>Display Name</th>
			<th>Time Zone</th>
			<th>Communication Type</th>
			<th>Total Sensors</th>
			<th>Tools</th>
		</tr>
        <tr class="<?php echo ($key % 2 == 0 ? 'c' : ''); ?>">
            <td><b><?php echo $station->station_id_code; ?></b></td>
            <td><div style="background:<?php echo $station->color; ?>; width:25px;height:10px;"></div></td>
            <td><?php echo Yii::app()->params['station_type'][$station->station_type]?></td>
            
			<td nowrap><?php echo CHtml::link($station->display_name, array('admin/StationSave', 'station_id' => $station->station_id), array('title' => 'Change Station Details')); ?></td>
			
			<td nowrap><?php echo $station->timezone_id; ?> (GMT <?php echo TimezoneWork::getOffsetFromUTC($station->timezone_id, 1); ?>)</td>
            <td nowrap>
                <?php 
					echo Yii::app()->params['com_type'][$station->communication_type] .' '; 
					
					if (($station->communication_type === 'direct') || ($station->communication_type === 'sms')) 
					{
						echo '('. $station->communication_port .')'; 
					} 
					else if ($station->communication_type === 'tcpip') 
					{
						echo '('. $station->communication_esp_ip .':'. $station->communication_esp_port .')';
					}
					else if ($station->communication_type === 'gprs') 
					{
						echo '('. $station->communication_esp_ip .':'. $station->communication_esp_port .')';
					}
					else if ($station->communication_type === 'server') 
					{
						echo '('. $station->communication_esp_ip .':'. $station->communication_esp_port .')';
					}
				?>
            </td>
            <td><?php echo count($station->sensors); ?></td>
            <td nowrap>
				<?php echo CHtml::link('Change', array('admin/StationSave', 'station_id' => $station->station_id), array('title' => 'Change Station Details')); ?> 
                &nbsp;&nbsp;&nbsp;
				<?php echo CHtml::link('Delete', array('admin/StationDelete', 'station_id' => $station->station_id), array('title' => 'Delete Station', 'onclick' => "return confirm('Do you really want to delete this station and all related sensors?')")); ?>
                &nbsp;&nbsp;&nbsp;
				<?php echo CHtml::link('Sensors', array('admin/Sensors', 'station_id' => $station->station_id), array('title' => 'Work with Sensors')); ?>
				&nbsp;&nbsp;&nbsp;
				<?php echo CHtml::link('Get config', array('admin/stations', 'station_id' => $station->station_id, 'get_config' => 1), array('title' => 'Station ID - Name - Last Update')); ?>
            </td>
        </tr>
			<tr>
				<th><?=$station->getAttributeLabel('station_number')?></th>
				<th><?=$station->getAttributeLabel('wmo_block_number')?></th>
				<th><?=$station->getAttributeLabel('wmo_member_state_id')?></th>
				<th><?=$station->getAttributeLabel('wmo_station_number')?></th>
				<th><?=$station->getAttributeLabel('wmo_originating_centre')?></th>
				<th><?=$station->getAttributeLabel('station_gravity')?></th>
				<th><?=$station->getAttributeLabel('status_message_period')?></th>
				<th><?=$station->getAttributeLabel('event_message_period')?></th>
			</tr>
			<tr>
				<td><?=$station->station_number?></td>
				<td><?=$station->wmo_block_number?></td>
				<td><?=$station->wmo_member_state_id?></td>
				<td><?=$station->wmo_station_number?></td>
				<td><?=$station->wmo_originating_centre?></td>
				<td><?=$station->station_gravity?></td>
				<td><?=Yii::app()->params['status_message_period'][$station->status_message_period]?></td>
				<td><?=Yii::app()->params['event_message_period'][$station->event_message_period]?></td>
			</tr>
		<tr>
			<td colspan="8">
				<?php if (count($station['sensors'])) {?>
					<table class="tablelist">
						<tbody>
						<tr>
							<th colspan="9"><b>Sensors</b> of <?php echo $station->station_id_code; ?></th>
							<th colspan="2">Calculations:</th>
						</tr>
						<tr>
							<th rowspan="2">No.</th>
							<th rowspan="2">Device ID</th>
							<th rowspan="2">Sensor name</th>
							<th colspan="3">Main Feature</th>
							<th colspan="3">Filters</th>
							<th rowspan="2">Dew Point</th>
							<th rowspan="2">Pressure</th>
						</tr>
						<tr>
							<th>Name</th>
							<th>Unit</th>
							<th>Height</th>
							<th>Min</th>
							<th>Max</th>
							<th>Diff</th>
						</tr>
					<?php $key=0; foreach ($station['sensors'] as $sensor) { $key++;?>


						<?php $main_feature = $sensor->main_feature ?>
						<?php $metric = RefbookMeasurementType::model()->with('metricMain')->findByAttributes(['code' => $main_feature->measurement_type_code]); ?>
						<tr class="<?php echo ($key % 2 == 0 ? 'c' : ''); ?>" id="station_sensor_<?php echo $sensor->station_sensor_id ?>">
							<td><?php echo $key; ?>.</td>
							<td><?php echo $sensor->sensor_id_code; ?></td>
							<td><?php echo $sensor->display_name; ?></td>

							<?php if ($main_feature):?>
								<td><?php echo $main_feature->feature_display_name ?></td>
								<td><?php echo $metric->metricMain->metric->html_code ?></td>
								<td>
									<?php if (isset($sensor['handler']['features']['height'])) {
										echo $sensor['handler']['features']['height']->feature_constant_value;
										if (isset($sensor['handler']['features']['height']['metric'])) {
											echo ' '; echo $sensor['handler']['features']['height']['metric']->html_code;
										}
									}
									?>
								</td>
								<td><?php echo $main_feature->filter_min ?></td>
								<td><?php echo $main_feature->filter_max ?></td>
								<td><?php echo $main_feature->filter_diff ?></td>
							<?php else : ?>
								<td colspan="6"></td>
							<?php endif ?>

							<td><?php echo (($sensor->hasCalculation('DewPoint')) ? 'Yes' : 'No'); ?></td>
							<td><?php echo (($sensor->hasCalculation('PressureSeaLevel')) ? 'Yes' : 'No'); ?></td>

						</tr>
					<?php } ?>

						</tbody>
					</table>
				<?php } else {?>

					Station has't any sensors

				<?php } ?>
			</td>
		</tr>
		<tr>
			<td colspan="8">
				<?php if (count($station['station_calculation'])) {?>
				<table class="tablelist">
					<tbody>
					<th colspan="8"><b>Calculations</b> of <?php echo $station->station_id_code; ?></th>
						<?php foreach ($station['station_calculation'] as $calculation) {?>

							<?php if (is_object($calculation['handler'])) {?>
							<tr>
								<td><?=$calculation['handler']->default_prefix?></td>
								<td><?=$calculation['handler']->display_name?></td>
								<td><?=$calculation['handler']['metric']->html_code?></td>
								<td></td>
							</tr>
							<?php } ?>
						<?php } ?>

					</tbody>
				</table>
				<?php } else {?>

					Station has't any calculations
				<?php } ?>
			</td>
		</tr>
		<tr>
			<th colspan="8" style="background: #fff;">&nbsp;</th>
		</tr>

    <?php
		}
	?>
    </table>
<?php endif; ?>

</div>

