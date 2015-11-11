<?php
if (!$render_data['handler_sensor'])
{
    echo It::t('home_aws', 'single__no_sensors');
}
else
{
    ?>
    <!-- Container with all sensors boxes -->
    <div id="aws_single_boxes_new">

    <!-- WIND BIG BLOCK -->
    <?php
    if(isset($render_data['handler_sensor']['wind']))
    {
        ?>
        <!-- WIND BLOCK -->
        <div class="data_box wind">
            <div class="header"><?php echo It::t('home_aws', 'single__block_wind')?></div>

            <!-- WIND SPEED BLOCK -->
            <?php if($render_data['handler_sensor']['wind']['WindSpeed']) { ?>
                <div class="content content_wind_speed">

                    <!-- if there are more than one wind_speed sensors - show arrows to list blocks -->
                    <?php if (count($render_data['handler_sensor']['wind']['WindSpeed']) > 1) {?>
                        <a class="list_left disabled" href="#"></a>
                        <a class="list_right" href="#"></a>
                    <?php } ?>

                    <?php foreach ($render_data['handler_sensor']['wind']['WindSpeed'] as $key => $value) { ?>
                        <table style="<?php echo ($key ? 'display:none;' : '')?>">
                            <tr>
                                <td>&nbsp;</td>
                                <td colspan="2" title="<?php echo ($value['sensor_display_name'].' - '.$value['sensor_id_code'])?> "><?php echo It::createTextPreview($value['sensor_display_name'], 15, '...')?></td>
                            </tr>
                            <tr>
                                <th><?php echo It::t('home_aws', 'single__block_wind_1m')?></th>
                                <td <?php if (isset($value['last_filter_errors'])){?> title="<?php echo implode("; ", $value['last_filter_errors'])?>" <?php }?>>
                                    <div class="cover <?php echo (isset($value['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['change']?>"><?php echo $value['last']?></div></div>
                                </td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo It::t('home_aws', 'single__block_wind_2m')?></th>
                                <td><div class="cover"><div><?php echo $value['2minute_average']?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo It::t('home_aws', 'single__block_wind_10m')?></th>
                                <td><div class="cover"><div><?php echo $value['10minute_average']?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr title="<?php echo $value['mami_title'];?>">
                                <th><?php echo It::t('home_aws', 'single__block_wind_min')?></th>
                                <td><div class="cover"><div><?php echo $value['min24']?$value['min24']:'-'?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr title="<?php echo $value['mami_title'];?>">
                                <th><?php echo It::t('home_aws', 'single__block_wind_max')?></th>
                                <td><div class="cover"><div><?php echo $value['max24']?$value['max24']:'-'?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr title="<?php echo $value['mami_title'];?>">
                                <th><?php echo It::t('home_aws', 'single__block_wind_avg')?></th>
                                <td><div class="cover"><div><?php echo $value['average']?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo It::t('home_aws', 'single__yesterday')?></th>
                            </tr>

                            <tr title="<?php echo $value['mami_title_y'];?>">
                                <th><?php echo It::t('home_aws', 'single__block_wind_min')?></th>
                                <td><div class="cover"><div><?php echo $value['min24_y']?$value['min24_y']:'-'?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr title="<?php echo $value['mami_title_y'];?>">
                                <th><?php echo It::t('home_aws', 'single__block_wind_max')?></th>
                                <td><div class="cover"><div><?php echo $value['max24_y']?$value['max24_y']:'-'?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr title="<?php echo $value['mami_title_y'];?>">
                                <th><?php echo It::t('home_aws', 'single__block_wind_avg')?></th>
                                <td><div class="cover"><div><?php echo $value['average_y']?$value['average_y']:'-'?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>

                        </table>
                    <?php }?>
                </div>
            <?php }?>
            <!-- END OF WIND SPEED BLOCK -->

            <!-- WIND DIRECTION BLOCK -->
            <?php
            if(isset($render_data['handler_sensor']['wind']['WindDirection']))
            {
                ?>
                <div class="content content_wind_direction" <?php if($render_data['handler_sensor']['wind']['WindSpeed']) { ?> style="margin-top:10px;" <?php }?>>

                    <!-- if there are more than one wind_direction sensors - show arrows to list blocks -->
                    <?php if (count($render_data['handler_sensor']['wind']['WindDirection']) > 1) {?>
                        <a class="list_left disabled" href="#"></a>
                        <a class="list_right" href="#"></a>
                    <?php } ?>

                    <?php foreach ($render_data['handler_sensor']['wind']['WindDirection'] as $key => $value) { ?>
                        <table style="<?php echo ($key ? 'display:none;' : '')?>">
                            <tr>
                                <td>&nbsp;</td>
                                <td colspan="2" title="<?php echo ($value['sensor_display_name'].' - '.$value['sensor_id_code'])?> "><?php echo It::createTextPreview($value['sensor_display_name'], 15, '...')?></td>
                            </tr>
                            <tr>
                                <th><?php echo It::t('home_aws', 'single__block_wind_1m')?></th>
                                <td <?php if (isset($value['last_filter_errors'])){?> title="<?php echo implode("; ", $value['last_filter_errors'])?>" <?php }?>>
                                    <div class="cover <?php echo (isset($value['last_filter_errors']) ? 'error' : '')?>"><div><?php echo $value['last']?></div></div>
                                </td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo It::t('home_aws', 'single__block_wind_2m')?></th>
                                <td><div class="cover"><div><?php echo $value['2minute_average']?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo It::t('home_aws', 'single__block_wind_10m')?></th>
                                <td><div class="cover"><div><?php echo $value['10minute_average']?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo It::t('home_aws', 'single__block_wind_min1')?></th>
                                <td><div class="cover"><div><?php echo $value['min1hr']?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo It::t('home_aws', 'single__block_wind_max1')?></th>
                                <td><div class="cover"><div><?php echo $value['max1hr']?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr title="<?php echo $value['mami_title'];?>">
                                <th><?php echo It::t('home_aws', 'single__block_wind_min')?></th>
                                <td><div class="cover"><div><?php echo $value['min24']?$value['min24']:'-'?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr title="<?php echo $value['mami_title'];?>">
                                <th><?php echo It::t('home_aws', 'single__block_wind_max')?></th>
                                <td><div class="cover"><div><?php echo $value['max24']?$value['max24']:'-'?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo It::t('home_aws', 'single__yesterday')?></th>
                            </tr>
                            <tr title="<?php echo $value['mami_title_y'];?>">
                                <th><?php echo It::t('home_aws', 'single__block_wind_min')?></th>
                                <td><div class="cover"><div><?php echo $value['min24_y']?$value['min24_y']:'-'?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                            <tr title="<?php echo $value['mami_title_y'];?>">
                                <th><?php echo It::t('home_aws', 'single__block_wind_max')?></th>
                                <td><div class="cover"><div><?php echo $value['max24_y']?$value['max24_y']:'-'?></div></div></td>
                                <td><?php echo $value['metric_html_code']; ?></td>
                            </tr>
                        </table>
                    <?php } ?>
                </div>
            <?php } ?>
            <!-- END OF WIND DIRECTION BLOCK -->

        </div>
        <!-- END OF WIND BLOCK -->



        <!-- WIND DIAL BLOCK -->
        <?php
        if (isset($render_data['handler_sensor']['wind']['WindDirection']))
        {
            ?>
            <div class="data_box wind_dial" style="width: 400px; height: 430px">

                <div class="header"><?php echo It::t('home_aws', 'single__block_dial')?></div>
                <div class="content">


                    <!-- if there are more than one wind_direction sensors - show arrows to list blocks -->
                    <?php if (count($render_data['handler_sensor']['wind']['WindDirection']) > 1) {?>
                        <a class="list_left disabled" href="#"></a>
                        <a class="list_right" href="#"></a>
                    <?php } ?>


                    <?php foreach ($render_data['handler_sensor']['wind']['WindDirection'] as $key => $value) { ?>

                        <table style="<?php echo ($key ? 'display:none;' : '')?>" id="wind_dial_id_<?php echo $key?>">
                            <tr class="not-hover">
                                <td>
                                    <div id="rose" style="width: 400px"></div>

                                    <?php
                                    if (isset($render_data['handler_sensor']['wind']['WindSpeed'][$key])) {
                                        $speed = $render_data['handler_sensor']['wind']['WindSpeed'][$key];
                                    } else {
                                        $speed = array();
                                    }
                                    $names = array(
                                        It::t('home_aws', 'single__block_dial_1m'),
                                        It::t('home_aws', 'single__block_dial_2m'),
                                        It::t('home_aws', 'single__block_dial_10m')
                                    );
                                    $data = array(
                                        array(array($value['last'],             isset($speed['last'])             ? (float)$speed['last']             : 0)),
                                        array(array($value['2minute_average'],  isset($speed['2minute_average'])  ? (float)$speed['2minute_average']  : 0)),
                                        array(array($value['10minute_average'], isset($speed['10minute_average']) ? (float)$speed['10minute_average'] : 0))
                                    );
                                    ?>

                                    <script language="javascript" type="text/javascript">
                                        var dataJs = [];
                                        dataJs['names'] = JSON.parse( '<?php echo json_encode($names) ?>');
                                        dataJs['data'] = JSON.parse( '<?php echo json_encode($data) ?>');
                                    </script>
                                </td>
                            </tr>
                        </table>
                    <?php } ?>
                </div>

            </div>

        <?php } ?>
        <!-- END OF WIND DIAL BLOCK -->

    <?php } ?>
    <!-- END OF WIND BIG BLOCK -->


    <!-- BLOCK SUN -->
    <?php
    if (isset($render_data['handler_sensor']['sun']))
    {
        ?>
        <div class="data_box sun">
            <div class="header"><?php echo It::t('home_aws', 'single__block_sun')?></div>

            <?php
            if (!isset($render_data['handler_sensor']['sun']['SunshineDuration']))
            {
                $render_data['handler_sensor']['sun']['SunshineDuration'] = array();
            }
            if (!isset($render_data['handler_sensor']['sun']['SolarRadiation']))
            {
                $render_data['handler_sensor']['sun']['SolarRadiation'] = array();
            }

            $values = array_merge((array)$render_data['handler_sensor']['sun']['SunshineDuration'], (array)$render_data['handler_sensor']['sun']['SolarRadiation']);
            ?>

            <div class="content">

                <!-- if there are more than one sun sensors - show arrows to list blocks -->
                <?php if (count($values) > 2) {?>
                    <a class="list_left disabled" href="#"></a>
                    <a class="list_right" href="#"></a>
                <?php }?>

                <?php for ($i = 0; $i < count($values); $i++) {?>
                    <?php $sun_class = 'sr'; //($values[$i]['handler_id_code'] == 'SunshineDuration' ? 'sr' : 'sr')?>
                    <?php $sun_class1 = 'sr'; //($values[$i+1]['handler_id_code'] == 'SunshineDuration' ? 'sr' : 'sr')?>
                    <table style="<?php echo ($i && fmod($i+2,2) == 0 ? 'display:none;' : '') ?>" >
                        <tr>
                            <td>&nbsp;</td>
                            <td colspan="2" title="<?php echo ($values[$i]['sensor_display_name'].' - '.$values[$i]['sensor_id_code'])?> "><?php echo It::createTextPreview($values[$i]['sensor_display_name'], 20, '...')?></td>
                            <?php if (isset($values[$i+1])) {?>
                                <td colspan="2" title="<?php echo ($values[$i+1]['sensor_display_name'].' - '.$values[$i+1]['sensor_id_code'])?> "><?php echo It::createTextPreview($values[$i+1]['sensor_display_name'], 20, '...')?></td>
                            <?php }?>
                        </tr>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__block_sun_last')?></th>

                            <td title="<?php echo $values[$i]['last']?> <?php if (isset($values[$i]['last_filter_errors'])){ echo '; '.implode("; ", $values[$i]['last_filter_errors']); }?>"><div class="cover <?php echo (isset($values[$i]['last_filter_errors']) ? 'error' : '')?> <?php echo $sun_class?>"><div class="<?php echo $values[$i]['change']?>"><?php echo It::createTextPreview($values[$i]['last'], 14, '...')?></div></div></td>
                            <td><?php echo $values[$i]['metric_html_code']?></td>
                            <?php if (isset($values[$i+1])) {?>
                                <td  title="<?php echo $values[$i+1]['last']?> <?php if (isset($values[$i+1]['last_filter_errors'])){ echo '; '.implode("; ", $values[$i+1]['last_filter_errors']); }?>"><div class="cover <?php echo (isset($values[$i+1]['last_filter_errors']) ? 'error' : '')?> <?php echo $sun_class1?>"><div class="<?php echo $values[$i+1]['change']?>"><?php echo It::createTextPreview($values[$i+1]['last'], 15, '...')?></div></div></td>
                                <td><?php echo $values[$i+1]['metric_html_code']?></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__block_sun_period')?></th>
                            <td><div class="cover <?php echo $sun_class?>"><div><?php echo $values[$i]['period']?></div></div></td>
                            <td><?php echo $values[$i]['metric_html_code']?></td>
                            <?php if (isset($values[$i+1])) {?>
                                <td><div class="cover <?php echo $sun_class1?>"><div><?php echo $values[$i+1]['period']?></div></div></td>
                                <td><?php echo 'min'; //$values[$i+1]['metric_html_code']?></td>
                            <?php }?>
                        </tr>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__block_sun_total_today')?></th>
                            <td title="<?php echo $values[$i]['total_today_title'];?>"><div class="cover <?php echo $sun_class?>"><div><?php echo It::createTextPreview($values[$i]['total_today_calculated'], 14, '...')?></div></div></td>
                            <td title="<?php echo $values[$i]['total_today_title'];?>"><?php echo $values[$i]['metric_html_code']?></td>
                            <?php if (isset($values[$i+1])) {?>
                                <td title="<?php echo $values[$i+1]['total_today_title'];?>"><div class="cover <?php echo $sun_class1?>"><div><?php echo It::createTextPreview($values[$i+1]['total_today_calculated'], 14, '...')?></div></div></td>
                                <td title="<?php echo $values[$i+1]['total_today_title'];?>"><?php echo $values[$i+1]['metric_html_code']?></td>
                            <?php }?>
                        </tr>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__block_sun_total_yesterday')?></th>
                            <td title="<?php echo $values[$i]['total_today_title_y'];?>"><div class="cover <?php echo $sun_class?>"><div><?php echo It::createTextPreview($values[$i]['total_today_calculated_y'] ? $values[$i]['total_today_calculated_y'] : '-', 14, '...')?></div></div></td>
                            <td title="<?php echo $values[$i]['total_today_title_y'];?>"><?php echo $values[$i]['metric_html_code']?></td>
                            <?php if (isset($values[$i+1])) {?>
                                <td title="<?php echo $values[$i+1]['total_today_title_y'];?>"><div class="cover <?php echo $sun_class1?>"><div><?php echo It::createTextPreview($values[$i+1]['total_today_calculated_y'] ? $values[$i+1]['total_today_calculated_y'] : '-', 14, '...')?></div></div></td>
                                <td title="<?php echo $values[$i+1]['total_today_title_y'];?>"><?php echo $values[$i+1]['metric_html_code']?></td>
                            <?php }?>
                        </tr>
                    </table>
                    <?php $i++;?>
                <?php }?>
            </div>
        </div>
    <?php }?>
    <!-- END OF SUN BLOCK -->


    <!-- BLOCK RAIN -->
    <?php
    if (isset($render_data['handler_sensor']['rain']))
    {
        ?>
        <div class="data_box rain">
            <div class="header"><?php echo It::t('home_aws', 'single__block_rain')?></div>
            <div class="content">

                <!-- if there are more than one rain sensors - show arrows to list blocks -->
                <?php if (count($render_data['handler_sensor']['rain']['RainAws']) >1 ) {?>
                    <a class="list_left disabled" href="#"></a>
                    <a class="list_right" href="#"></a>
                <?php }?>

                <?php foreach ($render_data['handler_sensor']['rain']['RainAws'] as $key => $value) {?>
                    <table style="<?php echo ($key ? 'display:none;' : '') ?>">
                        <tr>
                            <td>&nbsp;</td>
                            <td colspan="2" title="<?php echo ($value['sensor_display_name'].' - '.$value['sensor_id_code'])?> "><?php echo It::createTextPreview($value['sensor_display_name'], 15, '...')?></td>
                        </tr>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__block_rain_last')?></th>
                            <td <?php if (isset($value['last_filter_errors'])){?> title="<?php echo implode("; ", $value['last_filter_errors'])?>" <?php }?>>
                                <div class="cover <?php echo (isset($value['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['change']?>"><?php echo ($value['last'] ? $value['last'] : '-')?></div></div>
                            </td>
                            <td><?php echo $value['metric_html_code']?></td>
                        </tr>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__block_rain_1h')?></th>
                            <td><div class="cover"><div><?php echo ($value['total_hour'] ? $value['total_hour'] : '-')?></div></div></td>
                            <td><?php echo $value['metric_html_code']?></td>
                        </tr>
                        <tr title="<?php echo $value['total_today_title'];?>">
                            <th><?php echo It::t('home_aws', 'single__block_rain_total_today')?></th>
                            <td><div class="cover"><div><?php echo ($value['total_today'] ? $value['total_today'] : '-')?></div></div></td>
                            <td><?php echo $value['metric_html_code']?></td>
                        </tr>
                        <tr title="<?php echo $value['total_today_title_y'];?>">
                            <th><?php echo It::t('home_aws', 'single__block_rain_total_yesterday')?></th>
                            <td><div class="cover"><div><?php echo ($value['total_today_y'] ? $value['total_today_y'] : '-')?></div></div></td>
                            <td><?php echo $value['metric_html_code']?></td>
                        </tr>
                    </table>
                <?php }?>
            </div>
        </div>
    <?php }?>
    <!-- END OF BLOCK RAIN -->


    <!-- BLOCK PRESSURE -->
    <?php
    if (isset($render_data['handler_sensor']['pressure']))
    {
        ?>
        <div class="data_box pressure">
            <div class="header"><?php echo It::t('home_aws', 'single__block_pressure')?></div>
            <div class="content">

                <!-- if there are more than one pressure sensors - show arrows to list blocks -->
                <?php if (count($render_data['handler_sensor']['pressure']['Pressure']) > 1) {?>
                    <a class="list_left disabled" href="#"></a>
                    <a class="list_right" href="#"></a>
                <?php }?>

                <?php foreach($render_data['handler_sensor']['pressure']['Pressure'] as $key => $value) { ?>
                    <table style="<?php echo ($key ? 'display:none;' : '')?>">
                        <tr>
                            <td>&nbsp;</td>
                            <td colspan="2" title="<?php echo ($value['sensor_display_name'].' - '.$value['sensor_id_code'])?> "><?php echo It::createTextPreview($value['sensor_display_name'], 15, '...')?></td>
                        </tr>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__block_pressure_last')?></th>
                            <td <?php if (isset($value['last_filter_errors'])){?> title="<?php echo implode("; ", $value['last_filter_errors'])?>" <?php }?>>
                                <div class="cover <?php echo (isset($value['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['change']?>"><?php echo $value['last']?></div></div>
                            </td>
                            <td><?php echo $value['metric_html_code']?></td>
                        </tr>
                        <tr  title="<?php echo $value['mami_title'];?>">
                            <th><?php echo It::t('home_aws', 'single__block_pressure_min')?></th>
                            <td><div class="cover"><div><?php echo $value['min24']?$value['min24']:'-'?></div></div></td>
                            <td><?php echo $value['metric_html_code']?></td>
                        </tr>
                        <tr title="<?php echo $value['mami_title'];?>">
                            <th><?php echo It::t('home_aws', 'single__block_pressure_max')?></th>
                            <td><div class="cover"><div><?php echo $value['max24']?$value['max24']:'-'?></div></div></td>
                            <td><?php echo $value['metric_html_code']?></td>
                        </tr>
                        <?php
                        if (isset($value['PressureSeaLevel'])) {
                            ?>
                            <tr>
                                <th><?php echo It::t('home_aws', 'single__block_pressure_msl')?></th>
                                <td><div class="cover"><div class="<?php echo $value['PressureSeaLevel']['change']?>"><?php echo $value['PressureSeaLevel']['last']?></div></div></td>
                                <td><?php echo $value['PressureSeaLevel']['metric_html_code']?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__yesterday')?></th>
                        </tr>
                        <tr title="<?php echo $value['mami_title_y'];?>">
                            <th><?php echo It::t('home_aws', 'single__block_pressure_min')?></th>
                            <td><div class="cover"><div><?php echo $value['min24_y']?$value['min24_y']:'-'?></div></div></td>
                            <td><?php echo $value['metric_html_code']?></td>
                        </tr>
                        <tr title="<?php echo $value['mami_title_y'];?>">
                            <th><?php echo It::t('home_aws', 'single__block_pressure_max')?></th>
                            <td><div class="cover"><div><?php echo $value['max24_y']?$value['max24_y']:'-'?></div></div></td>
                            <td><?php echo $value['metric_html_code']?></td>
                        </tr>
                    </table>
                <?php }?>
            </div>
        </div>
    <?php
    }
    ?>
    <!-- END OF BLOCK PRESSURE -->


    <!-- BLOCK AIR TEMPERATURE -->
    <?php
    if (isset($render_data['handler_sensor']['temperature_and_humidity']))
    {
        ?>
        <div class="data_box temperature_and_humidity">

            <div class="header"><?php echo It::t('home_aws', 'single__block_airtp')?></div>
            <div class="content">
                <?php $values = array_merge((array)$render_data['handler_sensor']['temperature_and_humidity']['Humidity'], (array)$render_data['handler_sensor']['temperature_and_humidity']['Temperature']); ?>

                <!-- if there are more than two sensors - show arrows to list blocks -->
                <?php if (count($values) > 2) {?>
                    <a class="list_left disabled" href="#"></a>
                    <a class="list_right" href="#"></a>
                <?php }?>
                <?php for ($i = 0; $i < count($values); $i++) {?>
                    <table style="<?php echo ($i && fmod($i+2,2) == 0 ? 'display:none;' : '') ?>" >
                        <tr>
                            <td>&nbsp;</td>
                            <td colspan="2" title="<?php echo ($values[$i]['sensor_display_name'].' - '.$values[$i]['sensor_id_code'])?> "><?php echo It::createTextPreview($values[$i]['sensor_display_name'], 15, '...')?></td>
                            <?php if (isset($values[$i+1])) {?>
                                <td colspan="2" title="<?php echo ($values[$i+1]['sensor_display_name'].' - '.$values[$i+1]['sensor_id_code'])?> "><?php echo It::createTextPreview($values[$i+1]['sensor_display_name'], 15, '...')?></td>
                            <?php }?>
                        </tr>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__block_airtp_last')?></th>
                            <td <?php if (isset($values[$i]['last_filter_errors'])){?> title="<?php echo implode("; ", $values[$i]['last_filter_errors'])?>" <?php }?>>
                                <div class="cover <?php echo (isset($values[$i]['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $values[$i]['change']?>"><?php echo $values[$i]['last']?></div></div>
                            </td>
                            <td><?php echo $values[$i]['metric_html_code']; ?></td>
                            <?php if ($values[$i+1]) {?>
                                <td <?php if (isset($values[$i+1]['last_filter_errors'])){?> title="<?php echo implode("; ", $values[$i+1]['last_filter_errors'])?>" <?php }?>>
                                    <div class="cover <?php echo (isset($values[$i+1]['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $values[$i+1]['change']?>"><?php echo $values[$i+1]['last']?></div></div>
                                </td>
                                <td><?php echo $values[$i+1]['metric_html_code']; ?></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__block_airtp_min')?></th>
                            <td title="<?php echo $values[$i]['mami_title'];?>"><div class="cover"><div><?php echo $values[$i]['min24']?></div></div></td>
                            <td><?php echo $values[$i]['metric_html_code']; ?></td>
                            <?php if ($values[$i+1]) {?>
                                <td title="<?php echo $values[$i+1]['mami_title'];?>"><div class="cover"><div><?php echo $values[$i+1]['min24']?></div></div></td>
                                <td><?php echo $values[$i+1]['metric_html_code']; ?></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__block_airtp_max')?></th>
                            <td title="<?php echo $values[$i]['mami_title'];?>"><div class="cover"><div><?php echo $values[$i]['max24']?></div></div></td>
                            <td><?php echo $values[$i]['metric_html_code']; ?></td>
                            <?php if ($values[$i+1]) {?>
                                <td title="<?php echo $values[$i+1]['mami_title'];?>"><div class="cover"><div><?php echo $values[$i+1]['max24']?></div></div></td>
                                <td><?php echo $values[$i+1]['metric_html_code']; ?></td>
                            <?php } ?>
                        </tr>

                        <?php if (isset($values[$i]['DewPoint']) || isset($values[$i+1]['DewPoint'])) {?>
                            <tr>
                                <th><?php echo It::t('home_aws', 'single__block_airtp_dp')?></th>
                                <?php if (isset($values[$i]['DewPoint'])) {?>
                                    <td><div class="cover"><div class="<?php echo $values[$i]['DewPoint']['change']; ?>"><?php echo $values[$i]['DewPoint']['last']; ?></div></div></td>
                                    <td><?php echo $values[$i]['DewPoint']['metric_html_code']; ?></td>
                                <?php } else {?>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                <?php } ?>
                                <?php if ($values[$i+1]) {?>
                                    <?php if ($values[$i+1]['DewPoint']) {?>
                                        <td><div class="cover"><div class="<?php echo $values[$i+1]['DewPoint']['change']; ?>"><?php echo $values[$i+1]['DewPoint']['last']; ?></div></div></td>
                                        <td><?php echo $values[$i+1]['DewPoint']['metric_html_code']; ?></td>
                                    <?php } else {?>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    <?php } ?>
                                <?php }?>
                            </tr>
                        <?php }?>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__yesterday')?></th>
                        </tr>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__block_airtp_min')?></th>
                            <td title="<?php echo $values[$i]['mami_title_y'];?>"><div class="cover"><div><?php echo $values[$i]['min24_y']?$values[$i]['min24_y']:'-' ?></div></div></td>
                            <td><?php echo $values[$i]['metric_html_code']; ?></td>
                            <?php if ($values[$i+1]) {?>
                                <td title="<?php echo $values[$i+1]['mami_title_y'];?>"><div class="cover"><div><?php echo $values[$i+1]['min24_y']?$values[$i+1]['min24_y']:'-' ?></div></div></td>
                                <td><?php echo $values[$i+1]['metric_html_code']; ?></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__block_airtp_max')?></th>
                            <td title="<?php echo $values[$i]['mami_title_y'];?>"><div class="cover"><div><?php echo $values[$i]['max24_y']?$values[$i]['max24_y']:'-'?></div></div></td>
                            <td><?php echo $values[$i]['metric_html_code']; ?></td>
                            <?php if ($values[$i+1]) {?>
                                <td title="<?php echo $values[$i+1]['mami_title_y'];?>"><div class="cover"><div><?php echo $values[$i+1]['max24_y']?$values[$i+1]['max24_y']:'-'?></div></div></td>
                                <td><?php echo $values[$i+1]['metric_html_code']; ?></td>
                            <?php } ?>
                        </tr>
                    </table>
                    <?php $i++;?>
                <?php } ?>
            </div>
        </div>
    <?php }?>
    <!-- END OF BLOCK AIR TEMPERATURE -->


    <!-- BLOCK SOIL TEMPERATURE -->
    <?php
    if (isset($render_data['handler_sensor']['temperature_soil']))
    {
        ?>
        <div class="data_box temperature_soil">
            <div class="header"><?php echo It::t('home_aws', 'single__block_soiltp')?></div>
            <div class="content">
                <?php $values = array_merge((array)$render_data['handler_sensor']['temperature_soil']['TemperatureSoil'], (array)$render_data['handler_sensor']['temperature_soil']['TemperatureWater']);?>
                <table>
                    <tr>
                        <td><?php echo It::t('home_aws', 'single__block_soiltp_sensor')?></td>
                        <td><?php echo It::t('home_aws', 'single__block_soiltp_depth')?></td>
                        <td><?php echo It::t('home_aws', 'single__block_soiltp_last')?></td>
                        <td>&nbsp;</td>
                        <td><?php echo It::t('home_aws', 'single__block_soiltp_min')?></td>
                        <td>&nbsp;</td>
                        <td><?php echo It::t('home_aws', 'single__block_soiltp_max')?></td>
                        <td>&nbsp;</td>
                        <?php if(isset($render_data['calculation']['temperature_soil']['DewPoint'])) {?>
                            <td><?php echo It::t('home_aws', 'single__block_soiltp_dp')?></td>
                            <td>&nbsp;</td>
                        <?php }?>
                        <td colspan="2"><?php echo It::t('home_aws', 'single__yesterday')?><br><?php echo It::t('home_aws', 'single__block_soiltp_min')?></td>
                        <td colspan="2"><?php echo It::t('home_aws', 'single__yesterday')?><br><?php echo It::t('home_aws', 'single__block_soiltp_max')?></td>
                    </tr>

                    <?php foreach ($values as $key => $value) {?>
                        <tr>
                            <th title="<?php echo ($value['sensor_display_name'].' - '.$value['sensor_id_code'])?> "><?php echo It::createTextPreview($value['sensor_display_name'], 20, '...')?></th>
                            <td><?php echo $value['depth']?></td>
                            <td <?php if (isset($value['last_filter_errors'])){?> title="<?php echo implode("; ", $value['last_filter_errors'])?>" <?php }?>>
                                <div class="cover <?php echo (isset($value['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['change']?>"><?php echo $value['last']?></div></div>
                            </td>
                            <td><?php echo $value['metric_html_code']?></td>
                            <td title="<?php echo $value['mami_title'];?>"><div class="cover"><div><?php echo $value['min24']?$value['min24']:'-'?></div></div></td>
                            <td><?php echo $value['metric_html_code']?></td>
                            <td title="<?php echo $value['mami_title'];?>"><div class="cover"><div><?php echo $value['max24']?$value['max24']:'-'?></div></div></td>
                            <td><?php echo $value['metric_html_code']?></td>
                            <?php if(isset($render_data['calculation']['temperature_soil']['DewPoint'])) {?>
                                <?php if( $value['DewPoint']) {?>
                                    <td><div class="cover"><div class="<?php echo $value['DewPoint']['change']?>"><?php echo $value['DewPoint']['last']?></div></div></td>
                                    <td><?php echo $value['DewPoint']['metric_html_code']?></td>
                                <?php } else {?>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                <?php }?>
                            <?php }?>
                            <td title="<?php echo $value['mami_title_y'];?>"><div class="cover"><div><?php echo $value['min24_y']?$value['min24_y']:'-'?></div></div></td>
                            <td><?php echo $value['metric_html_code']?></td>
                            <td title="<?php echo $value['mami_title_y'];?>"><div class="cover"><div><?php echo $value['max24_y']?$value['max24_y']:'-'?></div></div></td>
                            <td><?php echo $value['metric_html_code']?></td>
                        </tr>
                    <?php }?>
                </table>
            </div>
        </div>
    <?php } ?>
    <!-- END OF BLOCK SOIL TEMPERATURE -->


    <!-- BLOCK VISIBILITY -->
    <!-- BLOCK VISIBILITY -->
    <?php
    if (isset($render_data['handler_sensor']['visibility']))
    {
        $this->renderPartial('aws_visibility', array('renderData' => $render_data['handler_sensor']['visibility']));
    }
    ?>
    <!-- END OF BLOCK VISIBILITY -->


    <!-- BLOCK SEA LEVEL -->
    <?php
    if (isset($render_data['handler_sensor']['sea_level']))
    {
        ?>
        <div class="data_box sea_level">
            <div class="header"><?php echo It::t('home_aws', 'single__block_sea')?></div>
            <div class="content">

                <?php if (count($render_data['handler_sensor']['sea_level']['SeaLevelAWS']) > 1) {?>
                    <a class="list_left disabled" href="#"></a>
                    <a class="list_right" href="#"></a>
                <?php }?>

                <?php foreach($render_data['handler_sensor']['sea_level']['SeaLevelAWS'] as $key => $value) { ?>
                    <table style="<?php echo ($key ? 'display:none;' : '')?>">
                        <tr>
                            <td>&nbsp;</td>
                            <td colspan="6" style="text-align: center;" title="<?php echo ($value['sensor_display_name'].' - '.$value['sensor_id_code'])?> "><?php echo It::createTextPreview($value['sensor_display_name'], 45, '...')?></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <th style="text-align: center;"><?php echo It::t('home_aws', 'single__block_sea_mean')?></th>
                            <th>&nbsp;</th>
                            <th style="text-align: center;"><?php echo It::t('home_aws', 'single__block_sea_sigma')?></th>
                            <th>&nbsp;</th>
                            <th style="text-align: center;"><?php echo It::t('home_aws', 'single__block_sea_wave')?></th>
                            <th>&nbsp;</th>
                        </tr>
                        <tr>
                            <th><?php echo It::t('home_aws', 'single__block_sea_last')?></th>
                            <td <?php if (isset($value['sea_level_mean']['last_filter_errors'])){?> title="<?php echo implode("; ", $value['sea_level_mean']['last_filter_errors'])?>" <?php }?>>
                                <div class="cover <?php echo (isset($value['sea_level_mean']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['sea_level_mean']['change']?>"><?php echo $value['sea_level_mean']['last']?></div></div>
                            </td>
                            <td><?php echo $value['sea_level_mean']['metric_html_code']?></td>
                            <td <?php if (isset($value['sea_level_sigma']['last_filter_errors'])){?> title="<?php echo implode("; ", $value['sea_level_sigma']['last_filter_errors'])?>" <?php }?>>
                                <div class="cover <?php echo (isset($value['sea_level_sigma']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['sea_level_sigma']['change']?>"><?php echo $value['sea_level_sigma']['last']?></div></div>
                            </td>
                            <td><?php echo $value['sea_level_sigma']['metric_html_code']?></td>
                            <td <?php if (isset($value['sea_level_wave_height']['last_filter_errors'])){?> title="<?php echo implode("; ", $value['sea_level_wave_height']['last_filter_errors'])?>" <?php }?>>
                                <div class="cover <?php echo (isset($value['sea_level_wave_height']['last_filter_errors']) ? 'error' : '')?>"><div class="<?php echo $value['sea_level_wave_height']['change']?>"><?php echo $value['sea_level_wave_height']['last']?></div></div>
                            </td>
                            <td><?php echo $value['sea_level_wave_height']['metric_html_code']?></td>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align:right;"><?php echo It::t('home_aws', 'single__block_sea_last_high')?></th>
                            <td>
                                <div class="cover"><div><?php echo (isset($value['last_high']) ? $value['last_high']['value']: '-')?></div></div>
                            </td>
                            <td><?php echo $value['sea_level_mean']['metric_html_code']?></td>
                            <td colspan="3">
                                <?php echo (isset($value['last_high']) ? date('m/d/y, H:i', strtotime($value['last_high']['measuring_timestamp'])) : '&nbsp;' ) ?>

                            </td>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align:right;"><?php echo It::t('home_aws', 'single__block_sea_last_low')?></th>
                            <td>
                                <div class="cover"><div><?php echo (isset($value['last_low']) ? $value['last_low']['value'] : '-')?></div></div>
                            </td>
                            <td><?php echo $value['sea_level_mean']['metric_html_code']?></td>
                            <td colspan="3">
                                <?php echo (isset($value['last_low']) ? date('m/d/y, H:i', strtotime($value['last_low']['measuring_timestamp'])) : '&nbsp;') ?>
                            </td>
                        </tr>
                    </table>
                <?php }?>
            </div>
        </div>
    <?php }?>
    <!-- END OF BLOCK SEA LEVEL -->


    <!-- BLOCK CLOUDS -->
    <?php
    if (isset($render_data['handler_sensor']['clouds']))
    {
        $data = isset($render_data['handler_sensor']['clouds']['CloudHeightAWS'])
            ? 'CloudHeightAWS'
            : 'CloudHeightAwsDlm13m';

        $viewName = ((isset($render_data['handler_sensor']['clouds'][$data][0]['cloud_amount_height_1']) &&
                ($render_data['handler_sensor']['clouds'][$data][0]['cloud_amount_height_1']['last'] !== '-')) ||

            (isset($render_data['handler_sensor']['clouds'][$data][0]['cloud_amount_height_2']) &&
                ($render_data['handler_sensor']['clouds'][$data][0]['cloud_amount_height_2']['last'] !== '-')) ||

            (isset($render_data['handler_sensor']['clouds'][$data][0]['cloud_amount_height_3']) &&
                ($render_data['handler_sensor']['clouds'][$data][0]['cloud_amount_height_3']['last'] !== '-')) ||

            (isset($render_data['handler_sensor']['clouds'][$data][0]['cloud_amount_height_4']) &&
                ($render_data['handler_sensor']['clouds'][$data][0]['cloud_amount_height_4']['last'] !== '-')
            ))
            ? 'aws_sky_conditions'
            : 'aws_clouds';

        $this->renderPartial($viewName, array('renderData' => $render_data['handler_sensor']['clouds'][$data]));
    }
    ?>
    <!-- END OF BLOCK CLOUDS -->

    <!-- BLOCK SNOW DEPTH -->
    <?php
    if (isset($render_data['handler_sensor']['snow_depth']))
    {
        $this->renderPartial('aws_snow_depth', array('renderData' => $render_data['handler_sensor']['snow_depth']));
    }
    ?>
    <!-- END OF BLOCK SNOW DEPTH -->

    <!-- BLOCK SPECI REPORT -->
    <?php
    if (!is_null($render_data['speciReport']))
    {
        $this->renderPartial('aws_speci_report', array('report' => $render_data['speciReport']));
    }
    ?>
    <!-- END OF BLOCK SPECI REPORT -->

    <?php

    if (!is_null($render_data['handler_sensor']['water']))
    {
        $this->renderPartial('aws_water', array('report' => $render_data['handler_sensor']['water']));
    }
    ?>

    <div class="clear"></div>
    </div>
    <!-- END OF Container with all sensors boxes -->
<?php
}
?>