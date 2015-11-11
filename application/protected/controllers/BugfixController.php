<?php

class BugfixController extends CController
{
    function actionIndex()
    {
        ini_set('memory_limit', '-1');
        print 'Checking `listener_log` table upgrade....<br/>';
        $sql = "SHOW COLUMNS FROM `".ListenerLog::model()->tableName()."` LIKE 'is_last'";
        $res =  Yii::app()->db->createCommand($sql)->queryAll();
        
        if (!$res) {
            print '<br/><br/>Checked - requires update.<br/><br/>Attention! Script going to make small update to your database.... Please, be patient, don\'t use system before script is completed.';
            $sql = "ALTER TABLE `".ListenerLog::model()->tableName()."` ADD `is_last` tinyint(1) NOT NULL DEFAULT '0' AFTER `is_processed`";
            Yii::app()->db->createCommand($sql)->query();
            
            $sql = "SELECT * FROM `".Station::model()->tableName()."`";
            $stations = Yii::app()->db->createCommand($sql)->queryAll();
            
            if ($stations) {
                foreach ($stations as $key => $value) {
                    ListenerLog::updateIsLastForStation($value['station_id']);
                }
            }
            
            print '<br><br>.....<br><br>Done!. <br/><br/>You can continue work with <a href="'.It::baseUrl().'">Delairco</a>';
        } else {
            print '<br/>Checked - doesn\'t require update. <br/><br/>You can continue work with <a href="'.It::baseUrl().'">Delairco</a>';
        }


    }
    
    function actionCheckComtool() {

        if (class_exists('COM') ) {
            
            try {
            $objComport	= new COM ( "ActiveXperts.Comport" );

            $objComport->Logfile     = "C:\\PhpSerialLog.txt";
            $objComport->Device      = "COM1";
            $objComport->Baudrate    = 9600;
            $objComport->ComTimeout  = 1000;

            $objComport->Open ();

            print '<br>Check errros of COMport tool using (trying to connect with COM1)';
            if ($objComport->LastError != 0) {

                if ($objComport->LastError >= 1000 &&  $objComport->LastError <= 1999) {
                    print '<br>LICENSING ERROR!!!';
                } else {
                    print '<br>no licensing errors, some errors with com-port connection';
                }
                $ErrorNum = $objComport->LastError;
                $ErrorDes = $objComport->GetErrorDescription ( $ErrorNum );

                Echo "<br><br>Error sending commands: #$ErrorNum ($ErrorDes).";       
            }
            } catch (Exception $e) {
                print_r($e->getMessage());
            } 

            $objComport->Close ();      
        } else {
            print "Class for work with COM ports is not available";
        }
    }
    public function filters()
    {
        return array('accessControl');
    }

    public function accessRules(){
        return array(
            array('deny',
                  'actions' => array(),
            )
        );
    }


}