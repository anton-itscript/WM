<?php

/*
 * Is called using command: "php console.php listen ARG1 ARG2"
 * starts running when Admin clicks START button. 
 * Stops running when Admin clicks STOP button. 
 * Is continuous process, auto-restarts when connection was broken (for TCP/IP)
 * 
 * This is process of listening COM ports or ESP hardware. Script listens for 
 * new messages and puts them into database as unprocessed messages.
 * These messages are processed further by prepareCommand
 */

class ListenCommand extends CConsoleCommand
{
    public function init()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        parent::init();
    }

    // $args[0] = port name
    // $args[1] = who has started
    public function run($args)
    {
        if (empty($args[0])) {
            exit();
        }

        $logger = LoggerFactory::getFileLogger('listener/'.$args[0]);
//        $logger = LoggerFactory::getConsoleLogger();
        $logger->log(__METHOD__ . ' args:' . print_r($args,1));
        // creates object of ProcessListen class, which duty is listening
        try {
            (new ProcessListen($logger, $args[0], $args[1], $args[2]))->run();
        } catch (Exception $e) {
            It::sendLetter(Yii::app()->params['developer_email'], 'Problem', $e->getMessage());
        }
    }
}