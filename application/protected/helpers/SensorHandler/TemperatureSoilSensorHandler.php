<?php

/*
 * Handler to work with data of Soil Temperature sensor
 * 
 */

Yii::import('application.helpers.SensorHandler.TemperatureSensorHandler');

class TemperatureSoilSensorHandler extends TemperatureSensorHandler
{
    public $features = array(
        array(
            'feature_name'          => 'Instantaneous Value', // name of measurement
            'feature_code'          => 'temperature_soil', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'temperature_soil', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 1, // display info about this measurement in places where we need  to display info about  only one measurement for sensor
            'aws_graph_using'       => 'Temperature Soil',  // if takes part in “AWS Graph” page
            'aws_panel_show'        => 1,
        )
    );

    public $extra_features = array(
        array(
            'feature_name'          => 'Depth',
            'feature_code'          => 'depth',
            'measurement_type_code' => 'depth',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
        )
    );      

    public function getSensorDescription()
	{
        return "<p>Processes string like \"<b>TP1 XXXX</b>\",  where: <b>XXXX</b> - is numerical from 0999 to 1999; <b>1st X:</b> 1 = positive, 0 = negative; <b>Last XXX</b>:  temperature value</p><p>Example: <b>TP10245</b> = TP1 sensor's value is -24.5.</p><p>This sensor has extra feature:  <b>Depth</b>.</p>";
    }

    //abrakadabra


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
            if (isset($sensorList['depth']) && is_array($sensorList['depth']))
            {
                foreach ($sensorList['depth'] as $sensor_id => $sensorFeature)
                {
                    $depth_array[$sensor_id] = $this->formatValue($sensorFeature->feature_constant_value, 'depth') .' '. $sensorFeature->metric->html_code;
                }
            }
        }

        $sensor_feature_ids = array();
        $sensor_feature_ids2 = array();

        if (isset($sensorList['temperature_soil']) && is_array($sensorList['temperature_soil']))
        {
            foreach ($sensor_ids as $sensor_id)
            {
                if (!isset($sensorList['temperature_soil'][$sensor_id]))
                    continue;

                $sensorFeature = $sensorList['temperature_soil'][$sensor_id];

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
                if (isset($sensorData['temperature_soil'][$station_id][$sensor_id]) && (count($sensorData['temperature_soil'][$station_id][$sensor_id]) > 0) &&
                    ($sensorData['temperature_soil'][$station_id][$sensor_id][0]->listener_log_id == (isset($last_logs_per_station[$station_id][0]) ? $last_logs_per_station[$station_id][0] : -1)))
                {
                    $sensorValue = $sensorData['temperature_soil'][$station_id][$sensor_id][0];

                    if ($sensorValue->is_m != 1)
                    {
                        $sensorValues['last'] = $this->formatValue($sensorValue->sensor_feature_value, 'temperature_soil');

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

                        if (count($sensorData['temperature_soil'][$station_id][$sensor_id]) > 3)
                        {
                            $previousSensorValue = array();
                            for($i=0;$i<4;$i++)
                                $previousSensorValue[]=$sensorData['temperature_soil'][$station_id][$sensor_id][$i]->sensor_feature_value;
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
                        $maxmin = $this->getMaxMinInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 1, 0, 'temperature_soil');
                        $sensorValues['max24'] = $maxmin['max'];
                        $sensorValues['min24'] = $maxmin['min'];
                        $sensorValues['mami_title'] = $maxmin['mami_title'];
                        $maxmin = $this->getMaxMinInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 2, 0, 'temperature_soil');
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

    public function formatValue($value, $feature_name = 'temperature_soil')
    {
        if ($feature_name === 'temperature_soil')
        {
            return ($value > 0 ? '+':'') . number_format(round($value, 1), 1);
        }
        else if ($feature_name === 'depth')
        {
            return round($value, 1);
        }
    }

    public function _prepareDataPairs()
    {
        $length = strlen($this->incoming_sensor_value);

        if ($length <> 4)
            return false;

        $needed_feature = array();

        foreach($this->sensor_features_info as $feature) {
            if ($feature['feature_code'] == 'temperature_soil') {
                $needed_feature = $feature;
            }
        }

        $info_1 = intval(substr($this->incoming_sensor_value, 0, 1));
        $coef = $info_1 == 1 ? 1 : -1;
        $value = substr($this->incoming_sensor_value, 1, 3)*$coef/10;
        $is_m = $this->incoming_sensor_value == 'MMMM' ? 1 : 0;
        $this->prepared_pairs[] = array(
            'feature_code' => 'temperature_soil',
            'period'       => 1,
            'value'        => $value,
            'metric_id'    => $needed_feature['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature['metric_code'], $needed_feature['general_metric_code']),
            'is_m'         => $is_m
        );

        return true;
    }

    public function getRandomValue($features) {

        $data = rand(-20, 40);

        $data = It::convertMetric($data, 'celsius', $features['temperature_soil']);
        $direction = $data > 0 ? '1' : '0';

        $data = str_pad(abs($data), 3, "0", STR_PAD_LEFT);

        return $direction.$data;
    }

    public function prepareXMLValue($xml_data, $db_features) {

        if ($xml_data['temperature_soil'][0] == 'M') {
            $direction = 'M';
            $data = 'MMM';
        } else {
            $tmp = 10*It::convertMetric($xml_data['temperature_soil'], 'celsius', $db_features['temperature_soil']);
            $direction = $tmp > 0 ? '1' : '0';
            $data = str_pad(abs(round($tmp)), 3, "0", STR_PAD_LEFT);
        }
        return $direction.$data;
    }

}

?>
