<?php

class SensorData extends CStubActiveRecord{

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

    public function tableName(){
        return 'sensor_data';
    }
	public function relations(){
        return array(
            'sensor_feature' => array(self::BELONGS_TO, 'StationSensorFeature', 'sensor_feature_id', 'order' => 'sensor_feature.feature_code asc'),
            'Sensor' => array(self::BELONGS_TO, 'StationSensor', 'sensor_id'),
            'feature' => array(self::BELONGS_TO, 'StationSensorFeature', 'sensor_feature_id')
        );
    }

    public static function getSensorData($last4Messages, $sensorList){
		$sensorFeatureIds = array();
		foreach ($sensorList as $sensorFeatureRecord){
            foreach ($sensorFeatureRecord as $sensorFeature){
                $sensorFeatureIds[] = $sensorFeature->sensor_feature_id;
            }
        }
        $last4MessageIds = array();
		foreach ($last4Messages as $stationMessages){
            foreach ($stationMessages as $message){
                $last4MessageIds[] = $message->log_id;
            }
        }
        $criteria = new CDbCriteria();
            $criteria->compare('t.listener_log_id', $last4MessageIds);
            $criteria->compare('t.sensor_feature_id', $sensorFeatureIds);
            $criteria->order = 't.measuring_timestamp desc';
            $criteria->with = array(
                'sensor_feature' => array(
                    'select' => 'feature_code'
                )
            );

		$sensorData = SensorData::model()->findAll($criteria);

        $result = array();
		foreach ($sensorData as $sensorDataRecord){
			$result
                [$sensorDataRecord->sensor_feature->feature_code]
                [$sensorDataRecord->station_id]
                [$sensorDataRecord->sensor_id]
                [] = $sensorDataRecord;
		}
		return $result;
	}
    public static function addSensorsData(&$sensorData,$logsId,$handlers){
        /*
        structure sensorData array:
        $sensorData =
        ['handlers']
            [handler_id]
                ['code']
                    [sensor_id_code]
                        ['stations']
                            [station_id]
                                ['view']
                                ['features']
                                    [feature_code]
                                        ['info'] = $feature - AR
                                        ['data']
                                            [sensor_data_id] = $data - AR


    */
        $featureAllow = array();
        foreach($handlers as $handler){
            foreach($handler->features as $feature){
                $featureAllow[]=$handler->handler_id.$feature->feature_code;
            }
        }
        $criteria = new CDbCriteria();
            $criteria->with = array(
                'feature.sensor',
                'feature.metric'
            );
            $criteria->compare('t.listener_log_id', $logsId);
            $criteria->compare('CONCAT(sensor.handler_id,feature.feature_code)', $featureAllow);
            $criteria->order = "sensor.sensor_id_code asc, t.measuring_timestamp desc";

        $result = SensorData::model()->findAll($criteria);

        foreach ($result as $data){
            $arr = &$sensorData['handlers'][$data->feature->sensor->handler_id]['code'];

            $count = $handlers[$data->feature->sensor->handler_id]->aws_panel_show - count($arr);

            if($count>0 OR array_key_exists($data->feature->sensor->sensor_id_code,$arr)){
                $arr
                    [$data->feature->sensor->sensor_id_code]
                    ['stations']
                    [$data->feature->sensor->station_id]
                    ['features']
                    [$data->feature->feature_code]
                    ['info'] = $data->feature;

                $arr
                    [$data->feature->sensor->sensor_id_code]
                    ['stations']
                    [$data->feature->sensor->station_id]
                    ['features']
                    [$data->feature->feature_code]
                    ['data']
                    [$data->sensor_data_id]= $data;
            }
        }
    }
}