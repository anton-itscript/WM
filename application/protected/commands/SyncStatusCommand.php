<?php

/**
 * Class SyncStatusCommand
 */
class SyncStatusCommand extends CConsoleCommand
{
    const SEND_MESSAGE_INTERVAL = 3;
    const WAIT_SECOND_MESSAGES  = 12;

    /** @var  UDPConnector */
    protected $connector;

    /** @var  Logger */
    protected $logger;

    /** @var  array */
    protected $message_features;

    /** @var  int - date in format YmdHi */
    protected $founded_in;

    /** @var  Synchronization */
    protected $settings;

    protected $time_waiting = 3000;
    protected $time_end_waiting = 0;
    protected $time_start = 0;

    public function init()
    {
        parent::init();

        ini_set('memory_limit', '-1');
        set_time_limit(0);
        error_reporting(E_ALL & ~E_WARNING);
        date_default_timezone_set('UTC');

        /**
         * For flexibility system
         */
        $this->founded_in = date('YmdHis');

        /**
         * Logger
         */
        $this->logger = LoggerFactory::getFileLogger('SyncStatusCommand');
//        $this->logger = LoggerFactory::getConsoleLogger();

        /**
         * Settings
         */
        $this->settings = new Synchronization();

        /**
         * Check pid
         */
        if ($this->settings->getSynsStatusCommandPid()
            && ProcessPid::isActiveProcess($this->settings->getSynsStatusCommandPid())
        ) {
            exit;
        } else {
            $this->settings->setSynsStatusCommandPid(getmypid());
        }

        /**
         * Connector
         */
        $server = [
            'ip'   => $this->settings->server_ip,
            'port' => $this->settings->server_port,
        ];
        $client = [
            'ip'   => $this->settings->remote_server_ip,
            'port' => $this->settings->remote_server_port,
        ];
        $this->connector = new UDPConnector($this->logger, $server, $client);

        /**
         * Message
         */
        $this->message_features = [
            SyncStatusHandler::MESSAGE_TYPE => 'S',
            SyncStatusHandler::MESSAGE_FROM => $this->settings->getIdentificator(),
            SyncStatusHandler::FOUNDED_IN   => $this->founded_in,
        ];

    }

    /**
     * @param array|null $args
     *
     * @return int|void
     */
    public function run($args)
    {
        if ($this->connector->getServer() && $this->connector->getClient()) {
            $status_message = SyncStatusHandler::initByFeatures($this->logger, $this->message_features);

            while (true) {
                $this->time_start = microtime(true);
                $for_wait = time();
                /**
                 * Send message
                 */
                if (!isset($status_message->getFeatures()[SyncStatusHandler::WM_STATUS])
                    || $status_message->getFeatures()[SyncStatusHandler::WM_STATUS] != $this->getCurrentStatus()
                ) {
                    $this->message_features[SyncStatusHandler::WM_STATUS] = $this->getCurrentStatus();
                    $status_message = SyncStatusHandler::initByFeatures($this->logger, $this->message_features);
                }
                $this->connector->send($status_message->getMessage());

                /**
                 * Read message
                 */
                $messages = [];
                if ($data = $this->connector->read(self::SEND_MESSAGE_INTERVAL - (time() - $for_wait))) {
                    /** @var SyncStatusHandler[] $messages */
                    $matches = [];
                    if (preg_match_all('(\@.*?\$)',$data['messages'], $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $math) {
                            $messages[] =
                                SyncStatusHandler::initByMessage($this->logger, $math[0])->setFrom($data['from']);
                        }
                    }
                }

                /**
                 * Process messages
                 */

                $this->time_end_waiting += microtime(true) - $this->time_start;
                $this->processMessages($messages);

                sleep(self::SEND_MESSAGE_INTERVAL - (time() - $for_wait));
            }
        }
    }

    /**
     * @param SyncStatusHandler[]|null $messages
     *
     */
    protected function processMessages($messages)
    {

        $this->logger->log(__METHOD__.' role: '.$this->getCurrentStatus() .'; main role: '. $this->getStatus());

        // If empty messages then check last connection
        if (empty($messages)) {

            $this->logger->log(__METHOD__.' messages is empty ');

            $last_read_data = isset($this->connector->getServerParams()['last_read_data'])
                              ? $this->connector->getServerParams()['last_read_data']
                              : null;

            if (!is_null($last_read_data) // Wait first connection
                && $last_read_data < time() - self::WAIT_SECOND_MESSAGES // 1.
                || $this->time_waiting < $this->time_end_waiting
            ) {
                $this->time_end_waiting = 0;
                $this->logger->log('Wait connection '. self::WAIT_SECOND_MESSAGES);

                if ($this->getCurrentStatus() != SyncStatusHandler::WM_STATUS_MASTER) {
                    $this->setCurrentStatus(SyncStatusHandler::WM_STATUS_MASTER);
                    if ($this->isFlexibility()) {
                       // $this->setStatus(SyncStatusHandler::WM_STATUS_MASTER);
                    }
                }
            }

        } else { // Then find last message and his status

            /** @var SyncStatusHandler $message */
            $message = array_pop($messages);
            $status = $message->getFeatures()[SyncStatusHandler::WM_STATUS];


            $this->logger->log('Then find last message and his status: '.$status);
            if ($this->getCurrentStatus() == $status) { // 3.
                if (!$this->isFlexibility()) { // 3.1.
                    if ($this->getCurrentStatus() != $this->getStatus()) { // 3.1.1.
                        $this->setCurrentStatus($this->getStatus());
                        $this->logger->log('it is not Flexibility');
                        $this->logger->log('Set role in: '.$this->getStatus());
                    }  // 3.1.2.
                } else { // 3.2.
                    $this->logger->log('it is  Flexibility');
                    if ($this->founded_in < $message->getFeatures()[SyncStatusHandler::FOUNDED_IN]) { // 3.2.1
                        $this->setCurrentStatus(SyncStatusHandler::WM_STATUS_MASTER);
                       // $this->setStatus(SyncStatusHandler::WM_STATUS_MASTER); // main role
                        $this->logger->log('Set role in: '.SyncStatusHandler::WM_STATUS_MASTER);

                    } elseif ($this->founded_in != $message->getFeatures()[SyncStatusHandler::FOUNDED_IN]) { // 3.2.2
                        $this->setCurrentStatus(SyncStatusHandler::WM_STATUS_SLAVE);
                        //$this->setStatus(SyncStatusHandler::WM_STATUS_SLAVE);

                        $this->logger->log('Set role in: '.SyncStatusHandler::WM_STATUS_SLAVE);
                    } else { // 3.2.3.

                        if ($this->getStatus() == SyncStatusHandler::WM_STATUS_MASTER) {
                            $this->setCurrentStatus(SyncStatusHandler::WM_STATUS_MASTER);
                            $this->logger->log('Set role in: '.SyncStatusHandler::WM_STATUS_MASTER);
                        } else {
                            $this->setCurrentStatus(SyncStatusHandler::WM_STATUS_SLAVE);
                            $this->logger->log('Set role in: '.SyncStatusHandler::WM_STATUS_SLAVE);
                        }
                    }
                }
            } else {// 2.
                if (!$this->isFlexibility()) {
                    if ($this->getCurrentStatus() != $this->getStatus()) {
                        $this->setCurrentStatus($this->getStatus());

                        $this->logger->log('Set role in: '.$this->getStatus());
                    }
                }
            }
        }
        $this->logger->log(__METHOD__.' role: '.$this->getCurrentStatus() .'; main role: '. $this->getStatus());
    }

    /**
     * @return string - status
     */
    protected function getCurrentStatus()
    {
        $is_master = $this->settings->isMaster();

        if ($is_master) {
            return SyncStatusHandler::WM_STATUS_MASTER;
        } else {
            return SyncStatusHandler::WM_STATUS_SLAVE;
        }
    }

    /**
     * @param string $status slave or master
     */
    protected function setCurrentStatus($status)
    {
        if ($status === SyncStatusHandler::WM_STATUS_MASTER) {
            $this->settings->setInMaster();
        } else {
            $this->settings->setInSlave();
        }
    }

    protected function getStatus()
    {
        $is_master = $this->settings->isMasterMainRole();

        if ($is_master) {
            return SyncStatusHandler::WM_STATUS_MASTER;
        } else {
            return SyncStatusHandler::WM_STATUS_SLAVE;
        }
    }

    protected function setStatus($status)
    {
        if ($status === SyncStatusHandler::WM_STATUS_MASTER) {
//            $this->ts = SyncStatusHandler::WM_STATUS_MASTER;//todo set master
                $this->settings->setMasterMainRole();
        } else {
//            $this->ts = SyncStatusHandler::WM_STATUS_SLAVE;//todo set slave
                $this->settings->setSlaveMainRole();
        }
    }

    /**
     * @return bool
     */
    protected function isFlexibility()
    {
        return $this->settings->isFlexibilitySwitchVariant();
    }
}
