<?php

class TestController extends CController
{
    function actionIndex()
    {
        SMSCommand::setResponse('@BAWS01DTOK234567890BV11267558E9F6$');
//        SMSCommand::setResponse('@BAWS01DTFAILE3290CA6$');

    }

    public function filters(){
        return array('accessControl');
    }

    public function accessRules(){
        return array(
            array('allow',
                'users' => array('superadmin'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }
}