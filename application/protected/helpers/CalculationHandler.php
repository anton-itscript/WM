<?php

/*
 * Class with functionality to make extra calculations for sensor's data. 
 * Has 2 extended classes: mean seal level & dewpoint.
 * 
 * The main usage:
 * 
 * $handler = CalculationHandler::create('DewPoint');
 * // or:
 * // $handler = CalculationHandler::create('PressureSeaLevel');
 *				
 * // get station object with station_id = 1
 * $station = Station::model()->findByPk(1); 
 * 
 * // run calculation for $station basing on message with id=23
 * $handler->calculate($station, 23);
 *  
 */

class CalculationHandler
{
    const classPrefix = 'CalculationHandler';
	/*
	 * id code of handler
	 * is described in extended class
	 */
    public $handler_id_code;
	
	/*
	 * HTML code of result metric
	 * is described in extended class
	 */
    public $metric_html_code;
	
	/*
	 * array of variables that take part in formula
	 * is described in extended class
	 */
    public $measurements;
	
	/*
	 * array of available formulas for calculation
	 * is described in extended class
	 */
    public $formulas;    
    
	/*
	 * details about current calculation (record from `station_calculation` table)
	 */
    public $calculation_details;
	
	/*
	 * object of Station model
	 */
    public $station_obj;
	
	/*
	 * message ID to make calculations for
	 */
    public $log_id;
	
	/*
	 * prepared params of formula: name of variable and value of propriate feature in database
	 */
    public $formula_params = array();
	
	/*
	 * name of calculation to display in views
	 */
    public $display_name;
    

    
    /**
	 * @return array - array of variables that take part in formula.
	 */
    public function getMeasurements()
	{
        return $this->measurements;
    }

	/**
	 *
	 * @return array - list of possible formulas 
	 */
    public function getFormulas()
	{
        return $this->formulas;
    }

    
	/*
	 * Calculate DewPoint or MSL
	 */
    function calculate($station_obj, $log_id)
	{
        $this->station_obj = $station_obj;
        $this->log_id      = $log_id;

        $this->prepareCalculationDetails();
        
		if ($this->calculation_details['calculation_id'])
		{
			$this->prepareFormulaParams();
            
			$this->makeCalculation();
            $this->storeCalculation();
        }
    }
    
	/*
	 * gets details of formula for this calculation from database
	 */
    public function prepareCalculationDetails()
	{
        $sql = "SELECT `t1`.`calculation_id`, `t1`.`formula`
                FROM `".StationCalculation::model()->tableName()."` `t1`
                JOIN `".CalculationDBHandler::model()->tableName()."`   `t2` ON `t2`.`handler_id` = `t1`.`handler_id`
                WHERE `t1`.`station_id` = ? AND `t2`.`handler_id_code` = ?";    
        $this->calculation_details = Yii::app()->db->createCommand($sql)->queryRow(true, array($this->station_obj->station_id, $this->handler_id_code));        
    }

	/*
	 * selects from `station_calculation_variable` formula's variables and sensor's features (which values should be used instead of variables)
	 */
    public function prepareFormulaParams()
		{
        
        $sql = "SELECT `t4`.`sensor_data_id`, `t1`.`sensor_feature_id`, `t4`.`metric_id`, `t4`.`sensor_feature_value`, `t5`.`code` AS `metric_code`, `t4`.`is_m`
                FROM `".StationCalculationVariable::model()->tableName()."` `t1`
                JOIN `".StationSensorFeature::model()->tableName()."`       `t3` ON `t3`.`sensor_feature_id` = `t1`.`sensor_feature_id`
                JOIN `".SensorData::model()->tableName()."`                 `t4` ON (`t4`.`sensor_feature_id` = `t1`.`sensor_feature_id` AND `t4`.`listener_log_id` = ? AND `t4`.`is_m` = '0')
                JOIN `".RefbookMetric::model()->tableName()."`              `t5` ON `t5`.`metric_id` = `t4`.`metric_id`
                WHERE `t1`.`calculation_id` = ? AND `t1`.`variable_name` = ?";
        $measurements = $this->getMeasurements();
        
        if ($measurements) {
            foreach ($measurements as $key => $value) {
                $res = Yii::app()->db->createCommand($sql)->queryRow(true, array($this->log_id, $this->calculation_details['calculation_id'], $value['variable_name']));
                if ($res) {
                    $this->formula_params[$value['variable_name']] = It::convertMetric($res['sensor_feature_value'], $res['metric_code'], $value['metric']);
                }
            }     
        }
        return true;
    }
    
     
	/*
	 * each extending class has its own implementation
	 */
    public function makeCalculation() {
        return false;
    }
    
	/*
	 * save calsulation results into database
	 */
    public function storeCalculation() {
        StationCalculationData::saveCaclulation($this->calculation_details['calculation_id'], $this->log_id, $this->calculation_details['value']);
    }

	/*
	 * is used to get calsulated values basing on messages
	 */
    public function getCalculatedValue($station_id, $last_logs)
	{
        $last_logs_ids = array();
        
		if (is_array($last_logs))
		{
            foreach ($last_logs as $value)
			{
				if (count($value) > 0)
				{
					$last_logs_ids[] = $value->log_id;
                }
            }
        } 
		
        $last_logs_ids[] = 0;
        
        $sql = "SELECT t1.listener_log_id, t1.value
                FROM `".StationCalculationData::model()->tableName()."` `t1`
                LEFT JOIN `".StationCalculation::model()->tableName()."` t2 ON t2.calculation_id = t1.calculation_id
                LEFT JOIN `".CalculationDBHandler::model()->tableName()."` t3 ON t3.handler_id = t2.handler_id
                LEFT JOIN `".ListenerLog::model()->tableName()."` t4 ON t4.log_id = t1.listener_log_id
                WHERE `t3`.handler_id_code = ? AND t2.station_id = ? AND t1.listener_log_id IN (".implode(',',$last_logs_ids).")
                ORDER BY t4.measuring_timestamp DESC
                LIMIT 2 ";
        $res = Yii::app()->db->createCommand($sql)->queryAll(true, array($this->handler_id_code, $station_id));

        $return = array('last' => '-', 'change' => 'no', 'metric_html_code' => $this->metric_html_code, 'display_name' => $this->display_name);
        if (!$res) {
            return $return;
        }
        
        

        if ($res[0]['listener_log_id'] && $res[0]['listener_log_id'] == $last_logs_ids[0]) {
            $return['last'] = number_format(round($res[0]['value'], 1),1); 
            if ($res[1]['listener_log_id']) {
                if ($res[0]['value'] > $res[1]['value']) {
                    $return['change'] = 'up';
                } else if ($res[0]['value'] < $res[1]['value']) {
                    $return['change'] = 'down';
                }
            }
        }     

        return $return;
    }

	/*
	 * gets sensor ID code of sensors which take part in calculation
	 */
    public function getUsedSensors($station_id)
	{
        $sql = "SELECT t5.sensor_id_code
                FROM `".StationCalculationVariable::model()->tableName()."` `t1`
                LEFT JOIN `".StationCalculation::model()->tableName()."` t2 ON t2.calculation_id = t1.calculation_id
                LEFT JOIN `".CalculationDBHandler::model()->tableName()."` t3 ON t3.handler_id = t2.handler_id
                LEFT JOIN `".StationSensorFeature::model()->tableName()."` t4 ON t4.sensor_feature_id = t1.sensor_feature_id
                LEFT JOIN `".StationSensor::model()->tableName()."` t5 ON t5.station_sensor_id = t4.sensor_id
                WHERE `t3`.handler_id_code = ? AND t2.station_id = ? 
                ORDER BY t5.sensor_id_code";
        
        $res = Yii::app()->db->createCommand($sql)->queryColumn(array($this->handler_id_code, $station_id));     
        return $res;
    }


	/*
	 * creates object of specific calculation
	 */
    /**
     * @param $handler_id
     * @return false|DewPointCalculationHandler|PressureSeaLevelCalculationHandler
     */
    static function create($handler_id) {
        $class = "{$handler_id}CalculationHandler";
        
        $res = Yii::import('application.helpers.CalculationHandler.'.$class);
        
        if (!$res || !class_exists($class))  {
            return false;
        }

        return new $class;        
    }
    /**
     * Returns value formatted as required for specific feature
     * @param float $value
     * @return float
     */
    static function formatValue($value){
        return number_format(round($value, 1), 1);
    }
    public static function checkTrend($arr,$trend = 1,$i = 0){

        //trend = 1 check up
        //trend = -1 check down

        //if(abs($trend)!=1)return false;// For i = 1 or -1
        //$trend = $trend != 0 ? gmp_sign($trend) : 1; // For all i

        if($arr[$i+1]){
            if($arr[$i]*$trend > $arr[$i+1]*$trend)
                return self::checkTrend($arr,$trend,$i+1);
            return false;
        }
        return true;
    }
    public static function getDataForAwsPanel(&$station,$lastMessageId,$handlerClass){
        $handlerClass.=self::classPrefix;
        //def value
        $value = '-';
        $change = 'no';
        $within = '';
        //
        $data = array_shift($station['data']);
        if($data->listener_log_id == $lastMessageId){
            //prepare date
            $value = $handlerClass::formatValue($data->value);
            //check trend
            $arrForTrend = array();
            foreach($station['data'] as $trendValue)
                $arrForTrend[]=$trendValue->value;
            if (self::checkTrend($arrForTrend,1))
                $change = 'up';
            else if (self::checkTrend($arrForTrend,-1))
                $change = 'down';
        }
        $station['view'] = array(
            'value'     => $value,
            'change'    => $change,//0,1,-1
            'within'    => $within,//0,1
        );


    }
}
?>