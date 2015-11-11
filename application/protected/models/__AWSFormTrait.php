<?php

/**
 * Class __AWSFormTrait
 *
 * @property $session_name string
 * @property $custom_sensor_features
 * @method hasErrors
 * @method addError
 */
trait __AWSFormTrait
{
    public $date_from;
    public $date_to;
    public $time_from;
    public $time_to;

    public $station_id;
    /** @var  array */
    public $sensor_feature_code;

    /** @var int - minutes for accumulation features
     *            range [0, 30, 60, 180, 360, 720, 1440, 2880], 0 - without accumulation
     *            getAccumulationList() */
    public $accumulation_period;
    /** @var array - allowed features for accumulation */
    private $accumulate_features = ['rain_in_period', 'solar_radiation_in_period', 'sun_duration_in_period'];

    private $group_sensor_features;
    private $calc_handlers = ['calc_1' => 1, 'calc_2' => 2];
    private $stations;

    private $isPrepareProcess = false;

    public function init()
    {
        $this->getFromMemory();

        $this->getStationsList();
        $this->getGroupSensorsFeaturesList();

        return parent::init();
    }

    public function attributeLabels() {
        return array(
            'station_id'          => It::t('site_label', 'filter_select_stations'),
            'sensor_feature_code' => It::t('site_label', 'filter_select_features'),
            'date_from'           => It::t('site_label', 'filter_date_from'),
            'date_to'             => It::t('site_label', 'filter_date_to'),
            'time_from'           => It::t('site_label', 'filter_time_from'),
            'time_to'             => It::t('site_label', 'filter_time_to'),
            'accumulation_period' => It::t('site_label', 'filter_accumulation_period'),
        );
    }

    public function afterValidate()
    {
        if (!$this->hasErrors()) {
            $this->isPrepareProcess = true;
            $this->putToMemory();
        }
        parent::afterValidate();
    }

    public function rules() {
        return array(
            array('station_id', 'checkStationId'),
            array('date_from,date_to', 'match', 'pattern' => '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/'),
            array('date_from,date_to', 'length', 'max' => 10),
            array('date_to', 'checkDatesInterval'),
            array('time_from,time_to', 'match', 'pattern' => '/^(\d{1,2}):(\d{1,2})$/'),
            array('time_from,time_to', 'length', 'max' => 5),
            array('sensor_feature_code', 'required'),
            array('sensor_feature_code', 'checkSensorFeatureCode'),
            array('accumulation_period', 'in', 'range' => array_keys($this->getAccumulationList())),
            array('accumulation_period', 'checkAccumulationPeriod'),
        );
    }

    public function checkDatesInterval()
    {
        if (!$this->hasErrors('date_from') && !$this->hasErrors('date_to') && !$this->hasErrors('time_from') && !$this->hasErrors('time_to')) {
            if (strtotime($this->date_to .' '. $this->time_to) <= strtotime($this->date_from .' '. $this->time_from)) {
                $this->addError('date_to', 'End date and time must be later than start.');
                return false;
            }
        }
        return true;
    }

    public function checkAccumulationPeriod()
    {
        if ($this->hasErrors()) {
            return false;
        }

        /** Sensor validate */
        if (isset($this->accumulation_period) && $this->accumulation_period != 0) {
            foreach($this->getSensorFeatureCode() as $feature_code) {
                if (!in_array($feature_code, $this->accumulate_features)) {
                    $this->addError('accumulation_period', 'Please fix the following input errors: Accumulation only applies to rain, sun duration and solar radiation. If you want to see accumulated rain or sun, make sure you do not select other features. Please select \'Rain\', \'Sun Radiation\' or \'Sun Duration\' and click \'Filter\' again');
                    return false;
                }
            }
        }

        /** station validate */
        $qb = new CDbCriteria();
        $qb->select = ['event_message_period', 'station_id_code'];
        $qb->addInCondition('station_id',$this->station_id);

        if (isset($this->accumulation_period)
            && $this->accumulation_period != 0
            && $stations = Station::model()->findAll($qb)
        ) {
            $stations_error = array();
            foreach($stations as $station) {
                if ($station->event_message_period > $this->accumulation_period) {
                    $stations_error[] = $station->station_id_code;
                }
            }
            if (count($stations_error)) {
                $this->addError('event_message_period','The period you selected is smaller than the weather message interval in stations ' . implode(',', $stations_error) . '. Please increase the period and click \'Filter\' again.');
            }
        }

        if ($this->hasErrors('accumulation_period')) {
            return false;
        } else {
            return true;
        }
    }

    public function checkStationId() {

        $stations = $this->getStationsList();

        if ($stations && $this->station_id && is_array($this->station_id)) {
            foreach ($this->station_id as $key => $value) {
                if (!in_array($value, array_keys($stations))) {
                    unset($this->station_id[$key]);
                }
            }
        }

        if (!$this->station_id || !is_array($this->station_id)) {
            $this->addError('station_id', 'Choose at least one station.');
            return false;
        }

        return true;
    }

    /**
     * Put to session
     */
    public function putToMemory()
    {
        $session = new CHttpSession();
        $session->open();

        $session[$this->session_name] = array(
            'station_id'            => $this->station_id,
            'date_from'             => $this->date_from,
            'date_to'               => $this->date_to,
            'time_from'             => $this->time_from,
            'time_to'               => $this->time_to,
            'sensor_feature_code'   => $this->sensor_feature_code,
            'accumulation_period'   => $this->accumulation_period,
        );
    }

    /**
     * Clear session
     */
    public function clearMemory()
    {
        $session = new CHttpSession();
        $session->open();
        $session[$this->session_name] = array();
        $this->getFromMemory();
    }

    /**
     * Get from session
     */
    public function getFromMemory()
    {
        $session = new CHttpSession();
        $session->open();

        $this->station_id            = $session[$this->session_name] ? $session[$this->session_name]['station_id'] : 0;
        $this->date_from             = $session[$this->session_name] ? $session[$this->session_name]['date_from'] : date('m/d/Y', mktime() - 259200);
        $this->date_to               = $session[$this->session_name] ? $session[$this->session_name]['date_to'] : date('m/d/Y');
        $this->time_from             = $session[$this->session_name] ? $session[$this->session_name]['time_from'] : '00:00';
        $this->time_to               = $session[$this->session_name] ? $session[$this->session_name]['time_to'] : '23:59';
        $this->sensor_feature_code   = $session[$this->session_name] ? $session[$this->session_name]['sensor_feature_code'] : [];
        $this->accumulation_period   = $session[$this->session_name] ? $session[$this->session_name]['accumulation_period'] : 0;
    }

    /**
     * @return array|null $this->stations
     */
    public function getStationsList()
    {
        if (!$this->stations) {
            $this->stations = Station::prepareStationList(['aws','awos']);
        }
        return $this->stations;
    }

    /**
     * List sensor feature are grouped
     *
     * @return array|null $this->group_sensor_features
     */
    public function getGroupSensorsFeaturesList()
    {
        if (!$this->group_sensor_features) {
            $handlers = SensorDBHandler::getHandlers('aws');
            $rs_data = array();

            if ($handlers) {
                $measurement_codes = array();

                foreach ($handlers as $handler) {
                    $sensor_features = SensorHandler::create($handler->handler_id_code)->getAwsGraphFeatures();

                    foreach ($sensor_features as $v) {
                        if ($v['measurement_type_code']) {
                            $measurement_codes[] = $v['measurement_type_code'];
                        }
                    }
                    // get stations
                    $stations = array();
                    $station_ids=array();
                    $cssClass = "";
                    if (count($handler->sensors)) {
                        foreach ($handler->sensors as $sensor) {
                            $station=array();
                            $station['station_id_code'] = $sensor->station->station_id_code;
                            $station['station_id'] = $sensor->station->station_id;
                            $station['color'] = $sensor->station->color;
                            $stations[$station['station_id']] = $station;
                            $station_ids[$station['station_id']] = $station['station_id'];

                        }
                        $stations       = array_values($stations);
                        $station_ids    = array_values($station_ids);
                        $cssClass       = implode('-station ',$station_ids);
                        $cssClass       .= '-station ';
                        array_multisort($stations, SORT_STRING, $stations );
                    }
                    $this->group_sensor_features[$handler->handler_id_code] = [
                        'name'            => $handler->display_name,
                        'sensor_features' => $sensor_features,
                        'stations'        => $stations,
                        'class'           => $cssClass,
                    ];
                }

                // Calculates
                $this->group_sensor_features['Temperature']['sensor_features']['calc_1'] = ['feature_name' => 'Dew Point, C degree'];
                $this->group_sensor_features['Pressure']['sensor_features']['calc_2'] = ['feature_name' => 'Pressure MSL, hPa'];

                // Measurement
                $sql = "SELECT `t3`.code,  `t5`.`short_name`,  `t5`.`full_name`
                    FROM `".RefbookMeasurementType::model()->tableName()."`       `t3`
                    LEFT JOIN `".RefbookMeasurementTypeMetric::model()->tableName()."` `t4` ON `t4`.`measurement_type_id` = `t3`.`measurement_type_id` AND `t4`.`is_main` = 1
                    LEFT JOIN `".RefbookMetric::model()->tableName()."`                `t5` ON `t5`.`metric_id` = `t4`.`metric_id`
                    WHERE `t3`.`code` IN ('".implode("','",  $measurement_codes)."')";

                $rs = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryAll();
                if ($rs) {
                    $rs_data = CHtml::listData($rs, 'code', 'short_name');
                }


                $wd_ws_station_params['stations'] = array();
                $wd_ws_station_params['class'] = '';
                foreach($this->group_sensor_features as $handler_id_code => &$group) {
                    if (in_array($handler_id_code,['WindDirection', 'WindSpeed'])
                        &&
                        in_array('WindSpeed',array_keys($this->group_sensor_features))
                        &&
                        in_array('WindDirection',array_keys($this->group_sensor_features))
                    ) {


                        $station_ids_temp = array();
                        foreach ($wd_ws_station_params['stations'] as $wd_ws_station) {
                            $station_ids_temp[] = $wd_ws_station['station_id'];
                        }

                        foreach ($group['stations'] as $station ) {
                            if(!in_array($station['station_id'],$station_ids_temp))
                                $wd_ws_station_params['stations'][] = $station;
                        }

                        $station_ids = array();
                        foreach ($wd_ws_station_params['stations'] as $wd_ws_station) {
                            $station_ids[] = $wd_ws_station['station_id'];
                        }
                        $wd_ws_station_params['class'] = implode('-station ',$station_ids).'-station ';
                    }

                    if (in_array($handler_id_code,['TemperatureWater', 'TemperatureSoil'])) {
                        foreach ($group['sensor_features'] as $key => $value) {
                            $group['sensor_features'][$key] =
                                $group['name'] .
                                ($rs_data[$value['measurement_type_code']] ? ', ' . $rs_data[$value['measurement_type_code']] : '');
                        }
                    } else {
                        foreach ($group['sensor_features'] as $key => $value) {
                            $group['sensor_features'][$key] =
                                $value['feature_name'] .
                                ($rs_data[$value['measurement_type_code']] ? ', ' . $rs_data[$value['measurement_type_code']] : '');
                        }
                    }
                }
            }

            /**
             * Load custom sensor feature */
            if (count($this->custom_sensor_features)) {
                $this->group_sensor_features['custom'] = [
                    'name'            => 'Custom',
                    'sensor_features' => $this->custom_sensor_features,
                    'stations'        => $wd_ws_station_params['stations'],
                    'class'           => $wd_ws_station_params['class'],
                ];
            }
        }

        return (array)$this->group_sensor_features;
    }

    /**
     * Get selected sensor feature group
     *
     * @return array
     */
    public function getSelectedGroupSensorFeatureCode()
    {
        $return = array();
        foreach($this->getGroupSensorFeatureCode() as $handler_id_code => $group) {
            if (is_array($group) && !empty($group)) {
                $return[$handler_id_code] = $group;
            }
        }

        return $return;
    }

    /**
     * List sensor feature
     *
     * @return array
     */
    public function getSensorsFeaturesList()
    {
        $result = [];
        foreach($this->getGroupSensorsFeaturesList() as $group) {
            if (is_array($group['sensor_features'])) {
                $result = array_merge($result, $group['sensor_features']);
            }
        }
        return $result;
    }

    /**
     * Selected sensor feature code are grouped
     *
     * @return array
     */
    public function getGroupSensorFeatureCode()
    {
        return $this->sensor_feature_code;
    }

    /**
     * Selected sensor feature code
     *
     * @return array
     */
    public function getSensorFeatureCode()
    {
        $result = [];
        foreach($this->getGroupSensorFeatureCode() as $group) {
            if (is_array($group)) {
                $result = array_merge($result, $group);
            }
        }
        return $result;
    }

    /**
     * Return accumulation list [key => label]
     *
     * @return array
     */
    public function getAccumulationList()
    {
        return [
            0    => 'Show All Values',
            30   => '30 Minutes',
            60   => '1 Hour',
            180  => '3 Hours',
            360  => '6 Hours',
            720  => '12 Hours',
            1440 => '1 Day',
            2880 => '2 Days',
        ];
    }

    /**
     * For view
     *
     * @return bool
     */
    public function isPrepare()
    {
        return $this->isPrepareProcess;
    }
}