<?php

/**
 * Description of MessageForwardingInfoForSerialPort
 *
 * @author
 */
class MessageForwardingInfoForSerialPort extends MessageForwardingInfoBase
{
	public $port;
	
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'tbl_message_forwarding_info';
	}

    public function rules()
	{
        return array(
            array('source', 'required'),
			
			array('port', 'length', 'max' => 5),
            
			array('description', 'length', 'allowEmpty' => true),
        );
    }
	
	public function attributeLabels() 
	{
		return array(
			'port' => 'Serial port',
		);
	}
	
	protected function beforeValidate()
	{
		$this->source = $this->port;

        return parent::beforeValidate();
    }
	
	protected function afterFind()
	{
        parent::afterFind();
		
		$this->port = $this->source;
    }
}

?>
