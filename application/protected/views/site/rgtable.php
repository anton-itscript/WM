
<div class="middlenarrow">

    <?php if (!$render_data['stations']) {?>
        <?php echo It::t('site_label', 'no_rg_stations') ?>
    <?php } else {?>
    
    <table class="tablelist rgtable" width="100%">
    <tr>
        <th rowspan="3" style="width: 120px;">
            <a href="<?php echo $current_url?>?of=name" class="<?php echo ($render_data['form']->order_field == 'name' && $render_data['form']->order_direction == 'DESC')?'desc':'asc'?> <?php echo $render_data['form']->order_field == 'name'?'selected':''?>"><?php echo It::t('home_rg', 'table__col_station_display_name')?></a>
        </th>
        <th rowspan="3" style="width: 35px;"><?php echo It::t('home_rg', 'table__col_station_id')?></th>
        <th rowspan="3" style="width: 40px;"><?php echo It::t('home_rg', 'table__col_voltage')?></th>
        <th colspan="8"><?php echo It::t('home_rg', 'table__col_rain')?></th>
        <th colspan="2" rowspan="2"><?php echo It::t('home_rg', 'table__col_last_check_msg')?></th>
    </tr>
    <tr>
        <th rowspan="2" style="width: 50px;">
            <a href="<?php echo $current_url?>?of=date" class="<?php echo ($render_data['form']->order_field == 'date' && $render_data['form']->order_direction == 'DESC')?'desc':'asc'?> <?php echo $render_data['form']->order_field == 'date'?'selected':''?>"><?php echo It::t('home_rg', 'table__col_date')?></a>
        </th>
        <th rowspan="2" style="width: 40px;"><?php echo It::t('home_rg', 'table__col_period')?><!-- Tx Time--></th>
        <th colspan="2"><?php echo It::t('home_rg', 'table__col_last_period')?> <!-- Last Tx--></th>
        <th colspan="2"><?php echo It::t('home_rg', 'table__col_last_hour')?></th>
        <th colspan="2"><?php echo It::t('home_rg', 'table__col_last_day')?></th>
    </tr>
    <tr>
        <th>
            <a href="<?php echo $current_url?>?of=lasttx" class="<?php echo ($render_data['form']->order_field == 'lasttx' && $render_data['form']->order_direction == 'DESC')?'desc':'asc'?> <?php echo $render_data['form']->order_field == 'lasttx'?'selected':''?>"><?php echo It::t('home_rg', 'table__col_amt')?><br/>(<?php echo $render_data['rain_metric']?>)</a>
        </th>
        <th><?php echo It::t('home_rg', 'table__col_rate')?><br/>(<?php echo $render_data['rain_metric']?>/hr)</th>
        <th>
            <a href="<?php echo $current_url?>?of=lasthr" class="<?php echo ($render_data['form']->order_field == 'lasthr' && $render_data['form']->order_direction == 'DESC')?'desc':'asc'?> <?php echo $render_data['form']->order_field == 'lasthr'?'selected':''?>"><?php echo It::t('home_rg', 'table__col_amt')?><br/>(<?php echo $render_data['rain_metric']?>)</a>
        </th>
        <th><?php echo It::t('home_rg', 'table__col_rate')?><br/>(<?php echo $render_data['rain_metric']?>/hr)</th>
        <th>
            <a href="<?php echo $current_url?>?of=last24hr" class="<?php echo ($render_data['form']->order_field == 'last24hr' && $render_data['form']->order_direction == 'DESC')?'desc':'asc'?> <?php echo $render_data['form']->order_field == 'last24hr'?'selected':''?>"><?php echo It::t('home_rg', 'table__col_amt')?><br/>(<?php echo $render_data['rain_metric']?>)</a>
        </th>
        <th><?php echo It::t('home_rg', 'table__col_avg_rate')?><br/>(<?php echo $render_data['rain_metric']?>/hr)</th>
        <th style="width: 90px;"><?php echo It::t('home_rg', 'table__col_datetime')?></th>
        <th><?php echo It::t('home_rg', 'table__col_status')?></th>
    </tr>
    <?php if (!$render_data['listing']) {?>
        <tr><td colspan="13"><?php echo It::t('home_rg', 'table__no_data')?></td></tr>
    <?php } else {?>
        <?php foreach ($render_data['listing'] as $key => $value) {
            
            $class = (fmod($key,2) == 0 ? 'c' : '');
            
            if (count($value['filter_errors'])) {
                $class .= " error";
            }
        ?>

        <tr class="<?php echo $class?>">
            <td class="title">
                <span><?php echo $value['display_name']?></span>
                <a href="#" onclick="$('#tr_msg_<?php echo $value['sensor_data_id']?>').toggle(); return false;"></a>
                <div class="clear"></div>
            </td>
            <td><?php echo $value['station_id_code']?></td>
            <td><?php echo $value['battery_voltage_formatted']?>V</td>
            <td><?php echo $value['tx_date_formatted']?></td>
            <td><?php echo $value['tx_time_formatted']?></td>
            <td><?php echo $value['tx_value_mm']?></td>
            <td><?php echo $value['tx_value_rate_mm']?> </td>
            <td><?php echo $value['hour_value_mm']?></td>
            <td><?php echo ($value['hour_value_rate_mm'] ? $value['hour_value_rate_mm'] : '&nbsp;')?></td>
            <td><?php echo $value['day_value_mm']?></td>
            <td>&nbsp;</td>
            <td><?php echo $value['tx_date_formatted']?> <?php echo $value['tx_time_formatted']?></td>
            <td class="status">
                <?php if (count($value['filter_errors'])) {?>
                <?php echo  implode('<br/>',$value['filter_errors'])?>
                <?php } else {?>
                OK
                <?php }?>
            </td>
        </tr>
        <tr class="<?php echo $class?> " style="display:none;" id="tr_msg_<?php echo $value['sensor_data_id']?>">
            <td colspan="13"><div class="rgmsg"><?php echo $value['message']?></div></td>
        </tr>
        <?php }?>
    <?php }?>
    </table>


    <?php if ($render_data['pages']->getPageCount() > 1){?>

	<div class="paginator" style="margin-top: 10px;">
            <?php  $this->widget(
                'CLinkPager',
                array(
                    'pages' => $render_data['pages'],
                    'header' => '',
                    'firstPageLabel' => 'First',
                    'lastPageLabel' => 'Last',
                    'nextPageLabel' => '&rarr;',
                    'prevPageLabel' => '&larr;',
                    'maxButtonCount' => 10
            ));
            ?>
                <div class="clear"></div>
	</div>

	<?php }?>


<?php }?>
<div class="spacer"></div>
</div>