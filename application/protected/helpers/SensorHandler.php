<?php

/*
 * Class to work with sensors data. SensorHandler.php contains functionality to:
 * 1. work with source data (part of data logger's message)
 * - prepare sensor's data from source (message) before putting this data into database
 * - store prepared data
 * 2. prepare sensor's data for visualization:
 * - prepare information for AWS Single/Panel basing on timestamp of measurement.
 * - prepare information about MIN/MAX/Total for different periods of time.
 * 3. has information about sensor's features and measurements: measurement type, 
 *    has/ doesn't have filters, cumulative or not (is used to support sensors management in admin section).
 *
 * There are 16 classes extended SensorHandler functionality. 
 * As far as each sensor can have his own features and provides us with 
 * information about different measurements – there are a lot of differences 
 * in ways of processing and visualization of  sensor's data. 
 * Each extended class is created only for 1 sensor. It has its own realization 
 * of  SensorHandler's functions according to specific information about 
 * each sensor from document [3].
 */

class SensorHandler extends BaseComponent{
    const classPrefix = 'SensorHandler';
	/*
	 * Each sensor provides us with 1..N measurements. 
     * E.g.: 1) BatteryVoltage sensor provides us with only one – voltage measurement value.
     *       2) SeaLevel sensor provides us with 3 measurements: mean, sigma, wave height.
     * All expected measurements are described as $features in appropriate sensor handler class.
     * Their main usage is to build template “add/change sensor” in “Admin/station/sensors” .
     * ( To list all measurements provided with this sensor, and to save into database filters and metrics for each measurement.)
	 * Each feature is an array of properties (we will list all features for each sensor below):
	 *	* feature_name (string)
	 *	* feature_code (string)
	 *	* measurement_type_code (string)
	 *	* has_filter_min (1 or 0)
	 *	* has_filter_max (1 or 0)
	 *	* has_filter_diff (1 or 0)
	 *	* is_cumulative (1 or 0)
	 *	* is_main (1 or 0)
	 *	* aws_graph_using (string or empty)
	 */
    public $features           = array();
	
	/*
	 * (static properties of sensor: height, depth...)
	 * Each sensor has its own array of extra features (if need). They are also 
	 * take part in creation of template where admin can add such information into database.
	 */
    public $extra_features     = array();
    
    public $incoming_sensor_value;
    public $incoming_measuring_timestamp;
    public $prepared_pairs;
    public $errors = array();
    public $sensor_features_info;
    public $checkPeriodArray = array(
        'solar_radiation_in_period' =>  'solar_radiation_in_day',
        'sun_duration_in_period'    =>  'sun_duration_in_day',
        'rain_in_period'            =>  'rain_in_day',
    );

    /*
	 * creates object of specific handler class
	 */
    public static function create($handler_id, $logger = null)
	{
        $class = "{$handler_id}SensorHandler";
        
        $res = Yii::import('application.helpers.SensorHandler.'. $class);
        
		if (!$res)
		{
			if (!is_null($logger))
			{
				$logger->log(__METHOD__ .' Can\'t import application.helpers.SensorHandler.'. $class .' class');
			}
			
			return false;
		}
		
		if (!class_exists($class)) 
		{
			if (!is_null($logger))
			{
				$logger->log(__METHOD__ .' Class '. $class .' is not exists.');
			}
			
            return false;
        }

		if (is_null($logger))
		{
			$logger = LoggerFactory::getFileLogger('sensor_handler');
		}
		
		$logger->log(__METHOD__, array('class' => $class));
		
		return new $class($logger);        
    }    
    
    public function getFeatures()
	{
        return $this->features;
    }  
    
    public function getExtraFeatures()
	{
        return $this->extra_features;
    }
    
    public function getFeatureName($feature_code)
	{
        if (is_array($this->features))
		{
            foreach ($this->features as $value)
			{
                if ($value['feature_code'] == $feature_code)
				{
                    return $value['feature_name'];
                }
            }
        }
    }
    
    /**
	 *
	 * @return array - list of features to work witn at AWS Graph page
	 */
    public function getAwsGraphFeatures()
	{
        $features = $this->getFeatures();
        
        $return = array();
        
        if ($features)
		{
            foreach ($features as $value)
			{
                if (!empty($value['aws_graph_using']))
				{
                    $return[$value['feature_code']] = array(
						'feature_name' => $value['aws_graph_using'],
						'measurement_type_code' => $value['measurement_type_code']
					);
                }
            }
        }
        
        return $return;
    }
    
    /**
	 * Returns value formatted as required for specific feature
	 * @param float $value
	 * @param string $feature_name
	 * @return float 
	 */
    public function formatValue($value, $feature_name = '')
	{
        return number_format($value);
    }  
    
    /**
	 * Applies offset to value (specific for wind direction)
	 * @param type $value
	 * @param type $offset
	 * @return float 
	 */
    public function applyOffset($value, $offset = 0)
	{
        return $value;
    }    
    
    /*
     * Function to get MAX and MIN values for some feature of sensor.
     * @param integer $sensor_feature_id   an ID of feature of some sensor
     * @param integer $measuring_timestamp a timestamp of moment to count X_hr back (to find extremums in that X_hr period)
     * @param integer $x_hr                a number of hours to calculate extremums in
     * @param inetegr $offset              an offset to update data with some value (is more actual for WIND)
     * @param string  $format_result       a name of feature to format according with its rules (default is FALSE - no format)
     */
    public function getMaxMinInLastXHr($sensor_feature_id, $measuring_timestamp, $x_hr = 24, $offset = 0, $format_result = false)
	{
        $time_start = mktime(
                date('H', $measuring_timestamp) - $x_hr, 
                date('i', $measuring_timestamp), 
                date('s', $measuring_timestamp), 
                date('m', $measuring_timestamp), 
                date('d', $measuring_timestamp), 
                date('Y', $measuring_timestamp) );

        $time_end = mktime(
                date('H', $measuring_timestamp) - $x_hr==48?24:0,
                date('i', $measuring_timestamp),
                date('s', $measuring_timestamp),
                date('m', $measuring_timestamp),
                date('d', $measuring_timestamp),
                date('Y', $measuring_timestamp) );
        
        $sql = "SELECT MAX(CAST(`sensor_feature_value` AS DECIMAL(15,4))) AS `max_value`, MIN(CAST(`sensor_feature_value` AS DECIMAL(15,4))) AS `min_value`
                FROM  `".SensorData::model()->tableName()."` 
                WHERE `sensor_feature_id`   =  '".$sensor_feature_id."' 
                  AND `measuring_timestamp` <= '".date('Y-m-d H:i:s',$time_end)."'
                  AND `measuring_timestamp` >  '".date('Y-m-d H:i:s',$time_start)."'
                  AND `is_m` = '0'";
        $res = Yii::app()->db->createCommand($sql)->queryRow();

        if ($res['max_value'] !== '')
		{
            $res['max_value'] = $this->applyOffset($res['max_value'], $offset);  
        }
        
		if ($res['min_value'] !== '')
		{
            $res['min_value'] = $this->applyOffset($res['min_value'], $offset);  
        }
        
		$return = array();
        
        if ($format_result !== false)
		{
            $return['max'] = $res['max_value'] === '' ? '-' : $this->formatValue($res['max_value'], $format_result);
            $return['min'] = $res['min_value'] === '' ? '-' : $this->formatValue($res['min_value'], $format_result);
        }
		else
		{
            $return['max'] = $res['max_value']; 
            $return['min'] = $res['min_value'];             
        }
		
        return $return;
    }    
    /*
     * Function to get MAX and MIN values for some feature of sensor.
     * @param integer $sensor_feature_id   an ID of feature of some sensor
     * @param integer $measuring_timestamp a timestamp of moment to count X_hr back (to find extremums in that X_hr period)
     * @param integer $day                 a number of day to calculate extremums in
     * @param inetegr $offset              an offset to update data with some value (is more actual for WIND)
     * @param string  $format_result       a name of feature to format according with its rules (default is FALSE - no format)
     */
    public function getMaxMinInDay($sensor_feature_id, $measuring_timestamp, $day = 1, $offset = 0, $format_result = false){
        $feature = StationSensorFeature::model()->with('sensor.handler')->findByPk($sensor_feature_id);
        $start_time = $feature->sensor->handler->start_time;

        if($start_time == -1) {
            $return = $this->getMaxMinInLastXHr($sensor_feature_id, $measuring_timestamp, $day*24, $offset, $format_result);
            $timeFormat = date('H', $measuring_timestamp).':'.gmdate('i', $measuring_timestamp);
            $return['mami_title']=$day==1?
                'since '.$timeFormat.'h today':
                'between '.$timeFormat.'h and '.$timeFormat.'h yesterday';
            return $return;
        }

        $check = $start_time < date('H', $measuring_timestamp) ? 1 : 0;

        $time_start = mktime(
            $start_time,
            0,
            0,
            date('m', $measuring_timestamp),
            date('d', $measuring_timestamp) - ($day - $check),
            date('Y', $measuring_timestamp) );
        $time_end = mktime(
            $day>1 ? $start_time  :date('H', $measuring_timestamp),
            $day>1 ? 0            :date('i', $measuring_timestamp),
            $day>1 ? 0            :date('s', $measuring_timestamp),
            date('m', $measuring_timestamp),
            date('d', $measuring_timestamp) - ($day>1 ? ($day-$check-1):0),
            date('Y', $measuring_timestamp));

        $sql = "SELECT MAX(CAST(`sensor_feature_value` AS DECIMAL(15,4))) AS `max_value`, MIN(CAST(`sensor_feature_value` AS DECIMAL(15,4))) AS `min_value`
                FROM  `".SensorData::model()->tableName()."`
                WHERE `sensor_feature_id`   =  '".$sensor_feature_id."'
                  AND `measuring_timestamp` <= '".date('Y-m-d H:i:s',$time_end)."'
                  AND `measuring_timestamp` >  '".date('Y-m-d H:i:s',$time_start)."'
                  AND `is_m` = '0'";
        $res = Yii::app()->db->createCommand($sql)->queryRow();

        if ($res['max_value'] !== '')
            $res['max_value'] = $this->applyOffset($res['max_value'], $offset);
		if ($res['min_value'] !== '')
            $res['min_value'] = $this->applyOffset($res['min_value'], $offset);

		$return = array();
        if ($format_result !== false){
            $return['max'] = $res['max_value'] === '' ? '-' : $this->formatValue($res['max_value'], $format_result);
            $return['min'] = $res['min_value'] === '' ? '-' : $this->formatValue($res['min_value'], $format_result);
        } else {
            $return['max'] = $res['max_value'];
            $return['min'] = $res['max_value'];
        }
        $timeFormat = ($start_time<10?'0'.$start_time:$start_time).':00';
        $return['mami_title']=$day==1?
            'since '.$timeFormat.'h today':
            'between '.$timeFormat.'h and '.$timeFormat.'h yesterday';

        return $return;
    }

    /**
	 * Function to get MAX and MIN values for some feature of sensor in last period starting from the hour.
	 * @param type $sensor_feature_id
	 * @param type $measuring_timestamp
	 * @param type $start_hour
	 * @param type $format_result
	 * @return type 
	 */
    public function getMaxMinFromHour($sensor_feature_id, $measuring_timestamp, $start_hour, $format_result = false)
	{
        $h_measurement_time = date('H', $measuring_timestamp);

		if ($h_measurement_time < $start_hour)
		{
            $today_start = mktime($start_hour, 0, 0, date('m', $measuring_timestamp), date('d', $measuring_timestamp)-1, date('Y', $measuring_timestamp) );
            $today_end = mktime($start_hour, 0, 0, date('m', $measuring_timestamp), date('d', $measuring_timestamp), date('Y', $measuring_timestamp) );
        }
		else
		{
            $today_start = mktime($start_hour,0,0, date('m', $measuring_timestamp), date('d', $measuring_timestamp), date('Y', $measuring_timestamp) );
            $today_end = mktime($start_hour,0,0, date('m', $measuring_timestamp), date('d', $measuring_timestamp)+1, date('Y', $measuring_timestamp) );
        }
        
        if ($today_end > $measuring_timestamp)
		{
            $today_end = $measuring_timestamp;
        }
        
        $sql = "SELECT MAX(CAST(`sensor_feature_value` AS DECIMAL(15,4))) AS `max_value`, MIN(CAST(`sensor_feature_value` AS DECIMAL(15,4))) AS `min_value`
                FROM `".SensorData::model()->tableName()."`
                WHERE `sensor_feature_id` = '".$sensor_feature_id."' 
                    AND `measuring_timestamp` <= '".date('Y-m-d H:i:s', $today_end)."' 
                    AND `measuring_timestamp` > '".date('Y-m-d H:i:s', $today_start)."'
                    AND `is_m` = '0'";
		
        $res = Yii::app()->db->createCommand($sql)->queryRow();
        
        $return = array();
        
		if ($format_result !== false){
            $return['max'] = $res['max_value'] == '' ? '-' : $this->formatValue($res['max_value'], $format_result); 
            $return['min'] = $res['min_value'] == '' ? '-' : $this->formatValue($res['min_value'], $format_result);         
           
        }else{
            $return['max'] = $res['max_value']; 
            $return['min'] = $res['min_value'];              
        }

        return $return;      
    }    
    
    /*
     * Function to get SUMMARY value for some feature of sensor.
     * @param integer $sensor_feature_id   an ID of feature of some sensor
     * @param integer $measuring_timestamp a timestamp of moment to count X_hr back (to find SUM in that X_hr period)
     * @param integer $x_hr                a number of hours to calculate SUM in
     * @param string  $format_result       a name of feature to format according with its rules (default is FALSE - no format)
     */    
    public function getTotalInLastXHr($sensor_feature_id, $measuring_timestamp, $x_hr = 24, $format_result = false)
	{
        $time_start = mktime(
                date('H', $measuring_timestamp) - $x_hr, 
                date('i', $measuring_timestamp), 
                date('s', $measuring_timestamp), 
                date('m', $measuring_timestamp), 
                date('d', $measuring_timestamp), 
                date('Y', $measuring_timestamp) );

        $sql = "SELECT SUM(CAST(`sensor_feature_value` AS DECIMAL(15,4))) AS `total`
                FROM  `".SensorData::model()->tableName()."` 
                WHERE `sensor_feature_id`   =  '".$sensor_feature_id."' 
                  AND `measuring_timestamp` < '".date('Y-m-d H:i:s', $measuring_timestamp)."' 
                  AND `measuring_timestamp` >=  '".date('Y-m-d H:i:s', $time_start)."'
                  AND `is_m` = '0'";
		
        $res = Yii::app()->db->createCommand($sql)->queryScalar();

        $return = array();
        
        if ($format_result !== false)
		{
			$return['total'] = $res === '' ? '-' : $this->formatValue($res, $format_result);
        }
		else
		{
            $return['total'] = $res;
        }
		
        return $return;        
    }    
    
    /*
     * Function to get SUMMARY value for some feature of sensor.
     * @param integer $sensor_feature_id   an ID of feature of some sensor
     * @param integer $measuring_timestamp a timestamp of moment to count SUMMARY before it 
     * @param integer $start_hour          a hour to calculate SUM from
     * @param string  $format_result       a name of feature to format according with its rules (default is FALSE - no format)
     */       
    public function getTotalInDay($sensor_feature_id, $measuring_timestamp, $day = 1, $format_result = false){
        $feature = StationSensorFeature::model()->with('sensor.handler')->findByPk($sensor_feature_id);
        $start_time = $feature->sensor->handler->start_time;

        if($start_time == -1) {
            $return = $this->getTotalInLastXHr($sensor_feature_id, $measuring_timestamp, $day*24, $format_result);
            $timeFormat = date('H', $measuring_timestamp).':'.date('i', $measuring_timestamp);
        } else {
            $check = $start_time < date('H', $measuring_timestamp) ? 1 : 0;
            $time_start = mktime(
                $start_time,
                0,
                0,
                date('m', $measuring_timestamp),
                date('d', $measuring_timestamp) - ($day - $check),
                date('Y', $measuring_timestamp) );
            $time_end = mktime(
                $day>1 ? $start_time  :date('H', $measuring_timestamp),
                $day>1 ? 0            :date('i', $measuring_timestamp),
                $day>1 ? 0            :date('s', $measuring_timestamp),
                date('m', $measuring_timestamp),
                date('d', $measuring_timestamp) - ($day>1 ? ($day-$check-1):0),
                date('Y', $measuring_timestamp));

            $sql = "SELECT SUM(CAST(`sensor_feature_value` AS DECIMAL(15,4))) AS `total_today`
                    FROM `".SensorData::model()->tableName()."`
                    WHERE `sensor_feature_id` = '".$sensor_feature_id."'
                        AND `measuring_timestamp` <= '".date('Y-m-d H:i:s', $time_end)."'
                        AND `measuring_timestamp` >= '".date('Y-m-d H:i:s', $time_start)."'
                        AND `is_m` = '0'";
            $res = Yii::app()->db->createCommand($sql)->queryScalar();

            $return = array();
            $timeFormat = ($start_time<10?'0'.$start_time:$start_time).':00';

            if ($format_result !== false) {
                $return['total'] = $res === '' ? '-' : $this->formatValue($res, $format_result);
            } else {
                $return['total'] = $res;
            }
        }
        $return['total_title']=$day==1?
            'since '.$timeFormat.'h today':
            'between '.$timeFormat.'h and '.$timeFormat.'h yesterday';

        return $return;
    }
    public function getTotalFromHour($sensor_feature_id, $measuring_timestamp, $start_hour, $format_result = false)
	{
        $h_measurement_time = date('H', $measuring_timestamp);

        if ($h_measurement_time < $start_hour){
            $today_start = mktime($start_hour, 0, 0, date('m', $measuring_timestamp), date('d', $measuring_timestamp)-1, date('Y', $measuring_timestamp));
            $today_end = mktime($start_hour, 0, 0, date('m', $measuring_timestamp), date('d', $measuring_timestamp), date('Y', $measuring_timestamp));
        } else {
            $today_start = mktime($start_hour,0,0, date('m', $measuring_timestamp), date('d', $measuring_timestamp), date('Y', $measuring_timestamp));
            $today_end = mktime($start_hour,0,0, date('m', $measuring_timestamp), date('d', $measuring_timestamp)+1, date('Y', $measuring_timestamp));
        }

        if ($today_end > $measuring_timestamp)
            $today_end = $measuring_timestamp;


        $sql = "SELECT SUM(CAST(`sensor_feature_value` AS DECIMAL(15,4))) AS `total_today`
                FROM `".SensorData::model()->tableName()."`
                WHERE `sensor_feature_id` = '".$sensor_feature_id."'
                    AND `measuring_timestamp` <= '".date('Y-m-d H:i:s', $today_end)."'
                    AND `measuring_timestamp` >= '".date('Y-m-d H:i:s', $today_start)."'
                    AND `is_m` = '0'";
        $res = Yii::app()->db->createCommand($sql)->queryScalar();

        if ($format_result !== false) {
            return $res === '' ? '-' : $this->formatValue($res, $format_result);
        }

        return $res;
    }

    /*
	 * converts sensor string into pares of data to add them into database
	 */
    public function prepareDataPairs($incoming_sensor_value, $incoming_measuring_timestamp, $sensor_features_info)
	{
        $this->incoming_sensor_value = $incoming_sensor_value;
        $this->incoming_measuring_timestamp = $incoming_measuring_timestamp;
        $this->sensor_features_info = $sensor_features_info;
        
        return $this->_prepareDataPairs();
    }
    
    public function _prepareDataPairs()
	{
        return false;
    }
    
    /*
	 * saves pairs of data into database
	 */
    public function saveDataPairs($params)
	{
        $this->_logger->log(__METHOD__);

		$this->_logger->log(__METHOD__, array('pid' => getmypid(), 'log_id' => $params['listener_log_id']));


        if ($this->prepared_pairs)
		{
            $sql ='';
            $boool = false;
            foreach ($this->prepared_pairs as $key=>$pair){
				$sensor_feature_id = $this->_findFeatureId($pair['feature_code'], $params['sensor_features']);
				if (!$sensor_feature_id){
					$this->_logger->log(__METHOD__ .' Attention: ', array('feature_code' => $pair['feature_code'], 'sensor_features' => print_r($params['sensor_features'], true)));
                }
                $measuring_timestamp = date('Y-m-d H:i:s', isset($pair['measuring_timestamp']) ? $pair['measuring_timestamp'] : $this->incoming_measuring_timestamp);
                $criteria = new CDbCriteria();
                    $criteria->compare('measuring_timestamp', $measuring_timestamp);
                    $criteria->compare('sensor_feature_id', $sensor_feature_id);
                    $criteria->compare('station_id', $params['sensor']->station_id);

                $sensor_data = SensorData::model()->find($criteria);
				if (is_null($sensor_data) || $params['rewrite_prev_values']){
                    if (is_null($sensor_data)){
						$sensor_data = new SensorData();
                        $sensor_data->sensor_data_id ="DEFAULT";
                        $sensor_data->created = "NOW()";
                    }
//
//                    if(array_key_exists($pair['feature_code'],$this->checkPeriodArray)){
//                        $pair = $this->checkPeriod(
//                                            $measuring_timestamp,
//                                                $this->_findFeatureId($this->checkPeriodArray[$pair['feature_code']],$params['sensor_features']),
//                                            $params['sensor']->station_id,
//                                            $key);
//
//                    }
					$sensor_data->station_id                        = $params['sensor']->station_id;
                    $sensor_data->sensor_id                         = $params['sensor']->station_sensor_id;
                    $sensor_data->sensor_feature_id                 = $sensor_feature_id;
                    $sensor_data->sensor_feature_value              = $pair['value'];
                    $sensor_data->period                            = $pair['period'];
                    $sensor_data->listener_log_id                   = $params['listener_log_id'];
                    $sensor_data->measuring_timestamp               = $measuring_timestamp;
                    $sensor_data->sensor_feature_exp_value          = isset($pair['exp_value']) ? $pair['exp_value'] : 0;
                    $sensor_data->metric_id                         = $pair['metric_id'];
                    $sensor_data->sensor_feature_normalized_value   = $pair['normilized_value'];
                    $sensor_data->is_m                              = $pair['is_m'] == 1 ? 1 : 0;

                    $sensor_data->updated = "NOW()";
                    $sql.=sqlBuilder::createInsertFromObject($sensor_data,SensorData::model()->tableName(),$boool);
                    $boool=true;

                }
            }
            $sql.=';';
            $connection=Yii::app()->db;
            $command=$connection->createCommand($sql);
            try{
                $count = $command->execute();
                $this->_logger->log(__METHOD__, array('count' => $count));
            } catch (Exception $e) {
                $this->_logger->log(__METHOD__,array('err' => $e->getMessage()));
            }

        }
    }
    
    public function _findFeatureId($feature_code, $sensor_db_features)
	{
        return $sensor_db_features[$feature_code]['sensor_feature_id'];
    }
	
    public function _findFeatureConstantValue($feature_code, $sensor_db_features)
	{
       return $sensor_db_features[$feature_code]['feature_constant_value'];
    }

    public function getInfoForAwsPanel($sensor_pairs, $sensorList, $sensorData, $for = 'panel')
	{
        return false;
    }
    
    public function getInfoForAWS($sensor_id, $last_logs, $for = 'panel')
	{
		return false;
	}
    
    public function getRandomValue($features)
	{
        return false;
    }
    
	public function afterDataPairsSaved($save_data_params)
	{
        return false;
    }   
	
	
	public static function getFullSensorList($station_ids,$handlersDefault){

		$criteria = new CDbCriteria();
		$criteria->with = array(
			'sensor.station',
			'metric'
		);
		
		$criteria->compare('station.station_id', $station_ids);

        $records = StationSensorFeature::model()->findAll($criteria);
		
		$result = array();

        foreach ($records as $record)
		{
            if(isset($handlersDefault)){
                $record->default = $handlersDefault[$record->sensor->handler_id]->features[$record->feature_code];
            }
            $result[$record->feature_code][$record->sensor_id] = $record;
		}

        return $result;
	}

    public static function checkTrend($arr,$trend = 1,$i = 0){

        //trend = 1 check up
        //trend = -1 check down

        //if(abs($trend)!=1)return false;// For i = 1 or -1
        //$trend = $trend != 0 ? gmp_sign($trend) : 1; // For all i

        if(isset($arr[$i+1])){
            if($arr[$i]*$trend > $arr[$i+1]*$trend)
                return self::checkTrend($arr,$trend,$i+1);
            return false;
        } elseif (count($arr) != 4) {
            return false;
        }
        return true;
    }

    //changes sensor data, if period is short
    public function checkPeriod($measuring_timestamp,$sensor_feature_id,$station_id,$key)
    {

        // todo
        $station = new Station();
        $stationData = $station->findbypk($station_id);
        if ($stationData['timezone_id']) {
            TimezoneWork::set($stationData['timezone_id']);
        }

        $last = $this->prepared_pairs[$this->checkPeriodArray[$key]];
        $criteria = new CDbCriteria();
            $criteria->select = 'measuring_timestamp, sensor_feature_value, sensor_feature_normalized_value, period, measuring_timestamp, sensor_feature_id, station_id';
            $criteria->addCondition('measuring_timestamp < "'.$measuring_timestamp.'"');
            $criteria->addCondition('sensor_feature_id = "'.$sensor_feature_id.'"');
            $criteria->addCondition('station_id = "'.$station_id.'"');
            $criteria->order = 'measuring_timestamp DESC';
            $criteria->limit = 1;
        $data = SensorData::model()->find($criteria);

        if(!is_null($data) and $data!=false){
            $value_norm = $last['normilized_value'] - $data['sensor_feature_normalized_value'];
            $getDate = getdate(strtotime($measuring_timestamp));
            $getDatePrev = getdate(strtotime($data['measuring_timestamp']));



            if (
                (
                    $getDate['yday'] != $getDatePrev['yday']
                    && ($getDate['hours'] != 0 || $getDate['minutes'] != 0 || $getDate['seconds'] != 0)
                )
                ||
                (
                    $getDate['yday'] == $getDatePrev['yday']
                    && $getDatePrev['hours'] == 0
                    && $getDatePrev['minutes'] == 0
                    && $getDatePrev['seconds'] == 0
                )
            ) {
                $this->prepared_pairs[$key]['period']= $getDate['hours']*60 + $getDate['minutes'];
                $this->prepared_pairs[$key]['value']=$last['value'];
                $this->prepared_pairs[$key]['normilized_value']=$last['normilized_value'];
            } else {
                $this->prepared_pairs[$key]['period']=round((strtotime($measuring_timestamp)-strtotime($data['measuring_timestamp']))/60);
                $this->prepared_pairs[$key]['value'] = $last['value'] - $data['sensor_feature_value'];
                $this->prepared_pairs[$key]['normilized_value']=$value_norm;

                if ($last['value'] == 0 && $this->prepared_pairs[$key]['value'] < 0) {
                    $this->prepared_pairs[$key]['value']= 0;
                    $this->prepared_pairs[$key]['normilized_value'] = 0.0000;
                }
            }

            $this->_logger->log(__METHOD__.' getDate: '.$measuring_timestamp);
            $this->_logger->log(__METHOD__.' sensor_feature_id: '.$sensor_feature_id);
            $this->_logger->log(__METHOD__.' station_id: '.$station_id);
            $this->_logger->log(__METHOD__.' getDatePrev: '.$data['measuring_timestamp']);

        }

        return $this->prepared_pairs[$key];

    }

    public static function getTrendForAwsPanel($stationData){
        $change = 'no';
        $arrForTrend = array();
        foreach($stationData as $trendValue)
            $arrForTrend[]=$trendValue->sensor_feature_value;
        if (SensorHandler::checkTrend($arrForTrend,1))
            $change = 'up';
        else if (SensorHandler::checkTrend($arrForTrend,-1))
            $change = 'down';
        return $change;
    }

    public static function getDataForAwsPanel(&$code,$handler,$stations){
        $handlerClass=$handler->handler_id_code.self::classPrefix;
        $metricArr = array();

        foreach($code['stations'] as $station_id => &$station){
            //def value
            $change = $within = '';
            $valueArr =array();
            $featureCount = count($station['features']);

            foreach($station['features'] as $feature){
                $data = array_shift($feature['data']);
                if($data->listener_log_id == $stations[$station_id]->lastMessage->log_id){
                    //prepare date
                    $valueArr[] = $handlerClass::formatValue(
                        $data->sensor_feature_normalized_value,
                        $handler->features[$feature['info']->feature_code]->feature_code);
                    if($featureCount==1){
                        //check trend
                        $change = $handlerClass::getTrendForAwsPanel($feature['data']);
                        //between
                        if( $data->sensor_feature_normalized_value < $handler->features[$feature['info']->feature_code]->filter_min OR
                            $data->sensor_feature_normalized_value > $handler->features[$feature['info']->feature_code]->filter_max)
                            $within = 'error';
                    }
                }
                $metricArr[]=$feature['info']->metric->html_code;
            }
            $value = implode($valueArr,', ');

            $station['view'] = array(
                'value'     => isset($value)  ? $value  : '-',
                'change'    => isset($change) ? $change : 'no',//0,1,-1
                'within'    => isset($within) ? $within : '0',//0,1
            );
        }
        $code['metric'] =
            implode(
                array_filter(
                    array_unique($metricArr),
                    function($e){ return !empty($e);}
                ),
                ', ');
    }

    public static function getGroupAwsPanel(&$handlerGroup){
        $handlerGroup = array(
            array(
                'BatteryVoltage' => array(),
            ),
            array(
                'WindSpeed' => array(),
                'WindDirection' => array(),
            ),
            array(
                'Temperature' => array(),
                'Humidity' => array(),
                'DewPoint' => array(),
            ),
            array(
                'Pressure' => array(),
                'PressureSeaLevel' => array(),
            ),
            array(
                'TemperatureSoil' => array(),
                'TemperatureWater' => array(),
            ),
            array(
                'SolarRadiation' => array(),
                'SunshineDuration' => array(),
            ),
            array(
                'RainAws' => array(),
            ),
            array(
                'WaterLevel' => array(),
            ),
            array(
                'SeaLevelAWS' => array(),
                'SnowDepthAwsDlm13m' => array(),
            ),
            array(
                'VisibilityAWS' => array(),
                'VisibilityAwsDlm13m' => array(),
                'CloudHeightAWS' => array(),
                'CloudHeightAwsDlm13m' => array(),
            ),
        );
    }

    public static function setGroupAwsPanel(&$groups,$code,$handler_id,$handler_name){
        foreach($groups as &$group){
            foreach($group as $key => &$val){
                if($key == $code){
                    $val=array(
                        'id'   => $handler_id,
                        'name' => $handler_name
                    );
                    return true;
                }
            }
        }
        return false;
    }
}

?>