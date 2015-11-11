<?php
class StationSensorEditForm extends CFormModel
{
    /** @var  int */
    public $sensor_id;
    /** @var  string */
    public $sensor_name;
    /** @var  array|StationSensorFeature[] */
    public $constant;
    /** @var  string */
    public $handler_name;
    /** @var  StationSensor */
    protected $sensor;

    public function rules()
    {
        return [
            ['sensor_id, sensor_name, handler_name', 'required'],
            ['constant','checkConstant']

        ];
    }

    /**
     * @return bool
     */
    public function checkConstant()
    {
        $error = false;

        foreach($this->getSensor($this->sensor_id)->ConstantFeature as $feature) {
            /** @var  StationSensorFeature $feature */
            if (!empty($this->constant[$feature->sensor_feature_id])
                && !empty($this->constant[$feature->sensor_feature_id]['value'])
            ) {
                $feature->feature_constant_value = $this->constant[$feature->sensor_feature_id]['value'];
            } else {
                $feature->feature_constant_value = null;
            }

            if (!$feature->validate()) {
                $this->addError("constant[$feature->sensor_feature_id]", $feature->getError('feature_constant_value'));
                $error = true;
            }
        }

        return !$error;
    }

    /**
     * @param int $sensor_id
     *
     * @return bool
     */
    public function loadBySensorId($sensor_id)
    {
        if (!is_null($sensor_id) && is_int($sensor_id) && !is_null($this->getSensor($sensor_id))) {
            $this->sensor_id    = $this->sensor->station_sensor_id;
            $this->sensor_name  = $this->sensor->display_name;
            $this->handler_name = $this->sensor->handler->display_name;

            foreach($this->sensor->ConstantFeature as $feature) {
                $metric = RefbookMeasurementType::model()->with('metricMain')->findByAttributes(['code' => $feature->measurement_type_code]);

                $this->constant[$feature->sensor_feature_id] = [
                    'name'   => $feature->feature_display_name,
                    'value'  => $feature->feature_constant_value,
                    'metric' => $metric->metricMain->metric->html_code
                ];
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function saveSensor()
    {
        $result = array();

        // Save Sensor
        $this->getSensor($this->sensor_id)->display_name = $this->sensor_name;
        if ($this->sensor && $this->sensor->validate()) {
            $this->sensor->save();
            $result = [
                'status'      => 'save',
                'sensor_id'   => $this->sensor_id,
                'sensor_name' => $this->sensor_name
            ];

            // Save constant
            foreach($this->sensor->ConstantFeature as $feature) {
                /** @var  StationSensorFeature $feature */
                $feature->feature_constant_value = $this->constant[$feature->sensor_feature_id]['value'];
                if ($feature->validate()) {
                    $feature->save();
                }
            }
        }

        return $result;
    }

    /**
     * @param $sensor_id
     *
     * @return StationSensor
     */
    protected function getSensor($sensor_id) {
        if (is_null($this->sensor)) {
            $this->sensor = StationSensor::model()->with(['ConstantFeature.metric'])->findByPk($sensor_id);
        }
        return $this->sensor;
    }

    /**
     * @return string|null
     */
    public function draw()
    {
        return Yii::app()->controller->renderPartial('form/station_sensor_edit',['form' => $this], true);
    }

}