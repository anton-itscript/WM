<?php

/**
 * Class dd
 * For full log
 */
class dd extends uLogs{
    protected static $file = 'SyncLongDB';
    protected static $micro_time_format = " %-6s";
}

class SyncDBCommand extends CConsoleCommand{
    //const
    const defDate = '0000-00-00 00:00:00'; //Min time in DB
    const defId = -1;//min ID in DB
    const tColumn = "updated";
    const defDel = "delete";

    // log
    protected $_logger = null;

    // default confiq
    protected static $LOG = false;//create full log
    protected static $_maxRow = 3000;//If 0 unlimited row
    protected static $_maxRowTemp = 3000;
    protected static $_delete_periodicity = 'DAY';
    protected static $_delete_period = 3;
    protected static $_status = false;//status BD
    protected static $_tables = array();//'tablename' => array(KEY,COLUMN_CHECK[,delete old data, use if COLUMN_CHECK = UPDATED])

    /**
     * Ini system param
     */
    public function init()
    {
        parent::init();

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $this->_logger = LoggerFactory::getFileLogger('process_sync_db');

    }

    /**
     * Set option from .conf
     * @return bool
     */
    protected function setOptions(){

        try{
            //self::$_status = ConfigManager::getConfigSection('install_database_status','done')==1?true:false;


            self::$_status = Yii::app()->params['install']['install_database_long_status']==1?true:false;
//            $conf = ConfigManager::getConfigSection('database_long_sync');
            $conf =  Yii::app()->params['db_long_sync_config'];
            if($conf['max_row'])self::$_maxRow = self::$_maxRowTemp = $conf['max_row'];
            if($conf['delete_periodicity']) self::$_delete_periodicity = $conf['delete_periodicity'];
            if($conf['delete_period']) self::$_delete_period = $conf['delete_period'];

            self::$_tables = array(
                //'tablename' => array(KEY,COLUMN_CHECK[,delete old data, use if COLUMN_CHECK = UPDATED])
                'CalculationDBHandler' 			    => array('id'=>'handler_id',                      'check'=>'handler_id',                    'del'=>''),
                'Listener' 			                => array('id'=>'listener_id',                     'check'=>self::tColumn,                   'del'=>''),
                'ListenerLog' 			            => array('id'=>'log_id',                          'check'=>self::tColumn,                   'del'=>self::defDel),
                'ListenerLogProcessError' 	        => array('id'=>'process_error_id',                'check'=>self::tColumn,                   'del'=>self::defDel),
                'ListenerProcess'                   => array('id'=>'listener_process_id',             'check'=>self::tColumn,                   'del'=>self::defDel),
                'MessageForwardingInfoBase'         => array('id'=>'id',                              'check'=>'id',                            'del'=>''),
                'ForwardedMessage'                  => array('id'=>'id',                              'check'=>self::tColumn,                   'del'=>self::defDel, 'where'=>'`status` LIKE \'sent\''),
                'RefbookMeasurementType' 		    => array('id'=>'measurement_type_id',             'check'=>'measurement_type_id',           'del'=>''),
                'RefbookMetric' 		        	=> array('id'=>'metric_id',                       'check'=>'metric_id',                     'del'=>''),
                'RefbookMeasurementTypeMetric' 	    => array('id'=>'measurement_type_metric_id',      'check'=>'measurement_type_metric_id',    'del'=>''),
                'Station' 			                => array('id'=>'station_id',                      'check'=>self::tColumn,                   'del'=>''),
                'StationSensor' 			        => array('id'=>'station_sensor_id',               'check'=>self::tColumn,                   'del'=>''),
                'StationSensorFeature' 			    => array('id'=>'sensor_feature_id',               'check'=>self::tColumn,                   'del'=>''),
                'SensorData'                        => array('id'=>'sensor_data_id',                  'check'=>self::tColumn,                   'del'=>self::defDel),
                'SensorDataMinute' 	        	    => array('id'=>'sensor_data_id',                  'check'=>self::tColumn,                   'del'=>self::defDel),
                'SensorDBHandler' 		        	=> array('id'=>'handler_id',                      'check'=>self::tColumn,                   'del'=>''),
                'SensorDBHandlerDefaultFeature' 	=> array('id'=>'handler_feature_id',              'check'=>self::tColumn,                   'del'=>''),
                'SeaLevelTrend' 			        => array('id'=>'trend_id',                        'check'=>self::tColumn,                   'del'=>self::defDel),
                'StationCalculation' 			    => array('id'=>'calculation_id',                  'check'=>self::tColumn,                   'del'=>''),
                'StationCalculationData' 			=> array('id'=>'calculation_data_id',             'check'=>self::tColumn,                   'del'=>self::defDel),
                'StationCalculationVariable' 		=> array('id'=>'calculation_variable_id',         'check'=>self::tColumn,                   'del'=>''),
                'XmlLog' 			                => array('id'=>'xml_log_id',                      'check'=>self::tColumn,                   'del'=>self::defDel),
//                'ScheduleReport' 			        => array('id'=>'schedule_id',                     'check'=>self::tColumn,                   'del'=>''),
//                'ScheduleReportProcessed' 			=> array('id'=>'schedule_processed_id',           'check'=>self::tColumn,                   'del'=>self::defDel),
//                'ScheduleReportDestination' 	    => array('id'=>'schedule_destination_id',         'check'=>'schedule_destination_id',       'del'=>''),
//                'ScheduleReportToStation' 	        => array('id'=>'id',                              'check'=>self::tColumn,                   'del'=>''),
            );
            return true;
        } catch(Exception $e) {
            $this->_logger->log(__METHOD__ . "ERROR: ",$e->getMessage());
            return false;
        }
    }

    /**
     * Sync table from $Class::model()->tableName()
     * Compare column $Column
     * $time - for log
     * @param $Class
     * @param $Column
     * @param null $time
     */
    protected function syncTable($Class,$Column,$time=null){
        $this->_logger->log(__METHOD__ ." ".$Class);
        try {
            if(self::$LOG AND $time==null)dd::msg_date(dd::sting_min_len(30,$Class::model()->tableName()));
            if(self::$LOG) $stTime = $time?$time:microtime(true);

            //if true use TIME else KEY
            $check = $Column['check'] == self::tColumn ? true: false;

            $sql = "SELECT MAX(".$Column['check'].") as maxCol FROM ".$Class::model()->tableName().";";
            $Updated = $Class::getDbConnect(true)->createCommand($sql)->queryRow();
            $lastUpdated = $Updated['maxCol'] ? $Updated['maxCol'] : ($check?self::defDate:self::defId);

            $sql = "SELECT * FROM ".$Class::model()->tableName();
            if($check)$sql.=
                    " WHERE ".$Column['check']." >= '".$lastUpdated."'" .
                    " ORDER BY ".$Column['check']." ASC, ".$Column['id']." ASC";
            else $sql.=
                    " WHERE ".$Column['check']." > '".$lastUpdated."'".
                    " ORDER BY ".$Column['check']." ASC";
            if(self::$_maxRow!=0)$sql.= " LIMIT ".self::$_maxRow;
            $rows = $Class::getDbConnect()->createCommand($sql.";")->queryAll();
            $count = count($rows);
            $createRow = $updateRow = $missRow = $errRow = 0; //counters

            if($count>0){
                foreach($rows as $out){
                    if($check) $in = $Class::model()->long()->findByPk($out[$Column['id']]);
                    if(!$check OR $in==null) {
                        $in = new $Class(true);
                        foreach($out as $key => $val)
                            $in->$key = $val;
                        try{
                            $createRow+=$in->save(false)?1:0;
                        } catch(Exception $e){
                            $errRow++;
                        }
                    } elseif($in->$Column['check'] <= $out[$Column['check']]) {
                        foreach($out as $key => $val)
                            $in->$key = $val;
                        try{
                            $updateRow+=$in->long()->save(false)?1:0;
                        }catch(Exception $e){
                            $errRow++;
                        }
                    } else {
                        $missRow++;
                    }
                }

                if($createRow+$updateRow == 0){
                    $sql = "SELECT MAX(".$Column['check'].") as maxCol FROM ".$Class::model()->tableName().";";
                    $MaxUpdated = $Class::getDbConnect()->createCommand($sql)->queryRow()['maxCol'];
                    if($MaxUpdated != $lastUpdated){
                        self::$_maxRow = self::$_maxRow+self::$_maxRowTemp;
                        if(!$errRow){
                            self::syncTable($Class,$Column,$stTime?$stTime:null);
                            return;
                        }

                    }
                }
                if($Column['del'] and $createRow+$updateRow>0)
                    self::$_tables[$Class]['del']=end($rows)[$Column['check']];

            }
            if(self::$LOG) {
                dd::msg(
                    "( ".dd::sting_min_len(20,$lastUpdated)."- ".
                    dd::sting_min_len(20,end($rows)[$Column['check']]).") ",
                    " Count row: ".dd::sting_min_len(5,$count).
                    " Created: ".dd::sting_min_len(5,$createRow).
                    " Updated:".dd::sting_min_len(5,$updateRow).
                    " Miss:".dd::sting_min_len(7,$missRow."/".$errRow),
                    " Av time: ".dd::time_micro((microtime(true)-$stTime)/(($count)?($count):1)).
                    "/".dd::time_micro((microtime(true)-$stTime)/(($createRow+$updateRow)?($createRow+$updateRow):1)).
                    " Full time: ".(microtime(true)-$stTime));
            }
            self::$_maxRow = self::$_maxRowTemp;
        } catch (Exception $e){
            $this->_logger->log(__METHOD__ . "ERROR: ". print_r($e->getMessage()));
            if(self::$LOG)dd::msg(" ERROR: ". print_r($e->getMessage()));
        }
    }

    /**
     * Delete old row from $Class::model()->tableName()
     * @param $Class
     * @param $Column
     */
    protected function deleteOldRowTable($Class,$Column){
        $this->_logger->log(__METHOD__ ." ".$Class);
        try{
            if(!$Column['del'] or $Column['del'] == self::defDel)return;
            if(self::$LOG) dd::msg_date(dd::sting_min_len(30,$Class::model()->tableName()));
            if(self::$LOG) $stTime = microtime(true);

            //$sql = "SELECT MAX(".$Column['check'].") - INTERVAL ".self::$_delete_period." ".self::$_delete_periodicity." as endDate FROM ".$Class::model()->tableName().";";
            $sql = "SELECT '".$Column['del']."' - INTERVAL ".self::$_delete_period." ".self::$_delete_periodicity." as endDate;";
            $query = $Class::getDbConnect(true)->createCommand($sql)->queryRow();
            $endDateForDelete = $query['endDate'] ? $query['endDate'] : self::defDate;

            $cri = new CDbCriteria;
                $cri->condition = $Column['check']." <'".$endDateForDelete."'";
            if(self::$LOG) $count = $Class::model()->count($cri);
            if(self::$LOG) $miss = 0;
            try{
                $result = $Class::model()->deleteAll($cri);
            }catch (Exception $e){
                if(isset($Column['where']))
                    $cri->condition = $Column['where'];
                $cri->order = $Column['check']." ASC";
                $cri->limit = self::$_maxRow;
                try{
                    $result = $Class::model()->deleteAll($cri);
                }catch (Exception $e){
                    $result = 0;
                }
            }
            if(self::$LOG) $miss = $count - $result;

            if(self::$LOG)dd::msg(
                    "( ".dd::sting_min_len(20,$endDateForDelete).") ",
                    " Count row: ".dd::sting_min_len(5,$count).
                    " Deleted: ".dd::sting_min_len(5,$result),
                    " Miss: ".dd::sting_min_len(5,$miss),
                    " Average time: ".dd::time_micro((microtime(true)-$stTime)/($count?$count:1)).
                    " Full time: ".(microtime(true)-$stTime));

        } catch (Exception $e){
            $this->_logger->log(__METHOD__ . "ERROR: ",$e->getMessage());
            if(self::$LOG)dd::msg("ERROR: ",$e->getMessage());
        }
    }

    /**
     * Start process
     * @param array $arg
     * @return int|void
     */
    public function run($arg){
        if(Yii::app()->mutex->lock('sync_data',300)) {

            if($this->setOptions() AND self::$_status){
                $this->_logger->log(__METHOD__ ." Insert/update data in db_long");
                if(self::$LOG){dd::msg("\n");dd::msg_date("Insert/update data in db_long");}
                foreach(self::$_tables as $Class => $Column){
                    $this->syncTable($Class,$Column);
                }
                $this->_logger->log(__METHOD__ ." Delete long term data in db");
                if(self::$LOG){dd::msg("\n");dd::msg_date("Delete long term data in db");}
                self::$_tables=array_reverse(self::$_tables);
                foreach(self::$_tables as $Class => $Column){
                    $this->deleteOldRowTable($Class,$Column);
                }
                $this->_logger->log(__METHOD__ ." END");
            }

            Yii::app()->mutex->unlock();
        }
    }
}
?>