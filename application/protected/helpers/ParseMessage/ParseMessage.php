<?php

/**
 * 
 * Process Message class.
 * Uses message (ListenerLog object, row from `listener_log` table) as input. 
 * 
 * Parses message, recognizes station and sensors, gets sensors data and puts them into database.
 * 
 */

class ParseMessage extends BaseComponent
{

	// errors occured during message parse
    public $errors      = array();
	
	// warnings occured during message parse
    public $warnings    = array();

	//  message
    protected $message;

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

    // station_id code
    protected $station_id;

    //sensor values
    protected $sensor_values;

	// battery voltage defined from message
    protected $rg_battery_voltage;

    public function __construct($logger, $message)
	{
		parent::__construct($logger);
		
		$this->_logger->log(__CLASS__.' '.__METHOD__);
		
        $this->message = $message;
        $this->run();
    }
    
	/**
	 * Run parsing of message:
	 * 1. check messages's integrity
	 * 2. parse message and add new sensors data to database
	 */
    public function run()
    {
		$this->_logger->log(__CLASS__.' '.__METHOD__);
		if (is_null($this->message))
		{
			$this->pushError('null_message', 'Message string is null');
			return;
		}

        $this->_logger->log(__CLASS__.' '.__METHOD__, array('message' => $this->message));

        $this->checkIntegrity();


//        if ($this->message_obj->failed)
//		{
//            $this->_logger->log(__CLASS__.' '.__METHOD__ .' Failed');
//            return;
//        }


        $this->_logger->log(__CLASS__.' '.__METHOD__ .' Complete');
    }

	/*
	 * check message integrity
	 */
    function checkIntegrity()
    {
		$this->_logger->log(__CLASS__.' '.__METHOD__);
		
		// sometimes arrived message can contain casual symbols before @ or after $
		// we should strip them to work with clear message
        $occurances_at = strpos($this->message, '@');
        $occurances_dl = strpos($this->message, '$');
		
        if (($occurances_at !== false) && ($occurances_dl !== false))
		{
            $this->message = substr($this->message, $occurances_at);
            
            $len = strlen($this->message);
            $occurances_dl = ($len - strpos($this->message, '$') - 1)*(-1);
            
            if ($occurances_dl)
			{
                $this->message = substr($this->message, 0, $occurances_dl);
            }
        }        
        
		// check if arrived message - is line from Rain Datalogger's log OR is regular message
        $rg_log_pattern = '/^\d{2}\/\d{2}\/\d{2},\d{2}:\d{2},\d{3},\d{0,5}$/';
         
        if (preg_match($rg_log_pattern, $this->message))
		{
            $this->_message_type = 'rg_log';
            $this->_type = 'rain';
        } 
		else
		{
            $this->_message_type = 'message';
        }
        
		$this->_logger->log(__CLASS__.' '.__METHOD__ .' Message type detected', array('type' => $this->_message_type));
                
        // if arrived message - is regular message    
        if ($this->_message_type == 'message')
		{
            // message must start with @
            if (substr($this->message, 0, 1) != '@')
			{
                $this->pushError('start_missed', 'Record does not start with @');
            }

			// message must end with $
            if (substr($this->message, -1, 1) != '$')
			{
                $this->pushError('end_missed', 'Record does not end with $');
            }

            if (!$this->errors)
			{
				// Rain DataLogger's message has R at 22nd position
                if (substr($this->message, 21, 1) == 'R')
				{
                    $this->_type = 'rain';
                    $this->_header = substr($this->message, 0, 21);
                }
				else
				{
                    $this->_type = 'aws';
                    $this->_header = substr($this->message, 0, 19);
                }

				// footer - is last 8 symbols of message without first and last symbols
                $this->_footer = substr($this->message, -9, -1);
                
				// crc source - is CRC code of other part of the message
                $compare_with_crc = substr($this->message, 1, -9);
                
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
                $this->_tx_date = str_replace('/', '', substr($this->message, 0, 8));
                $this->_tx_time = str_replace(':', '', substr($this->message, 9, 5));
                
				$this->rg_battery_voltage = substr($this->message, 15, 3);
				
                $this->_body = substr($this->message, 19);
            }
			else
			{
                if ($this->_type == 'aws')
				{
					// starts from 20s symbol and discards 9 symbols from end (CRC)
                    $this->_body = substr($this->message, 19, -9);
                    
					$this->_tx_date = substr($this->message, 7, 6);
                    $this->_tx_time = substr($this->message, 13, 4);

                    $this->station_id = substr($this->message, 2, 5);
                } 
				else
				{
					// starts from 22d symbol and discards 9 symbols from end (CRC)
                    $this->_body = substr($this->message, 21, -9);
					
                    $this->_tx_date = substr($this->message, 6, 6);
                    $this->_tx_time = substr($this->message, 12, 4);
                    $this->rg_battery_voltage = substr($this->message, 16, 3);

                    $this->station_id = substr($this->message, 2, 4);
                }

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

           $this->sensor_values =  $this->parseSensorsValues($this->_body);
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
        $this->_logger->log(__CLASS__.' '.__METHOD__, array('code' => $code, 'description' => $description));
		
		$this->errors[] = array($code, $description);

    }

    /**
     * Parse message's body into array with pairs SensorID:StringAfterSensorID
     *
     * This function conciders that SensorID - is 3-symbols sequense: Letter-Letter-Digit (doesn't contain "M" symbols)
     * String after SensorID - is Sensor's value. It can contain "M" symbols.
     * "M" symbols mean that some sensor's feature has unknown value.
     *
     * @param string $body
     * @return array
     */
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

    public function getMeasuringTimestamp()
    {
        $measuring_timestamp = mktime(substr($this->_tx_time,0,2), substr($this->_tx_time,2,2), 0, substr($this->_tx_date,2,2), substr($this->_tx_date,4,2), substr($this->_tx_date,0,2));
        return date('Y-m-d H:i:s',$measuring_timestamp);
    }

    public function getStationIdCode()
    {
        return $this->station_id;
    }

    public function __destruct()
    {
        if (get_class(Yii::app()) != 'CConsoleApplication')
		{
            $tz = Yii::app()->user->getTZ();
            $this->_logger->log(__METHOD__.' $tz: '.$tz);
            $this->_logger->log(__METHOD__.' $tz: '.$tz);
            $this->_logger->log(__METHOD__.' $tz: '.$tz);
            $this->_logger->log(__METHOD__.' $tz: '.$tz);
            if ($tz != date_default_timezone_get())
			{
                TimezoneWork::set($tz);
            }
        }
    }
}

?>