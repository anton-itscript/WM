<div class="data_box sky-conditions">
<div class="header"><?php echo It::t('home_aws', 'single__block_sky_conditions'); ?></div>
<div class="content">
	
	<?php 
		if (count($renderData) > 1)
		{
	?>
		<a class="list_left disabled" href="#"></a>
		<a class="list_right" href="#"></a>
	<?php 
		}
	
		foreach($renderData as $key => $value) 
		{
	?>
		<table style="<?php echo ($key ? 'display:none;' : ''); ?>">
			<tr>
				<td>&nbsp;</td>
				<td colspan="5" title="<?php echo ($value['sensor_display_name'] .' - '. $value['sensor_id_code']); ?> "><?php echo It::createTextPreview($value['sensor_display_name'], 45, '...'); ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<th style="text-align: center;">#1</th>
				<th>&nbsp;</th>
				<th style="text-align: center;">#2</th>
				<th>&nbsp;</th>
				<th style="text-align: center;">#3</th>
				<th>&nbsp;</th>
				<th style="text-align: center;">#4</th>
				<th>&nbsp;</th>
				<th style="text-align: center;"><?php echo It::t('home_aws', 'single__block_sky_conditions_total'); ?></th>
				<th>&nbsp;</th>
			</tr>
			<tr>
				<th style="white-space: nowrap;"><?php echo It::t('home_aws', 'single__block_sky_conditions_height'); ?></th>
				
				<?php 
					$features = array(
						'cloud_amount_height_1',
						'cloud_amount_height_2',
						'cloud_amount_height_3',
						'cloud_amount_height_4',
					);
					
					foreach ($features as $feature)
					{
						$hasFilterErrors = isset($value[$feature]['last_filter_errors']);
				?> 
				<td <?php if ($hasFilterErrors) echo 'title="'. implode("; ", $value[$feature]['last_filter_errors']) .'"'; ?>>
					
					<div class="cover <?php if ($hasFilterErrors) echo 'error'; ?>">
						
						<div class="<?php echo $value[$feature]['change']?>">
							&nbsp;<?php echo $value[$feature]['last']?>
						</div>
					</div>
				</td>
				<td>&nbsp;<?php echo $value[$feature]['metric_html_code']; ?></td>
				<?php
					}
				?>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<th style="white-space: nowrap;"><?php echo It::t('home_aws', 'single__block_sky_conditions_amount'); ?></th>
				
				<?php 
					$features = array(
						'cloud_amount_amount_1',
						'cloud_amount_amount_2',
						'cloud_amount_amount_3',
						'cloud_amount_amount_4',
						'cloud_amount_amount_total',
					);
					
					foreach ($features as $feature)
					{
						$hasFilterErrors = isset($value[$feature]['last_filter_errors']);
				?> 
				<td <?php if ($hasFilterErrors) echo 'title="'. implode("; ", $value[$feature]['last_filter_errors']) .'"'; ?>>
					
					<div class="cover <?php if ($hasFilterErrors) echo 'error'; ?>">
						
						<div class="<?php echo $value[$feature]['change']?>">
							&nbsp;<?php echo $value[$feature]['last']?>
						</div>
					</div>
				</td>
				<td>&nbsp;<?php echo $value[$feature]['metric_html_code']; ?></td>
				<?php
					}
				?>
			</tr>                       		             
		</table> 
	<?php 
		}
	?>  
</div>                
</div>