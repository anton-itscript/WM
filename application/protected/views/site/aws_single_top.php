<?php $current_url = $this->createUrl('site/awssingle'); ?>
<div class="left">

	<form action="<?php echo $current_url; ?>" method="get">
	<table class="info">        
	<tr>
		<th class="blue"><?php echo It::t('home_aws', 'single__station_name'); ?></th>
		<td>
			<select id="select" name="station_id" onchange="this.form.submit();">
				<?php foreach ($render_data['stations'] as $station) { ?>
					<option value="<?php echo $station->station_id; ?>" <?php echo ($station->station_id == $render_data['station']->station_id ? 'selected' : ''); ?> ><?php echo $station->display_name; ?> (ID: <?php echo $station->station_id_code; ?>)</option>
				<?php } ?>
			</select>                            
		</td>
		<td>&nbsp;&nbsp;</td>

		<th><?php echo It::t('home_aws', 'single__station_voltage'); ?></th>
		<td style="width: 80px;">
			<?php 
				if (!$render_data['handler_sensor']) 
				{
					echo 'Unknown';
				} 
				else 
				{
					foreach ($render_data['handler_sensor'] as $aws_group => $aws_handler) 
					{
						if ($aws_group === 'battery_voltage') 
						{
							foreach($aws_handler as $sensor_handler_results) 
							{
								foreach($sensor_handler_results as $sensor_data) 
								{
			?>
				<div class="cover"><div class="<?php echo $sensor_data['change']?>"><?php echo $sensor_data['last']?>  <?php echo $sensor_data['metric_html_code']?></div></div>
			<?php 
								}
							}
						}
					}
				}
			?> 
		</td>
	</tr>
	<tr>
		<th><?php echo It::t('home_aws', 'single__station_timezone'); ?></th>
		<td><?php echo $render_data['station']['timezone_id']?> (GMT <?php echo $render_data['station']['timezone_offset']?>)</td>
	</tr>
	</table>
	</form>    

	<table class="info">
	<tr>
		<th><?php echo It::t('home_aws', 'single__location'); ?></th>
		<td><?php echo ($render_data['station']->lat ? number_format($render_data['station']->lat,5) : '0.00') ?>; <?php echo ($render_data['station']->lng ? number_format($render_data['station']->lng,5): '0.00') ?></td>
		<td>&nbsp;|&nbsp;</td>

		<th><?php echo It::t('home_aws', 'single__altitude'); ?></th>
		<td><?php echo $render_data['station']['altitude'] ?>m</td>
		<td>&nbsp;|&nbsp;</td>
		<th><?php echo It::t('home_aws', 'single__wmo'); ?></th>
		<td><?php echo $render_data['station']->wmo_block_number . $render_data['station']->station_number; ?></td>
		<td>&nbsp;|&nbsp;</td>
		<th><?php echo It::t('home_aws', 'single__status'); ?></th>
		<td>OK</td>
	</tr>
	</table>
</div>   

<div class="right">
	<table class="info">
		<tr>
			<th>
				<?php echo It::t('home_aws', 'single__last_received'); ?> <?php echo (isset($render_data['last_logs'][0]) ? '['.$render_data['last_logs'][0]->log_id.']' : ''); ?>:
				<?php  if (isset($render_data['last_logs'][0])) {?><small>[<a href="#" onclick="$('#message_<?php echo $render_data['last_logs'][0]->log_id; ?>').toggle();">Show</a>]</small><?php }?>
			</th>
			<td><span <?php if ($render_data['station']->nextMessageIsLates) {?> class="late" title="New message had come on <?php echo $render_data['station']->nextMessageExpected; ?>" <?php }?>><?php echo $render_data['station']->lastMessage; ?></span></td>
		</tr>
		<?php  if (isset($render_data['last_logs'][0])) { ?>
		<tr id="message_<?php echo $render_data['last_logs'][0]->log_id; ?>" style="display:none;">
			<td colspan="2"><div style="font-size: 10px; width: 330px; overflow-x: scroll; white-space: nowrap;"><?php echo $render_data['last_logs'][0]->message; ?></div></td>
		</tr>
		<?php }?>                
		<tr>
			<th>
				<?php echo It::t('home_aws', 'single__previous_received'); ?> <?php echo (isset($render_data['last_logs'][1]) ? '['.$render_data['last_logs'][1]->log_id.']' : ''); ?>:
				<?php  if (isset($render_data['last_logs'][1])) {?>
					<small>[<a href="#" onclick="$('#message_<?php echo $render_data['last_logs'][1]->log_id; ?>').toggle();">Show</a>]</small>
				<?php }?>                      
			</th>
			<td><?php echo $render_data['station']->previousMessage; ?></td>
		</tr>
		<?php  if (isset($render_data['last_logs'][1])) {?>
		<tr id="message_<?php echo $render_data['last_logs'][1]->log_id; ?>" style="display:none;">
			<td colspan="2"><div style="font-size: 10px; width: 330px; overflow-x: scroll; white-space: nowrap;"><?php echo $render_data['last_logs'][1]->message; ?></div></td>
		</tr>
		<?php }?>                


	</table> 
	<br/>
	<div class="small" style="text-align: right;"><?php echo It::t('site_label', 'page_is_autorefreshing'); ?></div>

</div>
<div class="clear"></div>