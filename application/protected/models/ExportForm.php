<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ExportForm extends CFormModel {
    
    public $date_from;
    public $time_from;
    public $date_to;
    public $time_to;
    public $stations;
    public $station_id;
    
    public $start_timestamp;
    public $end_timestamp;
    
    public $all_stations;



    public static function model($className=__CLASS__) {
        return parent::model($className);
    }    
    
    public function init() {
        
        $cur_time = time();
        $some_time_ago = $cur_time - 86400;
        
        $sql = "SELECT `station_id`, CONCAT(`station_id_code`, ' - ', `display_name`) AS `name`
                FROM `".Station::model()->tableName()."`
                ORDER BY `station_id_code` ";
        $stations = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryAll();
        if ($stations) {
            $this->all_stations = CHtml::listData($stations, 'station_id', 'name');
        }        

        $this->date_from = date('m/d/Y', $some_time_ago);
        $this->date_to   = date('m/d/Y', $cur_time);
        $this->time_from = date('H:i', $some_time_ago);
        $this->time_to   = date('H:i', $cur_time);        

        return parent::init();
    }
    
    public function rules() {
        return array(

            array('date_from,date_to', 'match', 'pattern' => '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/'),
            array('date_from,date_to', 'length', 'max' => 10),
            array('time_from,time_to', 'match', 'pattern' => '/^(\d{1,2}):(\d{1,2})$/'),
            array('time_from,time_to', 'length', 'max' => 5),
            array('date_to', 'checkDatesInterval'),
            array('station_id', 'checkStationId'),
        );
    }
    
    public function checkDatesInterval() {
        if (!$this->hasErrors('date_from') && !$this->hasErrors('date_to') && !$this->hasErrors('time_from') && !$this->hasErrors('time_to')) {
            $this->start_timestamp = strtotime($this->date_from.' '.$this->time_from);
            $this->end_timestamp = strtotime($this->date_to.' '.$this->time_to);           
            if ($this->end_timestamp <= $this->start_timestamp) {
                $this->addError('date_to', 'End date and time must be later than start.');
            }
        }
    }

    public function checkStationId() {
        
        $stations = $this->stations;
        
        if ($stations && $this->station_id && is_array($this->station_id)) {
            foreach ($this->station_id as $key => $value) {
                if (!in_array($value, array_keys($stations))) {
                    unset($this->station_id[$key]);
                }
            }
        }
        
        if (!$this->station_id || !is_array($this->station_id)) {
            $this->addError('station_id', 'Choose at least one station.');
            return false;
        }      
        
        return true;
    }
    public function attributeLabels() {
         return array(
             'date_from'   => It::t('site_label', 'filter_date_from'),
             'date_to'     => It::t('site_label', 'filter_date_to'),
             'time_from'   => It::t('site_label', 'filter_time_from'),
             'time_to'     => It::t('site_label', 'filter_time_to'),
             'station_id'  => It::t('site_label', 'filter_select_stations'),
         );
     }
     
     public function createExport() {
         
         $return_string = "";
         $sql = "SELECT
                       `t1`.`measuring_timestamp` AS `TxDateTime`,
                       `t4`.`station_id_code` AS `StationId`, 
                       `t4`.`display_name` AS `StationDisplayName`,
                       `t3`.`sensor_id_code` AS `SensorId`, 
                       `t3`.`display_name` AS `SensorDisplayName`, 
                       
                       `t1`.`period` AS `MeasurementPeriod`,
                       `t1`.`sensor_feature_value` AS `Value`,
                       `t5`.`short_name` AS `Metric`,
                       `t8`.`value` AS `DewPoint`,
                       `t11`.`value` AS `PressureMSL`,
                       
                       `t12`.`handler_id_code`,
                       `t4`.`magnetic_north_offset`

                 FROM `".SensorData::model()->tableName()."` t1
                 LEFT JOIN `".StationSensorFeature::model()->tableName()."`          `t2`    ON `t2`.`sensor_feature_id` = `t1`.`sensor_feature_id`
                 LEFT JOIN `".StationSensor::model()->tableName()."`                 `t3`    ON `t3`.`station_sensor_id` = `t2`.`sensor_id`
                 LEFT JOIN `".Station::model()->tableName()."`                       `t4`    ON `t4`.`station_id` = `t1`.`station_id`
                 LEFT JOIN `".RefbookMetric::model()->tableName()."`                 `t5`    ON `t5`.`metric_id` = `t2`.`metric_id`

                 LEFT JOIN `".StationCalculation::model()->tableName()."`            `t6`    ON (`t6`.`station_id` = `t1`.`station_id` AND `t6`.`handler_id` = 1)
                 LEFT JOIN `".StationCalculationVariable::model()->tableName()."`    `t7`    ON (`t7`.`sensor_feature_id` = `t2`.`sensor_feature_id` AND `t7`.`calculation_id` = `t6`.`calculation_id`)
                 LEFT JOIN `".StationCalculationData::model()->tableName()."`        `t8`    ON (`t8`.`calculation_id` = `t7`.`calculation_id` AND `t8`.`listener_log_id` = `t1`.`listener_log_id`)

                 LEFT JOIN `".StationCalculation::model()->tableName()."`            `t9`    ON (`t9`.`station_id` = `t1`.`station_id` AND `t9`.`handler_id` = 2)
                 LEFT JOIN `".StationCalculationVariable::model()->tableName()."`    `t10`   ON (`t10`.`sensor_feature_id` = `t2`.`sensor_feature_id` AND `t10`.`calculation_id` = `t9`.`calculation_id`)
                 LEFT JOIN `".StationCalculationData::model()->tableName()."`        `t11`   ON (`t11`.`calculation_id` = `t10`.`calculation_id` AND `t11`.`listener_log_id` = `t1`.`listener_log_id`)

                 LEFT JOIN `".SensorDBHandler::model()->tableName()."`               `t12`   ON `t12`.`handler_id` = `t3`.`handler_id`

                 WHERE `t1`.`station_id` IN (".implode(',', $this->station_id).")
                   AND `t1`.`measuring_timestamp` >= FROM_UNIXTIME(".$this->start_timestamp.") 
                   AND `t1`.`measuring_timestamp` <= FROM_UNIXTIME(".$this->end_timestamp.")
                   AND `t2`.`is_main` = 1
                   AND `t1`.`is_m` = '0'
                 ORDER BY `t1`.`measuring_timestamp` DESC, `t4`.`station_id_code`, `t3`.`sensor_id_code`";
         $res = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryAll();
         
        if ($res) {

            foreach ($res as $key => $value) {

                $handler_obj = SensorHandler::create($value['handler_id_code']);

                $res[$key]['Value'] = $handler_obj->applyOffset($res[$key]['Value'], $res[$key]['magnetic_north_offset']);
                $res[$key]['Value'] = $handler_obj->formatValue($res[$key]['Value'], $res[$key]['feature_code']);
                
                unset($res[$key]['magnetic_north_offset']);
                unset($value['handler_id_code']);
                unset($res[$key]['feature_code']);
            }

            $return_string .= "\"AWS Stations:\"\n".It::prepareStringCSV($res);
        }
         
         $sql = "SELECT
                        `t1`.`measuring_timestamp` AS `TxDateTime`,
                        `t3`.`station_id_code` AS `StationId`, 
                        `t3`.`display_name` AS `StationDisplayName`,
                        `t2`.`sensor_id_code` AS `SensorId`, 
                        `t2`.`display_name` AS `SensorDisplayName`,  
                        (`t1`.`sensor_value` * `t1`.`bucket_size`) AS `Value`,
                        `t4`.`short_name` AS `Metric`                        
                 FROM `".SensorDataMinute::model()->tableName()."` t1
                 LEFT JOIN `".StationSensor::model()->tableName()."` t2 ON t2.station_sensor_id = t1.sensor_id
                 LEFT JOIN `".Station::model()->tableName()."` t3 ON t3.station_id = t1.station_id
                 LEFT JOIN `".RefbookMetric::model()->tableName()."` t4 ON t4.metric_id = t1.metric_id
                     
                 WHERE `t1`.`station_id` IN (".implode(',', $this->station_id).")
                   AND `t1`.`measuring_timestamp` >= FROM_UNIXTIME(".$this->start_timestamp.") 
                   AND `t1`.`measuring_timestamp` <= FROM_UNIXTIME(".$this->end_timestamp.")
                   AND `t1`.`is_tmp` = 0
                 ORDER BY t1.measuring_timestamp DESC, t3.station_id_code, t2.sensor_id_code
                ";
        $res = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryAll();
        if ($res) {
            $return_string .= "\n\n\"RG Stations:\"\n".It::prepareStringCSV($res);
        }

        It::downloadFile($return_string, 'export__'.date('Y-m-d_Hi').'.csv', 'text/csv');
    }
     
     public function afterValidate() {
        
         parent::afterValidate();
     }
}
?>