<?php

class ImportStations extends CFormModel
{

    /**
     * @var[]  CUploadedFile
     */
    public $files = array();
    public $files_content = array();
    public $errors = array();


    public function __get($property)
    {

        return $this->$property;
    }

    public function init()
    {
        parent::init();
    }

    public function rules()
    {
        return array(
            array('files', 'dataValidator'),
        );
    }


    public function attributeLabels()
    {
        $label = array();
        $label["imported_file"]   = 'File';
        return $label;
    }

    public function dataValidator()
    {
        $count = count($this->files);
        if ($count) {
            for ($i=0; $i<$count; $i++) {
                $fileResult = json_decode(file_get_contents($this->files[$i]->getTempName()),1);
                if ($fileResult  == false) {
                    $this->addError("Config",'Station config Invalid');
                } else {
                    $this->files_content[] = $fileResult;
                }

            }
        }
        $this->validateStations();

        if (count($this->files_content)) {
            for ($i=0; $i<count($this->files_content); $i++) {
                $this->validateSensors($this->files_content[$i]);
            }
        }

        if (count($this->errors)) {
            for ($i=0; $i<count($this->errors); $i++) {
                $this->addErrors($this->errors[$i]);
            }
            return false;
        }
        return true;
    }

    protected function validateStations()
    {
        for ($i=0;$i<count($this->files_content);$i++) {
            if (count($this->files_content[$i]['station'])) {
                $this->stationValidate($this->files_content[$i]['station']);
            } else {
                $this->addError("station",'Station config empty');
            }
        }
    }

    protected function stationValidate($stationParamArray)
    {
        $station = new Station();
        unset($stationParamArray['station_id']);
        unset($stationParamArray['created']);
        unset($stationParamArray['updated']);
        $station->attributes = $stationParamArray;
        if(!$station->validate()) {
            $this->errors[] = $station->getErrors();
        }

    }

    protected function addStations()
    {
        for ($i=0;$i<count($this->files_content);$i++) {
            $this->files_content[$i]['station']['station_id'] = $this->stationAdd($this->files_content[$i]['station']);
        }
    }

    /**
     * @param $stationParamsArray array
     */
    protected function stationAdd($stationParamArray)
    {
        $station = new Station();
        unset($stationParamArray['station_id']);
        unset($stationParamArray['created']);
        unset($stationParamArray['updated']);
        $station->attributes = $stationParamArray;
        $station->save();

        return $station->getPrimaryKey();
    }




    protected function stationSensorAdd($stationId,$sensorParamArray)
    {
        $handler = SensorDBHandler::model()->with ('features')->findAllByAttributes(array('handler_id_code' => $sensorParamArray['handler']));
        $handler = $handler[0];

        $sensor = new StationSensor();
        $sensor->station_id = $stationId;
        $sensor->handler_id = $handler->handler_id;
        $sensor->display_name = $sensorParamArray['display_name'];

        $sql = "SELECT UPPER(`sensor_id_code`) FROM `".StationSensor::model()->tableName()."` WHERE `station_id` = ? AND `sensor_id_code` <> ?";
        $used_code_id = Yii::app()->db->createCommand($sql)->queryColumn(array($stationId, $sensor->sensor_id_code ? $sensor->sensor_id_code : ''));
        for ($i=1; $i<=9; $i++) {
            $code = $handler->default_prefix.$i;
            if (!$used_code_id || !in_array($code, $used_code_id)){
                $sensor->sensor_id_code = $code;
                break;
            }
        }

        if (!$sensor->sensor_id_code) {
           //error
        }

        $sensorHandler = SensorHandler::create($handler->handler_id_code);
        $sensorFeatures = array();
        $ft_1 = $sensorHandler->getFeatures();
        $ft_2 = $sensorHandler->getExtraFeatures();
        if ($ft_2) {
            foreach ($ft_2 as $key => $value)
                $ft_2[$key]['is_extra'] = 1;
        }
        $handler_sensor_features = array_merge($ft_1, $ft_2);

        if ($handler_sensor_features) {
            foreach ($handler_sensor_features as $value) {
                $sf = new StationSensorFeature();
                $default = $handler->features[$value['feature_code']];
                $metric = RefbookMeasurementType::model()->with('metricMain')->findByAttributes(array(
                    'code' => $value['measurement_type_code']
                ));
                $sf->feature_constant_value = isset($value['default']) ? $value['default'] : null;

                if ($default) {
                    $sf->feature_constant_value = $default->feature_constant_value;
                    $sf->metric_id   = $default->metric_id;
                    $sf->filter_max  = $default->filter_max;
                    $sf->filter_min  = $default->filter_min;
                    $sf->filter_diff = $default->filter_diff;
                }

                foreach ($sensorParamArray['features'] as $sensorParamFeature) {
                    if ($sensorParamFeature['feature_code'] == $value['feature_code']) {
                        $sf->feature_constant_value = $sensorParamFeature['feature_constant_value'];
                    }
                }

                $sf->metric_id             = $metric->metricMain->metric_id;
                $sf->feature_code          = $value['feature_code'];
                $sf->feature_display_name  = $value['feature_name'];
                $sf->is_constant           = isset($value['is_extra']) ? 1 : 0;
                $sf->comment               = isset($value['comment']) ? $value['comment'] : null;
                $sf->measurement_type_code = $value['measurement_type_code'];
                $sf->is_cumulative         = $value['is_cumulative'];
                $sf->is_main               = $value['is_main'];
                $sf->has_filter_min        = $value['has_filter_min'];
                $sf->has_filter_max        = $value['has_filter_max'];
                $sf->has_filter_diff       = $value['has_filter_diff'];

                $sensorFeatures[] = $sf;
            }
        }



        $validated = $sensor->validate();
        if ($validated) {
            $this->errors[] = $sensor->getErrors();
        }

        if ($validated and $sensorFeatures) {
            foreach ($sensorFeatures as $feature){
                $feature->sensor_id = 1;
                $validated = $validated & $feature->validate();
            }

            if ($validated) {
                $sensor->save(false);
                if ($sensorFeatures){
                    foreach ($sensorFeatures as $feature){
                        $feature->sensor_id  = $sensor->station_sensor_id;
                        $feature->save(false);
                    }
                }
               // sensor Created
            }
        }
        // sensor Save Fail

    }

    protected function addStationSensors($stationSensorParamArray)
    {
        foreach ($stationSensorParamArray['sensors'] as $sensor) {
            $this->stationSensorAdd($stationSensorParamArray['station']['station_id'],$sensor);
        }
    }


    protected function sensorValidate($stationId,$sensorParamArray)
    {

        $handler = SensorDBHandler::model()->with ('features')->findAllByAttributes(array('handler_id_code' => $sensorParamArray['handler']));

        $handler = $handler[0];
        if (!is_object($handler)) {
            $this->addError('sensor','handler was not found');
        }
        $sensor = new StationSensor();
        $sensor->station_id = $stationId;
        $sensor->handler_id = $handler->handler_id;
        $sensor->display_name = $sensorParamArray['display_name'];

        $sql = "SELECT UPPER(`sensor_id_code`) FROM `".StationSensor::model()->tableName()."` WHERE `station_id` = ? AND `sensor_id_code` <> ?";
        $used_code_id = Yii::app()->db->createCommand($sql)->queryColumn(array($stationId, $sensor->sensor_id_code ? $sensor->sensor_id_code : ''));
        for ($i=1; $i<=9; $i++) {
            $code = $handler->default_prefix.$i;
            if (!$used_code_id || !in_array($code, $used_code_id)){
                $sensor->sensor_id_code = $code;
                break;
            }
        }

        if (!$sensor->sensor_id_code) {
            $this->addError('sensor','all numbers for sensor are busy');
        }

        $sensorHandler = SensorHandler::create($handler->handler_id_code);
        $sensorFeatures = array();
        $ft_1 = $sensorHandler->getFeatures();
        $ft_2 = $sensorHandler->getExtraFeatures();
        if ($ft_2) {
            foreach ($ft_2 as $key => $value)
                $ft_2[$key]['is_extra'] = 1;
        }
        $handler_sensor_features = array_merge($ft_1, $ft_2);

        if ($handler_sensor_features) {
            foreach ($handler_sensor_features as $value) {
                $sf = new StationSensorFeature();
                $default = $handler->features[$value['feature_code']];
                $metric = RefbookMeasurementType::model()->with('metricMain')->findByAttributes(array(
                    'code' => $value['measurement_type_code']
                ));
                $sf->feature_constant_value = isset($value['default']) ? $value['default'] : null;

                if ($default) {
                    $sf->feature_constant_value = $default->feature_constant_value;
                    $sf->metric_id   = $default->metric_id;
                    $sf->filter_max  = $default->filter_max;
                    $sf->filter_min  = $default->filter_min;
                    $sf->filter_diff = $default->filter_diff;
                }

                foreach ($sensorParamArray['features'] as $sensorParamFeature) {
                    if ($sensorParamFeature['feature_code'] == $value['feature_code']) {
                        $sf->feature_constant_value = $sensorParamFeature['feature_constant_value'];
                    }
                }

                $sf->metric_id             = $metric->metricMain->metric_id;
                $sf->feature_code          = $value['feature_code'];
                $sf->feature_display_name  = $value['feature_name'];
                $sf->is_constant           = isset($value['is_extra']) ? 1 : 0;
                $sf->comment               = isset($value['comment']) ? $value['comment'] : null;
                $sf->measurement_type_code = $value['measurement_type_code'];
                $sf->is_cumulative         = $value['is_cumulative'];
                $sf->is_main               = $value['is_main'];
                $sf->has_filter_min        = $value['has_filter_min'];
                $sf->has_filter_max        = $value['has_filter_max'];
                $sf->has_filter_diff       = $value['has_filter_diff'];

                $sensorFeatures[] = $sf;
            }
        }



        $validated = $sensor->validate();
        if ($validated) {
            $this->errors[] = $sensor->getErrors();
        }

        if ($validated and $sensorFeatures) {
            foreach ($sensorFeatures as $feature){
                $feature->sensor_id = 1;
                if (!$feature->validate()) {
                    $this->errors[] = $feature->getErrors();
                }
            }

        }
        // sensor Save Fail
    }

    protected function validateSensors($stationSensorParamArray)
    {
        if (is_array($stationSensorParamArray['sensors'])) {
            foreach ($stationSensorParamArray['sensors'] as $sensor) {
                $this->sensorValidate($stationSensorParamArray['station']['station_id'],$sensor);
            }
        }
    }

    public function save()
    {
        $this->addStations();

        if (count($this->files_content)) {
            for ($i=0; $i<count($this->files_content); $i++) {
                $this->addStationSensors($this->files_content[$i]);
            }
        }

        return true;
    }

}