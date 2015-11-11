<?php
/*
 * Generate Heartbeat Report
 * Save report in DB
 * Send email with attachment .xls
 */
class HeartbeatReportCommand extends CConsoleCommand
{
    // log
    protected $_logger = null;
    // Option
    private $status;
    private $email;
    private $period;
    private $clientName;
    // report
    private $report;
    // start time
    private $now;

    private $ftp;
    private $ftpPort;
    private $ftpUser;
    private $ftpFolder;
    private $ftpPassword;

    /**
     * Ini system param
     */
    public function init()
    {
        parent::init();

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $this->_logger = LoggerFactory::getFileLogger('process_heartbeat_report');

    }

    /**
     * Set option from Config DB
     * @return bool
     */
    private function setOptions()
    {
        $this->_logger->log(__METHOD__);
        try {
            $param = Config::get('HEARTBEAT_REPORT');

            $this->status       = $param['HEARTBEAT_REPORT_STATUS']->value;
            $this->email        = $param['HEARTBEAT_REPORT_EMAIL']->value;
            $this->period       = $param['HEARTBEAT_REPORT_PERIOD']->value;
            $this->clientName   = $param['HEARTBEAT_REPORT_CLIENT_NAME']->value;
            $this->ftp          = $param['HEARTBEAT_REPORT_FTP']->value;
            $this->ftpPort      = $param['HEARTBEAT_REPORT_FTP_PORT']->value;
            $this->ftpFolder    = $param['HEARTBEAT_REPORT_FTP_DIR']->value;
            $this->ftpUser      = $param['HEARTBEAT_REPORT_FTP_USER']->value;
            $this->ftpPassword  = $param['HEARTBEAT_REPORT_FTP_PASSWORD']->value;

            return true;
        } catch (Exception $e) {
            $this->_logger->log(__METHOD__ . "ERROR: ",$e->getMessage());
            return false;
        }
    }

    /**
     * Check class param
     * @return bool
     */
    private function checkOption()
    {
        $this->_logger->log(__METHOD__);
        return !(!$this->status || is_null($this->email) || is_null($this->period) || is_null($this->clientName));
    }

    /**
     * Check date cron
     * @return bool
     */
    private function checkDate()
    { 
        $this->_logger->log(__METHOD__);
        date_default_timezone_set("UTC");
        $this->now = $now = getdate();

        switch ($this->period) {
            case 'T':
                return true;
            case 'd':
                if ($now['hours'] == 0 && $now['minutes'] == 0){
                    return true;
                }
                return false;
            case 'w':
                if ($now['wday'] == 1 && $now['hours'] == 0 && $now['minutes'] == 0){
                    return true;
                }
                return false;
            case 'm':
                if ($now['mday'] == 1 && $now['hours'] == 0 && $now['minutes'] == 0){
                    return true;
                }
                return false;
            default:
                return false;
        }
    }

    /**
     * Main function
     * @return bool
     */
    private function work()
    {
        $this->_logger->log(__METHOD__);
        try {
            $this->report = HeartbeatReport::getReport(HeartbeatReport::create($this->period));
            $stat = new GetStatistics($this->report);

            if(!$stat->push($this->report->report_id)){
                $this->report->status('push_err');
                return false;
            }

            $excel_path = $stat->saveExcel($this->report);
            if($excel_path === false){
                $this->report->status('exel_err');
                return false;
            }

            $this->report->status('sending');
            if($excel_path !== false && !$this->emailSend($excel_path)){
                $this->report->status('email_err');
                return false;
            }

            if($excel_path !== false && !$this->ftpSend($excel_path)){
                $this->report->ftpStatus('ftp_err');
                return false;
            }

            return true;
        } catch (Exception $e) {
            $this->_logger->log(__METHOD__ . "ERROR: ",$e->getMessage());
            return false;
        }
    }

    /**
     * Send email messages with attachment $excel_path
     * @param $excel_path
     * @return bool
     */
    private function emailSend($excel_path)
    {
        $this->_logger->log(__METHOD__);

        $time = $this->now['mday'].' '.$this->now['month'].' '.$this->now['year'];

        $file_name = "HeartbeatReport, $time.xls";
        $title = "Heartbeat $this->clientName, $time";
        $body  = "Heartbeat $this->clientName, $time,<br>
                  Attached is the heartbeat report from $this->clientName, $time,<br>
                  Sincerely,<br>
                  Delairco Japan KK";

        $flag = true;

        try{
            foreach($this->email as $email){
                $flag = $flag && It::sendLetter($email,$title,$body, array(0 => array('file_path' => $excel_path, 'file_name' => $file_name)));
            }
        } catch(Exception $e) {
            $this->_logger->log(__METHOD__ . "ERROR: ",$e->getMessage());
            return false;
        }

        return $flag;
    }

    private function ftpSend($excel_path)
    {
        $this->_logger->log(__METHOD__);
        $time = $this->now['mday'].' '.$this->now['month'].' '.$this->now['year'];
        $file_name = "HeartbeatReport-$time.xls";

        $ftpClient =new FtpClient();
        $errors = $ftpClient->connect($this->ftp,$this->ftpPort)
                            ->login($this->ftpUser,$this->ftpPassword)
                            ->setFolder($this->ftpFolder)
                            ->openLocalFile($excel_path)
                            ->upload($file_name)
                            ->closeLocalFile()
                            ->getErrors();

        $this->_logger->log(__METHOD__ . "ERROR: " . print_r($errors,1));
    }
    /**
     * Start process
     * @param array $arg
     * @return int|void
     */
    public function run($arg)
    {
        $this->_logger->log(__METHOD__);
        if(Yii::app()->mutex->lock('heartbeat_report',600)){
            if ($this->setOptions() && $this->checkOption()
                && $this->checkDate()
            ) {
                $this->_logger->log(__METHOD__ . 'START HEARTBEAT REPORT');
                if ($this->work()) {
                    $this->report->status('done');
                }
                $this->_logger->log(__METHOD__ . 'END HEARTBEAT REPORT');
            }
            Yii::app()->mutex->unlock();
        }
    }
}

?>