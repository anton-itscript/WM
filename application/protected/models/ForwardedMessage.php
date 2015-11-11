<?php

/**
 * Description of ForwardedMessage
 *
 * @author 
 */
class ForwardedMessage extends CStubActiveRecord {

    public static function model($className=__CLASS__)
	{
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
        return 'tbl_forwarded_message';
	}
	
	public function relations()
    {
        return array(
            'message' => array(self::BELONGS_TO, 'ListenerLog', 'message_id'),        
            'messageTemp' => array(self::BELONGS_TO, 'ListenerLogTemp', 'message_id'),
        );
    }

    public function getNewMessages()
    {
        $result =  self::model()->with('message')->findAllByAttributes(array('status'=>':status'),array(':status'=>'new'));
        if (count($result)>0)
            return $result;
        else
            return array();
    }
    public function getNewTempMessages()
    {
        $result =  self::model()->with('messageTemp')->findAllByAttributes(array('status'=>':status'),array(':status'=>'new'));
        if (count($result)>0)
            return $result;
        else
            return array();
    }

    public function setStatus($id,$status=false) {
        if($status===false)
            return false;
        $item = self::model()->findbypk($id);
        if(!is_object($item))
            return false;

        $item->status = $status;
        $item->save();
        return true;
    }


}

?>
