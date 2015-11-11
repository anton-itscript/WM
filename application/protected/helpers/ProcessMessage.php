<?php

/**
 * 
 * Process Message class.
 * Uses message (ListenerLog object, row from `listener_log` table) as input. 
 * 
 * Parses message, recognizes station and sensors, gets sensors data and puts them into database.
 * 
 */

class ProcessMessage extends BaseComponent
{
	// message (ListenerLog object, row from `listener_log` table)
    /** @var null|ListenerLog  */
    public $message_obj = null;
	
	// errors occured during message parse
    public $errors      = array();
	
	// warnings occured during message parse
    public $warnings    = array();

	// rg_log OR message
    protected $_message_type; 
	
	// station type (RG or AWS)
    protected $_type;
	
	// header of message
    protected $_header;
	
	// footer of message
    protected $_footer;
	
	// body of message
    protected $_body;
	
	// measuring date from message
    protected $_tx_date;
	
	// measuring time from message
    protected $_tx_time;

	// object of Station model (row from `station` table related to station indicated inside of message
    protected $_station;
	
	// list of sensors and their information
    protected $_sensors = array();
	
	// battery voltage defined from message
    protected $rg_battery_voltage;

    public function __construct($logger, $message_obj_temp)
	{
		parent::__construct($logger);
		// тут надо производить замену модели  $message_obj с ListenerLogtemp на ListenrLog

        $message_obj = new ListenerLog();
        $message_obj->listener_id               = $message_obj_temp->listener_id;
        $message_obj->message                   = $message_obj_temp->message;
        $message_obj->rewrite_prev_values       = $message_obj_temp->rewrite_prev_values;
        $message_obj->is_processed              = $message_obj_temp->is_processed;
        $message_obj->is_processing             = $message_obj_temp->is_processing;
        $message_obj->source                    = $message_obj_temp->source;
        $message_obj->source_info               = $message_obj_temp->source_info;
        $message_obj->station_id                = 0;
        $message_obj->save();
		$this->_logger->log(__METHOD__);
        $this->message_obj = $message_obj;
    }
    
	/**
	 * Run parsing of message:
	 * 1. check messages's integrity
	 * 2. save some data defined during integrity
	 * 3. parse message and add new sensors data to database
	 * 4. run calculations basing on new data
	 * 
	 */
    public function run()
    {
		$this->_logger->log(__METHOD__);
		if (is_null($this->message_obj))
		{
			$this->pushError('null_message', 'Message object is null');
			return;
		}

        $this->_logger->log(__METHOD__, array('message' => $this->message_obj->message));

        $this->checkIntegrity();
		$this->saveMessage();

        if ($this->message_obj->failed)
		{
            $this->_logger->log(__METHOD__ .' Failed');
            return;
        }

        $this->logMessage();

        $this->runCalculations();

        $this->_logger->log(__METHOD__ .' Complete');
    }

	/**
	 * save some message information
	 */
    function saveMessage()
    {
		$this->_logger->log(__METHOD__);
		
        if ($this->_type)
		{
            $this->message_obj->station_type = $this->_type;
        }
        $this->message_obj->is_processed    = 1;
        $this->message_obj->is_processing   = 0;
		$result = $this->message_obj->save();
		
		$this->_logger->log(__METHOD__, array('Save result' => $result));
    }

	/*
	 * check message integrity
	 */
    function checkIntegrity()
    {
		$this->_logger->log(__METHOD__);
		
		// sometimes arrived message can contain casual symbols before @ or after $
		// we should strip them to work with clear message
        $occurances_at = strpos($this->message_obj->message, '@');
        $occurances_dl = strpos($this->message_obj->message, '$');
		
        if (($occurances_at !== false) && ($occurances_dl !== false))
		{
            $this->message_obj->message = substr($this->message_obj->message, $occurances_at);
            
            $len = strlen($this->message_obj->message);
            $occurances_dl = ($len - strpos($this->message_obj->message, '$') - 1)*(-1);
            
            if ($occurances_dl)
			{
                $this->message_obj->message = substr($this->message_obj->message, 0, $occurances_dl);
            }
        }        
        
		// check if arrived message - is line from Rain Datalogger's log OR is regular message
        $rg_log_pattern = '/^\d{2}\/\d{2}\/\d{2},\d{2}:\d{2},\d{3},\d{0,5}$/';
         
        if (preg_match($rg_log_pattern, $this->message_obj->message))
		{
            $this->_message_type = 'rg_log';
            $this->_type = 'rain';
        } 
		else
		{
            $this->_message_type = 'message';
        }
        
		$this->_logger->log(__METHOD__ .' Message type detected', array('type' => $this->_message_type));
                
        // if arrived message - is regular message    
        if ($this->_message_type == 'message')
		{
            // message must start with @
            if (substr($this->message_obj->message, 0, 1) != '@')
			{
                $this->pushError('start_missed', 'Record does not start with @');
            }

			// message must end with $
            if (substr($this->message_obj->message, -1, 1) != '$')
			{
                $this->pushError('end_missed', 'Record does not end with $');
            }

            if (!$this->errors)
			{
				// Rain DataLogger's message has R at 22nd position
                if (substr($this->message_obj->message, 21, 1) == 'R')
				{
                    $this->_type = 'rain';
                    $this->_header = substr($this->message_obj->message, 0, 21);
                }
				else
				{
                    $this->_type = 'aws';
                    $this->_header = substr($this->message_obj->message, 0, 19);
                }

				// footer - is last 8 symbols of message without first and last symbols
                $this->_footer = substr($this->message_obj->message, -9, -1);
                
				// crc source - is CRC code of other part of the message
                $compare_with_crc = substr($this->message_obj->message, 1, -9);
                
				$check_str = It::prepareCRC($compare_with_crc);
                
				if ($check_str !== $this->_footer)
				{
                    $this->pushError('crc_wrong', 'CRC code is incorrect');
                }
            }
        }
        
        if (!$this->errors)
		{
        	// RG log line, AWS and Rain messages have different position of date and time
			// RG message also has battery voltage value without specific sensor
			// station ID is located just after date and time substrings, so we can get it by the way
            if ($this->_message_type == 'rg_log')
			{
                $this->_tx_date = str_replace('/', '', substr($this->message_obj->message, 0, 8));
                $this->_tx_time = str_replace(':', '', substr($this->message_obj->message, 9, 5));
                
				$this->rg_battery_voltage = substr($this->message_obj->message, 15, 3);
				
                $this->_body = substr($this->message_obj->message, 19);
                
                $this->getStation('', $this->message_obj->station_id);
            }
			else
			{
                if ($this->_type == 'aws')
				{
					// starts from 20s symbol and discards 9 symbols from end (CRC)
                    $this->_body = substr($this->message_obj->message, 19, -9); 
                    
					$this->_tx_date = substr($this->message_obj->message, 7, 6);
                    $this->_tx_time = substr($this->message_obj->message, 13, 4);

                    $station_id = substr($this->message_obj->message, 2, 5);
                } 
				else
				{
					// starts from 22d symbol and discards 9 symbols from end (CRC)
                    $this->_body = substr($this->message_obj->message, 21, -9);
					
                    $this->_tx_date = substr($this->message_obj->message, 6, 6);
                    $this->_tx_time = substr($this->message_obj->message, 12, 4);
                    $this->rg_battery_voltage = substr($this->message_obj->message, 16, 3);

                    $station_id = substr($this->message_obj->message, 2, 4);
                }
				
                $this->getStation($station_id);
            }
            // Check length string of DATE & TIME
            // Good if strlen(date + time) == 6 + 4 and date + time is numeric
            if (empty($this->_tx_date)
                || empty($this->_tx_time)
                || strlen($this->_tx_date . $this->_tx_time) != 10
                || !is_numeric($this->_tx_date . $this->_tx_time)
            ){
                $this->pushError('date_format', 'Time or date is incorrect:' . $this->_tx_date . $this->_tx_time);
            }
        }
    }

	/**
	 * Pushes error message to errors stack
	 * 
	 * @param string $code
	 * @param string $description 
	 */
    function pushError($code, $description = '')
	{
        $this->_logger->log(__METHOD__, array('code' => $code, 'description' => $description));
		
		$this->errors[] = array($code, $description);
		
		if (!is_null($this->message_obj))
		{
			$this->message_obj->failed = 1;
		
			$process_error = new ListenerLogProcessError();
        
			$process_error->log_id = $this->message_obj->log_id;
			$process_error->type = 'error';
			$process_error->code = $code;
			$process_error->description = $description;

			$process_error->save();
		}
    }
    
	/**
	 * Pushes warning message to warnings stack
	 * 
	 * @param string $code
	 * @param string $description 
	 */
    function pushWarning($code, $description = '')
	{
        $this->_logger->log(__METHOD__, array('code' => $code, 'description' => $description));
		
		$this->warnings[] = array($code, $description);
        
		$process_error = new ListenerLogProcessError();
        
		$process_error->log_id = $this->message_obj->log_id;
        $process_error->type = 'warning';
        $process_error->code = $code;
        $process_error->description = $description;
        
		$process_error->save();
    }
    
	/**
	 * Gets station information
	 * 
	 * @param string $station_id_code
	 * @param int $station_id
	 * @return boolean 
	 */
    function getStation($station_id_code = '', $station_id = 0)
    {
		$this->_logger->log(__METHOD__, array('station_id_code' => $station_id_code, 'station_id' => $station_id));
		
        if ($station_id_code)
		{
            $station = Station::model()->find('station_id_code = :station_id_code', array(':station_id_code' => $station_id_code));
        } 
		else if ($station_id)
		{
            $station = Station::model()->find('station_id = :station_id', array(':station_id' => $station_id));
        }
        
        if (!$station)
		{
            $this->pushError('unknown_station', 'Can not find station for station_id_code="'.$station_id_code.'" in the DB, station_id="'.$station_id.'"');
        }
		else
		{
            $this->message_obj->station_id = $station->station_id;
            $this->_station = $station;
            
			$this->_logger->log(__METHOD__ .' Station detected', array('id' => $station->station_id, 'code' => $station->station_id_code, 'timezone' => $station->timezone_id));
            
			return true;
        }
		
        return false;
    }

	/**
	 * Gets information about sensor by sensor ID code
	 *  
	 * @access protected
	 * @param string $sensor_id_code
	 * @return array 
	 */
    protected function getSensorInfo($sensor_id_code)
    {
		if (!array_key_exists($sensor_id_code, $this->_sensors))
		{
            $sensor = StationSensor::getInfoForHandler($sensor_id_code, $this->_station->station_id);
        
			if (is_null($sensor))
			{
                $this->pushWarning('unknown_sensor', 'Can not find sensor "'. $sensor_id_code .'" for station "'. $this->_station->station_id_code .'" in the database. Sensor value was not saved.');
                
				return false;
            } 
			else
			{
				$this->_sensors[$sensor_id_code]['sensor'] = $sensor;
                $this->_sensors[$sensor_id_code]['features'] = StationSensorFeature::getInfoForHandler($sensor->station_sensor_id);
            }
        }
		
        return $this->_sensors[$sensor_id_code];
    }

	/**
	 * However Rain Datalogger can have more than 1 sensor.
	 * Message from Rain Datalogger's log has information about only 1 sensor. 
	 * This function is used to get information about first sensor of this station
	 * 
	 *  @access protected
	 *  @return array 
	 */
    protected function getFirstRGSensor()
	{
		$criteria = new CDbCriteria();
		$criteria->with = array('handler', 'first_sensor_feature');
		
		$criteria->compare('station_id', $this->_station->station_id);
		$criteria->compare('first_sensor_feature.sensor_feature_id', '>0');
		
		$sensor = StationSensor::model()->find($criteria);
		
		if (is_null($sensor))
		{
            $this->pushWarning('cant_find_rain_sensor', 'Can not find rain sensor for RG station "'.$this->_station->station_id_code.'" in the DB. Sensor value was not saved.');
            return false;
        } 
		else
		{
			return array(
				'sensor' => $sensor,
				'features' => StationSensorFeature::getInfoForHandler($sensor->station_sensor_id),
			);
        }        
    }

    
    /**
	 * parses message and adds parsed values to database
	 */
    function logMessage()
    {
		$this->_logger->log(__METHOD__);

        if ($this->_station->timezone_id){
            TimezoneWork::set($this->_station->timezone_id);
//            TimezoneWork::setReverse($this->_station->timezone_id);
        }

		// saves measuring timestamp
        $measuring_timestamp = mktime(substr($this->_tx_time,0,2), substr($this->_tx_time,2,2), 0, substr($this->_tx_date,2,2), substr($this->_tx_date,4,2), substr($this->_tx_date,0,2));

//        if ($this->_station->timezone_id){
//            $measuring_timestamp = TimezoneWork::setTimeByLocalTz($this->_station->timezone_id,$measuring_timestamp);
//        }



        $this->message_obj->measuring_timestamp = date('Y-m-d H:i:s',$measuring_timestamp);

        $this->_logger->log(__METHOD__." measuring_timestamp: ".$measuring_timestamp);
        $this->_logger->log(__METHOD__." date: ".$this->message_obj->measuring_timestamp);

        $this->message_obj->save();
		// there are different ways to parse LOG message and REGULAR message
        if ($this->_message_type === 'rg_log'){
            // LOG provides us with info from only 1 sensor (however RG station can have more sensors)
            $sensor_info = $this->getFirstRGSensor();
            if ($sensor_info !== false){
                $save_data_params = array(
                    'listener_log_id'     => $this->message_obj->log_id,
                    'rewrite_prev_values' => $this->message_obj->rewrite_prev_values,
                    'battery_voltage'     => $this->rg_battery_voltage,
                    'sensor'              => $sensor_info['sensor'],
                    'sensor_features'     => $sensor_info['features']
                );
				// create object of sensor handler
                $handler_obj = SensorHandler::create('RainRgLog');

                // parse part of message which is going just after sensor ID.
				// prepare data pairs basing on this string

                $res = $handler_obj->prepareDataPairs($this->_body, $measuring_timestamp, $sensor_info['features']);
				if ($res === false){
                    $this->pushWarning('incorrect_sensor_value_format', 'Handler '. $sensor_info['sensor']->sensor_id_code .' can not get data from "'. $this->_body .'"');
                    //continue;
                }

                // save prepared sensor's data
                $handler_obj->saveDataPairs($save_data_params);

            }
        }
		else{
			// parse body into pairs SensorId::DataString
            $message_sensors_strings = $this->parseSensorsValues($this->_body);

			if (is_array($message_sensors_strings) && count($message_sensors_strings) > 0)
			{
                $save_data_params = array(
                    'listener_log_id'     => $this->message_obj->log_id,
                    'rewrite_prev_values' => $this->message_obj->rewrite_prev_values,
                    'battery_voltage'     => $this->rg_battery_voltage
                );
                foreach ($message_sensors_strings as $message_sensor_string)
				{
					// get sensor's info by sensor ID code
                    $sensor_info = $this->getSensorInfo($message_sensor_string[0]);
					if (!is_null($sensor_info)){
						$save_data_params['sensor'] = $sensor_info['sensor'];
                        $save_data_params['sensor_features'] = $sensor_info['features'];
						// create object of sensor handler
                        $handler_obj = SensorHandler::create($sensor_info['sensor']->handler->handler_id_code);
						// parse string coming after sensor ID into data pairs (feature:value)
                        $res = $handler_obj->prepareDataPairs($message_sensor_string[1], $measuring_timestamp, $sensor_info['features']);
						if ($res === false){
                            $this->pushWarning('incorrect_sensor_value_format', 'Handler '.$message_sensor_string[0].' can not get data from "'.$message_sensor_string[1].'"');
                            continue;
                        }
                        // save features values
                        $handler_obj->saveDataPairs($save_data_params);
						// some actions can be done after data is saved (depends on handler)
                        $handler_obj->afterDataPairsSaved($save_data_params);
                    }
                }
            } 
        }
    }    
	
	/**
	 * AWS and AWOS stations can have calculations (dewPoint, MSL)
	 * Run calculations basing on already parsed data
	 * 
	 * @return type 
	 */
    public function runCalculations()
	{
		$this->_logger->log(__METHOD__);
		
		if (!in_array($this->_station->station_type, array('aws', 'awos')))
		{
            return false;
        }        
        
		// get calculations for station ID defined from arrived message
        $records = StationCalculation::getStationCalculationHandlers(array($this->_station->station_id));
		
        $handlers = array_key_exists($this->_station->station_id, $records) 
						? $records[$this->_station->station_id] 
						: null;
		
        if (is_array($handlers))
		{
            foreach ($handlers as $value)
			{
				// create object of calculation handler
                $handler = CalculationHandler::create($value->handler->handler_id_code);
				
				// run calculation for station basing on message's ID
                $handler->calculate($this->_station, $this->message_obj->log_id);
            }
        }
    }    
	
	/**
	 * Parse message's body into array with pairs SensorID:StringAfterSensorID
	 * 
	 * This function conciders that SensorID - is 3-symbols sequense: Letter-Letter-Digit (doesn't contain "M" symbols)
	 * String after SensorID - is Sensor's value. It can contain "M" symbols.
	 * "M" symbols mean that some sensor's feature has unknown value.
	 * 
	 * @deprecated
	 * @param string $body
	 * @return array 
	 */
    protected function parseSensorsValuesOld($body)
	{
        $res = preg_match_all("/(([A-Z]{2})([\d]{1}))/", $body, $sensor_ids);
        
        $sensor_ids_values = array();
        
        if ($sensor_ids[0])
		{
            $tmp_body = $body;
            $total = count($sensor_ids[0]);
            
            $prev = 0;
            $prev_sensor_data_part = '';
            foreach ($sensor_ids[0] as $key => $sensor_id)
			{
                $prev_sensor_data_part = strstr($tmp_body, $sensor_id, true);
                $after_occ = strstr($tmp_body, $sensor_id, false);                
                
                $tmp_body = substr($after_occ, 3);
                
                if ($sensor_ids[2][$key] == 'MM')
				{
                    $sensor_ids_values[$prev][1] .= $prev_sensor_data_part;
                    $sensor_ids_values[$prev][1] .= $sensor_id;
                
					if ($key+1 == $total)
					{
                        $sensor_ids_values[$prev][1] .= $tmp_body;
                    }                      
                    continue;
                }
                $sensor_ids_values[$key] = array(0 => $sensor_id, 1 => '');
                
                if ($key > 0)
				{
                    $sensor_ids_values[$prev][1] .= $prev_sensor_data_part;
                }
				
                if ($key+1 == $total)
				{
                    $sensor_ids_values[$key][1] .= $tmp_body;
                }
                
                $prev = $key;
            }
        }
		
        return $sensor_ids_values;
    }
	
	protected function parseSensorsValues($input)
	{
        $sensor_ids_values = array();
        
		$count = preg_match_all("/([A-LN-Z]{2}\d)(.+?)(?=\z|[A-LN-Z]{2}\d)/", $input, $sensor_ids);
		
        if ($count !== false)
		{
			for($i = 0; $i < $count; $i++)
			{
				$sensor_ids_values[] = array(
					$sensor_ids[1][$i],
					$sensor_ids[2][$i],
				);
			}
		}
		
        return $sensor_ids_values;
    }


    public function geLogMessage()
    {
        return $this->message_obj;
    }



    public function __destruct()
    {
        if (get_class(Yii::app()) != 'CConsoleApplication')
		{
            $tz = Yii::app()->user->getTZ();

            if ($tz != date_default_timezone_get())
			{
                TimezoneWork::set($tz);
            }
        }
    }
}

?>