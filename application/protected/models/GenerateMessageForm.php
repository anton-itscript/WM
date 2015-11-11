<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class GenerateMessageForm extends CFormModel {
    
    public $station_id;
    public $date_from;
    public $time_from;
    public $date_to;
    public $time_to;
    public $interval;
    public $sensor_id;
    public $stations;
    public $do_import;
    
    private $all_stations;
    public $start_timestamp;
    public $end_timestamp;
    public $choosed_station;


    public static function model($className=__CLASS__) {
        return parent::model($className);
    }    
    
    public function init() {
        
        $cur_time = strtotime(date('Y-m-d H:i:s'));
        $some_time_ago = $cur_time - 7200;
        
        $this->all_stations = Station::getList('all', false);
       
        $this->date_from = date('m/d/Y', $some_time_ago);
        $this->date_to   = date('m/d/Y', $cur_time);
        $this->time_from = date('H:i', $some_time_ago);
        $this->time_to   =  date('H:i', $cur_time);
        $this->interval = 30;
        if ( $this->all_stations) {
            foreach( $this->all_stations as $key => $value)
                $this->stations[$value['station_id']] = $value['station_id_code'].' '.$value['display_name'];
        }
        $this->do_import  = 0;
        $this->station_id =  $this->all_stations[0]['station_id'];
        return parent::init();
    }
    
    public function rules() {
        return array(
            array('station_id', 'numerical', 'integerOnly' =>true,  'allowEmpty' => false),
            array('station_id', 'checkStation'),
            array('date_from,date_to', 'match', 'pattern' => '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/'),
            array('date_from,date_to', 'length', 'max' => 10),
            array('time_from,time_to', 'match', 'pattern' => '/^(\d{1,2}):(\d{1,2})$/'),
            array('time_from,time_to', 'length', 'max' => 5),
            array('date_to', 'checkDatesInterval'),
            array('interval', 'numerical', 'integerOnly' => true, 'allowEmpty' => false),
            array('interval', 'length', 'max' => 5),
            array('do_import', 'boolean', 'allowEmpty' => false, 'trueValue' => 1, 'falseValue' => 0),
            array('sensor_id', 'checkSensors')
        );
    }
    
    public function checkStation() {
        $this->choosed_station = null;
        if (!$this->hasErrors('station_id')) {
             if ($this->all_stations) {
                 foreach ($this->all_stations as $key => $value) {
                     if ($value['station_id'] == $this->station_id) {
                         $this->choosed_station = $value;
                     }
                 }
             }   
             
             if (!$this->choosed_station) {
                 $this->addError('station_id', 'Unknown station');
             }
        }
    }
    
    public function checkSensors() {        
        if (!$this->hasErrors('station_id')) {
            $this->sensor_id = $this->sensor_id ? $this->sensor_id : array(0);
            $this->sensor_id = array_map('intval', $this->sensor_id);
        }
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

     public function attributeLabels() {
         return array(
             'station_id' => Yii::t('project', 'Station:'),
             'date_from'  => Yii::t('project', 'Start Date:'),
             'date_to'    => Yii::t('project', 'End Date:'),
             'time_from'  => Yii::t('project', 'Time:'),
             'time_to'    => Yii::t('project', 'Time:'),
             'interval'   => Yii::t('project', 'Interval:'),
             'do_import'  => Yii::t('project', 'Import into system immediately: ')
         );
     }
     
     public function afterValidate() {
        
         parent::afterValidate();
     }
}
?>