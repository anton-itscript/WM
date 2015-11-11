<?php

class ProcessLogLine {

    
    private $_station;
    private $_sensor;
    
    var $message_obj      = null;
    var $integrity_errors = array();
    var $data_errors      = array();

    function __construct($message_obj)            
    {
        $this->message_obj  = $message_obj;
    }

    function run()
    {
        
        $this->checkIntegrity();

        $this->saveMessage();

        if ($this->integrity_errors) {
            return true;
        }

        if ($this->_station->timezone_id) {
            TimezoneWork::set($this->_station->timezone_id);
        }

        if ($this->_station->station_type == 'rain') {
            $this->logRainMessage();
        } else {
            $this->logAWSMessage();
        }

    }

    function getStation()
    {
        $station = Station::model()->findByPk($this->message_obj->station_id);
        if ($station) {
            $this->_station = $station;
        }
    }
    
    private function checkIntegrity()
    {
        $this->getStation();
        if (!$this->_station) {
            $this->integrity_errors[] = 'unknown_station_id__'.$this->message_obj->station_id;
            return false;
        }

        // Temporary stub
        if ($this->_station->station_type != 'rain') {
            $this->integrity_errors[] = 'import_works_only_for_rain';
            return false;
        }

        if ($this->_station->station_type == 'rain') {
            $parts = str_getcsv($this->message_obj->message);
            if (count($parts) != 4) {
                $this->integrity_errors[] = 'incorrect_data_format';
                return false;
            }
            $date_pattern = "/^(\d{1,2})\/(\d{1,2})\/(\d{1,2})$/";
            $time_pattern = "/^(\d{1,2}):(\d{1,2})$/";
            $voltage_pattern = "/^(\d{3})$/";
            if (!preg_match($date_pattern, $parts[0]) || !preg_match($time_pattern, $parts[1]) || !preg_match($voltage_pattern, $parts[2])) {
                $this->integrity_errors[] = 'incorrect_data_format';
                return false;
            }

            $sensor = StationSensor::model()->find('station_id = :station_id', array(':station_id' => $this->message_obj->station_id));
            if (!$sensor) {
                $this->integrity_errors[] = 'station_dont_have_sensors';
            } else {
                $this->_sensor = $sensor;
                return true;
            }
        } else {
            // TO DO
        }
    }

    private function saveMessage()
    {
        if ($this->_station->station_type) {
            $this->message_obj->station_type = $this->_station->station_type;
        }
        
        if ($this->integrity_errors) {
            $this->message_obj->failed = 1;
            $this->message_obj->fail_description = implode(',',$this->integrity_errors);
        }
        $this->message_obj->save();

    }

    private function logRainMessage()
    {
        $parts = str_getcsv($this->message_obj->message);
        $date = explode('/', $parts[0]);
        $time = explode(':', $parts[1]);
        $measuring_timestamp = mktime($time[0], $time[1], 0, $date[1], $date[2], '20'.$date[0]);

        $criteria = new CDbCriteria();
        $criteria->condition = "DATE_FORMAT(measuring_timestamp, '%Y-%m-%d %H:%i:%s') = :measuring_timestamp AND sensor_id = :sensor_id";
        $criteria->params = array(':measuring_timestamp' => date('Y-m-d H:i:s', $measuring_timestamp), ':sensor_id' => $this->_sensor->station_sensor_id);
        $sensor_data = SensorDataMinute::model()->find($criteria);

        if (!$sensor_data || $sensor_data->is_tmp || $this->message_obj->rewrite_prev_values) {

            if (!$sensor_data ) {
                $sensor_data = new SensorDataMinute();
                $sensor_data->sensor_id = $this->_sensor->station_sensor_id;
                $sensor_data->station_id = $this->message_obj->station_id;
            }

            $sensor_data->sensor_value = $parts[3];
            $sensor_data->bucket_size  = $this->_sensor->bucket_size;
            $sensor_data->listener_log_id = $this->message_obj->log_id;
            $sensor_data->measuring_timestamp = date('Y-m-d H:i:s', $measuring_timestamp);
            $sensor_data->battery_voltage = $parts[2];
            $sensor_data->is_tmp = 0;
            $sensor_data->save();
        }
    }

    private function logAWSMessage()
    {
        // TO DO
    }

    function __destruct()
    {
        if (get_class(Yii::app()) != 'CConsoleApplication') {
            $tz = Yii::app()->user->getTZ();

            if ($tz != date_default_timezone_get()) {
                TimezoneWork::set($tz);
            }
        }
    }
}

?>