

<div class="middlenarrow" id="stations-overview">
<h1>Stations Overview</h1>



<?php if (count($stations) > 0) :?>
    <table class="tablelist" style="width: 500px;">
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
    <?php 
		foreach ($stations as $key => $station) 
		{
	?>
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
				<td colspan="8">
				<table class="tablelist">
					<tbody>
					<th colspan="8"><b>Sensors</b> of <?php echo $station->station_id_code; ?></th>
					<?php if (count($station['sensors'])) {?>
						<?php foreach ($station['sensors'] as $sensor) {?>


								<tr>
									<td><?=$sensor->sensor_id_code?></td>
									<td><?=$sensor->display_name?></td>
									<td>Handler name: <?=$sensor['handler']->display_name?></td>
									<td><?php //echo $sensor['handler']['features']['metric']->html_code?></td>
								</tr>

						<?php } ?>
					<?php } else {?>
						<tr>
							<td colspan="3">Station has't any sensors</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
				</td>
			</tr>
			<tr>
				<td colspan="8">
					<table class="tablelist">
						<tbody>
						<th colspan="8"><b>Calculations</b> of <?php echo $station->station_id_code; ?></th>
						<?php if (count($station['station_calculation'])) {?>
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
						<?php } else {?>
							<tr>
								<td colspan="3">Station has't any calculations</td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</td>
			</tr>
    <?php 
		}
	?>
    </table>
<?php endif; ?>

</div>