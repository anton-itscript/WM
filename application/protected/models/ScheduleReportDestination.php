<?php

/**
 * Destiantion of created report.
 * Possible destinations: mail, ftp, local folder.
 * 
 * @author
 */
class ScheduleReportDestination extends CStubActiveRecord
{
    public static function model($className=__CLASS__)
	{
        return parent::model($className);
    }

    public function tableName()
	{
        return 'schedule_report_destination';
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
				$this->destination_ip_folder = '/';
            }
			
            $this->destination_ip_folder = '//'. $this->destination_ip_folder;
            $res = explode('/', $this->destination_ip_folder);
            
            $str = '';
            
			if ($res)
			{
                foreach ($res as $value)
				{
                    if ($value)
					{
                        $str .= '/'. $value;
                    }
                }
            }
            
			$this->destination_ip_folder = $str ? $str : '/';
            
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
			
            if (!preg_match('/^[A-Z,a-z,0-9,_,\s,-]{0,255}$/', $this->destination_local_folder, $matches))
			{    
                $this->addError('destination_local_folder', 'Destination Local Folder is invalid.');
                return false;                 
            }

			$criteria = new CDbCriteria();
			
			$criteria->compare('destination_local_folder', $this->destination_local_folder);
			
			if (!$this->isNewRecord)
			{
                $criteria->addNotInCondition('schedule_destination_id',[$this->schedule_destination_id]);
			}
			
			$destinations = ScheduleReportDestination::model()->findAll($criteria);
			
            if (count($destinations) > 0)
			{
                $this->addError('destination_local_folder', 'Such folder is already used.');
                return false;                  
            }
			
            if (!$this->getErrors())
			{
				$path = Yii::app()->user->getSetting('scheduled_reports_path') . DIRECTORY_SEPARATOR . $this->destination_local_folder;
				
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
	
    public static function getList($schedule_id)
	{
    	return ScheduleReportDestination::model()->findAllByAttributes(array('schedule_id' => $schedule_id));
    }
    
    public static function getTypes()
	{
        return array(
            'mail'         => It::t('home_schedule', 'type_mail'),
            'ftp'          => It::t('home_schedule', 'type_ftp'),
            'local_folder' => It::t('home_schedule', 'type_folder'), 
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
}