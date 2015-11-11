<?php

class CheckMailingForm extends CFormModel
{
    public $email;
    public $subject = 'Test Subject';
    public $message = 'Test Message';

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        return array(
            array('email', 'email'),
            array('subject,message,email', 'required'),
            array('subject,message', 'length', 'max' => 250),
        );
    }

    public function send()
    {
        return It::sendLetter($this->email, $this->subject, $this->message, array());
    }
}
