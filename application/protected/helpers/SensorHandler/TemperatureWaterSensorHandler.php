<?php

/*
 * Handler to work with data of Water Temperature sensor
 * 
 */

Yii::import('application.helpers.SensorHandler.TemperatureSensorHandler');

class TemperatureWaterSensorHandler extends TemperatureSensorHandler
{
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
}

?>
