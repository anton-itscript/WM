<?php


/**
 * Help script. Purpose: re-run listening if process was stopped by such reasons as power off or reboot.
 */
class CheckProcessesCommand extends CConsoleCommand
{
    public function run($args)
	{
		$logger = LoggerFactory::getFileLogger('check_processes');
		
		$logger->log(__METHOD__ . ' Start checkprocesses command.');

        $criteria = new CDbCriteria();
		$criteria->compare('stopped', 0);
		
		$connections = Listener::model()->findAll($criteria);
		
		$logger->log(__METHOD__ . ' Found '. count($connections) .' connections.');
		
		foreach ($connections as $connection)
		{
			$logger->log(__METHOD__ .' Listener info:', array('process_pid' => $connection->process_pid, 'listener_id' => $connection->listener_id, 'source' => $connection->source));
			
			if (ProcessPid::isActiveProcess($connection->process_pid) === false)
			{
				ListenerProcess::addComment($connection->listener_id, 'comment', 'System found out that process is not active any more. Process will be re-run right now.');
				
				Listener::stopConnection($connection->listener_id, time());
				Listener::runConnection($connection->source, $connection->additional_param, 'auto');
			}                 
		}
//        ProcessPid::killProcess(12376);

        $synchronization = new Synchronization;
        if ($synchronization->isProcessed() && ProcessPid::isActiveProcess($synchronization->getTcpServerPid()) === false) {
            $synchronization->startTcpServer();
        }

        if ($synchronization->isProcessed() && ProcessPid::isActiveProcess($synchronization->getTcpClientPid()) === false) {
            $synchronization->startTcpClient();
        }

    }
}