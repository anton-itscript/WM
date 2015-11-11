<?php

/**
 * Description of ForwardedMessage
 *
 * @author 
 */
class ForwardedSlaveMessage extends CStubActiveRecord {

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
    }

    public function beforeSave(){
        if(!$this->getUseLong()){
            if ($this->isNewRecord){
                $this->forwarded_slave_created = new CDbExpression('NOW()');
            }
            $this->forwarded_slave_updated = new CDbExpression('NOW()');
        }
        return parent::beforeSave();
    }

	public function tableName()
	{
        return 'tbl_forwarded_slave';
	}
	
	public function relations()
    {
        return array(
            'slave_message' => array(self::BELONGS_TO, 'ListenerLogTemp', 'forwarded_slave_message_id'),
        );
    }

    public function getNewMessages()
    {
        $result =  self::model()->with('slave_message')->findAllByAttributes(array('forwarded_slave_status'=>':status'),array(':status'=>'new'));
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

        $item->forwarded_slave_status = $status;
        $item->save();
        return true;
    }


}

?>
