<?php

class HeartbeatReportForm extends CFormModel{
        const optionName = 'HEARTBEAT_REPORT';

        static public $periodArray = array(
//            'T' => '1 min(test)',
            'd' => '1 day',
            'w' => '1 week',
            'm' => '1 month '
        );
        static public $periodDescription = array(
            'd' => 'sends a heart beat every day at 0:00h UTC',
            'w' => 'sends a heart beat every Sunday at 0:00h UTC',
            'm' => 'sends a heart beat every month at 0:00h UTC on the first day of the new month',
        );

        /*
         * Parameters
         */
        public $period;
        public $clientName;
        /*
         * for view
         */
        public $status;
        public $email;
        public $newEMail;

        /*
         * for ftp
         */
        public $ftp;
        public $ftpDir;
        public $ftpUser;
        public $ftpPassword;
        public $ftpPort;

        public function rules()
        {
            return array(
                array('status, period, clientName', 'safe'),
                array('newEMail, ftp, ftpDir, ftpUser, ftpPassword, ftpPort', 'safe'),
                array('newEMail', 'email','allowEmpty'=> true, 'on'=>'Add'),
                array('newEMail', 'checkEmail', 'on'=>'Add'),

            );
        }

        public function checkEmail()
        {
            if (!$this->hasErrors('newEMail')){
                if(!array_search($this->newEMail,$this->email)){
                    return true;
                }
                $this->addError('newEMail', 'This email exists');
                return false;
            } else {
                return false;
            }

        }

        public function update()
        {
            switch($this->scenario){
                case 'Save':
                    Config::set(self::optionName.'_PERIOD',$this->period);
                    Config::set(self::optionName.'_EMAIL',$this->email);
                    Config::set(self::optionName.'_FTP',$this->ftp);
                    Config::set(self::optionName.'_FTP_PORT',$this->ftpPort);
                    Config::set(self::optionName.'_FTP_DIR',$this->ftpDir);
                    Config::set(self::optionName.'_FTP_USER',$this->ftpUser);
                    Config::set(self::optionName.'_FTP_PASSWORD',$this->ftpPassword);
                    break;
                case 'Start':
                    $this->status = 1;
                    Config::set(self::optionName.'_STATUS',$this->status);
                    break;
                case 'Stop':
                    $this->status = 0;
                    Config::set(self::optionName.'_STATUS',$this->status);
                    break;
                case 'Add':
                    $this->email[]=$this->newEMail;
                    Config::set(self::optionName.'_EMAIL',$this->email);
                    break;
                case 'Delete':
                    Config::set(self::optionName.'_EMAIL',$this->email);
                    break;
            }
        }
        private function addDefault()
        {
            Config::edit(self::optionName.'_STATUS','0','bool','status');
            Config::edit(self::optionName.'_EMAIL',array(),'array','report to');

            Config::edit(self::optionName.'_PERIOD','d','char','period');
            Config::edit(self::optionName.'_CLIENT_NAME','CLIENT','string','email client name');
        }

        public function init()
        {
//            $this->addDefault();
            $param = Config::get(self::optionName);

            $this->period       = $param[self::optionName.'_PERIOD']->value;
            $this->clientName   = $param[self::optionName.'_CLIENT_NAME']->value;

            $this->status = $param[self::optionName.'_STATUS']->value;
            $this->email  = $param[self::optionName.'_EMAIL']->value;

            $this->ftp  = $param[self::optionName.'_FTP']->value;
            $this->ftpPort  = $param[self::optionName.'_FTP_PORT']->value;
            $this->ftpDir  = $param[self::optionName.'_FTP_DIR']->value;
            $this->ftpUser  = $param[self::optionName.'_FTP_USER']->value;
            $this->ftpPassword  = $param[self::optionName.'_FTP_PASSWORD']->value;

            return parent::init();
        }
	}
?>