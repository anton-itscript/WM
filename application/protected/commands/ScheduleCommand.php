<?php

/*
 * Is called using command: "php console.php schedule", doesn't requires arguements
 * Is called every minute using schtasks
 * 
 * This console script is looking for scheduled reports that 
 * have to be generated at the moment.
 */

class ScheduleCommand extends CConsoleCommand
{
	/**
	 * Logger.
	 * 
	 * @access protected
	 * @var ILogger
	 */
	protected  $logger = null;

	public function init()
	{
		parent::init();
		
		ini_set('memory_limit', '-1');
		set_time_limit(0);

		// All reports are generated basing on data in UTC time.
        TimezoneWork::set('UTC');
	}
	
    public function run($args)
    {

        $this->logger = LoggerFactory::getFileLogger('scheduleReports');
//        $this->logger = LoggerFactory::getConsoleLogger();
        new ScheduleReports($this->logger, $args);

        if(!Yii::app()->mutex->lock('ScheduleTypeReports',3600)) {
            Yii::app()->end();
        }
        $this->logger = LoggerFactory::getFileLogger('typesReports');
        new ScheduleTypeReports($this->logger, $args);
        Yii::app()->mutex->unlock();
    }
}

?>