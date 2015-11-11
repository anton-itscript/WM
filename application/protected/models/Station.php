<?php

class Station extends CStubActiveRecord
{
	public $previousMessage;
	public $lastMessage;
	public $nextMessageExpected;
	public $nextMessageIsLates = null;
	public $displaySensors;
	public $displaySensorsValues = array();
	public $calculations = array();

    public $beforeSave = true;

    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function beforeSave(){
        if(!$this->getUseLong() && $this->beforeSave){
            if ($this->isNewRecord)
                $this->created = new CDbExpression('NOW()');

            $this->timezone_offset = TimezoneWork::getOffsetFromUTC($this->timezone_id, 1);
            $this->updated = new CDbExpression('NOW()');
        }
        return parent::beforeSave();
    }

    public function afterFind()
    {

        if(empty($this->color)) {
            $this->beforeSave = false;
            $this->color = Color::randomColor();
            $this->save(false);
        }
        return parent::afterFind();
    }

	public function tableName()
	{
		return 'station';
	}

    public function rules()
	{
        return array(

            array('station_id_code,display_name', 'required'),
            array('station_id_code', 'unique'),
            array('station_id_code', 'checkStationIdCode'),
            
            array('station_id_code', 'length', 'max' => 5, 'min' => 4, 'allowEmpty' => false),
            array('display_name', 'length', 'max' => 35, 'min' => 1, 'allowEmpty' => false),
            
            array('station_type', 'in', 'range' => array('rain', 'aws', 'awos')),

            array('communication_type', 'in', 'range' => array('direct', 'sms', 'tcpip', 'gprs', 'server')),
            array('communication_port', 'length', 'max' => 5),
            array('communication_port', 'checkPort'),
            array('communication_esp_ip', 'length', 'max' => 16),
            array('communication_esp_ip', 'checkESPIP'),
            array('communication_esp_port', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('communication_esp_port', 'checkESPPort'),            
            
            array('display_name', 'length', 'allowEmpty' => true),
            array('icao_code', 'length', 'allowEmpty' => true, 'min' => 4, 'max' => 4),
            array('details', 'length', 'allowEmpty' => true),

            array('status_message_period, event_message_period', 'numerical', 'integerOnly' => true, 'allowEmpty' => false),

            array('timezone_id', 'length', 'allowEmpty' => false),
            array('lat', 'numerical', 'allowEmpty' => 1, 'message' => 'Latitude must be a number.'),
            array('lng', 'numerical', 'allowEmpty' => 1, 'message' => 'Longitude must be a number.'),
            
            array('wmo_block_number', 'length', 'max' => 2, 'allowEmpty' => true),
            array('wmo_block_number','checkWmoBlock'),
            
                 
            array('station_number', 'length', 'max' => 3, 'allowEmpty' => true),
            array('station_number','checkStationNumber'),       
            
            array('wmo_member_state_id', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('wmo_member_state_id', 'length', 'is' => 3, 'max' => 3, 'allowEmpty' => true), 
            array('wmo_originating_centre', 'length', 'is' => 3, 'max' => 3, 'allowEmpty' => true),       
            array('wmo_originating_centre', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            
            array('national_aws_number', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('national_aws_number', 'length', 'max' => 9),
            
            array('magnetic_north_offset', 'numerical', 'integerOnly' => true, 'allowEmpty' => false),
            array('magnetic_north_offset', 'length', 'max' => 3),
            
            array('altitude', 'numerical', 'integerOnly' => true),
            array('altitude', 'length', 'max' => 9),
			array('logger_type', 'in', 'range' => array('DLM11', 'DLM13M')),
			
			array('phone_number, sms_message', 'length', 'allowEmpty' => true),

            array('station_gravity', 'numerical', 'min' => 0),
            array('station_gravity', 'compare', 'operator' => '!=', 'compareValue' => '0'),
            array('color', 'ColorValidator'),
        );
    }
    
	public function relations()
    {
        return array(
            'sensors'   => array(self::HAS_MANY, 'StationSensor', 'station_id', 'order' => 'sensors.display_name asc'),
            'messages'  => array(self::HAS_MANY, 'ListenerLog', 'station_id'),
            'schedule'  => array(self::HAS_MANY, 'ScheduleReport', 'station_id'),
            'station_calculation'  => array(self::HAS_MANY, 'StationCalculation', 'station_id'),
        );
    }

    public function beforeDelete()
    {
        $cr_sd = new CDbCriteria();
        $cr_sd->addCondition('station_id = ' . $this->station_id);

        $cr_cd = new CDbCriteria();
        $cr_cd->with = [
            'calculation' => [
                'select' => false,
                'condition' => 'calculation.station_id = ' . $this->station_id
            ]
        ];

        $cr_slt = new CDbCriteria();
        $cr_slt->with = [
            'sensor' => [
                'select' => false,
                'condition' => 'sensor.station_id = ' . $this->station_id,
            ]
        ];

        if ($this->getUseLong()) {
            SensorData::model()->long()->deleteAll($cr_sd);
            StationCalculationData::model()->long()->deleteAll($cr_cd);
            SeaLevelTrend::model()->long()->deleteAll($cr_slt);
        } else {
            SensorData::model()->deleteAll($cr_sd);
            StationCalculationData::model()->deleteAll($cr_cd);
            SeaLevelTrend::model()->long()->deleteAll($cr_slt);
        }

        return parent::beforeDelete();
    }
	
	public function checkAwosFolder()
	{
        if ($this->station_type == 'awos')
		{
            if ($this->awos_msg_source_folder == '')
			{
                 $this->addError('awos_msg_source_folder', "AWOS XML folder can not be empty!");
                 return false;
            }
        }
		
        return true;
    }

    public function checkStationIdCode()
	{
        if ($this->station_id_code)
		{
            $errors = array();
            $length = $this->station_type == 'rain' ? 4 : 5;
            
            if (!preg_match('/^[A-Z,a-z,0-9]{'.$length.'}$/', $this->station_id_code, $matches))
			{    
                if ($length == 4)
                    $errors[] = 'Rain Station ID can contain only Letters (A-Z) and Figures (1-9), and must be of '.$length.' chars length.';
                else
                    $errors[] = 'AWS Station ID can contain only Letters (A-Z) and Figures (1-9), and must be of '.$length.' chars length.';
            }   
            
            if ($errors)
			{
                $this->addError('station_id_code', implode(' ', $errors));
                
				return false;
            }
        }
		
        return true;
    }
    
    public function checkStationNumber()
	{
        if ($this->station_type != 'rain')
		{
            if ($this->station_number == '')
			{
                $this->addError('station_number', 'WMO Station # cannot be blank.');
                
				return false;
            }
			
            if (!is_numeric($this->station_number))
			{
                $this->addError('station_number', 'WMO Station # must be an integer.');
                
				return false;                
            }
			
            if (strlen($this->station_number) > 3)
			{
                $this->addError('station_number', 'WMO Station # is of the wrong length (should be not more 3 characters).');
                
				return false;                
            }            
        }
		else
		{
            $this->station_number = '';
        }
		
        return true;        
    }

    public function checkWmoBlock()
	{
        if ($this->station_type != 'rain')
		{
            if ($this->wmo_block_number == '')
			{
                $this->addError('wmo_block_number', 'WMO Block # cannot be blank.');
                
				return false;
            }
			
            if (!is_numeric($this->wmo_block_number))
			{
                $this->addError('wmo_block_number', 'WMO Block # must be an integer.');
                
				return false;                
            }
			
            if (strlen($this->wmo_block_number) != 2)
			{
                $this->addError('wmo_block_number', 'WMO Block # is of the wrong length (should be 2 characters).');
                
				return false;                
            }            
        }
		else
		{
            $this->wmo_block_number = '';
        }
		
        return true;
    }
    
    public function checkPort()
    {
        $this->communication_port = strtoupper($this->communication_port);
        
		if (in_array($this->communication_type, array('direct', 'sms')))
		{
            if ($this->communication_port == '')
			{
                $this->addError('communication_port', 'COM Port name can\'t be empty');
                
				return false;
            }
            
			if (!preg_match('/^COM([1-99]{1,2})$/', $this->communication_port, $matches))
			{
                $this->addError('communication_port', 'COM Port name has incorrect format');
                
				return false;                
            }
        }
		else
		{
            $this->communication_port = '';
        }
		
        return true;
    }
    
    public function checkESPIP()
	{
        if (in_array($this->communication_type, array('tcpip', 'gprs', 'server')))
		{
            if ($this->communication_esp_ip == '')
			{
                $this->addError('communication_esp_ip', 'IP address can\'t be empty');
                
				return false;
            }
			
            $long = ip2long($this->communication_esp_ip);
            
			if (($long == -1 || $long === false) && (preg_match('/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}|[a-zA-Z0-9\-\.]+\.[a-zA-Z]+|localhost)$/i', $this->communication_esp_ip) === 0))
			{
                $this->addError('communication_esp_ip', 'Address is invalid.');
				
                return false;                
            }
        }
		else
		{
            $this->communication_esp_ip = '';
            $this->communication_esp_port = 0;
        }
		
        return true;        
    }
    
    public function checkESPPort()
	{
        if (in_array($this->communication_type, array('tcpip', 'gprs', 'server')))
		{
            if (!$this->communication_esp_port)
			{
                $this->addError('communication_esp_port', 'Port can\'t be empty or null');
                
				return false;
            }
        }
		else
		{
            $this->communication_esp_ip = '';
            $this->communication_esp_port = 0;
        }
		
        return true;         
    }

    public function attributeLabels()
	{
        return array (
            'station_id_code'        => It::t ('attributes', 'station_id_code'),
            'station_type'           => It::t ('attributes', 'station_type'),
            'communication_type'     => It::t ('attributes', 'communication_type'),
            'communication_port'     => It::t ('attributes', 'communication_port'),
            'communication_esp_ip'   => It::t ('attributes', 'communication_esp_ip'),
            'communication_esp_port' => It::t ('attributes', 'communication_esp_port'),
            'display_name'           => It::t ('attributes', 'display_name'),
            'icao_code'              => It::t ('attributes', 'icao_code'),
            'details'                => It::t ('attributes', 'details'),
            'timezone_id'            => It::t ('attributes', 'timezone_id'),
            'station_number'         => It::t ('attributes', 'station_number'),
            'wmo_block_number'       => It::t ('attributes', 'wmo_block_number'),
            'wmo_member_state_id'    => It::t ('attributes', 'wmo_member_state_id'),
            'wmo_originating_centre' => It::t ('attributes', 'wmo_originating_centre'),
            'national_aws_number'    => It::t ('attributes', 'national_aws_number'),
            'altitude'               => It::t ('attributes', 'altitude'),
            'awos_msg_source_folder' => It::t ('attributes', 'awos_msg_source_folder'),
            'event_message_period'   => It::t ('attributes', 'event_message_period'),
            'sms_message'            => It::t ('attributes', 'sms_message'),
        );
    }

    public function beforeValidate()
	{
        foreach(array('station_id_code', 'station_type', 'communication_type', 'communication_port', 'communication_esp_ip', 'communication_esp_port', 'display_name', 'details', 'timezone_id', 'station_number', 'wmo_block_number', 'wmo_block_number', 'wmo_member_state_id', 'national_aws_number' , 'altitude', 'awos_msg_source_folder') as $value)
		{
            $this->$value = trim($this->$value);
        }
		
        $this->national_aws_number = $this->national_aws_number ? $this->national_aws_number : '';
        $this->communication_esp_port = $this->communication_esp_port ? $this->communication_esp_port : 0;
        
        return parent::beforeValidate();
    }
    
    public static function getTotal($type = 'rain')
	{
        $sql_where = '';
		
        if ($type == 'rain')
		{
            $sql_where = "WHERE `station_type` = 'rain'";
        } 
		else if ($type == 'aws')
		{
            $sql_where = "WHERE `station_type` IN ('aws','awos')";
        }      
        
		$sql = "SELECT COUNT(*) FROM `".Station::model()->tableName()."` {$sql_where} ";
        
        return Yii::app()->db->createCommand($sql)->queryScalar();
    }
	
    public static function getList($type = '', $key_to_id = true)
	{
        $sql_where = '';
        
		if ($type != '' && $type != 'all')
		{
            $sql_where = "WHERE `station_type` IN (".$type.")";
        }
        
        if ($type == 'rain')
		{
            $sql = "SELECT `st`.*,
                            `sn`.`station_sensor_id`
                    FROM `".Station::model()->tableName()."` `st`
                    LEFT JOIN `".StationSensor::model()->tableName()."`        `sn` ON `sn`.`station_id` = `st`.`station_id`
                    WHERE `st`.`station_type` = 'rain'
                    GROUP BY `st`.`station_id`
                    ORDER BY `st`.`station_id_code`";            
//            $sql = "SELECT `st`.*,
//                            `sn`.`station_sensor_id`,
//                            `t3`.`filter_max`  AS `filter_limit_max`,
//                            `t3`.`filter_min`  AS `filter_limit_min`,
//                            `t3`.`filter_diff` AS `filter_limit_diff`
//                    FROM `".Station::model()->tableName()."` `st`
//                    LEFT JOIN `".StationSensor::model()->tableName()."`        `sn` ON `sn`.`station_id` = `st`.`station_id`
//                    LEFT JOIN `".StationSensorFeature::model()->tableName()."` `t3` ON `t3`.`sensor_id` = `sn`.`station_sensor_id` AND `t3`.`feature_code` = 'rain'
//                    WHERE `st`.`station_type` = 'rain'
//                    GROUP BY `st`.`station_id`
//                    ORDER BY `st`.`station_id_code`";
        }
		else
		{
            $sql = "SELECT * 
                    FROM `".Station::model()->tableName()."`
                    {$sql_where}
                    ORDER BY `station_id_code` ";            
        }

        $res = Yii::app()->db->createCommand($sql)->queryAll();

        $stations = array();
		
        if ($key_to_id)
		{
            if ($res)
			{
                foreach ($res as $value)
				{
                    $stations[$value['station_id']] = $value;
                }
            }
        }
		else
		{
            $stations = $res;
        }

        return $stations;
    }

    /**
     * @param $stations
     * @param $groupStation
     * @param null|CPagination $pages
     * @return array
     */
    public static function stationFromGroup(&$stations, $groupStation, &$pages = null){
        $criteria = new CDbCriteria();
            $criteria->select = 'station_id, display_name, station_id_code, station_type, event_message_period';
            $criteria->compare('station_type', array('aws', 'awos'));
            if(isset($groupStation))
                $criteria->compare('station_id', $groupStation);
            $criteria->order = 'station_id_code asc';
        if(isset($pages)){
            $pages->setItemCount(Station::model()->count($criteria));
            $pages->applyLimit($criteria);
        }
        $stationRecords = Station::model()->findAll($criteria);

        foreach($stationRecords as $station)
            $stations[$station->station_id] = $station;

        return array_keys($stations);
    }

    public static function getStationName(){
        $criteria = new CDbCriteria();
            $criteria->select = "station_id_code, station_id";
            $criteria->index = "station_id";
        return self::model()->findAll($criteria);
    }

    /**
     * @param $station_type array
     *                      Example: ['aws','awos']
     *
     * @return array
     *         [ station_id => 'station_id_code, display_name']
     */
    public static function prepareStationList(array $station_type , $color_flag=true)
    {
        $qb = new CDbCriteria();
        $qb->select = ['station_id', 'station_id_code', 'display_name','color'];
        $qb->addInCondition('station_type',$station_type);
        $qb->order = 'station_id_code';

        if ($color_flag==true) {
            if ($stations = Station::model()->findAll($qb)) {
                return CHtml::listData($stations, 'station_id', function($station){
                    return  $station->station_id_code . ', ' . $station->display_name . '<span style="background-color:'.$station->color.';" class="station-color"> </span>';
                });
            }

        } else {
            if ($stations = Station::model()->findAll($qb)) {
                return CHtml::listData($stations, 'station_id', function($station){
                    return  $station->station_id_code . ', ' . $station->display_name;
                });
            }
        }


        return array();
    }


    public static function getStationByCode($station_id_code, $relations)
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition("station_id_code=:station_id_code");
        $criteria->params = array("station_id_code" => $station_id_code);
        $criteria->with = $relations;
        return Station::model()->find($criteria);

    }
    
    public function getStationsWithSensorsFeatures($stationIds=array())
    {
        $cdbcriteria = new CDbCriteria();
        $cdbcriteria->alias = 'st';
        foreach($stationIds as $stationId) {
            $cdbcriteria->addCondition('st.station_id='.$stationId, 'OR');
        }
        $cdbcriteria->select = ['station_id', 'station_id_code', 'display_name','color'];
        $cdbcriteria->order = 'station_id_code';
        $cdbcriteria->with = array('station_calculation.handler','sensors.handler'=>array('alias'=>'sh'),'sensors.features',);

        return $this->findAll($cdbcriteria);

    }


}