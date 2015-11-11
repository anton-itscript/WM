<?php

/**
 * Description of MessageForwardingInfo
 *
 * @author
 */
class MessageForwardingInfoForTcpServer extends MessageForwardingInfoBase
{
	/**
	 * Protocol name.
	 * @var string 
	 */
	public $protocol = 'tcp'; // Default value is 'tcp'.
	
	public $address;
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
			
			array('protocol', 'length', 'min' => 3),
			array('address', 'length', 'min' => 8, 'max' => 50),
			array('address', 'checkAddress'),
			
			array('port', 'length', 'max' => 5),
            array('port', 'numerical', 'integerOnly' => true, 'allowEmpty' => false),
			
			array('description', 'length', 'allowEmpty' => true),
        );
    }
	
	public function attributeLabels() 
	{
		return array(
			'protocol' => 'Protocol',
			'address' => 'Address',
			'port' => 'Port',
		);
	}
	
	protected function beforeValidate()
	{
		// "tcp" (or other protocol name) should be in lower case.
        $this->source = $this->protocol .':'. $this->address .':'. $this->port;

        return parent::beforeValidate();
    }
	
	protected function afterFind()
	{
        parent::afterFind();
		
		$matches = array();
		
		if(preg_match('#^([a-zA-Z]{2,})\:([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}|[a-zA-Z0-9\-\.]+\.[a-zA-Z]+|localhost)\:([0-9]{1,5})$#i', $this->source, $matches))
		{
			$this->address = $matches[2];
			$this->port = $matches[3];
		}
    }
	
	public function checkAddress()
	{
        $longIp = ip2long($this->address);
            
		if (($longIp == -1 || $longIp === false) && (preg_match('/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}|[a-zA-Z0-9\-\.]+\.[a-zA-Z]+|localhost)$/i', $this->address) === 0))
		{
			$this->addError('address', 'Address is invalid.');

			return false;                
		}
		
        return true;        
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