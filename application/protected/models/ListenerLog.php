<?php

class ListenerLog extends CStubActiveRecord
{
	protected static $_messageCache = array();

    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName(){
        return 'listener_log';
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
           // ListenerLog::updateIsLastForStation($this->station_id);
        }
        parent::afterSave();
    }

    public function updateIsLastForStation($station_id)
    {
        $table = ListenerLog::model()->tableName();

        // Delete old last messages
        $sql = "UPDATE
					`". $table ."`
				SET
					`is_last` = 0
				WHERE
					`is_last` = 1 AND
					`station_id` = ?";

        Yii::app()->db->createCommand($sql)->query(array($station_id));


        // Set new last messages
        $sql = "UPDATE
					`". $table ."`
				SET
					`is_last` = 1
				WHERE
					`log_id` IN (
						SELECT
							ids.`log_id`
						FROM
							(
							SELECT
								`measuring_timestamp`, MAX(`log_id`) as `log_id`
							FROM
								`". $table ."`
							WHERE
								`station_id` = ? AND
								`failed` = 0
							GROUP BY
								`measuring_timestamp`
							ORDER BY
								`measuring_timestamp` DESC
							LIMIT 2
							) ids
					)";
        Yii::app()->db->createCommand($sql)->query(array($station_id));
    }

    public function afterDelete()
    {
        if(!$this->getUseLong()){
            ScheduleReportProcessed::model()->deleteAllByAttributes(array('listener_log_id' => $this->log_id));
        }
        parent::afterDelete();
    }

    public static function addNew($msg, $listener_id, $rewrite_prev_values, $source = '', $station_id = 0, $source_info = null)
    {
        $message_obj = new ListenerLog();

        $message_obj->listener_id         = $listener_id;
        $message_obj->message             = $msg;
        $message_obj->is_processed        = 0;
        $message_obj->rewrite_prev_values = $rewrite_prev_values;
        $message_obj->source              = $source;
        $message_obj->source_info         = $source_info;
        $message_obj->station_id          = $station_id;

        $message_obj->save();

        return $message_obj->log_id;
    }

    public static function getAllLast2Messages($station_ids, $start_log_id = null){
        $result = array();
        foreach ($station_ids as $station_id){
            $result[$station_id] = self::getLast2Messages($station_id, $start_log_id);
        }

        return $result;
    }

    /**
     * Return last to messages for a gibven station.
     * If start_log_id is specified then returns message with log_id = start_log_ig + previous message.
     *
     * @param int $station_id
     * @param int $start_log_id
     * @return array
     */
    public static function getLast2Messages($station_id, $start_log_id = null)
    {
        if (isset($start_log_id))
        {
            $result = array();

            $message = ListenerLog::model()->findByPk($start_log_id);

            if (!is_null($message))
            {
                $result[] = $message;

                $criteria = new CDbCriteria();

                $criteria->compare('station_id', $station_id);
                $criteria->compare('failed', 0);
                $criteria->compare('measuring_timestamp', '<'. $message->measuring_timestamp);

                $criteria->order = 'measuring_timestamp desc, log_id asc';
                $criteria->limit = 1;

                $prevMessage = ListenerLog::model()->find($criteria);

                if (!is_null($prevMessage))
                {
                    $result[] = $prevMessage;
                }
            }

            return $result;
        }

        $criteria = new CDbCriteria();

        $criteria->compare('station_id', $station_id);
//		$criteria->compare('is_last', 1);
        $criteria->compare('failed', 0);

        $criteria->order = 'measuring_timestamp desc, log_id asc';
        $criteria->limit = 4;//was limit = 2

        return ListenerLog::model()->findAll($criteria);
    }

    public static function getMessageWithTime($station_id, $timestamp, $timestamp_start = '')
    {
        if (!isset(self::$_messageCache[$station_id][$timestamp][empty($timestamp_start) ? 'empty' : $timestamp_start]))
        {
            $criteria = new CDbCriteria();

            $criteria->compare('failed', 0);

            $criteria->compare('station_id', $station_id);
            $criteria->compare('measuring_timestamp', '<='. $timestamp);
            $criteria->compare('measuring_timestamp', '>'. $timestamp_start);

            $criteria->order = 'log_id desc';
            $criteria->limit = 1;

            self::$_messageCache[$station_id][$timestamp][empty($timestamp_start) ? 'empty' : $timestamp_start] = ListenerLog::model()->find($criteria);
        }
        return self::$_messageCache[$station_id][$timestamp][empty($timestamp_start) ? 'empty' : $timestamp_start];
    }

    public static function lastMsgIds($station_ids, &$stations = null){
        $lastMessages = ListenerLog::getAllLast2Messages($station_ids);
        $lastMessageIds = array();
        foreach ($lastMessages as $key => $stationMessages){
            //station last message
            if(isset($stations[$key])){
                $next_expected = strtotime($stationMessages[0]->measuring_timestamp) + $stations[$key]->event_message_period*60 + 300;
                $stations[$key]->nextMessageIsLates = $next_expected < time() ? 1 : 0;
                $stations[$key]->lastMessage=$stationMessages[0];
            }
            //log id
            foreach ($stationMessages as $message){
                $lastMessageIds[] = $message->log_id;
            }
        }
        return $lastMessageIds;
    }



    public function relations(){
        return array(
            'Station' => array(self::BELONGS_TO, 'Station', 'station_id'),
            'sensor_data' => array(self::HAS_MANY, 'SensorData', 'listener_log_id'),
        );
    }

    public function getAllDataInType($stationType, $start_time=false, $end_time=false)
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition("`listener`.`station_type` = '" . $stationType . "'");
        $criteria->alias = 'listener';
        if ($start_time != false)
            $criteria->addCondition("`listener`.`measuring_timestamp` >= '" . $start_time . "'");
        if ($end_time   != false)
            $criteria->addCondition("`listener`.`measuring_timestamp` < '" . $end_time . "'");
        $criteria->order = '`listener`.`created` DESC ';

        $log = LoggerFactory::getFileLogger('listenerModel');
        $log->log(__METHOD__.print_r($criteria,1));

        $criteria->with = array(
            'Station'=>array('select'=>'station_id, station_id_code, station_type'),
            'sensor_data'=>array('select'=>'sensor_feature_value, sensor_id, sensor_data_id, sensor_feature_id, metric_id'),

            'Station',
            'sensor_data.sensor_feature.metric',
            'sensor_data.Sensor',

        );
        $logger = LoggerFactory::getFileLogger('getAllDataInType');
        // $criteria->limit = '2';
        $result_short = $this->findAll($criteria);
        $result_long = $this->long()->findAll($criteria);

//        $logger->log(__METHOD__.' $result_short '. print_r($result_short,1));
//        $logger->log(__METHOD__.' $result_long '. print_r($result_long,1));

        $logger->log(__METHOD__.' $criteria: '. print_r($criteria,1));
        $logger->log(__METHOD__.' $result_short COUNT'. count($result_short));
        $logger->log(__METHOD__.' $result_long COUNT'. count($result_long));

        if (count($result_short) && count($result_long)) {
            $short_ids = array();

            foreach($result_short as $item) {
                $short_ids[] = $item->log_id;
            }

            $result_long_temp = array();

            foreach($result_long as $item) {
                if(!in_array($item->log_id,$short_ids))
                        $result_long_temp[] = $item;
            }
            foreach($result_long_temp as $item) {
                $result_short[] = $item;
            }

            $logger->log(__METHOD__.' result  COUNT'. count($result_short));
            return  $result_short ;
        } elseif(is_array($result_short)) {
            return  $result_short ;
        } elseif(is_array($result_short)) {
            return  $result_long ;
        } else
            return null;


    }

}