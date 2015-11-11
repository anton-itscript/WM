<?php
	$visibilityClass = (isset($renderData['VisibilityAWS']) ? 'VisibilityAWS' : 'VisibilityAwsDlm13m'); 
	
	$visibilityDivClass = ($visibilityClass === 'VisibilityAWS' ? 'visibility' : 'visibility-dlm13m'); 
?>

<div class="data_box <?php echo $visibilityDivClass; ?>">
	
	<div class="header"><?php echo It::t('home_aws', 'single__block_vis'); ?></div>
	
	<div class="content">
		<?php 
			if (isset($renderData[$visibilityClass]))
			{
				if (count($renderData[$visibilityClass]) > 1) 
				{
		?>
			<a class="list_left disabled" href="#"></a>
			<a class="list_right" href="#"></a>
		<?php 
				}

				foreach($renderData[$visibilityClass] as $key => $renderDataRecord) 
				{
					$visibillityData = ($visibilityClass === 'VisibilityAWS' ? $renderDataRecord : $renderDataRecord['visibility_1']);

					$hasFilterErrors = isset($visibillityData['last_filter_errors']);	
		?>
		<table style="<?php echo ($key ? 'display:none;' : ''); ?>">
			<tr>
				<td>&nbsp;</td>

				<td colspan="2" title="<?php echo $renderDataRecord['sensor_display_name'] .' - '. $renderDataRecord['sensor_id_code']; ?> ">
					<?php echo It::createTextPreview($renderDataRecord['sensor_display_name'], 15, '...'); ?>
				</td>
			</tr>    
			<tr>
				<th><?php echo It::t('home_aws', 'single__block_vis_last'); ?></th>

				<td <?php if ($hasFilterErrors) { ?> title="<?php echo implode("; ", $visibillityData['last_filter_errors']); ?>" <?php } ?>>
					
					<div class="cover <?php echo ($hasFilterErrors ? 'error' : ''); ?>">
						
						<div class="<?php echo $visibillityData['change']; ?>">
							<?php echo $visibillityData['last']; ?>
						</div>
					</div>
				</td>
				<td><?php echo $visibillityData['metric_html_code']; ?></td>
			</tr>
			<tr>
				<th><?php echo It::t('home_aws', 'single__block_vis_min'); ?></th>

				<td>
					<div class="cover">
						<div><?php echo $visibillityData['min24']; ?></div>
					</div>
				</td>
				<td><?php echo $visibillityData['metric_html_code']; ?></td>
			</tr>
			<tr>
				<th><?php echo It::t('home_aws', 'single__block_vis_max'); ?></th>

				<td>
					<div class="cover">
						<div><?php echo $visibillityData['max24']; ?></div>
					</div>
				</td>
				<td><?php echo $visibillityData['metric_html_code']; ?></td>
			</tr>
			
			<?php
					if (isset($visibillityData['status']))
					{
			?>
			<tr>
				<th><?php echo It::t('home_aws', 'single__block_vis_status'); ?></th>

				<td>
					<div class="cover">
						<div><?php echo $visibillityData['status']; ?></div>
					</div>
				</td>
				<td>&nbsp;</td>
			</tr>
			<?php
					}
			
					if (isset($renderDataRecord['extinction']))
					{
			?>
			<tr>
				<th><?php echo It::t('home_aws', 'single__block_vis_extinction'); ?></th>

				<td>
					<div class="cover">
						<div><?php echo $renderDataRecord['extinction']['last']; ?></div>
					</div>
				</td>
				<td>&nbsp;</td>
			</tr>
			<?php
					}
			?>
		</table>
		<?php 
					
				}
			}
		?>
	</div>               
</div>