<?php

class AWSGraphForm extends CFormModel
{
    use __AWSFormTrait;

    private $session_name = 'aws_graph';
    private $custom_sensor_features = ['custom_wind_rose' => 'Wind Rose'];

    public function checkSensorFeatureCode()
    {
        if ($this->hasErrors()) {
            return false;
        }

        if (!count($this->getSelectedGroupSensorFeatureCode())) {
            $this->addError('sensor_feature_code',$this->getAttributeLabel('sensor_feature_code') . ' cannot be blank.');
            return false;
        }

        // select temperature
        $bool = 0;
        foreach($this->getSelectedGroupSensorFeatureCode() as $handler_id_code => $group) {
            $bool += in_array($handler_id_code,['Temperature', 'TemperatureWater', 'TemperatureSoil']) ? 1 : -1;
        }

        if ($bool == count($this->getSelectedGroupSensorFeatureCode())) {
            return true;
        } elseif (count($this->getSelectedGroupSensorFeatureCode()) != 1) {
            $this->addError('sensor_feature_code', 'Please, select features from one group.');
            return false;
        }

        return true;
    }

    public function prepareList()
    {
        $result = ['series_names' => [], 'series_data' => []];

        date_default_timezone_set('UTC');
        $start_datetime = strtotime($this->date_from . ' ' . $this->time_from);
        $end_datetime   = strtotime($this->date_to . ' ' . $this->time_to);
        $series_names = array();
        $series_data = array();

        if (!$this->hasErrors() && $this->station_id) {
            $stationResult = Station::model()->getStationsWithSensorsFeatures($this->station_id);



            foreach ($this->sensor_feature_code as $group_code => $group) {
                if (!empty($group)) {
                    $i=0;
                    foreach ($stationResult as $station) {
                        $colorWalker = new Color($station->color);

                        foreach ($group as $sensor_feature_code) {

                            //sensors data
                            foreach ($station->sensors as $sensor) {
                                foreach ($sensor->features as $sensorFeature) {

                                    if ($sensor_feature_code == $sensorFeature->feature_code) {
                                        $series_names[$i]['name'] = $station->station_id_code .', '.$sensor->sensor_id_code.' '.$this->getGroupSensorsFeaturesList()[$sensor->handler->handler_id_code]['sensor_features'][$sensorFeature->feature_code];
                                        $series_names[$i]['params']['color'] = '#'.$colorWalker->getHex(); ;

//                                        $colorWalker->mix('888888');
                                        $colorWalker->darken();



                                        $qb = new CDbCriteria();
                                        $qb->select ='t.sensor_feature_normalized_value, t.sensor_feature_value, t.measuring_timestamp';
                                        $qb->addCondition('t.sensor_feature_id = :sensor_feature_id');
                                        $qb->addBetweenCondition('t.measuring_timestamp',date('Y-m-d H:i:s', $start_datetime),date('Y-m-d H:i:s', $end_datetime));
                                        $qb->order = 't.measuring_timestamp ASC';
                                        $qb->params[':sensor_feature_id'] = $sensorFeature->sensor_feature_id;
                                        $found_data = SensorData::model()->long()->findAll($qb);


                                        $tmp = array();
                                        if ($this->accumulation_period != 0) {
                                            foreach($found_data as $data) {
                                                $period = ($start_datetime + (intval((strtotime($data->measuring_timestamp) - $start_datetime) / ($this->accumulation_period * 60)) + 1) * $this->accumulation_period * 60);
                                                $period = $period > $end_datetime ? $end_datetime : $period;
                                                $period *= 1000;
                                                $tmp[$period] = [
                                                    'x' => $period,
                                                    'y' => $tmp[$period] ? $tmp[$period]['y'] : 0 + floatval($data->sensor_feature_value),

                                                ];
                                            }
                                        } else {
                                            foreach ($found_data as $data) {
                                                $period = strtotime($data->measuring_timestamp) * 1000;
                                                $tmp[$period] = [
                                                    'x' => $period,
                                                    'y' => floatval($data->sensor_feature_value),


                                                ];
                                            }
                                        }

                                        $series_data[$i] = array_values($tmp);

                                        $i++;
                                    }

                                }

                            }

                            //calculation data
                            foreach ($station->station_calculation as $stationCalculation) {

                                if ($stationCalculation->handler_id == $this->calc_handlers[$sensor_feature_code]) {
                                    $colorWalker = new Color($station->color);
                                    $series_names[$i]['name'] = $station->station_id_code . ', ' . $stationCalculation->handler->display_name;
                                    $series_names[$i]['params']['color'] = '#' . $colorWalker->darken();

                                    $start_datetime = strtotime($this->date_from . ' ' . $this->time_from);
                                    $end_datetime = strtotime($this->date_to . ' ' . $this->time_to);

                                    $qb = new CDbCriteria();
                                    $qb->with = [
                                        'ListenerLog' => [
                                            'select' => 'ListenerLog.measuring_timestamp',
                                            'condition' => "ListenerLog.measuring_timestamp BETWEEN '" . date('Y-m-d H:i:s', $start_datetime) . "' AND '" . date('Y-m-d H:i:s', $end_datetime) . "'"
                                        ]
                                    ];

                                    $qb->select = 't.calculation_id, t.value';
                                    $qb->order = 'ListenerLog.measuring_timestamp ASC';
                                    $qb->condition = 't.calculation_id = ?';
                                    $qb->params = [$stationCalculation->calculation_id];
                                    $found_data = StationCalculationData::model()->long()->findAll($qb);

                                    $tmp = array();
                                    foreach ($found_data as $data) {
                                        $tmp[] = ['x' => strtotime($data->ListenerLog->measuring_timestamp) * 1000, 'y' => floatval($data->value)];
                                    }
                                    $series_data[$i] = $tmp;

                                }
                            }


                            //custom_wind_rose
                            if (in_array($sensor_feature_code, array_keys($this->custom_sensor_features))) {
                                switch ($sensor_feature_code) {
                                    case 'custom_wind_rose':
                                        return $this->prepareCustomWindRose();
                                    default:
                                        return array();
                                }
                            }
                        }

                        $i++;

                    }

                }
            }

        }

        $result['series_names'] = array_values($series_names);
        $result['series_data'] = array_values($series_data);

        /**
         * depracated
         */
        if (!$this->hasErrors() && $this->station_id
            && true==false
        ) {
            foreach ($this->sensor_feature_code as $group_code => $group) {
                if (!empty($group)) {
                    foreach ($group as $sensor_feature_code) {
                        if (in_array($sensor_feature_code, array_keys($this->calc_handlers))) {
                            $data = $this->prepareCalculationList($this->calc_handlers[$sensor_feature_code]);


                        } elseif (in_array($sensor_feature_code, array_keys($this->custom_sensor_features))) {
                            switch ($sensor_feature_code) {
                                case 'custom_wind_rose':
                                    return $this->prepareCustomWindRose();
                                default:
                                    return array();
                            }
                        } else {
                            $data = $this->prepareSensorList($sensor_feature_code,$group_code);
                        }

                        $result['series_names'] = array_merge($result['series_names'], $data['series_names']);
                        $result['series_data'] = array_merge($result['series_data'], $data['series_data']);
                    }
                }
            }
        }


        if (empty($result['series_names'])) {
            return array();
        }

        return $result;
    }

    /**
     * Prepare custom: Wind Rose
     *
     * @return array
     */
    private function prepareCustomWindRose()
    {
        /**
         * Param
         */
        $speed_limit = 99;
        $speed_range = array(0.5,2,4,6,8,10,$speed_limit);
        $direction_range = range(22.5, 360, 22.5);

        /**
         * Load data
         */
        $start_datetime = strtotime($this->date_from.' '.$this->time_from);
        $end_datetime = strtotime($this->date_to.' '.$this->time_to);

        $qb = new CDbCriteria();
        $qb->select = 't.feature_code';
        $qb->with = array(
            'sensor' => array('select' => 'sensor.station_id'),
            'sensor_data' => array(
                'alias' => 'sd',
                'select' => 'sd.measuring_timestamp, sd.sensor_feature_normalized_value'
            )
        );
        $qb
            ->addInCondition('t.feature_code',array('wind_direction_1', 'wind_speed_1'))
            ->addInCondition('sensor.station_id',$this->station_id)
            ->addBetweenCondition('sd.measuring_timestamp',date('Y-m-d H:i:s', $start_datetime),date('Y-m-d H:i:s', $end_datetime));

        $data = StationSensorFeature::model()->long()->findAll($qb);
        if (!$data) return array();

        /**
         * Prepare
         */
        $prepare_data = array();
        foreach($data as $sensor_feature) {
            foreach($sensor_feature->sensor_data as $sensor_data) {
                $prepare_data
                    [$sensor_feature->sensor->station_id . '_' . $sensor_data->measuring_timestamp]
                    [$sensor_feature->feature_code]
                        = $sensor_data->sensor_feature_normalized_value;
            }
        }

        /**
         * Processing
         */
        $result = array();
        $count_data = 0;
        foreach($speed_range as $sp_key => $speed) {
            switch($speed) {
                case $speed_range[0]:
                    $name = '< ' . $speed;
                    break;
                case $speed_limit:
                    $name = '> ' . $speed_range[$sp_key-1];
                    break;
                default:
                    $name = $speed_range[$sp_key-1] . '-' . $speed;
            }
            $result[$speed] = array(
                'name' => $name,
                'data' => array_fill(0,count($direction_range) - 1,0)
            );
        }

        foreach($prepare_data as $v) {
            if (count($v) == 2) {
                $count_data++;
                // detect speed
                $sp = $speed_limit;
                foreach($speed_range as $speed) {
                    if ($v['wind_speed_1'] <= $speed) {
                        $sp = $speed;
                        break;
                    }
                }
                // detect direction
                $dr = count($direction_range) - 1;
                foreach($direction_range as $dr_id => $direction) {
                    if ($v['wind_direction_1'] <= $direction) {
                        $dr = $dr_id;
                        break;
                    }
                }
                $result[$sp]['data'][$dr]++;
            }
        }

        // Convert to %
        if ($count_data) {
            foreach($speed_range as $speed) {
                foreach($direction_range as $dr_id => $direction){
                    $result[$speed]['data'][$dr_id] = round($result[$speed]['data'][$dr_id]/$count_data, 3);
                }
            }
        } else {
            return array();
        }


        return $result;
    }

    /**
     * Prepare calculation list
     *
     * @param $handler_id int
     * @return array
     */
    private function prepareCalculationList($handler_id)
    {
        $qb = new CDbCriteria();
        $qb->with = [
            'Station' => [
                'select'    => array('Station.station_id_code', 'Station.color'),
                'condition' => 'Station.station_id IN (' . implode(',', $this->station_id) . ')',
            ]
        ];

        $qb->condition = "t.handler_id = {$handler_id}";
        $calculations = StationCalculation::model()->long()->findAll($qb);

        if ($calculations) {
            $i = 0;
            foreach ($calculations as $calculation) {
                $colorWorker = new Color($calculation->Station->color);
                $series_names[$i]['name'] = $calculation->Station->station_id_code . ', '. $calculation->handler->display_name;
                $series_names[$i]['params']['color'] = '#'.$colorWorker->getHex();
                $i++;
            }

            $start_datetime = strtotime($this->date_from . ' ' . $this->time_from);
            $end_datetime   = strtotime($this->date_to . ' ' . $this->time_to);

            $qb = new CDbCriteria();
            $qb->with = [
                'ListenerLog' => [
                    'select'    => 'ListenerLog.measuring_timestamp',
                    'condition' => "ListenerLog.measuring_timestamp BETWEEN '" . date('Y-m-d H:i:s', $start_datetime) . "' AND '" . date('Y-m-d H:i:s', $end_datetime)."'"
                ]
            ];
            $qb->select = 't.calculation_id, t.value';
            $qb->order = 'ListenerLog.measuring_timestamp ASC';
            $qb->condition = 't.calculation_id = ?';

            foreach ($calculations as $calculation) {
                $qb->params = [$calculation->calculation_id];
                $found_data = StationCalculationData::model()->long()->findAll($qb);

                $tmp = array();
                foreach ($found_data as $data) {
                    $tmp[] = ['x' => strtotime($data->ListenerLog->measuring_timestamp)*1000, 'y' => floatval($data->value)];
                }
                $series_data[] = $tmp;
            }

        }

        return [
            'series_names' => isset($series_names)?$series_names:[],
            'series_data'  => isset($series_data)?$series_data:[]
        ];
    }

    /**
     * Prepare sensor list
     *
     * @param $sensor_feature_code
     * @param $handler_id_code
     *
     * @return array
     */
    private function prepareSensorList($sensor_feature_code, $handler_id_code)
    {
        date_default_timezone_set('UTC');
        $start_datetime = strtotime($this->date_from . ' ' . $this->time_from);
        $end_datetime   = strtotime($this->date_to . ' ' . $this->time_to);

        $qb = new CDbCriteria();
        $qb->with = [
            'sensor.station' => [
                'select' => array('station.station_id_code','station.color'),
                'condition' => 'station.station_id IN (' . implode(',', $this->station_id) . ')',
            ],
            'sensor.handler' => [
                'select' => false,
                'condition' => "handler.handler_id_code LIKE '" . $handler_id_code . "'",
            ]
        ];
        $qb->select = ['t.feature_code', 't.feature_display_name'];
        $qb->addCondition("t.feature_code LIKE '$sensor_feature_code'");
        $features = StationSensorFeature::model()->long()->findAll($qb);

        if ($features) {
            $i = 0;
            foreach ($features as $feature) {

                    $colorWorker = new Color($feature->sensor->station->color);
                $series_names[$i] = array();

                $series_names[$i]['name'] = $feature->sensor->station->station_id_code. ', '. $feature->sensor->sensor_id_code . ', ' . $this->getGroupSensorsFeaturesList()[$handler_id_code]['sensor_features'][$feature->feature_code];
                $series_names[$i]['params']['color'] = '#'.$colorWorker->getHex();
                $i++;
                $colorWorker = new Color($feature->sensor->station->color);
            }

            $qb = new CDbCriteria();
            $qb->select ='t.sensor_feature_normalized_value, t.sensor_feature_value, t.measuring_timestamp';
            $qb->addCondition('t.sensor_feature_id = :sensor_feature_id');
            $qb->addBetweenCondition('t.measuring_timestamp',date('Y-m-d H:i:s', $start_datetime),date('Y-m-d H:i:s', $end_datetime));
            $qb->order = 't.measuring_timestamp ASC';

            foreach ($features as $feature) {
                $qb->params[':sensor_feature_id'] = $feature->sensor_feature_id;
                $found_data = SensorData::model()->long()->findAll($qb);

                $tmp = array();
                if ($this->accumulation_period != 0) {
                    foreach($found_data as $data) {
                        $period = ($start_datetime + (intval((strtotime($data->measuring_timestamp) - $start_datetime) / ($this->accumulation_period * 60)) + 1) * $this->accumulation_period * 60);
                        $period = $period > $end_datetime ? $end_datetime : $period;
                        $period *= 1000;
                        $tmp[$period] = [
                            'x' => $period,
                            'y' => $tmp[$period] ? $tmp[$period]['y'] : 0 + floatval($data->sensor_feature_value),
                        ];
                    }
                } else {
                    foreach ($found_data as $data) {
                        $period = strtotime($data->measuring_timestamp) * 1000;
                        $tmp[$period] = [
                            'x' => $period,
                            'y' => floatval($data->sensor_feature_value)];
                    }
                }
                $series_data[] = array_values($tmp);
            }
        }

        return [
            'series_names' => isset($series_names) ? $series_names : [],
            'series_data'  => isset($series_data) ? $series_data : []
        ];
    }
}