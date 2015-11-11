<?php

class SynchronizationForm extends CFormModel
{

    protected $server_ip;
    protected $server_port;
    protected $remote_server_ip;
    protected $remote_server_port;
    protected $switch_variant;
    protected $flexibility_role;
    protected $process_status;
    protected $main_role;
    protected $forwarding_messages_ip;
    protected $forwarding_messages_port;
    protected $for_send_messages_to_ip;
    protected $for_send_messages_port;
    protected $identificator;
    protected $tcp_server_command_port;
    protected $tcp_client_command_port;
    protected $tcp_server_command_pid;
    protected $tcp_client_command_pid;


    protected $_data;

    /*
     * @var $_configForm ConfigForm
     * */
    protected $_configForm;

    public function __get($property)
    {
        if ($property) {
            return $this->$property;
        }
    }

    public function init()
    {

        $this->_configForm                  = new ConfigForm(Synchronization::getSettingsFilePath());
        $this->_data                        = $this->_configForm->getConfig();
        $this->server_ip                    = $this->_data['SERVER_IP']['value'];
        $this->server_port                  = $this->_data['SERVER_PORT']['value'];
        $this->remote_server_ip             = $this->_data['REMOTE_SERVER_IP']['value'];
        $this->remote_server_port           = $this->_data['REMOTE_SERVER_PORT']['value'];
        $this->switch_variant               = $this->_data['SWITCH_VARIANT']['value'];
        $this->flexibility_role             = $this->_data['FLEXIBILITY_ROLE']['value'];
        $this->process_status               = $this->_data['PROCESS_STATUS']['value'];
        $this->main_role                    = $this->_data['MAIN_ROLE']['value'];
        $this->forwarding_messages_ip       = $this->_data['FOR_COMES_FORWARDING_MESSAGES_IP']['value'];
        $this->forwarding_messages_port     = $this->_data['FOR_COMES_FORWARDING_MESSAGES_PORT']['value'];
        $this->for_send_messages_to_ip      = $this->_data['FOR_SEND_MESSAGES_TO_IP']['value'];
        $this->for_send_messages_port       = $this->_data['FOR_SEND_MESSAGES_PORT']['value'];
        $this->identificator                = $this->_data['IDENTIFICATOR']['value'];
        $this->tcp_server_command_port      = $this->_data['TCP_SERVER_COMMAND_PORT']['value'];
        $this->tcp_client_command_port      = $this->_data['TCP_CLIENT_COMMAND_PORT']['value'];
        $this->tcp_server_command_pid      = $this->_data['TCP_SERVER_COMMAND_PID'];
        $this->tcp_client_command_pid      = $this->_data['TCP_CLIENT_COMMAND_PID'];


        parent::init();
    }


    public function rules()
    {
        return array(
            array('server_port, tcp_server_command_port, tcp_client_command_port, remote_server_port, forwarding_messages_port, for_send_messages_port','numerical','integerOnly' => true),
            array('server_port, remote_server_port, forwarding_messages_port, for_send_messages_port','required'),
            array('remote_server_ip,server_ip','ipValidator'),
            array('remote_server_ip,server_ip, identificator','required'),
            array('identificator','match', 'pattern'=>'/[a-zA-Z0-9]{5}/','message'=>'Identificator must consist of five letters and numbers'),
            array('switch_variant, main_role, flexibility_role','numerical'),
        );
    }

    public function attributeLabels()
    {
        $label = array();

        $label["server_ip"]                     = $this->_data['SERVER_IP']['label'];
        $label["server_port"]                   = $this->_data['SERVER_PORT']['label'];
        $label["remote_server_ip"]              = $this->_data['REMOTE_SERVER_IP']['label'];
        $label["remote_server_port"]            = $this->_data['REMOTE_SERVER_PORT']['label'];
        $label["switch_variant"]                = $this->_data['SWITCH_VARIANT']['label'];
        $label["flexibility_role"]              = $this->_data['FLEXIBILITY_ROLE']['label'];
        $label["process_status"]                = $this->_data['PROCESS_STATUS']['label'];
        $label["main_role"]                     = $this->_data['MAIN_ROLE']['label'];
        $label["forwarding_messages_ip"]        = $this->_data['FOR_COMES_FORWARDING_MESSAGES_IP']['label'];
        $label["forwarding_messages_port"]      = $this->_data['FOR_COMES_FORWARDING_MESSAGES_PORT']['label'];
        $label["for_send_messages_to_ip"]       = $this->_data['FOR_SEND_MESSAGES_TO_IP']['label'];
        $label["for_send_messages_port"]        = $this->_data['FOR_SEND_MESSAGES_PORT']['label'];
        $label["identificator"]                 = $this->_data['IDENTIFICATOR']['label'];
        $label["tcp_server_command_port"]       = $this->_data['TCP_SERVER_COMMAND_PORT']['label'];
        $label["tcp_client_command_port"]       = $this->_data['TCP_CLIENT_COMMAND_PORT']['label'];
        return $label;
    }

    public function save()
    {


        $this->_configForm->updateParam('SERVER_IP',array('label'=>$this->_data['SERVER_IP']['label'], 'value'=>$this->server_ip ));
        $this->_configForm->updateParam('SERVER_PORT',array('label'=>$this->_data['SERVER_PORT']['label'], 'value'=>$this->server_port ));
        $this->_configForm->updateParam('REMOTE_SERVER_IP',array('label'=>$this->_data['REMOTE_SERVER_IP']['label'], 'value'=>$this->remote_server_ip ));
        $this->_configForm->updateParam('REMOTE_SERVER_PORT',array('label'=>$this->_data['REMOTE_SERVER_PORT']['label'], 'value'=>$this->remote_server_port ));
        $this->_configForm->updateParam('SWITCH_VARIANT',array('label'=>$this->_data['SWITCH_VARIANT']['label'], 'value'=>$this->switch_variant ));
        $this->_configForm->updateParam('FLEXIBILITY_ROLE',array('label'=>$this->_data['FLEXIBILITY_ROLE']['label'], 'value'=>$this->flexibility_role ));
        $this->_configForm->updateParam('PROCESS_STATUS',array('label'=>$this->_data['PROCESS_STATUS']['label'], 'value'=>$this->process_status ));
        $this->_configForm->updateParam('MAIN_ROLE',array('label'=>$this->_data['MAIN_ROLE']['label'], 'value'=>$this->main_role ));
        $this->_configForm->updateParam('FOR_COMES_FORWARDING_MESSAGES_IP',array('label'=>$this->_data['FOR_COMES_FORWARDING_MESSAGES_IP']['label'], 'value'=>$this->server_ip ));
        $this->_configForm->updateParam('FOR_COMES_FORWARDING_MESSAGES_PORT',array('label'=>$this->_data['FOR_COMES_FORWARDING_MESSAGES_PORT']['label'], 'value'=>$this->forwarding_messages_port ));
        $this->_configForm->updateParam('FOR_SEND_MESSAGES_TO_IP',array('label'=>$this->_data['FOR_SEND_MESSAGES_TO_IP']['label'], 'value'=>$this->remote_server_ip ));
        $this->_configForm->updateParam('FOR_SEND_MESSAGES_PORT',array('label'=>$this->_data['FOR_SEND_MESSAGES_PORT']['label'], 'value'=>$this->for_send_messages_port ));
        $this->_configForm->updateParam('IDENTIFICATOR',array('label'=>$this->_data['IDENTIFICATOR']['label'], 'value'=>$this->identificator ));
        $this->_configForm->updateParam('TCP_SERVER_COMMAND_PORT',array('label'=>$this->_data['TCP_SERVER_COMMAND_PORT']['label'], 'value'=>$this->tcp_server_command_port ));
        $this->_configForm->updateParam('TCP_CLIENT_COMMAND_PORT',array('label'=>$this->_data['TCP_CLIENT_COMMAND_PORT']['label'], 'value'=>$this->tcp_client_command_port ));

        $this->_configForm->updateParam('TCP_SERVER_COMMAND_PID',array($this->tcp_server_command_pid));
        $this->_configForm->updateParam('TCP_CLIENT_COMMAND_PID',array($this->tcp_client_command_pid));

        $this->_configForm->saveToFile();

    }

    public static function getSwitchVariants()
    {
        return array(
            '1'=>'Fixed',
            '2'=>'Flexible',
        );
    }

    public static function getFlexibilityRole()
    {
        return array(
            '1'=>'master',
            '2'=>'slave',
        );
    }



}