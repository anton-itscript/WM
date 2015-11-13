<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 21.07.2015
 * Time: 18:44
 */

abstract class Exchange {

    protected $message;
    protected $messageIdent;
    protected $messageData;
    protected $errors;
    protected $ident = '';
    protected $step = '01';

    protected $closeConnection = 0;

    public abstract function clientMessage();
    public abstract function serverMessage();






    public function init($message)
    {
        $this->errors = array();
        $this->loadMessage($message);
        $this->parseMessage();
        if ($this->isIdentificationMessage() && !$this->isFailMessage()) {
            return true;
        } else {
            return false;
        }
    }

    public function returnReceivedMessage()
    {
        return $this->message;
    }

    public function loadMessage($message)
    {
        $this->message = $message;
    }

    public function parseMessage()
    {
        $unserializeMessage =  @unserialize($this->message);
        if ($unserializeMessage===false) {
            $this->errors['unserialize'] = "unserilize failed";
        } else {
            if(empty($unserializeMessage['messageIdent']) or !isset($unserializeMessage['messageIdent'])) {
                $this->errors['messageIdent'] = "messageIdent is empty";
            } else {
                $this->messageIdent =  $unserializeMessage['messageIdent'];
            }
            if(empty($unserializeMessage['messageData']) or !isset($unserializeMessage['messageData'])) {
                $this->errors['messageData'] = "messageData is empty";
            } else {
                $this->messageData =  $unserializeMessage['messageData'];
            }
            if(empty($unserializeMessage['step']) or !isset($unserializeMessage['step'])) {
                $this->errors['step'] = "step is empty";
            } else {
                $this->step =  $unserializeMessage['step'];
            }
            if(!isset($unserializeMessage['closeConnection'])) {
                $this->errors['closeConnection'] = "closeConnection does not exists";
            } else {
                $this->closeConnection =  $unserializeMessage['closeConnection'];
            }
        }

    }

    public function isIdentificationMessage()
    {
        if($this->messageIdent == $this->ident)
            return true;
        return false;
    }

    protected function isFailMessage()
    {
        if(count($this->errors)>0)
            return true;
        return false;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function closeConnection()
    {
        if ($this->closeConnection) {
            $this->step = '01';
            return true;
        }
        return false;
    }

    /**\
     * @param $step string
     * @param $closeConnection bool true|false
     * @param $messageData mixed
     */
    public function createClientMessage($step, $closeConnection, $messageData)
    {
        $this->clientMessage = array('messageIdent'=>$this->ident, 'step'=>$step, 'closeConnection'=>$closeConnection, 'messageData'=>$messageData);
    }

    /**\
     * @param $step string
     * @param $closeConnection bool true|false
     * @param $messageData mixed
     */
    public function createServerMessage($step, $closeConnection, $messageData)
    {
        $this->serverMessage = array('messageIdent'=>$this->ident, 'step'=>$step, 'closeConnection'=>$closeConnection, 'messageData'=>$messageData);
    }

} 