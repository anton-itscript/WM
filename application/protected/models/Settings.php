<?php

class Settings extends CStubActiveRecord 
{
    public static function model($className=__CLASS__) 
	{
		return parent::model($className);
    }

    public function tableName()
	{
		return 'settings';
    }

    public function rules()
	{
		$res =  array(
            array('overwrite_data_on_import,overwrite_data_on_listening', 'boolean', 'falseValue' => 0, 'trueValue' => 1, 'on' => 'other'),
            array('current_company_name', 'required', 'on' => 'other'),
            array('current_company_name', 'length', 'max' => 50, 'allowEmpty' => false, 'on' => 'other'),
           // array('scheduled_reports_path,xml_messages_path', 'length', 'max' => 255, 'allowEmpty' => false, 'on' => 'other'),
           // array('scheduled_reports_path', 'checkScheduledReportsPath', 'on' => 'other'),
            array('xml_check_frequency', 'numerical', 'integerOnly' => true, 'on' => 'other'),
            array('local_timezone_id', 'length', 'allowEmpty' => false, 'on' => 'other'),
            
            array('mail__use_fake_sendmail', 'boolean', 'allowEmpty' => false, 'trueValue' => 1, 'falseValue' => 0, 'on' => 'mail'),
            array('mail__sender_address', 'email', 'allowEmpty' => false, 'on' => 'mail'),
            array('mail__sender_address,mail__sender_name,mail__sender_password,mail__smtp_server', 'length', 'max' => 255, 'allowEmpty' => false, 'on' => 'mail'),
            array('mail__smtp_port', 'numerical', 'integerOnly' => true, 'on' => 'mail'),
            array('mail__smtps_support', 'in', 'range' => array( 'auto', 'ssl', 'tls', 'none'), 'allowEmpty' => false, 'on' => 'mail'),
            
            array('db_exp_enabled',      'boolean',         'allowEmpty' => false, 'trueValue' => 1, 'falseValue' => 0, 'on' => 'dbexport'),
            array('db_exp_period',       'numerical',       'integerOnly' => true, 'on' => 'dbexport'),
            array('db_exp_frequency',    'numerical',       'integerOnly' => true, 'on' => 'dbexport'),
            array('db_exp_sql_host',     'checkHost',       'on' => 'dbexport'),
            array('db_exp_sql_host',     'checkHostExists', 'on' => 'dbexport'),   
            array('db_exp_sql_port',     'numerical',       'integerOnly' => true, 'allowEmpty' => false, 'min' => 1, 'on' => 'dbexport'),
            array('db_exp_sql_dbname',   'match',           'pattern' => '/^[A-Z,a-z,0-9,_,-]{0,30}$/', 'on' => 'dbexport'),
            array('db_exp_sql_dbname,db_exp_sql_login',   'required', 'on' => 'dbexport'),
            array('db_exp_sql_dbname,db_exp_sql_login',   'length',   'allowEmpty' => false, 'max' => 255, 'on' => 'dbexport'),
            array('db_exp_sql_password', 'checkUser',       'on' => 'dbexport'),
        );
		
        if (It::isLinux()) {
            $pattern = '/^[\/]([A-Za-z0-9-_\s\/\.]){1,251}$/';
            //$res[] = array('scheduled_reports_path, xml_messages_path', 'match', 'pattern' => $pattern, 'on' => 'other');
        } elseif (It::isWindows()) {
            $pattern = '/^([A-Z]{1})(:\\\)([A-Za-z0-9-_\s\.]+[\\\]?){1,251}$/';
            $res[] = array('scheduled_reports_path, xml_messages_path', 'match', 'pattern' => $pattern, 'on' => 'other');
        }
        
        return $res;
    }

    
    public function checkHost()
	{
        if (!$this->hasErrors('db_exp_sql_host'))
		{
             if ($this->db_exp_sql_host == 'localhost') 
                 return true;

             if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $this->db_exp_sql_host)) 
                 return true;
             
             if (preg_match('/^[a-zA-Z-\d]+(?:\.[a-zA-Z-\d]+)$/', $this->db_exp_sql_host))
                 return true;

            $this->addError('db_exp_sql_host', 'Incorrent host value');
            
			return false;    
        }
     
        return true;
    }
    
    public function checkHostExists()
	{
        if (!$this->hasErrors('db_exp_sql_host') && !$this->hasErrors('db_exp_sql_port'))
		{  
            $fp = @fsockopen($this->db_exp_sql_host, $this->db_exp_sql_port);
            
			if($fp === false)
			{
                $this->addError('db_exp_sql_host', 'This host is unreachable');
                
				return false;                
            }
         }
		 
         return true;
    }   
	
    public function checkUser()
	{
        if ($this->hasErrors('db_exp_sql_host') || 
			$this->hasErrors('db_exp_sql_dbname') || 
			$this->hasErrors('db_exp_sql_login') || 
			$this->hasErrors('db_exp_sql_password') || 
			$this->hasErrors('db_exp_sql_port')) 
		{
        	return false;
        }
		
        try
		{
            $connection = new CDbConnection('mysql:host='. $this->db_exp_sql_host, $this->db_exp_sql_login, $this->db_exp_sql_password);
            $res = $connection->setActive(true);
            
            $sql = "CREATE DATABASE IF NOT EXISTS `{$this->db_exp_sql_dbname}` CHARACTER SET UTF8";
            $res = $connection->createCommand($sql)->query();            
        } 
		catch (CDbException $e)
		{
            $this->addError('db_exp_sql_login', $e->getMessage());
            
			return false;
       }
	   
       return true;
    }    
    
    public function checkScheduledReportsPath()
	{
        /*
        if (!preg_match('/^([A-Z]{1})(:\\\)([A-Za-z0-9-_\s]+[\\\]?){1,251}$/', $this->scheduled_reports_path, $matches)) {    
            $this->addError('scheduled_reports_path', 'Scheduled Reports Path is invalid.');
            return false;
        }
        */
        if (!$this->getErrors())
		{
            $this->scheduled_reports_path = rtrim($this->scheduled_reports_path, DIRECTORY_SEPARATOR);
            
            if (!file_exists($this->scheduled_reports_path))
			{
                if (@mkdir($this->scheduled_reports_path, 0777, true) === false)
				{
                    $this->addError('scheduled_reports_path', 'Can not create directory.');
                    
					return false;
                }
            }
        }
        return true;
    }

    public function attributeLabels()
	{
        return array (
            'overwrite_data_on_import'    => Yii::t('project', 'Overwrite data on import'),
            'overwrite_data_on_listening' => Yii::t('project', 'Overwrite data on listening'),
            'mail__sender_address'        => Yii::t('project', 'Sender Address'),
            'mail__sender_name'           => Yii::t('project', 'Sender Name'),
            'mail__sender_password'       => Yii::t('project', 'Account Password'),
            'mail__smtp_server'           => Yii::t('project', 'SMTP Server'),
            'mail__smtp_port'             => Yii::t('project', 'SMTP Port'),
            'mail__smtps_support'         => Yii::t('project', 'SMTPS (SSL) Support'),
            'local_timezone_id'           => Yii::t('project', 'Local Timezone'),
            'db_exp_enabled'              => Yii::t('project', 'Export old data to a backup database.'),
            'db_exp_period'               => Yii::t('project', 'Minimum period with data'),
            'db_exp_frequency'            => Yii::t('project', 'Export frequency'),
            'db_exp_sql_host'             => Yii::t('project', 'MySQL Host'),
            'db_exp_sql_port'             => Yii::t('project', 'MySQL Port'),
            'db_exp_sql_dbname'           => Yii::t('project', 'MySQL DBName'),
            'db_exp_sql_login'            => Yii::t('project', 'MySQL Login'),
            'db_exp_sql_password'         => Yii::t('project', 'MySQL Password'),
        );
    }
    
    public function beforeValidate()
	{
        foreach($this->attributes as $key => $value)
		{
            $this->$key = trim($value);
        }
        
        if ($this->scenario == 'dbexport')
		{
            $this->db_exp_sql_host     = $this->db_exp_enabled ? $this->db_exp_sql_host : '';
            $this->db_exp_sql_port     = $this->db_exp_enabled ? $this->db_exp_sql_port : '';
            $this->db_exp_sql_dbname   = $this->db_exp_enabled ? $this->db_exp_sql_dbname : '';
            $this->db_exp_sql_login    = $this->db_exp_enabled ? $this->db_exp_sql_login : '';
            $this->db_exp_sql_password = $this->db_exp_enabled ? $this->db_exp_sql_password : '';       
        }
		
        return parent::beforeValidate();
    }    
    
    public function beforeSave()
	{
        $this->local_timezone_offset = TimezoneWork::getOffsetFromUTC($this->local_timezone_id, 1);
        
		return parent::beforeSave();
    }
    
    public function afterSave()
	{
        if ($this->scenario == 'mail')
		{
            if ($this->mail__use_fake_sendmail)
			{
                $sendmail_ini_path = dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR."sendmail".DIRECTORY_SEPARATOR."sendmail.ini";
                
                $values = parse_ini_file($sendmail_ini_path, true);
                
                $values['sendmail']['smtp_server'] = $this->mail__smtp_server;
                $values['sendmail']['smtp_port'] = $this->mail__smtp_port;
                $values['sendmail']['smtp_ssl'] = $this->mail__smtps_support;
                $values['sendmail']['auth_username'] = $this->mail__sender_address;
                $values['sendmail']['auth_password'] = $this->mail__sender_password;
                
                InstallConfig::setConfigSection('sendmail', $values['sendmail'], $sendmail_ini_path);
            }
        }
		
        parent::afterSave();
    }
}