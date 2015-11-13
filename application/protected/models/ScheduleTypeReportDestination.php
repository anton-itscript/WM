<?php

/**
 * Destiantion of created report.
 * Possible destinations: mail, ftp, local folder.
 * 
 * @author
 */
class ScheduleTypeReportDestination extends CStubActiveRecord
{
    public $uid;
    public $address_name;
    public static function model($className=__CLASS__)
	{
        return parent::model($className);
    }

    public function afterFind()
    {
        $this->generateUID();
        $this->generateAddressName();
        return parent::afterFind();
    }

    public function tableName()
	{
        return 'ex_schedule_report_destination';
    }

    public function rules()
	{
        return array(
            array('method', 'required'),
            array('method', 'checkDestination'),
            array('destination_email', 'email', 'allowEmpty' => true),
            array('destination_local_folder', 'length', 'max' => 255, 'allowEmpty' => true),
            array('destination_ip', 'length', 'max' => 15, 'allowEmpty' => true),
            array('destination_ip_folder', 'length', 'allowEmpty' => true),
            array('destination_ip_user,destination_ip_password', 'length', 'allowEmpty' => true),
            array('destination_ip_port', 'numerical', 'allowEmpty' => true, 'integerOnly' => true),
        );
    }

    public function attributeLabels()
	{
         return array(
             'destination_local_folder' => It::t('home_schedule', 'dest_param_local_folder'),
             'destination_email'        => It::t('home_schedule', 'dest_param_email'),
             'destination_ip'           => It::t('home_schedule', 'dest_param_ftp_ip'),
             'destination_ip_port'      => It::t('home_schedule', 'dest_param_ftp_port'),
             'destination_ip_user'      => It::t('home_schedule', 'dest_param_ftp_user'),
             'destination_ip_password'  => It::t('home_schedule', 'dest_param_ftp_password'),
             'destination_ip_folder'    => It::t('home_schedule', 'dest_param_ftp_folder'),
         );
     }    
    
    public function checkDestination()
	{
        if ($this->method === 'mail')
		{
            if (empty($this->destination_email))
			{
                $this->addError('destination_email', 'Destination email address can not be empty.');
                return false;
            }
        }
		else if ($this->method === 'ftp')
		{
            if (!$this->destination_ip_port)
			{
				$this->addError('destination_ip_port', 'Destination ip port can\'t be empty.');
            }
            
            if (!$this->destination_ip_folder)
			{
				$this->destination_ip_folder = '';
            }
			
            //$this->destination_ip_folder = '//'. $this->destination_ip_folder;
            $res = explode('/', $this->destination_ip_folder);
            
            $str = '';
            
			if ($res)
			{
                foreach ($res as $value)
				{
                    if ($value)
					{
                        $str .=  $value.'/';
                    }
                }
            }
            
//			$this->destination_ip_folder = $str ? $str : '/';
			$this->destination_ip_folder = $str ;

            $long = ip2long($this->destination_ip);
            
            if ($long == -1 || $long === false)
			{
                $this->addError('destination_ip', 'Destination ip address is invalid.');
                return false;                
            }
        }
		else if ($this->method === 'local_folder')
		{
			$matches = array();
			
            if (!preg_match('/^[A-Z,a-z,0-9,_,\.\s,-]{0,255}$/', $this->destination_local_folder, $matches))
			{    
                $this->addError('destination_local_folder', 'Destination Local Folder is invalid.');
                return false;                 
            }

			$criteria = new CDbCriteria();
			
			$criteria->compare('destination_local_folder', $this->destination_local_folder);
			
			if (!$this->isNewRecord)
			{
                $criteria->addNotInCondition('ex_schedule_destination_id',[$this->ex_schedule_destination_id]);
			}
			
			$destinations = ScheduleTypeReportDestination::model()->findAll($criteria);
			
            if (count($destinations) > 0)
			{
                $this->addError('destination_local_folder', 'Such folder is already used.');
                return false;                  
            }
			
            if (!$this->getErrors())
			{
                $scheduleTypeReportProcessed = new ScheduleTypeReportProcessed;
				$path = $scheduleTypeReportProcessed->getFileDir() . DIRECTORY_SEPARATOR . $this->destination_local_folder;
				
                if (!file_exists($path))
				{
                    if (@mkdir($path, 0777, true) === false)
					{
                        $this->addError('destination_local_folder', 'Can\'t create directory.');
                        return false;                     
                    }
                }                
            }
        }
    }

    public function beforeValidate()
	{
        $this->destination_local_folder = $this->method === 'local_folder' ? $this->destination_local_folder : '';
        $this->destination_ip           = $this->method === 'ftp' ? $this->destination_ip : '';
        $this->destination_ip_port      = $this->method === 'ftp' ? $this->destination_ip_port : 21;
        $this->destination_ip_user      = $this->method === 'ftp' ? $this->destination_ip_user : '';
        $this->destination_ip_password  = $this->method === 'ftp' ? $this->destination_ip_password : '';
        $this->destination_ip_folder    = $this->method === 'ftp' ? $this->destination_ip_folder : '';
        $this->destination_email        = $this->method === 'mail' ? $this->destination_email : '';
        
        foreach($this->attributes as $key => $value)
		{
			$this->$key = trim($value);
        }
        
        return parent::beforeValidate();
    }    
	
    public static function getList($ex_schedule_id)
	{
    	return ScheduleTypeReportDestination::model()->findAllByAttributes(array('ex_schedule_id' => $ex_schedule_id));
    }
    
    public static function getTypes()
	{
        return array(
            'mail'         => It::t('home_schedule', 'type_mail'),
            'ftp'          => It::t('home_schedule', 'type_ftp'),
            //'local_folder' => It::t('home_schedule', 'type_folder'),
        );
    }

    public function addSuffixToLocalFolder($suffix)
    {
        if ($this->isNewRecord) {
            $this->destination_local_folder .= $suffix;
            return true;
        }
        else {
            return false;
        }
    }

    public function generateUID()
    {

        if ($this->method == 'mail') {
            $this->uid =  $this->method.'::'. $this->destination_email;
        } else if ($this->method == 'ftp') {
            $this->uid =  $this->method.'::'. $this->destination_ip.':'.$this->destination_ip_port.'/'.$this->destination_ip_folder;
        } else if ($this->method == 'local_folder') {
            $this->uid =  $this->method.'::'. $this->destination_local_folder;
        }

    }

    public static function parseUid($uid)
    {
        if (preg_match("/^([0-9a-zA-Z_]+)([:]{2})([a-zA-Z_.\-0-9@]*)[:]*([0-9]*)([\/]*)([a-z-A-Z\-_0-9\/]*)$/i",$uid,$matches)) {

            /*
             Array
                    (
                        [0] => ftp::192.168.101.202:22/asd/asd/asd
                        [1] => ftp
                        [2] => ::
                        [3] => 192.168.101.202
                        [4] => 22
                        [5] => /
                        [6] => asd/asd/asd
                    )
             */
            switch ($matches[1]) {
                case 'mail':
                    return array('method'=>$matches[1], 'destination_email'=>$matches[3]);
                    break;
                case 'ftp':
                    return array(
                                    'method'=>$matches[1],
                                    'destination_ip'=>$matches[3],
                                    'destination_ip_port'=>$matches[4],
                                    'destination_ip_folder'=>$matches[6],
                    );
                    break;
                case 'local_folder':
                    return array(   'method'=>$matches[1],
                                    'destination_local_folder'=>$matches[3],
                    );
                    break;
            }
        }
        return false;
    }

    public function getUid()
    {
        return $this->uid;
    }

    protected function generateAddressName()
    {
        if ($this->method == 'mail') {
            $this->address_name =  $this->method.' '. $this->destination_email;
        } else if ($this->method == 'ftp') {
            $this->address_name =  $this->method.'://'. $this->destination_ip.':'.$this->destination_ip_port.'/'.$this->destination_ip_folder;
        } else if ($this->method == 'local_folder') {
            $this->address_name =  'local folder: '. $this->destination_local_folder;
        }
    }


}