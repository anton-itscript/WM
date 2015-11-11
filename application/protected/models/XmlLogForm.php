<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class XmlLogForm extends CFormModel {
    
    public $date_from;
    public $time_from;
    public $date_to;
    public $time_to;
    
    private $session_name = 'xml_log';
    
    private $start_timestamp;
    private $end_timestamp;

    public static function model($className=__CLASS__) {
        return parent::model($className);
    }    
    
    public function init() {
        
        $this->getFromMemory();

        return parent::init();
    }
    
    //===== validation
    public function rules() {
        return array(
            array('date_from,date_to', 'match', 'pattern' => '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/'),
            array('date_from,date_to', 'length', 'max' => 10),
            array('time_from,time_to', 'match', 'pattern' => '/^(\d{1,2}):(\d{1,2})$/'),
            array('time_from,time_to', 'length', 'max' => 5),
            array('date_to', 'checkDatesInterval'),
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
 
        //$possible_timezones = $this->getPossibleTimezones();
        
        $session[$this->session_name] = array(
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
        
        $this->date_from       = $session[$this->session_name] ? $session[$this->session_name]['date_from'] : '';
        $this->date_to         = $session[$this->session_name] ? $session[$this->session_name]['date_to'] : '';
        $this->time_from       = $session[$this->session_name] ? $session[$this->session_name]['time_from'] : '00:00';
        $this->time_to         = $session[$this->session_name] ? $session[$this->session_name]['time_to'] : '23:59';        
    }
    //==== 
    
    
    public function prepareList() {
        
        
            
            $sql_where = array();


            //---------------- Start date filter
            if ($this->date_from) {
                $sql_where[] = "`t1`.`created` >= '".date('Y-m-d H:i:s', strtotime($this->date_from.' '.$this->time_from))."'";
            }
            if ($this->date_to) {
                $sql_where[] = "`t1`.`created` <= '".date('Y-m-d H:i:s', strtotime($this->date_to .' '.$this->time_to))."'";
            }
            //---------------- End date filter
            $sql_where_str = $sql_where ? 'WHERE '.implode(' AND ', $sql_where) : '';

            $sql = "SELECT COUNT(*)
                    FROM `".XmlLog::model()->tableName()."` `t1`
                    ".$sql_where_str;

            $total = Yii::app()->db->createCommand($sql)->queryScalar();

            $pages = new CPagination($total);
            $pages->pageSize = 50;
            
            $sql = "SELECT t1.*
                    FROM `".XmlLog::model()->tableName()."` `t1`
                    ".$sql_where_str."
                    ORDER BY t1.created DESC
                    LIMIT ".($pages->currentPage *$pages->pageSize).", ".$pages->pageSize;

            
            $res = Yii::app()->db->createCommand($sql)->queryAll();
       
        return array('list' => $res, 'pages' => $pages);    
    }
    

    
    public function attributeLabels() {
         return array(
             'source' => Yii::t('project', 'Select Source:'),
             'date_from'  => Yii::t('project', 'Start Date'),
             'date_to'    => Yii::t('project', 'End Date:'),
             'time_from'  => Yii::t('project', 'Time:'),
             'time_to'    => Yii::t('project', 'Time:'),
         );
     }
     
    public function afterValidate() {
         $this->putToMemory();
         parent::afterValidate();
    }
}
?>