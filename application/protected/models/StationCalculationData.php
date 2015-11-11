<?php

class StationCalculationData extends CStubActiveRecord {



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

    public function tableName() {
            return 'station_calculation_data';
    }
    public function relations(){
        return array(
            'calculation' => array(self::BELONGS_TO, 'StationCalculation', 'calculation_id'),
            'ListenerLog' => array(self::BELONGS_TO, 'ListenerLog', 'listener_log_id'),
        );
    }

    public static function saveCaclulation($calculation_id, $listener_log_id, $value) {
        $data = StationCalculationData::model()->find('calculation_id = :calculation_id AND listener_log_id = :listener_log_id', array(':calculation_id' => $calculation_id, ':listener_log_id' => $listener_log_id));
        if ($value === FALSE) {
            if ($data) {
                $data->delete();
            }
            return false;
        }
        if (!$data) {
            $data = new StationCalculationData;
            $data->calculation_id = $calculation_id;
            $data->listener_log_id = $listener_log_id;
        }
        $data->value = $value;
        $data->save();
        return true;
    }

    public static function addCalculationData(&$sensorData,$logsId,$handlersId){
        /*
        structure sensorData array:
        $sensorData =
            ['handlersCalc']
                [handler_id]
                    ['stations']
                        [station_id]
                            ['data']
                                [calculation_data_id] = $data - AR
                            ['view']
    */
        $criteria = new CDbCriteria();
            $criteria->with = array('calculation');
            $criteria->compare('t.listener_log_id', $logsId);
            $criteria->compare('calculation.handler_id', $handlersId);
            $criteria->order = "t.created desc";

        $result = self::model()->findAll($criteria);

        foreach ($result as $data){
            //calculations data
            $sensorData
            ['handlersCalc']
            [$data->calculation->handler_id]
            ['stations']
            [$data->calculation->station_id]
            ['data']
            [$data->calculation_data_id] = $data;

        }
    }
}