<div class="data_box clouds">
<div class="header"><?php echo It::t('home_aws', 'single__block_clouds')?></div>
<div class="content">            
	<?php if (count($renderData) > 1) {?>
		<a class="list_left disabled" href="#"></a>
		<a class="list_right" href="#"></a>
	<?php }?> 

	<?php 
		foreach($renderData as $key => $value) 
		{
	?>
		<table style="<?php echo ($key ? 'display:none;' : '')?>">
		<tr>
			<td>&nbsp;</td>
			<td colspan="3" title="<?php echo ($value['sensor_display_name'] .' - '.$value['sensor_id_code']); ?>"><?php echo It::createTextPreview($value['sensor_display_name'], 45, '...')?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<th style="text-align: center;">#1</th>
			<th>&nbsp;</th>
			<th style="text-align: center;">#2</th>
			<th>&nbsp;</th>
			<th style="text-align: center;">#3</th>
			<th>&nbsp;</th>
		</tr>
		<tr>
			<th><?php echo It::t('home_aws', 'single__block_clouds_height')?></th>
			<td <?php if (isset($value['cloud_height_height_1']['last_filter_errors'])){?> title="<?php echo implode("; ", $value['cloud_height_height_1']['last_filter_errors'])?>" <?php }?>>
				<div class="cover <?php echo (isset($value['cloud_height_height_1']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['cloud_height_height_1']['change']?>"><?php echo $value['cloud_height_height_1']['last']?></div></div>
			</td>
			<td><?php echo $value['cloud_height_height_1']['metric_html_code']?></td>
			<td <?php if (isset($value['cloud_height_height_2']['last_filter_errors'])){?> title="<?php echo implode("; ", $value['cloud_height_height_2']['last_filter_errors'])?>" <?php }?>>
				<div class="cover <?php echo (isset($value['cloud_height_height_2']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['cloud_height_height_2']['change']?>"><?php echo $value['cloud_height_height_2']['last']?></div></div>
			</td>
			<td><?php echo $value['cloud_height_height_2']['metric_html_code']?></td>
			<td <?php if (isset($value['cloud_height_height_3']['last_filter_errors'])){?> title="<?php echo implode("; ", $value['cloud_height_height_3']['last_filter_errors'])?>" <?php }?>>
				<div class="cover <?php echo (isset($value['cloud_height_height_3']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['cloud_height_height_3']['change']?>"><?php echo $value['cloud_height_height_3']['last']?></div></div>
			</td>
			<td><?php echo $value['cloud_height_height_3']['metric_html_code']?></td>
		</tr>
		<tr>
			<th><?php echo It::t('home_aws', 'single__block_clouds_depth')?></th>
			<td <?php if (isset($value['cloud_height_depth_1']['last_filter_errors'])){?> title="<?php echo implode("; ", $value['cloud_height_depth_1']['last_filter_errors'])?>" <?php }?>>
				<div class="cover <?php echo (isset($value['cloud_height_depth_1']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['cloud_height_depth_1']['change']?>"><?php echo $value['cloud_height_depth_1']['last']?></div></div>
			</td>
			<td><?php echo $value['cloud_height_depth_1']['metric_html_code']?></td>
			<td <?php if (isset($value['cloud_height_depth_2']['last_filter_errors'])){?> title="<?php echo implode("; ", $value['cloud_height_depth_2']['last_filter_errors'])?>" <?php }?>>
				<div class="cover <?php echo (isset($value['cloud_height_depth_2']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['cloud_height_depth_2']['change']?>"><?php echo $value['cloud_height_depth_2']['last']?></div></div>
			</td>
			<td><?php echo $value['cloud_height_depth_2']['metric_html_code']?></td>
			<td <?php if (isset($value['cloud_height_depth_3']['last_filter_errors'])){?> title="<?php echo implode("; ", $value['cloud_height_depth_3']['last_filter_errors'])?>" <?php }?>>
				<div class="cover <?php echo (isset($value['cloud_height_depth_3']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['cloud_height_depth_3']['change']?>"><?php echo $value['cloud_height_depth_3']['last']?></div></div>
			</td>
			<td><?php echo $value['cloud_height_depth_3']['metric_html_code']?></td>
		</tr>                        
		<?php /*
		<tr>
			<th>Cloud Amount</th>
			<td <?php if ($value['cloud_height_amount_1']['last_filter_errors']){?> title="<?php echo implode("; ", $value['cloud_height_amount_1']['last_filter_errors'])?>" <?php }?>>
				<div class="cover <?php echo ($value['cloud_height_amount_1']['last_filter_errors'] ? 'error' : '')?>"><div class="<?php echo $value['cloud_height_amount_1']['change']?>">&nbsp;<?php echo $value['cloud_height_amount_1']['last']?></div></div>
			</td>
			<td>&nbsp;</td>
			<td <?php if ($value['cloud_height_amount_2']['last_filter_errors']){?> title="<?php echo implode("; ", $value['cloud_height_amount_2']['last_filter_errors'])?>" <?php }?>>
				<div class="cover <?php echo ($value['cloud_height_amount_2']['last_filter_errors'] ? 'error' : '')?>"><div class="<?php echo $value['cloud_height_amount_2']['change']?>">&nbsp;<?php echo $value['cloud_height_amount_2']['last']?></div></div>
			</td>
			<td>&nbsp;</td>
			<td <?php if ($value['cloud_height_amount_3']['last_filter_errors']){?> title="<?php echo implode("; ", $value['cloud_height_amount_3']['last_filter_errors'])?>" <?php }?>>
				<div class="cover <?php echo ($value['cloud_height_amount_3']['last_filter_errors'] ? 'error' : '')?>"><div class="<?php echo $value['cloud_height_amount_3']['change']?>">&nbsp;<?php echo $value['cloud_height_amount_3']['last']?> </div></div>
			</td>
			<td>&nbsp;</td>
		</tr>
		*/?>

		<tr>
			<th><?php echo It::t('home_aws', 'single__block_clouds_vis')?></th>
			<td <?php if (isset($value['cloud_vertical_visibility']['last_filter_errors'])){?> title="<?php echo implode("; ", $value['cloud_vertical_visibility']['last_filter_errors'])?>" <?php }?>>
				<div class="cover <?php echo (isset($value['cloud_vertical_visibility']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['cloud_vertical_visibility']['change']?>">&nbsp;<?php echo $value['cloud_vertical_visibility']['last']?></div></div>
			</td>
			<td><?php echo $value['cloud_vertical_visibility']['metric_html_code']?></td>
			<th colspan="2" style="text-align: right;"><?php echo It::t('home_aws', 'single__block_clouds_range')?></th>
			<td <?php if (isset($value['cloud_measuring_range']['last_filter_errors'])){?> title="<?php echo implode("; ", $value['cloud_measuring_range']['last_filter_errors'])?>" <?php }?>>
				<div class="cover <?php echo (isset($value['cloud_measuring_range']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['cloud_measuring_range']['change']?>">&nbsp;<?php echo $value['cloud_measuring_range']['last']?></div></div>
			</td>                            
			<td><?php echo $value['cloud_measuring_range']['metric_html_code']?></td>
		</tr>                       
		</table> 

	<?php }?>  
</div>                
</div>