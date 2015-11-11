<div class="middlenarrow">

<?php if (!$render_data['stations']) {?>
    <?php echo It::t('site_label', 'no_rg_stations') ?>
    
<?php } else {?>

    <div id="rg_panel_boxes">
    <?php $key = 0;?>    
    <?php foreach ($render_data['stations'] as $station_id => $station_data) {?>

        <div class="data_box <?php echo (($key && fmod($key+1,4) == 0) ? '' : 'margin')?>">

            <div class="header">
                <a href="<?php echo $this->createUrl('site/rgtable', array('station_id' => $station_data['station_id']))?>" title="<?php echo $station_data['display_name']?>"><?php echo It::createTextPreview($station_data['display_name'], 23, '...')?></a><br/>
                <?php echo $station_data['station_id_code']?><br/>
                <?php echo It::t('home_rg', 'panel__rain_guage_station') ?>
                 
            </div>

            <div class="content">
                <table>
                <tr>
                    <th><?php echo It::t('home_rg', 'panel__last_msg') ?></th>
                    <td><div class="cover <?php echo ($station_data['sensor_details']['next_lates'] ? 'late' : '') ?>" <?php if ($station_data['sensor_details']['next_lates']) {?> title="New message had come on <?php echo $station_data['sensor_details']['next_expected'] ?>" <?php }?>><div ><?php echo $station_data['sensor_details']['last_msg']?></div></div></td>
                    <td>&nbsp;</td>
                </tr>
                <tr >
                    <th><?php echo It::t('home_rg', 'panel__amount') ?></th>
                    <td <?php if (isset($station_data['filter_errors'])) {?>title="<?php echo implode('; ',$station_data['filter_errors']) ?>"<?php }?> ><div class="cover <?php echo (isset($station_data['filter_errors']) ? 'error' : '')?>"><div><?php echo $station_data['sensor_details']['amount']?></div></div></td>
                    <td><?php echo $station_data['sensor_details']['sensor_data_id'] ? $station_data['sensor_details']['metric'] : '&nbsp;'?></td>
                </tr>
                <tr>
                    <th><?php echo It::t('home_rg', 'panel__period') ?></th>
                    <td><div class="cover"><div><?php echo $station_data['sensor_details']['period']?></div></div></td>
                    <td><?php echo $station_data['sensor_details']['sensor_data_id'] ? 'Minutes' : '&nbsp;'?></td>
                </tr>
                <tr>
                    <th><?php echo It::t('home_rg', 'panel__rate') ?></th>
                    <td><div class="cover"><div><?php echo $station_data['sensor_details']['rate']?></div></div></td>
                    <td><?php echo $station_data['sensor_details']['sensor_data_id'] ? $station_data['sensor_details']['metric'].'/hr' : '&nbsp;'?></td>
                </tr>
                <tr>
                    <th><?php echo It::t('home_rg', 'panel__total1') ?></th>
                    <td><div class="cover"><div><?php echo $station_data['sensor_details']['1hr_total']?></div></div></td>
                    <td><?php echo $station_data['sensor_details']['sensor_data_id'] ? $station_data['sensor_details']['metric'] : '&nbsp;'?></td>
                </tr>
                <tr>
                    <th><?php echo It::t('home_rg', 'panel__battery') ?></th>
                    <td><div class="cover"><div><?php echo $station_data['sensor_details']['batt_volt']?></div></div></td>
                    <td><?php echo $station_data['sensor_details']['sensor_data_id'] ? 'V' : '&nbsp;'?></td>
                </tr>
                <tr>
                    <th><?php echo It::t('home_rg', 'panel__total24') ?></th>
                    <td><div class="cover"><div><?php echo $station_data['sensor_details']['24hr_total']?></div></div></td>
                    <td><?php echo $station_data['sensor_details']['sensor_data_id'] ? $station_data['sensor_details']['metric'] : '&nbsp;'?></td>
                </tr>
                </table>
            </div><!-- div.content -->
        </div>

        <?php $key++;?>
    <?php }?>

    <div style="clear: both;"></div>
    
    </div><!-- div#rg_panel_boxes -->
<?php }?>

</div><!-- div.middlenarrow -->