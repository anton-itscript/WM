<?php

class SensorDataMinute extends CStubActiveRecord {


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
    public function afterSave(){
        if(!$this->getUseLong()){
            SensorDataMinute::processSums($this->measuring_timestamp, $this->sensor_id, $this->station_id, $this->listener_log_id, $this->bucket_size, $this->battery_voltage);
        }
    }

	public function tableName() {
		return 'sensor_data_minute';
	}

    public function processSums($meas_datetime, $sensor_id, $station_id, $listener_log_id, $bucket_size, $battery_voltage)
    {
        //$meas_datetime = $date.' '.$time;
        $meas_timestamp = strtotime($meas_datetime);
        $meas_hour = date('H',$meas_timestamp);
        $meas_min = date('i',$meas_timestamp);
        $meas_month = date('m',$meas_timestamp);
        $meas_day = date('d',$meas_timestamp);
        $meas_year = date('Y',$meas_timestamp);

        $minute = date('i', $meas_timestamp);

        $tmp = array('00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55');
        foreach ($tmp as $key => $value) {

            $tmp_time = $meas_year.'-'.$meas_month.'-'.$meas_day.' '.$meas_hour.':'.$value;

            $sql = "SELECT `sensor_data_id` FROM `".SensorDataMinute::model()->tableName()."` WHERE DATE_FORMAT(`measuring_timestamp`, '%Y-%m-%d %H:%i') = '".$tmp_time."' AND sensor_id = '".$sensor_id."'";
            $res = Yii::app()->db->createCommand($sql)->queryScalar();

            if (!$res) {
                $sql = "INSERT INTO `".SensorDataMinute::model()->tableName()."` (`sensor_id`, `station_id`, `listener_log_id`, `bucket_size`, `measuring_timestamp`, `is_tmp`, `battery_voltage`) VALUES ('".$sensor_id."', '".$station_id."', '".$listener_log_id."', '".$bucket_size."', '".$tmp_time."', '1', '".$battery_voltage."')  ";
                $res = Yii::app()->db->createCommand($sql)->query();
            }
        }

        $tmp_time = $meas_year.'-'.$meas_month.'-'.$meas_day.' 00:00';
        $sql = "SELECT `sensor_data_id` FROM `".SensorDataMinute::model()->tableName()."` WHERE DATE_FORMAT(`measuring_timestamp`, '%Y-%m-%d %H:%i') = '".$tmp_time."' AND sensor_id = '".$sensor_id."'";
        $res = Yii::app()->db->createCommand($sql)->queryScalar();
                if (!$res) {
                    $sql = "INSERT INTO `".SensorDataMinute::model()->tableName()."` (`sensor_id`, `station_id`, `listener_log_id`, `bucket_size`, `measuring_timestamp`, `is_tmp`, `battery_voltage`) VALUES ('".$sensor_id."', '".$station_id."', '".$listener_log_id."', '".$bucket_size."', '".$tmp_time."', '1', '".$battery_voltage."')  ";
                    $res = Yii::app()->db->createCommand($sql)->query();
                }

        $tmp_time = date('Y-m-d H:i', mktime(0,0,0,$meas_month,$meas_day+1, $meas_year));
        $sql = "SELECT `sensor_data_id` FROM `".SensorDataMinute::model()->tableName()."` WHERE DATE_FORMAT(`measuring_timestamp`, '%Y-%m-%d %H:%i') = '".$tmp_time."' AND sensor_id = '".$sensor_id."'";
        $res = Yii::app()->db->createCommand($sql)->queryScalar();
                if (!$res) {
                    $sql = "INSERT INTO `".SensorDataMinute::model()->tableName()."` (`sensor_id`, `station_id`, `listener_log_id`, `bucket_size`, `measuring_timestamp`, `is_tmp`, `battery_voltage`) VALUES ('".$sensor_id."', '".$station_id."', '".$listener_log_id."', '".$bucket_size."', '".$tmp_time."', '1', '".$battery_voltage."')  ";
                    $res = Yii::app()->db->createCommand($sql)->query();
                }

        $to_update = array();

        //-----------------------------------------------------
        $start = floor($minute / 5)*5;
        $shift = ($start == $minute) ? 5 : 0;

        $to_update['5min_sum'] = array(
            date('Y-m-d H:i:s', mktime($meas_hour, $start - $shift + 1, 0, $meas_month, $meas_day, $meas_year)),
            date('Y-m-d H:i:s', mktime($meas_hour, $start - $shift + 5, 0, $meas_month, $meas_day, $meas_year))
        );

        //-----------------------------------------------------
        $start = floor($minute / 10)*10;
        $shift = ($start == $minute) ? 10 : 0;

        $to_update['10min_sum'] = array(
            date('Y-m-d H:i:s', mktime($meas_hour, $start - $shift + 1, 0, $meas_month, $meas_day, $meas_year)),
            date('Y-m-d H:i:s', mktime($meas_hour, $start - $shift + 10, 0, $meas_month, $meas_day, $meas_year))
        );

        //-----------------------------------------------------
        $start = floor($minute / 20)*20;
        $shift = ($start == $minute) ? 20 : 0;

        $to_update['20min_sum'] = array(
            date('Y-m-d H:i:s', mktime($meas_hour, $start - $shift + 1, 0, $meas_month, $meas_day, $meas_year)),
            date('Y-m-d H:i:s', mktime($meas_hour, $start - $shift + 20, 0, $meas_month, $meas_day, $meas_year))
        );
        //-----------------------------------------------------
        $start = floor($minute / 30)*30;
        $shift = ($start == $minute) ? 30 : 0;

        $to_update['30min_sum'] = array(
            date('Y-m-d H:i:s', mktime($meas_hour, $start - $shift + 1, 0, $meas_month, $meas_day, $meas_year)),
            date('Y-m-d H:i:s', mktime($meas_hour, $start - $shift + 30, 0, $meas_month, $meas_day, $meas_year))
        );
        //-----------------------------------------------------
        $start = floor($minute / 60)*60;
        $shift = ($start == $minute) ? 60 : 0;

        $to_update['60min_sum'] = array(
            date('Y-m-d H:i:s', mktime($meas_hour, $start - $shift + 1, 0, $meas_month, $meas_day, $meas_year)),
            date('Y-m-d H:i:s', mktime($meas_hour, $start - $shift + 60, 0, $meas_month, $meas_day, $meas_year))
        );

        $to_update['1day_sum'] = array(
            date('Y-m-d H:i:s', mktime(0, 0, 1, $meas_month, $meas_day, $meas_year)),
            date('Y-m-d H:i:s', mktime(0, 0, 0, $meas_month, $meas_day+1, $meas_year))
        );


        //-----------------------------------------------------

        foreach ($to_update as $field_name => $rangs) {
            $sql = "UPDATE `".SensorDataMinute::model()->tableName()."` `t1`  SET `t1`.`".$field_name."` = (
                                            SELECT SUM(`t2`.`sensor_value`)
                                            FROM (
                                                    SELECT *
                                                    FROM `".SensorDataMinute::model()->tableName()."` `t3`
                                                    WHERE `t3`.`sensor_id` = '".$sensor_id."' AND `t3`.`measuring_timestamp` >= '".$rangs[0]."' AND `t3`.`measuring_timestamp` <= '".$rangs[1]."'
                                                 ) `t2`
                                            WHERE `t2`.`measuring_timestamp` <= `t1`.`measuring_timestamp`
                                        )
                    WHERE t1.sensor_id = '".$sensor_id."' AND `t1`.`measuring_timestamp` >= '".$meas_datetime."' AND `t1`.`measuring_timestamp` <= '".$rangs[1]."' ";
            Yii::app()->db->createCommand($sql)->query();
        }
    }

}