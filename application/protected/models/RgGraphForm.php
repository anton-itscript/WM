<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class RgGraphForm extends CFormModel {
    
    public $date_from;
    public $date_to;
    public $time_from;
    public $time_to;
    public $station_id;
    
    private $stations;
    private $session_name = 'rg_graph4';
    
    private $start_timestamp;
    private $end_timestamp;

    public static function model($className=__CLASS__) {
        return parent::model($className);
    }    
    
    public function init() {
    
        $sql = "SELECT `station_id`, CONCAT(`station_id_code`, ' - ', `display_name`) AS `name`
                FROM `".Station::model()->tableName()."`
                WHERE `station_type` = 'rain' ORDER BY `station_id_code` ";
        $stations = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryAll();
        if ($stations) {
            $this->stations = CHtml::listData($stations, 'station_id', 'name');
        }
        
        $this->getFromMemory();
        
        return parent::init();
    }
    
    //===== validation
    public function rules() {
        return array(
            array('station_id', 'numerical', 'integerOnly' => true,  'allowEmpty' => true),
            array('date_from,date_to', 'match', 'pattern' => '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/'),
            array('date_from,date_to', 'length', 'max' => 10),
            array('date_to', 'checkDatesInterval'),
            array('time_from,time_to', 'match', 'pattern' => '/^(\d{1,2}):(\d{1,2})$/'),
            array('time_from,time_to', 'length', 'max' => 5),  
            array('date_from,date_to,time_from,time_to', 'required'),
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

    //======
    
    //==== work with memory
    public function putToMemory() {
        
        $session = new CHttpSession();
        $session->open();
         
        $first_date = strtotime($this->date_from.' '.$this->time_from);
        $last_date  = strtotime($this->date_to.' '.$this->time_to);

        if ($last_date <= $first_date) {
            $date_to = $first_date + 3600;
            $this->date_to = date('m/d/Y',$date_to);
            $this->time_to = date('H:i',$date_to);
        }        
        
        $session[$this->session_name] = array(
            'station_id'      => $this->station_id,
            'date_from'       => $this->date_from,
            'date_to'         => $this->date_to,
            'time_from'       => $this->time_from,
            'time_to'         => $this->time_to,        
        );
    }
    
    public function clearMemory() {
        
        $session = new CHttpSession();
        $session->open();
        $session[$this->session_name] = array();
        $this->getFromMemory();
    }
    
    public function getFromMemory() {
       
        $session = new CHttpSession();
        
        $session->open();
        
        $cur_time = time();
        $some_time_ago = mktime(0, 0, 0, date('m',$cur_time), date('d',$cur_time)-1, date('Y',$cur_time));        
        
        $this->station_id      = $session[$this->session_name] ? $session[$this->session_name]['station_id']  : 0;
        $this->date_from       = $session[$this->session_name] ? $session[$this->session_name]['date_from']   : date('m/d/Y', $some_time_ago);
        $this->date_to         = $session[$this->session_name] ? $session[$this->session_name]['date_to']     : date('m/d/Y', $cur_time);
        $this->time_from       = $session[$this->session_name] ? $session[$this->session_name]['time_from']   : '00:00';
        $this->time_to         = $session[$this->session_name] ? $session[$this->session_name]['time_to']     : '23:59';        
        
        if (!$this->station_id) {
            if ($this->stations) {
                foreach($this->stations as $key => $value) {
                    $this->station_id = $key;
                }
            }
        }
        
    }
    //==== 
        

    public function getStationsList() {
        return $this->stations;
    }

    public function getRateVolumes() {
        return array(
            '1'  => '1 min',
            '5'  => '5 min',
            '10' => '10 min',
            '20' => '20 min',
            '30' => '30 min', 
            '60' => '60 min',
            '1440' => '1 day'
        );
    }
    
    public function prepareList($total_ticks = 60) {

        if ($this->station_id) {

            $first_date = strtotime($this->date_from.' '.$this->time_from);
            $last_date  = strtotime($this->date_to.' '.$this->time_to);
            $series_data = array();
            


            $sql_where = array();

            $series_stations = array($this->station_id);
            $sql_where[] = "`sd`.`station_id` IN (".implode(',', $series_stations).") ";
            $sql_where[] = "`sd`.`measuring_timestamp` >= '".date('Y-m-d H:i:s', $first_date)."'";
            $sql_where[] = "`sd`.`measuring_timestamp` <= '".date('Y-m-d H:i:s', $last_date)."'";


                 
            $sql = "SELECT `sd`.`station_id`,
                           `sd`.`sensor_id`,
                           `sd`.`measuring_timestamp`,
                           `sd`.`sensor_value`,
                           `sd`.`bucket_size`
                    FROM `".SensorDataMinute::model()->tableName()."`    `sd`
                    WHERE ".implode(' AND ', $sql_where)."
                    ORDER BY `sd`.`measuring_timestamp` ASC ";
            
            $res = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryAll();
                    
           
            if ($res) {

                $total_found = count($res);
                for ($i = 0; $i < $total_found; $i++) {
                    $time = strtotime($res[$i]['measuring_timestamp'])*1000;
                    $rain_value = $res[$i]['sensor_value'] * $res[$i]['bucket_size'];
                    $series_data[0][] = array(
                        $time, $rain_value
                    );
                }
            }
            
            $series_names = array();

            $series_names[0] = $this->stations[$this->station_id];
        }        
        
        return array(
            'series_names' => $series_names, 
            'series_data'  => $series_data,
            'total_ticks'  => count($series_data[0]),
            'min_tick'     => date('Y-m-d H:i', $first_date),
            'max_tick'     => date('Y-m-d H:i', $last_date)
        );    
    }
    

    
    public function attributeLabels() {
         return array(
             'station_id' => It::t('site_label', 'filter_select_station'),
             'date_from'  => It::t('site_label', 'filter_date_from'),
             'date_to'    => It::t('site_label', 'filter_date_to'),
             'time_from'  => It::t('site_label', 'filter_time_from'),
             'time_to'    => It::t('site_label', 'filter_time_to'),
         );
     }
     
    public function afterValidate() {
         $this->putToMemory();
         parent::afterValidate();
    }
}
?>