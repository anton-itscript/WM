<?php

/**
 * Flags bit field:
 * 1 - aws_station_uses - applicable for AWS stations
 * 2 - rain_station_uses - applicable for Rain stations
 * 3 - awa_station_uses - applicable for AWOS (?) stations
 * 4 - DLM11 logger type - applicable for DLM11 logger type
 * 5 - DLM13M logger type - applicable for DLM13M logger type
 */
class SensorDBHandler extends CStubActiveRecord{

    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName(){
        return 'sensor_handler';
    }
    public function relations(){
        return array(
            'features' => array(self::HAS_MANY, 'SensorDBHandlerDefaultFeature', 'handler_id',
                'index' => 'feature_code'
            ),
            'sensors' => array(self::HAS_MANY, 'StationSensor', 'handler_id')
        );
    }

    public function rules()
    {
        return [
            ['handler_default_display_name, start_time', 'required']
        ];
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

    /**
     * @param $station_type
     *
     * @return array|SensorDBHandler[]
     */
    public static function getHandlers($station_type)
	{
		$criteria = new CDbCriteria();
		
		switch (strtolower($station_type))
		{
			case 'rain':
				
				$criteria->addCondition('flags & 2 = 2');
				
				break;

			case 'aws':
				
				// AWS + DLM11 (1+8) or AWS + DLM13M (1+16)
				$criteria->addCondition('flags & 9 = 9 OR flags & 17 = 17');
				
				break;
			
			// Default AWS DLM11
			default:

				// 8 + 1: AWS + DLM11
				$criteria->addCondition('flags & 9 = 9');
				
				break;
		}

        $criteria->alias = 'h';
        $criteria->order = 'h.display_name asc';
        $criteria->with = array('sensors.station');
		
		return SensorDBHandler::model()->findAll($criteria);
    }

    public static function checkHandlersFor24h($handler_id){
//        return true;
        $arr_id_for_24h = array(
            'TP',
            'RN',
            'HU',
            'WD',
            'WS',
            'PR',
            'SN',
            //
            'SD',
            'RN',
            'SR',
        );
        return in_array($handler_id,$arr_id_for_24h);
    }

    public static function handlerWithFeature(&$handlers,$for){
        $criteria = new CDbCriteria();
        $criteria->with = array(
            'features'
        );
        $criteria->index = 'handler_id';

        switch($for){
            case 'aws_panel':
                $criteria->compare('features.aws_panel_show', 1);
                $criteria->compare('t.aws_station_uses', 1);
                $criteria->addCondition('t.aws_panel_show > 0');
                $criteria->order = "t.aws_panel_display_position asc";
                break;
            case 'aws_table':
            case 'aws_graph':
                $criteria->addCondition('flags & 9 = 9 OR flags & 17 = 17');
                $criteria->order = 'display_name asc';
                break;
            case 'rg':
                $criteria->compare('t.rain_station_uses',1);
                break;
        }
        $handlers = SensorDBHandler::model()->findAll($criteria);

        return array_keys($handlers);
    }
}

?>