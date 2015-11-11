<?php

class SensorDataPeriod extends CStubActiveRecord {

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'sensor_data_period';
	}


    public function beforeSave() {
        if ($this->isNewRecord) {
            $this->created = new CDbExpression('NOW()');
        }
        $this->updated = new CDbExpression('NOW()');

        return parent::beforeSave();
    }

    public function afterSave()
    {
        $hour = substr($this->tx_time,0,2);
        $sql = "SELECT `sensor_data_id`, `sensor_id`, `tx_date`, `tx_time`
                FROM `".SensorDataPeriod::model()->tableName()."`
                WHERE `sensor_id` = '".$this->sensor_id."' AND `tx_date` = '".$this->tx_date."' ";
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        if ($res) {
            foreach ($res as $key => $value) {
                $update = array();

                $update['day_value_mm'] = SensorDataPeriod::getDaySum($value['sensor_id'], $value['tx_date'], $value['tx_time']);

                $value_hour = substr($value['tx_time'],0,2);
                if ($hour == $value_hour) {
                    $update['hour_value_mm'] = SensorDataPeriod::getHourSum($value['sensor_id'], $value['tx_date'], $value['tx_time']);
                }

                SensorDataPeriod::model()->updateByPk($value['sensor_data_id'], $update);
            }
        }
    }


    public function getHourSum($sensor_id, $tx_date, $tx_time)
    {
        $sql = "SELECT SUM(`sensor_value` * `bucket_size`)
                FROM `".SensorDataPeriod::model()->tableName()."`
                WHERE `sensor_id` = '".$sensor_id."' AND `tx_date` = '".$tx_date."' AND `tx_time` <= '".$tx_time."'  AND `tx_time` LIKE '".substr($tx_time,0,2).":%'";
        return Yii::app()->db->createCommand($sql)->queryScalar();
    }

    public function getDaySum($sensor_id, $tx_date, $tx_time)
    {
        $sql = "SELECT SUM(`sensor_value` * `bucket_size`)
                FROM `".SensorDataPeriod::model()->tableName()."`
                WHERE `sensor_id` = '".$sensor_id."' AND `tx_date` = '".$tx_date."' AND `tx_time` <= '".$tx_time."'";
        return Yii::app()->db->createCommand($sql)->queryScalar();
    }


}