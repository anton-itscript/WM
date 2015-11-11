<div class="middlenarrow">
<?php
	if (!$render_data['stations']) {echo 'No AWS stations registered in the database';}
	else {
		$middle_station = floor(count($render_data['stations']) / 2) - 1; //count rows
?>
    <div id="aws_panel_boxes" >
        
    <div class="left">
        <!--td-->
        <?php $counter = 0; ?>    
        <?php foreach ($render_data['stations'] as $key => $station_data) {?>
            
        <div class="data_box <?php (($counter && fmod($counter+1,2) == 0) ? '' : 'margin')?>">
            <div class="header">
                    <?php echo CHtml::link($station_data->display_name, array('site/awssingle', 'station_id' => $station_data->station_id)); ?> - 
                    <?php echo $station_data->station_id_code; ?>
                    
                    <span <?php if (isset($station_data->nextMessageIsLates)) {?> class="late" title="New message had come on <?php echo $station_data->nextMessageExpected; ?>" <?php }?> >Last Rx: <?php echo $station_data->lastMessage; ?></span>
                </div>
                <div class="content">
                
				<?php 
					if (count($station_data->displaySensors) === 0) 
					{
						echo 'There are no any sensors registered at this station.';
					} 
					else 
					{
						if ($station_data->displaySensorsValues['last_min24_max_24'] || (count($station_data->calculations) > 0)) 
						{
				?>    
                    <table>
                    <tr>
                        <td>&nbsp;</td>
                        <td>Last</td>
                        <td>Min (24hr)</td>                    
                        <td>Max (24hr)</td>
                        <td>&nbsp;</td>                    
                    </tr>    
                        <?php 
							if (isset($station_data->displaySensorsValues['last_min24_max_24'])) 
							{
								foreach ($station_data->displaySensorsValues['last_min24_max_24'] as $k1 => $v1) 
								{
						?>
                            <tr>
                                <th title="<?php echo $v1['sensor_id_code']?>"><?php echo $v1['sensor_display_name']?></th>

                                <td <?php if (isset($v1['last_filter_errors'])){?> title="<?php echo implode("; ", $v1['last_filter_errors'])?>" <?php }?> >
                                    <?php if (in_array('last', array_keys($v1))) {?>
                                    <div class="cover <?php echo (isset($v1['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['change']?>">&nbsp;<?php echo $v1['last']?></div></div>
                                    <?php } else {?>    
                                        &nbsp;
                                    <?php }?>
                                </td>                                
                                <td>
                                    <?php if (in_array('min24', array_keys($v1))) {?>
                                        <div class="cover"><div>&nbsp;<?php echo $v1['min24']?></div></div>
                                    <?php } else {?>    
                                        &nbsp;
                                    <?php }?>
                                </td>
                                <td>
                                    <?php if (in_array('max24', array_keys($v1))) {?>
                                        <div class="cover"><div>&nbsp;<?php echo $v1['max24']?></div></div>
                                    <?php } else {?>    
                                        &nbsp;
                                    <?php }?>
                                </td>
                                <td><?php echo $v1['metric_html_code']?>
                            </td>

                            </tr>
                        <?php 
								}
							}
						?>

                        <?php if (count($station_data->calculations) > 0) {?>    
                        <?php foreach ($station_data->calculations as $k1 => $v1) {?>
                            <tr>
                                <th ><?php echo $v1['display_name']?></th>
                                <td><div class="cover"><div class="<?php echo $v1['change']?>"><?php echo ($v1['last']?$v1['last']:'&nbsp;')?></div></div></td>
                                <td><?php echo $v1['metric_html_code']?></td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        <?php }?>                        
                        <?php }?>
                    </table>        
                    <?php }?>


                            
                    <?php 
						if (isset($station_data->displaySensorsValues['sea_level_data'])) 
						{
					?>   
                    <table>
                    <tr>
                        <td>&nbsp;</td>
                        <td>Mean</td>
                        <td>&nbsp;</td>
                        <td>Sigma</td> 
                        <td>&nbsp;</td>
                        <td>Wave</td>
                        <td>&nbsp;</td>                    
                    </tr>    
                    <?php foreach ($station_data->displaySensorsValues['sea_level_data'] as $k1 => $v1) {?>
                        <tr>
                            <th title="<?php echo $v1['sensor_id_code']?>"><?php echo It::createTextPreview($v1['sensor_display_name'], 20, '...')?></th>

                            <td <?php if (isset($v1['sea_level_mean']['last_filter_errors'])){?> title="<?php echo implode("; ", $v1['sea_level_mean']['last_filter_errors'])?>" <?php }?> >
                                <?php if ($v1['sea_level_mean'] && in_array('last', array_keys($v1['sea_level_mean']))) {?>
                                <div class="cover smaller <?php echo (isset($v1['sea_level_mean']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['sea_level_mean']['change']?>"><?php echo ($v1['sea_level_mean']['last']?$v1['sea_level_mean']['last']:'&nbsp;')?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td><?php echo $v1['sea_level_mean']['metric_html_code']?></td>
                            <td>
                                <?php if ($v1['sea_level_sigma'] && in_array('last', array_keys($v1['sea_level_sigma']))) {?>
                                <div class="cover smaller <?php echo (isset($v1['sea_level_sigma']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['sea_level_sigma']['change']?>"><?php echo ($v1['sea_level_sigma']['last']?$v1['sea_level_sigma']['last']:'&nbsp;')?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td><?php echo $v1['sea_level_sigma']['metric_html_code']?></td>
                            <td>
                                <?php if ($v1['sea_level_wave_height'] && in_array('last', array_keys($v1['sea_level_wave_height']))) {?>
                                <div class="cover <?php echo (isset($v1['sea_level_wave_height']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['sea_level_wave_height']['change']?>"><?php echo ($v1['sea_level_wave_height']['last']?$v1['sea_level_wave_height']['last']:'&nbsp;')?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td><?php echo $v1['sea_level_wave_height']['metric_html_code']?></td>                                
                        </tr>
                    <?php }?>
                    </table>    
                    <?php 
						}
						
						if (isset($station_data->displaySensorsValues['last_period_today'])) 
						{
					?>  
                    <table> 
                    <tr>
                        <td>&nbsp;</td>
                        <td>Last</td>
                        <td>Period</td>                    
                        <td>Today</td>
                        <td>&nbsp;</td>                    
                    </tr>    
                    <?php foreach ($station_data->displaySensorsValues['last_period_today'] as $k1 => $v1) {?>
                        <tr>
                            <th title="<?php echo $v1['sensor_id_code']?>"><?php echo $v1['sensor_display_name']?></th>
                            <td <?php if (isset($v1['last_filter_errors'])){?> title="<?php echo implode("; ", $v1['last_filter_errors'])?>" <?php }?> >
                                <?php if (in_array('last', array_keys($v1))) {?>
                                    <div class="cover <?php echo (isset($v1['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['change']?>"><?php echo ($v1['last']?$v1['last']:'&nbsp;')?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td>
                                <?php if (in_array('period', array_keys($v1))) {?>
                                    <div class="cover"><div><?php echo ($v1['period']?$v1['period']:'&nbsp;')?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td>
                                <?php if (in_array('total_today_calculated', array_keys($v1))) {?>
                                    <div class="cover"><div><?php echo ($v1['total_today_calculated']?$v1['total_today_calculated']:'&nbsp;')?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td><?php echo $v1['metric_html_code']?></td>
                        </tr>
                    <?php }?>
                    </table>    
                    <?php 
						}
						
						if (isset($station_data->displaySensorsValues['last_hour_day'])) 
						{
					?>
                    <table>
                    <tr><td colspan="5">&nbsp;</td></tr>    
                    <tr>
                        <td>&nbsp;</td>
                        <td>Last</td>
                        <td>Last Hr</td>                    
                        <td>Last 24Hr</td>
                        <td>&nbsp;</td>                    
                    </tr>    
                    <?php foreach ($station_data->displaySensorsValues['last_hour_day'] as $k1 => $v1) {?>
                        <tr>
                            <th title="<?php echo $v1['sensor_id_code']?>"><?php echo $v1['sensor_display_name']?></th>
                            
                            <td <?php if (isset($v1['last_filter_errors'])){?> title="<?php echo implode("; ", $v1['last_filter_errors'])?>" <?php }?>>
                                <?php if (in_array('last', array_keys($v1))) {?>
                                    <div class="cover <?php echo (isset($v1['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['change']?>"><?php echo ($v1['last']?$v1['last']:'&nbsp;')?></div></div>
                                <?php } else {?>    
                                    &nbsp; 
                                <?php }?>
                            </td>
                            <td>
                                <?php if (in_array('total_hour', array_keys($v1))) {?>
                                    <div class="cover"><div><?php echo ($v1['total_hour']?$v1['total_hour']:'&nbsp;')?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td>
                                <?php if (in_array('total_today', array_keys($v1))) {?>
                                    <div class="cover"><div><?php echo ($v1['total_today']?$v1['total_today']:'&nbsp;')?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td><?php echo $v1['metric_html_code']?></td>
                        </tr>
                    <?php }?>   
                    </table>    
                    <?php 
						}
						
						if (isset($station_data->displaySensorsValues['clouds']))
						{
					?>
                    <table>
                        <tr><td colspan="7">&nbsp;</td></tr>    
                        <tr>
                            <td>&nbsp;</td>
                            <td>#1</td>
                            <td>&nbsp;</td>
                            <td>#2</td>  
                            <td>&nbsp;</td>
                            <td>#3</td>
                            <td>&nbsp;</td>                    
                        </tr> 
                        <?php foreach ($station_data->displaySensorsValues['clouds'] as $k1 => $v1) {?>
                        <tr>
                            <th title="<?php echo $v1['sensor_display_name'].', '.$v1['sensor_id_code']?>">Cloud Height</th>
                            <td <?php if (isset($v1['cloud_height_height_1']['last_filter_errors'])){?> title="<?php echo implode("; ", $v1['cloud_height_height_1']['last_filter_errors'])?>" <?php }?> >
                                <?php if (in_array('last', array_keys($v1['cloud_height_height_1']))) {?>
                                    <div class="cover smaller <?php echo (isset($v1['cloud_height_height_1']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['cloud_height_height_1']['change']?>">&nbsp;<?php echo $v1['cloud_height_height_1']['last']?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td><?php echo $v1['cloud_height_height_1']['metric_html_code']?></td>
                            <td <?php if (isset($v1['cloud_height_height_2']['last_filter_errors'])){?> title="<?php echo implode("; ", $v1['cloud_height_height_2']['last_filter_errors'])?>" <?php }?> >
                                <?php if (in_array('last', array_keys($v1['cloud_height_height_2']))) {?>
                                    <div class="cover smaller <?php echo (isset($v1['cloud_height_height_2']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['cloud_height_height_2']['change']?>">&nbsp;<?php echo $v1['cloud_height_height_2']['last']?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td><?php echo $v1['cloud_height_height_2']['metric_html_code']?></td>
                            <td <?php if (isset($v1['cloud_height_height_3']['last_filter_errors'])){?> title="<?php echo implode("; ", $v1['cloud_height_height_3']['last_filter_errors'])?>" <?php }?> >
                                <?php if (in_array('last', array_keys($v1['cloud_height_height_3']))) {?>
                                <div class="cover <?php echo (isset($v1['cloud_height_height_3']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['cloud_height_height_3']['change']?>">&nbsp;<?php echo $v1['cloud_height_height_3']['last']?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>  
                            <td><?php echo $v1['cloud_height_height_3']['metric_html_code']?></td>
                        </tr>
                        <?php /*
                        <tr>
                            <th title="<?php echo $v1['sensor_display_name'].', '.$v1['sensor_id_code']?>">Cloud Amount</th>
                            <td <?php if ($v1['cloud_height_amount_1']['last_filter_errors']){?> title="<?php echo implode("; ", $v1['cloud_height_amount_1']['last_filter_errors'])?>" <?php }?> >
                                <?php if (in_array('last', array_keys($v1['cloud_height_amount_1']))) {?>
                                <div class="cover smaller <?php echo ($v1['cloud_height_amount_1']['last_filter_errors'] ? 'error' : '')?>"><div class="<?php echo $v1['cloud_height_amount_1']['change']?>">&nbsp;<?php echo $v1['cloud_height_amount_1']['last']?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td>&nbsp;</td>
                            <td <?php if ($v1['cloud_height_amount_2']['last_filter_errors']){?> title="<?php echo implode("; ", $v1['cloud_height_amount_2']['last_filter_errors'])?>" <?php }?> >
                                <?php if (in_array('last', array_keys($v1['cloud_height_amount_2']))) {?>
                                <div class="cover smaller <?php echo ($v1['cloud_height_amount_2']['last_filter_errors'] ? 'error' : '')?>"><div class="<?php echo $v1['cloud_height_amount_2']['change']?>">&nbsp;<?php echo $v1['cloud_height_amount_2']['last']?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td>&nbsp;</td>
                            <td <?php if ($v1['cloud_height_amount_3']['last_filter_errors']){?> title="<?php echo implode("; ", $v1['cloud_height_amount_3']['last_filter_errors'])?>" <?php }?> >
                                <?php if (in_array('last', array_keys($v1['cloud_height_amount_3']))) {?>
                                <div class="cover <?php echo ($v1['cloud_height_amount_3']['last_filter_errors'] ? 'error' : '')?>"><div class="<?php echo $v1['cloud_height_amount_3']['change']?>">&nbsp;<?php echo $v1['cloud_height_amount_3']['last']?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td> 
                            <td>&nbsp;</td>
                        </tr>  
                         */ ?>
                        <tr>
                            <th title="<?php echo $v1['sensor_display_name'].', '.$v1['sensor_id_code']?>">Cloud Depth</th>
                            <td <?php if (isset($v1['cloud_height_depth_1']['last_filter_errors'])){?> title="<?php echo implode("; ", $v1['cloud_height_depth_1']['last_filter_errors'])?>" <?php }?> >
                                <?php if ($v1['cloud_height_depth_1'] && in_array('last', array_keys($v1['cloud_height_depth_1']))) {?>
                                    <div class="cover smaller <?php echo (isset($v1['cloud_height_depth_1']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['cloud_height_depth_1']['change']?>">&nbsp;<?php echo $v1['cloud_height_depth_1']['last']?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td><?php echo $v1['cloud_height_depth_1']['metric_html_code']?></td>
                            <td <?php if (isset($v1['cloud_height_depth_2']['last_filter_errors'])){?> title="<?php echo implode("; ", $v1['cloud_height_depth_2']['last_filter_errors'])?>" <?php }?> >
                                <?php if ($v1['cloud_height_depth_2'] && in_array('last', array_keys($v1['cloud_height_depth_2']))) {?>
                                    <div class="cover smaller <?php echo (isset($v1['cloud_height_depth_2']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['cloud_height_depth_2']['change']?>">&nbsp;<?php echo $v1['cloud_height_depth_2']['last']?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td><?php echo $v1['cloud_height_depth_2']['metric_html_code']?></td>
                            <td <?php if (isset($v1['cloud_height_depth_3']['last_filter_errors'])){?> title="<?php echo implode("; ", $v1['cloud_height_depth_3']['last_filter_errors'])?>" <?php }?> >
                                <?php if ($v1['cloud_height_depth_3'] && in_array('last', array_keys($v1['cloud_height_depth_3']))) {?>
                                <div class="cover <?php echo (isset($v1['cloud_height_depth_3']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['cloud_height_depth_3']['change']?>">&nbsp;<?php echo $v1['cloud_height_depth_3']['last']?></div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>  
                            <td><?php echo $v1['cloud_height_depth_3']['metric_html_code']?></td>
                        </tr>                         
                        <tr>
                            <th title="<?php echo $v1['sensor_display_name'].', '.$v1['sensor_id_code']?>">VV</th>
                            <td  <?php if (isset($v1['cloud_vertical_visibility']['last_filter_errors'])){?> title="<?php echo implode("; ", $v1['cloud_vertical_visibility']['last_filter_errors'])?>" <?php }?> >
                                <?php if (in_array('last', array_keys($v1['cloud_vertical_visibility']))) {?>
                                <div class="cover smaller <?php echo (isset($v1['cloud_vertical_visibility']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['cloud_vertical_visibility']['change']?>">&nbsp;<?php echo $v1['cloud_vertical_visibility']['last']?> </div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td>
                            <td ><?php echo $v1['cloud_vertical_visibility']['metric_html_code']?></td>
                            <td colspan="2" style="text-align: right;" title="<?php echo $v1['sensor_display_name'].', '.$v1['sensor_id_code']?>">Range:</td>
                            <td  <?php if (isset($v1['cloud_measuring_range']['last_filter_errors'])){?> title="<?php echo implode("; ", $v1['cloud_measuring_range']['last_filter_errors'])?>" <?php }?> >
                                <?php if (in_array('last', array_keys($v1['cloud_measuring_range']))) {?>
                                <div class="cover <?php echo (isset($v1['cloud_measuring_range']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $v1['cloud_measuring_range']['change']?>">&nbsp;<?php echo $v1['cloud_measuring_range']['last']?> </div></div>
                                <?php } else {?>    
                                    &nbsp;
                                <?php }?>
                            </td> 
                            <td ><?php echo $v1['cloud_measuring_range']['metric_html_code']?></td>
                        </tr>                          
                        <?php }?>   
                        </table>
                    <?php }?>    
                    
                    
                <?php }?>
                </div><!-- div.content -->
            </div><!-- div.data_box -->  
            
            <?php if ($counter == $middle_station) {?>
                </div><div class="right">
            <?php }?>
                  
            <?php $counter ++;?>
        <?php }?>
        </div>            
        <div class="clear"></div>
    <!--/tr-->    
    </div><!-- #aws_panel_boxes -->
<?php 
	}
?>

</div><!-- div.middlenarrow -->

