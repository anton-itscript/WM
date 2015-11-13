<?php

/*
 * Handler to work with data of Water Temperature sensor
 * 
 */

//Format WL1XXXXX
//Where XXXXX = height in MM 00198 = 19.8cm (= raw value)
//We need an offset in the sensor setup = number
//Adjusted value = raw value + offset value


class WaterLevelSensorHandler extends SensorHandler
{
    public $logger;
    public $features = array(
        array(
            'feature_name'          => 'Water Level',
            'feature_code'          => 'water_level',
            'measurement_type_code' => 'water_level',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 1,
            'aws_graph_using'       => 'Water Level',
            'aws_panel_show'        => 1,
        )
    );

     public $extra_features = array(
        array(
            'feature_name'          => 'Level Offset',
            'feature_code'          => 'level_offset',
            'measurement_type_code' => 'level_offset',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
        )
    );

    public function __construct($logger)
    {
        parent::__construct($logger);

    }

    public function getSensorDescription()
	{
        return "<p>Processes string like \"<b>WL1XXXXX</b>\",  where: <b>XXXXX</b> - is numerical from 00000 to 99999; </p><p>Example: <b>WL100198</b> = WL1 sensor's value height in MM 00198 = 19.8cm.</p>";
    }


    public function getRandomValue($features) {

        $data = rand(00000, 99999);
        return str_pad($data, 5, "0", STR_PAD_LEFT);
    }

    public function getFeatures()
    {
        return $this->features;
    }

    public function _prepareDataPairs() {

        $length = strlen($this->incoming_sensor_value);

        if ($length != 5)
            return false;

        $needed_feature = array();
        $level_offset_value=0;
        foreach($this->sensor_features_info as $feature) {
            if ($feature['feature_code'] == 'water_level') {
                $needed_feature = $feature;
                $offset_level_metric = $feature['metric_code'];
            }

            if ($feature['feature_code'] == 'level_offset') {
                $level_offset_value = $feature['feature_constant_value'];
                $offset_level_metric = $feature['metric_code'];
            }

        }



        $is_m = 0;


        if($this->incoming_sensor_value == 'MMMMM') {
            $is_m =  1;
            $value = $this->incoming_sensor_value;
        } else {
            $value = It::convertMetric($this->incoming_sensor_value, 'millimeter',$needed_feature['metric_code']) +  It::convertMetric($level_offset_value, $offset_level_metric, $needed_feature['metric_code']);
        }


        $this->prepared_pairs[] = array(
            'feature_code'     => 'water_level',
            'period'           => 1,
            'value'            => $value,
            'metric_id'        => $needed_feature['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature['metric_code'], $needed_feature['general_metric_code']),
            'is_m'             => $is_m
        );

        return true;
    }


    public function formatValue($value, $feature_name = '') {

        return number_format(round($value,1),1);
    }


    public function prepareXMLValue($xml_data, $db_features) {

        if ($xml_data['water_level'][0] == 'M') {
            $direction = 'M';
            $data = 'MMM';
        } else {
            $tmp = 10*It::convertMetric($xml_data['water_level'], 'mm', $db_features['water_level']);
            $direction = $tmp > 0 ? '1' : '0';
            $data = str_pad(abs(round($tmp)), 3, "0", STR_PAD_LEFT);
        }
        return $direction.$data;
    }


    public function getInfoForAwsPanel($sensor_pairs, $sensorList, $sensorData, $for = 'panel')
    {
        $return = array();

        $sensor_ids = array();
        $sensor_measuring_time = array();
        $last_logs_ids = array();
        $last_logs_per_station = array();

        foreach ($sensor_pairs as $value)
        {
            $sensor_ids[] = $value['sensor_id'];

            if (count($value['last_logs']) > 0)
            {
                $last_logs_ids[] = $value['last_logs'][0]->log_id;
                $sensor_measuring_time[$value['sensor_id']] = $value['last_logs'][0]->measuring_timestamp;
                $last_logs_per_station[$value['station_id']][0] = $value['last_logs'][0]->log_id;
            }

            if (count($value['last_logs']) > 1)
            {
                $last_logs_ids[] = $value['last_logs'][1]->log_id;
                $last_logs_per_station[$value['station_id']][1] = $value['last_logs'][1]->log_id;
            }
        }

        $depth_array = array();

        if ($for === 'single')
        {
            if (isset($sensorList['water_level']) && is_array($sensorList['water_level']))
            {
                foreach ($sensorList['water_level'] as $sensor_id => $sensorFeature)
                {
                    $depth_array[$sensor_id] = $this->formatValue($sensorFeature->feature_constant_value, 'water_level') .' '. $sensorFeature->metric->html_code;
                }
            }
        }

        $sensor_feature_ids = array();
        $sensor_feature_ids2 = array();

        if (isset($sensorList['water_level']) && is_array($sensorList['water_level']))
        {
            foreach ($sensor_ids as $sensor_id)
            {
                if (!isset($sensorList['water_level'][$sensor_id]))
                    continue;

                $sensorFeature = $sensorList['water_level'][$sensor_id];

                $sensor_feature_ids[] = $sensorFeature->sensor_feature_id;
                $sensor_feature_ids2[$sensor_id] = $sensorFeature->sensor_feature_id;

                $return[$sensorFeature->sensor->station_id][$sensor_id] = array(
                    'sensor_display_name'    => $sensorFeature->sensor->display_name,
                    'sensor_id_code'         => $sensorFeature->sensor->sensor_id_code,
                    'metric_html_code'       => is_null($sensorFeature->metric) ? '' : $sensorFeature->metric->html_code,
                    'group'                  => 'last_min24_max_24',
                    'last'                   => '-',
                    'max24'                  => '-',
                    'min24'                  => '-',
                    'change'                 => 'no',
                    'timezone_id'            => $sensorFeature->sensor->station->timezone_id,
                    'filter_max'             => $sensorFeature->default->filter_max,
                    'filter_min'             => $sensorFeature->default->filter_min,
                    'filter_diff'            => $sensorFeature->default->filter_diff,
                    'has_filter_max'         => $sensorFeature->has_filter_max,
                    'has_filter_min'         => $sensorFeature->has_filter_min,
                    'has_filter_diff'        => $sensorFeature->has_filter_diff,
                    'depth'                  => isset($depth_array[$sensor_id]) ? $depth_array[$sensor_id] : '-'
                );
            }
        }

        if (count($last_logs_ids) === 0)
        {
            return $return;
        }

        foreach ($return as $station_id => &$sensors)
        {
            foreach ($sensors as $sensor_id => &$sensorValues)
            {
                if (isset($sensorData['water_level'][$station_id][$sensor_id]) && (count($sensorData['water_level'][$station_id][$sensor_id]) > 0) &&
                    ($sensorData['water_level'][$station_id][$sensor_id][0]->listener_log_id == (isset($last_logs_per_station[$station_id][0]) ? $last_logs_per_station[$station_id][0] : -1)))
                {
                    $sensorValue = $sensorData['water_level'][$station_id][$sensor_id][0];

                    if ($sensorValue->is_m != 1)
                    {
                        $sensorValues['last'] = $this->formatValue($sensorValue->sensor_feature_value, 'water_level');

                        if (isset($sensorValues['has_filter_max']) && ($sensorValues['has_filter_max'] == 1))
                        {
                            if ($sensorValue->sensor_feature_value > $sensorValues['filter_max'])
                            {
                                $sensorValues['last_filter_errors'][] = "R > ". $sensorValues['filter_max'];
                            }
                        }

                        if (isset($sensorValues['has_filter_min']) && ($sensorValues['has_filter_min'] == 1))
                        {
                            if ($sensorValue->sensor_feature_value < $sensorValues['filter_min'])
                            {
                                $sensorValues['last_filter_errors'][] = "R < ". $sensorValues['filter_min'];
                            }
                        }

                        if (count($sensorData['water_level'][$station_id][$sensor_id]) > 3)
                        {
                            $previousSensorValue = array();
                            for($i=0;$i<4;$i++)
                                $previousSensorValue[]=$sensorData['water_level'][$station_id][$sensor_id][$i]->sensor_feature_value;
                            if (SensorHandler::checkTrend($previousSensorValue,1))
                                $sensorValues['change'] = 'up';
                            else if (SensorHandler::checkTrend($previousSensorValue,-1))
                                $sensorValues['change'] = 'down';

                            if (isset($sensorValues['has_filter_diff']) && ($sensorValues['has_filter_diff'] == 1))
                            {
                                if (abs($sensorValue->sensor_feature_value - $previousSensorValue[1]) > $sensorValues['filter_diff'])
                                {
                                    $sensorValues['last_filter_errors'][] = "|R1 - R0| > ". $sensorValues['filter_diff'];
                                }
                            }
                        }
                    }

                    if (isset($sensor_measuring_time[$sensor_id]))
                    {
                        $maxmin = $this->getMaxMinInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 1, 0, 'water_level');
                        $sensorValues['max24'] = $maxmin['max'];
                        $sensorValues['min24'] = $maxmin['min'];
                        $sensorValues['mami_title'] = $maxmin['mami_title'];
                        $maxmin = $this->getMaxMinInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 2, 0, 'water_level');
                        $sensorValues['max24_y'] = $maxmin['max'];
                        $sensorValues['min24_y'] = $maxmin['min'];
                        $sensorValues['mami_title_y'] = $maxmin['mami_title'];
                    }
                }

                foreach (array('filter_min', 'filter_max', 'filter_diff', 'has_filter_min', 'has_filter_max', 'has_filter_diff') as $unsetfield)
                {
                    unset($return[$station_id][$sensor_id][$unsetfield]);
                }
            }
        }

        return $return;
    }


}

?>
