<?php

class TypeReportSendStatus extends PFileModel
{

    protected $filename__ =   'type_report_send_status.json';

    public function initFieldsValues()
    {
        $array = array();
        $array['has_new_report'] = 0;
        return $array;
    }


    public function newReportAdd()
    {
        $this->has_new_report = 1;
        $this->save();
    }

    public function noReport()
    {
        $this->has_new_report = 0;
        $this->save();
    }

    public function hasNewReport()
    {
       return  $this->has_new_report == 1 ? true : false ;
    }

}