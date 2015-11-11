<?php

/**
 * Description of MessageForwardingInfoBase
 *
 * @author
 */
class MessageForwardingInfoBase extends CStubActiveRecord
{
    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName()
	{
		return 'tbl_message_forwarding_info';
	}
	
	public function search()
    {
        $criteria = new CDbCriteria;

		return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
			'sort' => array('defaultOrder' => array('id' => false)),

			'pagination' => array(
                'pageSize' => 15,
            ),
        ));
    }
}

?>
