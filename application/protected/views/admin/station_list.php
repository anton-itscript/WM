

<div class="middlenarrow">
<h1>Stations</h1>

<?php echo CHtml::link('Add new Station', array('admin/StationSave')); ?><br /><br />

<?php if (count($stations) > 0) :?>
    <table class="tablelist" style="width: 500px;">
    <tr>
        <th>Station ID</th>
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
            <td><?php echo $station->station_id_code; ?></td>
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
    <?php 
		}
	?>
    </table>
<?php endif; ?>
	<?php echo CHtml::errorSummary($importStations); ?>
	<h1>Import Stations</h1>
	<?php echo CHtml::beginForm($this->createUrl('admin/stations'), 'post', array('id' => 'formadminstations','enctype'=>'multipart/form-data')); ?>
	<?php echo CHtml::hiddenField('import_stations',1)?>
	<table class="">
		<tr>
			<th><?php echo CHtml::activeLabel($importStations ,'files')?>:</th>
			<td><?php echo CHtml::activeFileField($importStations, 'files[]',array('multiple'=>'multiple'))?></td>
		</tr>
	</table>
	<br/><br/>
	<?php  echo CHtml::submitButton('Import Stations')?>
	<?php echo CHtml::endForm(); ?>
</div>