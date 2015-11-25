<?php

class SMSCOMPort extends PFileModel
{

    protected $filename__ =   'sms_com_port.json';

    public function initFieldsValues()
    {
        $array = array();
        $array['COM'] = yii::app()->params['com_for_send_sms_command'];
        return $array;
    }

    public static function getLinuxComName($com)
    {
        $comsArray = SysFunc::getAvailableComPortsList();
        return $comsArray[$com];
    }



}