<?php

class ListenerLogTemp extends CStubActiveRecord
{

    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName(){
        return 'listener_log_temp';
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


        parent::afterSave();
    }


    public function afterDelete()
    {
//        if(!$this->getUseLong()){
//            ScheduleReportProcessed::model()->deleteAllByAttributes(array('listener_log_id' => $this->temp_log_id));
//        }
        parent::afterDelete();
    }

    /**
     * @param $msg
     * @param $listener_id
     * @param $rewrite_prev_values
     * @param string $source
     * @param int $station_id
     * @param null $source_info
     * @return int
     * @var $synchronization Synchronization
     */
    public static function addNew($msg, $listener_id, $rewrite_prev_values, $source = '', $station_id = 0, $source_info = null)
    {
        $synchronization = new Synchronization();
        $listener_id_from_master = $synchronization->getListenerId();

        $parseMessage = new ParseMessage(LoggerFactory::getFileLogger('parse_message'),$msg);

        $message_obj = new ListenerLogTemp();
        $message_obj->listener_id               = $listener_id;
        $message_obj->station_id_code           = $parseMessage->getStationIdCode();
        $message_obj->message                   = $msg;
        $message_obj->measuring_timestamp       = $parseMessage->getMeasuringTimestamp();
        $message_obj->is_processed              = 0;
        $message_obj->is_processing             = 0;

        if ($listener_id_from_master===$listener_id and $listener_id != 0 ) {
            $message_obj->from_master           = 1;
        } else {
            $message_obj->from_master           = 0;
        }

        $message_obj->source                    = $source;
        $message_obj->source_info               = $source_info;

        if ($synchronization->isProcessed()) {
            $message_obj->synchronization_mode = $synchronization->isMaster() ? 'master' :( $synchronization->isSlave() ? 'slave' : 'none' );
        } else {
            $message_obj->synchronization_mode = 'none';
        }

        $message_obj->rewrite_prev_values       = $rewrite_prev_values;
        $message_obj->save();

        return $message_obj->temp_log_id;
    }


}