<?php

/**
 * Class AWSFormat
 */
class AWSFormat extends CFormModel
{

    protected $list_formats = array();
    protected $selected_aws_format;


    public function init()
    {
        $this->selected_aws_format = 1;
        $this->list_formats = self::listFormats();
        parent::init();
    }

    public static function listFormats()
    {
        return array('1'=>'long', '2'=>'short');
    }

    public function getAWSFormat($aws_id)
    {
        if (isset($this->list_formats[$aws_id])) {
            return $this->list_formats[$aws_id];
        }
            return false;
    }

    public function isOldAWSFormat()
    {
        return $this->selected_aws_format ==  1 ? true : false ;
    }

    public function setAWSFormat($aws_id)
    {
        $this->selected_aws_format = $aws_id;
    }

}