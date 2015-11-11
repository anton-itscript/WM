<?php


class XmlLog extends CStubActiveRecord {


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

    public function tableName() {
        return 'xml_process_log';
    }

    public function addNew($msg) {

        $message_obj = new XmlLog();
        $message_obj->comment = $msg;
        $message_obj->save();
        return $message_obj->xml_log_id;
    }

}