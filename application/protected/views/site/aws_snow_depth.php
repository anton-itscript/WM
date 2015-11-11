<div class="data_box snow_depth">
	<div class="header"><?php echo It::t('home_aws', 'single__block_snow_depth'); ?></div>
	<div class="content">

		<!-- if there are more than one snow depth sensors - show arrows to list blocks --> 
		<?php 
			if (count($renderData['SnowDepthAwsDlm13m']) > 1) 
			{
		?>
		<a class="list_left disabled" href="#"></a>
		<a class="list_right" href="#"></a>
		<?php 
			}
		?>

		<?php 
			foreach($renderData['SnowDepthAwsDlm13m'] as $key => $renderDataRecord) 
			{
				$hasFilterErrors = isset($renderDataRecord['last_filter_errors']);
		?>
		<table style="<?php echo ($key ? 'display:none;' : ''); ?>">
			<tr>
				<td>&nbsp;</td>
				<td colspan="2" title="<?php echo ($renderDataRecord['sensor_display_name'] .' - '. $renderDataRecord['sensor_id_code']); ?> ">
					<?php echo It::createTextPreview($renderDataRecord['sensor_display_name'], 15, '...'); ?>
				</td>
			</tr>    
			<tr>
				<th><?php echo It::t('home_aws', 'single__block_pressure_last'); ?></th>
				<td <?php if ($hasFilterErrors) echo 'title="'. implode("; ", $renderDataRecord['last_filter_errors']) .'"'; ?>>
					<div class="cover <?php echo ($hasFilterErrors ? 'error' : ''); ?>">
						<div class="<?php echo $renderDataRecord['change']; ?>">
							<?php echo $renderDataRecord['last']; ?>
						</div>
					</div>
				</td>
				<td><?php echo $renderDataRecord['metric_html_code']; ?></td>
			</tr>
			<tr title="<?php echo $renderDataRecord['mami_title'];?>">
				<th><?php echo It::t('home_aws', 'single__block_pressure_min'); ?></th>
				<td><div class="cover"><div><?php echo $renderDataRecord['min24']; ?></div></div></td>
				<td><?php echo $renderDataRecord['metric_html_code']; ?></td>
			</tr>
			<tr title="<?php echo $renderDataRecord['mami_title'];?>">
				<th><?php echo It::t('home_aws', 'single__block_pressure_max'); ?></th>
				<td><div class="cover"><div><?php echo $renderDataRecord['max24']; ?></div></div></td>
				<td><?php echo $renderDataRecord['metric_html_code']; ?></td>
			</tr>
			<tr>
				<th><?php echo It::t('home_aws', 'single__yesterday'); ?></th>
			</tr>
			<tr title="<?php echo $renderDataRecord['mami_title_y'];?>">
				<th><?php echo It::t('home_aws', 'single__block_pressure_min'); ?></th>
				<td><div class="cover"><div><?php echo $renderDataRecord['min24_y']; ?></div></div></td>
				<td><?php echo $renderDataRecord['metric_html_code']; ?></td>
			</tr>
			<tr title="<?php echo $renderDataRecord['mami_title_y'];?>">
				<th><?php echo It::t('home_aws', 'single__block_pressure_max'); ?></th>
				<td><div class="cover"><div><?php echo $renderDataRecord['max24_y']; ?></div></div></td>
				<td><?php echo $renderDataRecord['metric_html_code']; ?></td>
			</tr>
		</table>
		<?php 
			}
		?>
	</div>
</div>