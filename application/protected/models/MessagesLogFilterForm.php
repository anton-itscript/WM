<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class MessagesLogFilterForm extends CFormModel {
    
    public $types;
    public $date_from;
    public $time_from;
    public $date_to;
    public $time_to;

    public $order_field;
    public $order_direction;

    public $total;
    private $session_name = 'message_log_filter1';

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
    }    
    
    public function init(){
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
            array('types', 'required')
        );
    }    
    
    public function checkDatesInterval() {
        if (!$this->hasErrors('date_from') && !$this->hasErrors('date_to') && !$this->hasErrors('time_from') && !$this->hasErrors('time_to')) {
            $start_timestamp = strtotime($this->date_from.' '.$this->time_from);
            $end_timestamp = strtotime($this->date_to.' '.$this->time_to);      
            
            if ($this->date_from && $this->date_to && $end_timestamp < $start_timestamp) {
                $this->addError('date_to', 'End date and time must be later than start.');
            }
        }
    }
    //======
    
    public function getTypes() {
        return array('failed' => 'Failed', 'warning' => 'Warnings', 'successfull' => 'Successfull', 'not_processed_yet' => 'Not Processed yet');
    }
    
    //==== work with memory
    public function putToMemory() {
        $session = new CHttpSession();
        $session->open();
 
        $session[$this->session_name] = array(
            'types'           => $this->types,
            'date_from'       => $this->date_from,
            'date_to'         => $this->date_to,
            'time_from'       => $this->time_from,
            'time_to'         => $this->time_to,        
            'order_field'     => $this->order_field,
            'order_direction' => $this->order_direction,
            'total'           => $this->total,
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
        
        $this->types            = $session[$this->session_name] ? $session[$this->session_name]['types'] : array('failed','warning');
        $this->date_from        = $session[$this->session_name] ? $session[$this->session_name]['date_from'] : '';
        $this->date_to          = $session[$this->session_name] ? $session[$this->session_name]['date_to'] : '';
        $this->time_from        = $session[$this->session_name] ? $session[$this->session_name]['time_from'] : '00:00';
        $this->time_to          = $session[$this->session_name] ? $session[$this->session_name]['time_to'] : '';
        $this->order_field      = $session[$this->session_name] ? $session[$this->session_name]['order_field'] : 'date';
        $this->order_direction  = $session[$this->session_name] ? $session[$this->session_name]['order_direction'] : 'DESC';
        $this->total            = $session[$this->session_name] ? $session[$this->session_name]['total'] : '0';
    }
    //==== 

   
    public function setOrders($field) {
        
        if (in_array($field, array('message', 'date'))) {
            
            if ($field == $this->order_field) {
                $this->order_direction = $this->order_direction == 'ASC' ? 'DESC' : 'ASC';
            } else {
                $this->order_direction = 'ASC';
            }
            $this->order_field = $field;    
            $this->putToMemory();
        }
    }
    
    
    private function _prepareSqlCondition() {
        $sql_where = array();
        $sql_where_tmp = array();
        
        if ($this->types && in_array('failed', $this->types)) {
            $sql_where_tmp[] = "(`t1`.`is_processed` = 1 AND `t1`.`failed` = 1)";
        }
        if ($this->types && in_array('warning', $this->types)) {
            $sql_where_tmp[] = "(`t1`.`is_processed` = 1 AND `t2`.`process_error_id` > 0)";
        }        
        if ($this->types && in_array('successfull', $this->types)) {
            $sql_where_tmp[] = "(`t1`.`is_processed` = 1 AND `t2`.`process_error_id` IS NULL AND `t1`.`failed` = 0)";
        }
        if ($this->types && in_array('not_processed_yet', $this->types)) {
            $sql_where_tmp[] = "(`t1`.`is_processed` = 0)";
        }        
        
        if ($sql_where_tmp) {
            $sql_where[] = implode(' OR ', $sql_where_tmp);
        }
        
        if ($this->date_from) {
            $sql_where[] = "`t1`.`measuring_timestamp` >= '".date('Y-m-d H:i:s', strtotime($this->date_from.' '.$this->time_from))."'";
        }
        if ($this->date_to) {
            $sql_where[] = "`t1`.`measuring_timestamp` <= '".date('Y-m-d H:i:s', strtotime($this->date_to .' '.$this->time_to))."'";
        }
        
        $sql_where_str = $sql_where ? ' WHERE '.implode(' AND ', $sql_where) : '';
        
        return $sql_where_str;
    }
    
    private function _prepareSqlOrder()
    {
        if ($this->order_field == 'date') {
            $sql_order = "`t1`.`log_id` ".$this->order_direction;
        } elseif ($this->order_field == 'message') {
            $sql_order = "`t1`.`message` ".$this->order_direction;
        } 
        return ' ORDER BY '.$sql_order;    
    }

    public function getTotal(){
        $sql_where_str = $this->_prepareSqlCondition();
        $sql = "SELECT count(DISTINCT `t1`.`log_id`)
                    FROM `".ListenerLog::model()->tableName()."` `t1`
                    LEFT JOIN `".ListenerLogProcessError::model()->tableName()."` `t2` ON `t2`.`log_id` = `t1`.`log_id`
                    {$sql_where_str}";

        $this->total = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryScalar();
    }

    public function prepareList($page_size = 50) {
        $sql_where_str = $this->_prepareSqlCondition();
        $sql_order_str = $this->_prepareSqlOrder();

        if ($page_size) {
            $total = $this->total;
        }
        if ($total || !$page_size) {
            if ($page_size) {
                $pages = new CPagination($total);
                $pages->pageSize = $page_size;                
            }
            $sql = "SELECT `t1`.`log_id`, `t1`.`updated`,`t1`.`measuring_timestamp`, `t1`.`created`, `t1`.`message`, `t1`.`fail_description`, t3.station_id_code, t3.display_name, t3.station_type, `t2`.`process_error_id`, `t1`.`is_processed`
                    FROM `".ListenerLog::model()->tableName()."` t1
                    LEFT JOIN `".ListenerLogProcessError::model()->tableName()."` `t2` ON `t2`.`log_id` = `t1`.`log_id`
                    LEFT JOIN `".Station::model()->tableName()."` `t3` ON `t3`.`station_id` = `t1`.`station_id`
                    {$sql_where_str}
                    GROUP BY `t1`.`log_id`
                    {$sql_order_str}";
            if ($page_size) {
                $sql .= " LIMIT ".($pages->currentPage *$pages->pageSize).", ".$pages->pageSize;
            }

            $res = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryAll();
           
            if ($res) {
                $ids = array();
                foreach ($res as $key => $value) {
                    $ids[] = $value['log_id'];
                    if ($value['fail_description']) {
                        $value['errors'] = explode(',',$value['fail_description']);
                    }
                    $list[$value['log_id']] = $value;
                }
                
                $sql = "SELECT * FROM `".ListenerLogProcessError::model()->tableName()."` WHERE `log_id` IN (".implode(',',$ids).")";
                $res2 = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryAll();
                
                if ($res2) {
                    foreach ($res2 as $key => $value) {
                        if ($value['type'] == 'error') {
                            $list[$value['log_id']]['errors'][] = $value['description'];
                        } else if ($value['type'] == 'warning') {
                            $list[$value['log_id']]['warnings'][] = $value['description'];
                        }
                    }
                }
            }
        }
        
        
        return array('list' => $list, 'pages' => $pages);    
    }
    
    
    public function makeExport() {
        
        
        $return_string = "\"Messages History\"\n\nChoosed filters:\n\n";
        
        if ($this->types) {
            
            $subtypes = array();
            if (in_array('failed', $this->types)) {
                $subtypes[]= "Failed";
            }
            if (in_array('warning', $this->types)) {
               $subtypes[]= "Warning";
            }        
            if (in_array('successfull', $this->types)) {
                $subtypes[]= "Successfull";
            }
            if ($this->types && in_array('not_processed_yet', $this->types)) {
                $subtypes[]= "Not procecessed yet";
            }    
            $return_string .= "* Types ".implode(' OR ', $subtypes);
        }
   
        
        if ($this->date_from || $this->date_to) {
            $return_string .= "\n* Message was added into DB in period ";
            if ($this->date_from) 
                $return_string .= " from: ".date('Y-m-d H:i:s', strtotime($this->date_from.' '.$this->time_from));
            if ($this->date_to) {
                if ($this->date_from) {
                    $return_string .= ' and';
                }
                $return_string .= ' Before '.date('Y-m-d H:i:s', strtotime($this->date_to .' '.$this->time_to));
            }
        }
        
        $res = $this->prepareList(0);
        if (!$res['list']) {
            $return_string .= "\n\nList is empty";
        } else {
            $prepared_res = array();
            foreach ($res['list'] as $key => $value) {
                $prepared_res[] = array(
                    'Message' => $value['message'],
                    'Added' => $value['created'],
                    'St Id' => ($value['station_id_code'] ?  $value['station_id_code'] : 'Unknown'),
                    'St Name' => ($value['station_id_code'] ?  $value['display_name'] : 'Unknown'),
                    'St Type' => ($value['station_id_code'] ?  $value['station_type'] : 'No'),
                    'Has Errors?' => ($value['errors'] ? 'Fatal errors. Was not processed.' : 'No'),
                    'Has Warnings?' => ($value['warnings'] ? 'Data from some sensors were not processed.' : 'No'),
                    'Errors Description' => addslashes(($value['errors'] ? implode(' ; ', $value['errors']) : 'No')),
                    'Warnings Description' => addslashes(($value['warnings'] ? implode(' ; ', $value['warnings']) : 'No')),
                );
            }
            
            $return_string .= "\n\n".It::prepareStringCSV($prepared_res);
        }
        
        It::downloadFile($return_string, 'export_history__'.date('Y-m-d_Hi').'.csv', 'text/csv');
        
    }

    
    public function attributeLabels() {
         return array(
             'type'        => It::t('site_label', 'filter_type'),
             'date_from'   => It::t('site_label', 'filter_date_from'),
             'date_to'     => It::t('site_label', 'filter_date_to'),
             'time_from'   => It::t('site_label', 'filter_time_from'),
             'time_to'     => It::t('site_label', 'filter_time_to'),
         );
     }
     
    public function afterValidate() {
        $this->getTotal();
        $this->putToMemory();
        parent::afterValidate();
    }
}
?>