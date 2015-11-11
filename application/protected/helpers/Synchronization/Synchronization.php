<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 08.05.2015
 * Time: 13:58
 */

/**
 * Class Synchronization
 *
 * @property string $server_ip
 * @property string $server_port
 * @property string $remote_server_ip
 * @property string $remote_server_port
 */
class Synchronization
{
    const file_settings = "synchronization_settings.php";

    protected $cron_process_sync_status_command_id = 'syncstatus';
//    protected $cron_process_slave_id = 'slavetcpclient';
//    protected $cron_process_master_id = 'mastertcpserver';

    protected $forwarding_messages_ip;
    protected $forwarding_messages_port;
    protected $process_status;
    /** @var  string */
    protected $server_ip;
    protected $server_port;

    protected $remote_server_ip;
    protected $remote_server_port;

    protected $tcp_server_command_port;
    protected $tcp_client_command_port;
    protected $tcp_server_command_pid;
    protected $tcp_client_command_pid;

    protected $_data;
    protected $_loadedFile;
    protected $_configForm;


    public function __get($property)
    {
        if ($property) {
            return $this->$property;
        }
    }

    public function __construct()
    {
        // for write
        $this->_configForm = new ConfigForm(self::getSettingsFilePath());

        // for read
        $this->_loadedFile = new loadFile(self::getSettingsFilePath());

        $this->_data                    = $this->_loadedFile->getFileData();

        $this->process_status           = $this->_data['PROCESS_STATUS']['value'];
        $this->forwarding_messages_ip   = $this->_data['FOR_COMES_FORWARDING_MESSAGES_IP']['value'];
        $this->forwarding_messages_port = $this->_data['FOR_COMES_FORWARDING_MESSAGES_PORT']['value'];

        $this->remote_server_ip         = $this->_data['REMOTE_SERVER_IP']['value'];
        $this->remote_server_port       = $this->_data['REMOTE_SERVER_PORT']['value'];

        $this->tcp_server_command_port  = $this->_data['TCP_SERVER_COMMAND_PORT']['value'];
        $this->tcp_client_command_port  = $this->_data['TCP_CLIENT_COMMAND_PORT']['value'];
        $this->tcp_server_command_pid   = $this->_data['TCP_SERVER_COMMAND_PID'];
        $this->tcp_client_command_pid   = $this->_data['TCP_CLIENT_COMMAND_PID'];

        $this->server_ip                = $this->_data['SERVER_IP']['value'];
        $this->server_port              = $this->_data['SERVER_PORT']['value'];

    }

    public static function getSettingsFilePath(){

        return Yii::app()->getBasePath().DIRECTORY_SEPARATOR.''.'nosqlvars'.DIRECTORY_SEPARATOR.self::file_settings;
    }

    public function switchProcess()
    {
        if ($this->process_status == 'processed') {
            $this->process_status = 'stopped';
        } else {
            $this->process_status = 'processed';
        }

        $this->_configForm->updateParam('PROCESS_STATUS',array('label'=>$this->_data['PROCESS_STATUS']['label'], 'value'=>$this->process_status ));
        $this->_configForm->saveToFile();

        if ($this->process_status=='processed') {

            $this->startListenFromMasterForwardingMessages();
            $this->startTcpServer();
            $this->startTcpClient();
            $this->startCronTask();

        } else {

            $this->stopListenFromMasterForwardingMessages();
            $this->stopTcpServer();
            $this->stopTcpClient();
            $this->clearCronTask($this->cron_process_sync_status_command_id);

        }
        return true;
    }


    public function setInMaster()
    {
        $this->_configForm->reloadFileProperties($this->_loadedFile->getFileData());
        $this->_configForm->updateParam('FLEXIBILITY_ROLE',array('label'=>$this->_data['FLEXIBILITY_ROLE']['label'], 'value'=>'1'));
        $this->_configForm->saveToFile();
    }

    public function setInSlave()
    {
        $this->_configForm->reloadFileProperties($this->_loadedFile->getFileData());
        $this->_configForm->updateParam('FLEXIBILITY_ROLE',array('label'=>$this->_data['FLEXIBILITY_ROLE']['label'], 'value'=>'2' ));
        $this->_configForm->saveToFile();
    }


    public function isMaster()
    {
        $this->_data = $this->_loadedFile->getFileData();
        return $this->_data['FLEXIBILITY_ROLE']['value'] == 1 ? true : false;
    }

    public function isSlave()
    {
        $this->_data = $this->_loadedFile->getFileData();
        return $this->_data['FLEXIBILITY_ROLE']['value'] == 2 ? true : false;
    }

    public function getRemoteIpToSentMessages()
    {
        $this->_data = $this->_loadedFile->getFileData();
        return $this->_data['FOR_SEND_MESSAGES_TO_IP']['value'];
    }

    public function getRemotePortToSentMessages()
    {
        $this->_data = $this->_loadedFile->getFileData();
        return $this->_data['FOR_SEND_MESSAGES_PORT']['value'];
    }

    public function getData()
    {
        $this->_data = $this->_loadedFile->getFileData();
        return $this->_data;
    }



    protected function startCronTask()
    {

        $command = Yii::app()->params['applications']['php_exe_path'] . " -f  " . dirname(Yii::app()->request->scriptFile) . DIRECTORY_SEPARATOR . "console.php syncstatus";
        $this->clearCronTask($this->cron_process_sync_status_command_id);
        TaskManager::create($this->cron_process_sync_status_command_id, $command, 'minutely', 1);

    }

    /**
     * @param $pid
     * @return bool
     */
    public static function setSynsStatusCommandPid($pid)
    {
        if ((int)$pid) {
            $exemplar = new Synchronization();
            $exemplar->_configForm->reloadFileProperties($exemplar->_loadedFile->getFileData());
            $exemplar->_configForm->updateParam('SYNS_STATUS_COMMAND_PID',$pid);
            $exemplar->_configForm->saveToFile();
            return true;
        }
        return false;
    }

    public function getSynsStatusCommandPid()
    {

        $_data = $this->_loadedFile->getFileData();
        return $_data['SYNS_STATUS_COMMAND_PID'];
    }


    protected function clearCronTask($id)
    {
        if (TaskManager::check($id) === true)
            TaskManager::delete($id);

        if ($pid = $this->getSynsStatusCommandPid()) {
            ProcessPid::killProcess($pid);
            $this->_configForm->updateParam('SYNS_STATUS_COMMAND_PID',false);
//            $this->_configForm->updateParam('MASTER_TCP_SERVER_COMMAND_PID',false);
            $this->_configForm->saveToFile();
        }
    }


    protected function startListenFromMasterForwardingMessages()
    {
        $source = 'TCP:'.$this->forwarding_messages_ip.":".$this->forwarding_messages_port;
        $last_connection = Listener::getLastConnectionInfoForSynch($source);
        if ($last_connection && !$last_connection['stopped']) {

        } else {
            $command = Yii::app()->params['applications']['php_exe_path'] . ' -f ' . Yii::app()->params['applications']['console_app_path'] . ' listen '. $source .' synchronization Admin';
            It::runAsynchCommand($command);
        }
    }

    protected function stopListenFromMasterForwardingMessages()
    {
        $source  = 'TCP:'.$this->forwarding_messages_ip.":".$this->forwarding_messages_port;
        $last_connection = Listener::getLastConnectionInfoForSynch($source);

        if ($last_connection && !$last_connection['stopped'])
        {
            ProcessPid::killProcess($last_connection['process_pid']);
            ListenerProcess::addComment($last_connection['listener_id'], 'comment', 'Server has set master');
            Listener::stopConnection($last_connection['listener_id']);
            $this->_configForm->updateParam('LISTENER_ID_FROM_MASTER',false);
            $this->_configForm->saveToFile();
        }

    }

    public static function trySetActualListenerId($source, $listenerId)
    {
        $exemplar = new Synchronization();
        if ('TCP:'.$exemplar->forwarding_messages_ip.":".$exemplar->forwarding_messages_port == $source ) {
            $exemplar->_configForm->reloadFileProperties($exemplar->_loadedFile->getFileData());
            $exemplar->_configForm->updateParam('LISTENER_ID_FROM_MASTER',$listenerId);
            $exemplar->_configForm->saveToFile();
            return true;
        }
        return false;
    }

    public static function getListenerId()
    {
        $exemplar = new Synchronization();
        $_data = $exemplar->_loadedFile->getFileData();

        return $_data['LISTENER_ID_FROM_MASTER'];
    }



    //---------------- EXCHANGE ----------------------//
    public function startTcpClient()
    {
        $command = Yii::app()->params['applications']['php_exe_path'] . ' -f ' . Yii::app()->params['applications']['console_app_path'] . ' tcpclient ' ;
        It::runAsynchCommand($command);
    }

    public function stopTcpClient()
    {
        ProcessPid::killProcess($this->tcp_client_command_pid);
        $this->_configForm->updateParam('TCP_CLIENT_COMMAND_PID',false);
        $this->_configForm->saveToFile();
    }

    public static function setTcpClientPid($pid)
    {
        if ((int)$pid) {
            $exemplar = new Synchronization();
            $exemplar->_configForm->reloadFileProperties($exemplar->_loadedFile->getFileData());
            $exemplar->_configForm->updateParam('TCP_CLIENT_COMMAND_PID',$pid);
            $exemplar->_configForm->saveToFile();
            return true;
        }
        return false;
    }

    public function getTcpClientPid()
    {
        $_data = $this->_loadedFile->getFileData();
        return $_data['TCP_CLIENT_COMMAND_PID'];
    }

    public function startTcpServer()
    {
        $source = 'TCP:'.$this->forwarding_messages_ip.":".$this->tcp_server_command_port;
        $last_connection = Listener::getLastConnectionInfoForSynch($source);
        if ($last_connection && !$last_connection['stopped']) {

        } else {
            $command = Yii::app()->params['applications']['php_exe_path'] . ' -f ' . Yii::app()->params['applications']['console_app_path'] . ' tcpserver ' ;
            It::runAsynchCommand($command);
        }
    }

    protected function stopTcpServer()
    {
        ProcessPid::killProcess($this->tcp_server_command_pid);
        $this->_configForm->updateParam('TCP_SERVER_COMMAND_PID',false);
        $this->_configForm->saveToFile();

    }

    public static function setTcpServerPid($pid)
    {
        if ((int)$pid) {
            $exemplar = new Synchronization();
            $exemplar->_configForm->reloadFileProperties($exemplar->_loadedFile->getFileData());
            $exemplar->_configForm->updateParam('TCP_SERVER_COMMAND_PID',$pid);
            $exemplar->_configForm->saveToFile();
            return true;
        }
        return false;
    }

    public function getTcpServerPid()
    {
        $_data = $this->_loadedFile->getFileData();
        return $_data['TCP_SERVER_COMMAND_PID'];
    }
    //-----------------END EXCHANGE -------------------------------//


    public function getSwitchVariant()
    {
        return $this->switch_variant;
    }

    public function isFixedSwitchVariant()
    {
        $this->_data = $this->_loadedFile->getFileData();
        return $this->_data['SWITCH_VARIANT']['value'] == 1 ? true: false;
    }

    public function isFlexibilitySwitchVariant()
    {
        $this->_data = $this->_loadedFile->getFileData();
        return $this->_data['SWITCH_VARIANT']['value'] == 2 ? true: false;
    }

    public function setFixedSwitchVariant()
    {
        $this->_configForm->reloadFileProperties($this->_loadedFile->getFileData());
        $this->_configForm->updateParam('SWITCH_VARIANT',array('label'=>$this->_data['SWITCH_VARIANT']['label'], 'value'=>'1'));
        $this->_configForm->saveToFile();
    }

    public function setFlexibilitySwitchVariant()
    {
        $this->_configForm->reloadFileProperties($this->_loadedFile->getFileData());
        $this->_configForm->updateParam('SWITCH_VARIANT',array('label'=>$this->_data['SWITCH_VARIANT']['label'], 'value'=>'2'));
        $this->_configForm->saveToFile();
    }
    
    public function isMasterMainRole()
    {
        $this->_data = $this->_loadedFile->getFileData();
        return $this->_data['MAIN_ROLE']['value'] == 1 ? true: false;
    }

    public function isSlaveMainRole()
    {
        $this->_data = $this->_loadedFile->getFileData();
        return $this->_data['MAIN_ROLE']['value'] == 2 ? true: false;
    }

    public function isProcessed()
    {
        $this->_data = $this->_loadedFile->getFileData();
        return $this->_data['PROCESS_STATUS']['value'] == 'processed' ? true: false;
    }

    public function setMasterMainRole()
    {
        $this->_data = $this->_loadedFile->getFileData();
        $this->_configForm->updateParam('MAIN_ROLE',array('label'=>$this->_data['MAIN_ROLE']['label'], 'value'=>'1'));
        $this->_configForm->saveToFile();
    }

    public function setSlaveMainRole()
    {
        $this->_data = $this->_loadedFile->getFileData();
        $this->_configForm->updateParam('MAIN_ROLE',array('label'=>$this->_data['MAIN_ROLE']['label'], 'value'=>'2'));
        $this->_configForm->saveToFile();
    }

    public function getMainRole()
    {
        $this->_data = $this->_loadedFile->getFileData();
        $array = SynchronizationForm::getFlexibilityRole();

        return $array[$this->_data['MAIN_ROLE']['value']];
    }

    public function getRole()
    {
        $this->_data = $this->_loadedFile->getFileData();
        $array = SynchronizationForm::getFlexibilityRole();
        return $array[$this->_data['FLEXIBILITY_ROLE']['value']];
    }

    public function getIdentificator()
    {
        $this->_data = $this->_loadedFile->getFileData();
        return $this->_data['IDENTIFICATOR']['value'];
    }

//    public function setInFixed()
//    {
//        $this->switch_variant = 1;
//        $this->save();
//    }
//
//    public function setInFlexibility()
//    {
//        $this->switch_variant = 2;
//        $this->save();
//    }

}



