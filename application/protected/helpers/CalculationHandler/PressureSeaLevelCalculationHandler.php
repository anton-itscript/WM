<?php

/*
 * Extending for CalculationHandler
 * Serves features and methods for Pressure Sea Level calculation
 */


class PressureSeaLevelCalculationHandler extends CalculationHandler {
    
    var $measurements = array(
        array('variable_name' => 'temperature', 'display_name' => 'Temperature', 'required' => 1, 'metric' => 'celsius'),
        array('variable_name' => 'pressure',    'display_name' => 'Pressure',    'required' => 1, 'metric' => 'hpascal'),
    );
    
    var $formulas = array();  
    var $handler_id_code = 'PressureSeaLevel';
    var $metric_html_code = 'hPa';
    var $display_name = 'Pressure MSL';
    
    public function prepareFormulaParams()
    {
        $this->formula_params['h_station_above_sea'] = $this->station_obj->altitude;

        // h_barometer_above_station
        $sensor_id_codes = $this->getUsedSensors($this->station_obj->station_id);
        $sensor_id_code = array_shift(preg_grep('/^PR.$/',$sensor_id_codes));

        $qb = new CDbCriteria();

        $qb->with = ['sensor.handler','metric'];
        $qb->addCondition('sensor.sensor_id_code LIKE \'' . $sensor_id_code . '\'');
        $qb->addCondition('sensor.station_id = ' . $this->station_obj->station_id);
        $qb->addCondition('handler.handler_id_code LIKE \'Pressure\'');
        $qb->addCondition('t.feature_code LIKE \'height\'');
        $res = StationSensorFeature::model()->find($qb);

        $h_barometer_above_station = 0;
        if ($res && !is_null($res->feature_constant_value) && !is_null($res->metric->code)) {
            $h_barometer_above_station = It::convertMetric($res->feature_constant_value, $res->metric->code, 'meter');
        }
        $this->formula_params['h_barometer_above_station'] = $h_barometer_above_station;

        // coefficient_from_station_gravity
        $station_gravity = floatval($this->station_obj->station_gravity);
        $this->formula_params['coefficient_from_station_gravity'] = ($station_gravity > 0 ? $station_gravity : array_shift(array_keys(yii::app()->params['station_gravity']))) / 0.0065 / 287;

        return parent::prepareFormulaParams();
    }        
    
    public function makeCalculation() {
        
        switch ($this->calculation_details['formula']) {
            case 'complex':
            case 'simple':
            default:    
                $res = $this->makeCalculationDefault();
                break;
        }
        $this->calculation_details['value'] = $res;
        return true;
    }  
    
    private function makeCalculationDefault() {
        if (isset($this->formula_params['pressure']) && isset($this->formula_params['temperature'])) {
            $h = 0.0065 * ($this->formula_params['h_station_above_sea'] + $this->formula_params['h_barometer_above_station']);
            $res = $this->formula_params['pressure'] / pow((1 - $h / ($this->formula_params['temperature'] + 273.15 + $h)), $this->formula_params['coefficient_from_station_gravity']);
            return $res;
        }
        return false;
    }
}
?>