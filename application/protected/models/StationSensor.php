<?php

/**
 * @property  array|StationSensorFeature[] $ConstantFeature
 */
class StationSensor extends CStubActiveRecord
{
    public static function model($className=__CLASS__){
        return parent::model($className);
    }
    public function beforeSave(){
        if(!$this->getUseLong()){
            if ($this->isNewRecord){
                $this->created = new CDbExpression('NOW()');
            }
            $this->updated = new CDbExpression('NOW()');
        }
        return parent::beforeSave();
    }
    public function beforeDelete(){
        $features = StationSensorFeature::model()->selectDb($this->getUseLong())->
            findAll('sensor_id = :sensor_id',array(':sensor_id'=>$this->station_sensor_id));
        foreach($features as $feature){
            SensorData::model()->selectDb($this->getUseLong())->
                deleteAll('sensor_feature_id = :feature_id',array(':feature_id'=>$feature->sensor_feature_id));
        }
        return parent::beforeDelete();
    }
	public function tableName()
	{
		return 'station_sensor';
	}

    public function rules()
	{
        return array(
            array('station_id,sensor_id_code', 'required'),
            array('sensor_id_code', 'length', 'max' => 3, 'is' => 3, 'allowEmpty' => false),
            array('sensor_id_code', 'checkSensorIdCode'),
            array('display_name', 'required'),
            array('display_name', 'length', 'allowEmpty' => true),
            array('handler_id', 'numerical', 'allowEmpty' => false, 'integerOnly' => true),
        );
    }
	
	public function relations()
    {
        return array(
            'station' => array(self::BELONGS_TO, 'Station', 'station_id'),
			'handler' => array(self::BELONGS_TO, 'SensorDBHandler', 'handler_id'),
			'first_sensor_feature' => array(self::HAS_ONE, 'StationSensorFeature', 'sensor_id', 'on' => 'first_sensor_feature.feature_code = "rain"'),
			'main_feature' => array(self::HAS_ONE, 'StationSensorFeature', 'sensor_id', 'on' => 'main_feature.is_main = 1'),
			'ConstantFeature' => array(self::HAS_MANY, 'StationSensorFeature', 'sensor_id', 'on' => 'ConstantFeature.is_constant = 1'),
			'features' => array(self::HAS_MANY, 'StationSensorFeature', 'sensor_id'),
        );
    }

    public function checkSensorIdCode()
	{
        if ($this->sensor_id_code != '')
		{
            if (preg_match('/^([A-Z,a-z]{2})([1-9]{1})$/', $this->sensor_id_code, $matches))
			{
                if (!$this->isNewRecord)
                    $res = StationSensor::model()->count('station_id = :station_id AND station_sensor_id <> :sensor_id AND sensor_id_code LIKE :sensor_id_code', array(':station_id' => $this->station_id, ':sensor_id' => $this->station_sensor_id, ':sensor_id_code' => $this->sensor_id_code));
                else
                    $res = StationSensor::model()->count('station_id = :station_id AND sensor_id_code LIKE :sensor_id_code', array(':station_id' => $this->station_id, ':sensor_id_code' => $this->sensor_id_code));
                if ($res > 0) {
                    $this->addError('sensor_id_code', Yii::t('project', 'This Station already has Sensor with ID = "'.$this->sensor_id_code.'"'));                    
                }
            } 
			else
			{
                $this->addError('sensor_id_code', Yii::t('project', 'Sensor ID should contain two letters and 1 digit. Ex.: TP1'));                    
            }
        }
		
        return true;
    }

    public function attributeLabels() {
        return array (
            'sensor_id_code' => Yii::t('project', 'Sensor ID (in msg)'),
            'display_name' => Yii::t('project', 'Display Name'),
            'handler_id'   => Yii::t('project', 'Handler'),
        );
    }

    public static function getInfoForHandler($sensor_id_code, $station_id)
	{
		$criteria = new CDbCriteria();
		$criteria->with = array('handler');
		
		$criteria->compare('station_id', $station_id);
		$criteria->compare('sensor_id_code', $sensor_id_code);
		
		return StationSensor::model()->find($criteria);
    }


    public static function getSensorsForAWSDisplay($station_ids, $for = 'aws_panel'){
		$criteria = new CDbCriteria();
		$criteria->with = array(
            'handler'
        );
		
		$criteria->compare('t.station_id', $station_ids);
		
		$criteria->order = ($for === 'aws_panel') 
								? "handler.aws_panel_display_position asc, t.sensor_id_code asc"
								: "handler.aws_single_display_position asc, SUBSTRING(t.sensor_id_code, 3) asc, t.sensor_id_code asc";
        
		$sensors = StationSensor::model()->findAll($criteria);
		
        $result = array();
        
		foreach ($sensors as $sensor){
			$result[$sensor->station_id][] = $sensor;
		}
		
        return $result;
    }
    public static function getList($station_id)
	{
        $sql = "SELECT * 
                FROM `".StationSensor::model()->tableName()."`
                WHERE `station_id` = ?
                ORDER BY `sensor_id_code`";
        
		return $res = Yii::app()->db->createCommand($sql)->queryAll(true, array($station_id));        
    }

	public function hasCalculation($calculationCode)
	{
		if (is_null($this->main_feature))
		{
			return false;
		}
		
		$calculationCode = strtolower($calculationCode);
		
		foreach ($this->main_feature->calculations as $calculation)
		{
			if (strtolower($calculation->calculation->handler->handler_id_code) === $calculationCode)
			{
				return true;
			}
		}
		
		return false;
	}
    //for aws panel
    public static function addSensorsForStations(&$stations){
        $station_ids = array_keys($stations);
        $station_sensor_ids = array();

        $criteria = new CDbCriteria();
            $criteria->with = array('handler');
            $criteria->compare('t.station_id', $station_ids);
            $criteria->order = "handler.aws_panel_display_position asc, t.sensor_id_code asc";

        $sensors = StationSensor::model()->findAll($criteria);

        foreach ($sensors as $sensor){
            $stations[$sensor->station_id]['sensors'][$sensor->station_sensor_id] = $sensor;
            $station_sensor_ids[]=$sensor->station_sensor_id;
        }

        return $station_sensor_ids;
    }

}