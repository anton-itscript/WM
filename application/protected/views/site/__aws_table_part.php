<?php
/** @var AWSTableForm $form */
/** @var array $res */
/** @var int $show_station */

?>
     
<?php if ($show_station > 0): ?>
    <div class="dateScroll">
        <table class="tablelist awstable">
            <tr>
                <th rowspan="2">Date & UTC Time</th>
            </tr>
        </table>
    </div>

    <div class="topScroll">
        <table class="tablelist awstable">
            <tr>
                <?php foreach ($res['prepared_header'] as $prepared_header_key => $prepared_header_value): ?>
                    <?php if ($prepared_header_value['station_sensors'][$show_station]): ?>
                        <th colspan="<?php echo $prepared_header_value['station_sensors'][$show_station]; ?>">
                            <?php if ($prepared_header_value['handler_id_code']) : ?>
                                <?php echo $form->getGroupSensorsFeaturesList()[$prepared_header_value['handler_id_code']]['sensor_features'][$prepared_header_value['sensor_feature_code']]; ?>
                            <?php else: ?>
                                <?php echo $form->getSensorsFeaturesList()[$prepared_header_value['sensor_feature_code']]; ?>
                            <?php endif; ?>
                        </th>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($res['prepared_header'] as $prepared_header_key => $prepared_header_value): ?>
                    <?php if($prepared_header_value['station_sensors'][$show_station]): ?>
                        <?php foreach ($prepared_header_value['sensors'] as $prepared_header_value_sensor_key => $prepared_header_value_sensor_value): ?>
                            <?php if ($prepared_header_value_sensor_value['station_id'] == $show_station): ?>
                                <th><?php echo $prepared_header_value_sensor_value['sensor_id_code']; ?></th>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach;?>
            </tr>
        </table>
    </div>
    <div class="leftScroll">
        <table class="tablelist awstable">
            <?php foreach ($res['prepared_data'] as $prepared_data_key => $prepared_data_value):?>
                <?php if (in_array($show_station, $prepared_data_value['stations']) && $prepared_data_value['data']): ?>
                    <tr>
                        <td><?php echo $prepared_data_key ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </table>
    </div>
    <div class="mainScroll">
        <table class="tablelist awstable">
        <?php foreach ($res['prepared_data'] as $prepared_data_key => $prepared_data_value): ?>
            <?php if (in_array($show_station, $prepared_data_value['stations']) && $prepared_data_value['data']): ?>
            <tr>
                <?php foreach($prepared_data_value['data'] as $prepared_data_value_k => $prepared_data_value_v): ?>
                    <?php if ($prepared_data_value_v['station_id'] == $show_station): ?>
                        <td>&nbsp;<?php echo $prepared_data_value_v['value']; ?></td>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </table>
    </div>


<?php else: ?>
    <div class="dateScroll" style="height: 62px">
        <table class="tablelist awstable">
            <tr>
                <th rowspan="2">Date & Time</th>
            </tr>
        </table>
    </div>

    <div class="topScroll">
        <table class="tablelist awstable">
            <tr>
                <?php foreach ($res['prepared_header'] as $prepared_header_key => $prepared_header_value): ?>
                    <?php if($prepared_header_value['station_sensors']): ?>
                        <th colspan="<?php echo count($prepared_header_value['sensors'])?>"><?php echo $form->getSensorsFeaturesList()[$prepared_header_value['sensor_feature_code']]; ?></th>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($res['prepared_header'] as $prepared_header_key => $prepared_header_value): ?>
                    <?php if($prepared_header_value['station_sensors']): ?>
                        <?php foreach ($prepared_header_value['sensors'] as $prepared_header_value_sensor_key => $prepared_header_value_sensor_value): ?>
                                <th>
                                    <div class="small"><?php echo $form->getStationsList()[$prepared_header_value_sensor_value['station_id']]; ?>,</div>
                                    <?php echo $prepared_header_value_sensor_value['sensor_id_code']; ?>
                                </th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>

        </table>
    </div>
    <div class="leftScroll">
        <table class="tablelist awstable">
            <?php foreach ($res['prepared_data'] as $prepared_data_key => $prepared_data_value): ?>
                <tr>
                    <td><?php echo $prepared_data_key  ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div class="mainScroll">
        <table class="tablelist awstable">
            <?php foreach ($res['prepared_data'] as $prepared_data_key => $prepared_data_value): ?>
                <tr>
                    <?php foreach($prepared_data_value['data'] as $prepared_data_value_k => $prepared_data_value_v): ?>
                        <td><?php echo $prepared_data_value_v['value']; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

<?php endif; ?>