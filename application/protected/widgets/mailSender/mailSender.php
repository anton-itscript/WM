<?php
	

class mailSender
{
    public $view;
    public $dataArray;


    public $recipient;
    public $from;
    public $from_name;

    public $subject;
    public $body;
    public $attachments;

    public $html=false;
    public $char_set='UTF-8';

    public function __construct($view,$dataArray)
	{
        $this->view = $view;
        $this->dataArray = $dataArray;

    }

    public function setCharSet($char_set)
    {
        //todo validation
        $this->char_set = $char_set;

        return $this;
    }

    public function setHtmlBody()
    {
        $this->html=true;
        return $this;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function setFrom($form,$from_name)
    {
        $this->from         = $form;
        $this->from_name    = $from_name;
        return $this;
    }

    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }

	public function send()
	{
//		$this->body = (new CController('__MAIL__'))->renderPartial( Yii::app()->basePath .'application.widgets.mailSender.views.'.$this->view,$this->dataArray, true);
		$this->body = CBaseController::renderInternal( Yii::app()->basePath .'/widgets/mailSender/views/' .$this->view.'.php',$this->dataArray, true);

        $mailer = Yii::createComponent('application.extensions.mailer.EMailer');

        $mailer->From     = $this->from;
        $mailer->FromName = $this->from_name;
        if (is_array($this->recipient)) {
            foreach ($this->recipient as $key => $recipient) {
                $mailer->AddAddress($recipient);
            }
        } elseif (is_string($this->recipient)) {
            $mailer->AddAddress($this->recipient);
        }
        $mailer->isHTML($this->html);
        $mailer->Subject  = $this->subject;
        $mailer->Body     = $this->body;
        $mailer->CharSet  = 'UTF-8';

        if ($this->attachments) {
            foreach ($this->attachments as $key => $attachment) {
                $mailer->AddAttachment($attachment['file_path'], $attachment['file_name']);
            }
        }

        $result = $mailer->Send();
        return $result;
    }
	

	
	

}