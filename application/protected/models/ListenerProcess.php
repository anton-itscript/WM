<?php


class ListenerProcess extends CStubActiveRecord
{

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

    public function tableName()
	{
        return 'listener_process';
    }
	
	public function relations(){
        if(!$this->getUseLong()){
            return array(
                'listener' => array(self::BELONGS_TO, 'Listener', 'listener_id'),
            );
        }
        return array();
    }

    public static function addComment($listener_id, $status, $comment = '')
	{
        $obj = new ListenerProcess();
        
		$obj->listener_id = $listener_id;
        $obj->status = $status;
        $obj->comment = $comment;
        
		$obj->save();
    }
}