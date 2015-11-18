<?php
class MainMenu
{
    public $part;
    public $template;
    public $data;
    public $current_controller;
    public $current_action;

    private static  $instance = null;
    protected static $className = __CLASS__;

    protected $levels;
    protected $levelsTemp;

    /**
     * @return MainMenu
     */
    public static function getInstance($controller, $action)
    {
        if (null === self::$instance)
        {
            self::$instance = new self::$className($controller,$action);
        }

        return self::$instance;
    }

    public function init()
    {
//        ini_set('xdebug.max_nesting_level', 0);
        if ($this->current_controller == 'site' && $this->current_action != 'login') {
            $total_rain_stations = Station::getTotal ('rain');
            $total_aws_stations = Station::getTotal ('aws');
        }

        $this->data = array(
            'admin' => array(
                'stationsoverview'=>array(
                    "label"=>"admin_stations",
                    'items'=>array(
                        'stations'          =>  array("label"=>'admin_stations_list'),
                        'stationsoverview'  =>  array("label"=>"admin_station_overview"),
                        //'stationsave'       =>  array("label"=>"admin_create_station"),
                        'stationgroups'     =>  array("label"=>'admin_station_groups')
                    ),
                ),

                'connections'=>array(
                    "label"=>"admin_connections_connections",
                    'items'=>array(
                        'connections'       =>  array("label"=>'admin_connections_connections'),
                        'connectionslog'    =>  array("label"=>'admin_connections_log'),
                        'xmllog'            =>  array("label"=>'admin_connections_xmllog')
                    ),
                ),
                'importmsg'=>array(
                    "label"=>"admin_import_data",
                    'items'=>array(
                        'awsfiltered'                   =>  array("label"=>"admin_aws_filtered_data"),
                        'forwardlist'                   =>  array("label"=>"message_forwarding_list"),
                        'stationtypedataexport'         =>  array("label"=>"ODSS Export",
                            "items" => array(
                                'stationtypedatahistory'        => array(),
                            ),
                        ),
                        'importmsg'                     =>  array("label"=>'admin_import_message'),
                        'msggeneration'                 =>  array("label"=>"admin_msg_generation"),
                        'importxml'                     =>  array("label"=>'admin_import_xml'),




                    ),
                ),

                'users'=>array(
                    "label"=>"superadmin_users",
                    "items"=>array(
//                        'users' => array('label' =>'superadmin_users'),
//                        'user'  => array('label' =>'superadmin_user'),
                    )
                ),
                'heartbeatreports'=>array(
                    "label"=>"superadmin_heartbeatreport",
                    'items'=>array()
                ),
                'sendsmscommand'=>array(
                    "label"=>"superadmin_sendsmscommand",
                    'items'=>array(

                    )
                ),
                'setupother'=>array(
                    "label"=>"admin_setup",
                    "items"=>array(
                        'setupother'        => array("label"=>'Main'),
                        'setupsensors'      => array("label"=>'admin_setup_setup_sensors'),
                        'dbsetup'           => array("label"=>'admin_setup_db_setup'),
                        'mailsetup'         => array("label"=>'admin_setup_mail_setup'),
                        'smscommandsetup'   => array("label"=>"SMS"),

                        'dbbackup'          => array(),
                        'checkcomstatus'    => array(),
                        'dbexport'          => array(),
                        'dbexporthistory'   => array(),
                    )
                ),

            ),
            'superadmin' => array(
//                'users'=>array(
//                    "label"=>"superadmin_users",
//                    "items"=>array(
//                        'users' => array('label' =>'superadmin_users'),
//                        'user'  => array('label' =>'superadmin_user'),
//                    )
//                ),
//                'access'=>array(
//                    "label"=>"superadmin_access",
//                    "items"=>array(
//                        'access'        => array('label' => 'superadmin_access'),
//                        'accessedit'    => array('label' => 'superadmin_accessedit')
//                    )
//                ),

                'longdbsetup'=>array(
                    "label"=>"Setup",
                    "items"=>array(
                        'longdbsetup'       =>  array('label' => 'superadmin_longdbsetup'),
                        'longdbtask'        =>  array('label' => 'superadmin_longdbtask'),
                        'syncsettings'      =>  array("label"=>"superadmin_syncsettings","items"=>array()),
                        'heartbeatreport'   =>  array("label"=>"superadmin_heartbeatreport",'items'=>array()),
                        'config'            =>  array("label"=>"superadmin_config",'items'=>array()),
                        'awsformat'         =>  array("label"=>"superadmin_aws_format",'items'=>array()),
                    )
                ),
                'metrics'=>array(
                    "label"=>"superadmin_metrics",
                    'items'=>array()
                ),




                'exportadminssettings'=>array(
                    "label"=>"admin_import_export_admins_settings",
                    'items'=>array()
                ),
            ),

           );
        if ($total_aws_stations) {
            $this->data['site']['awspanel'] = array(
                "label" => "home_aws_panel",
                'items' => array()
            );
            $this->data['site']['awspanelold'] = array(
                "label" => "home_aws_panel",
                'items' => array()
            );
            $this->data['site']['awssingle'] = array(
                "label" => "home_aws_single",
                'items' => array()
            );
            $this->data['site']['awsgraph'] = array(
                "label" => "home_aws_graph",
                'items' => array()
            );
            $this->data['site']['awstable'] = array(
                "label" => "home_aws_table",
                'items' => array()
            );
        }

        if ($total_rain_stations) {
            $this->data['site']['rgpanel'] = array(
                "label" => "home_rg_panel",
                'items' => array()
            );
            $this->data['site']['rgtable'] = array(
                "label" => "home_rg_table",
                'items' => array()
            );
            $this->data['site']['rggraph'] = array(
                "label" => "home_rg_graph",
                'items' => array()
            );
        }

        if ($total_aws_stations || $total_rain_stations) {
            $this->data['site']['msghistory'] = array(
                "label" => "home_msg_history",
                'items' => array()
            );
            $this->data['site']['export'] = array(
                "label" => "home_export",
                'items' => array()
            );
        }
        if ($total_aws_stations) {
            $this->data['site']['schedule'] = array(
                "label" => "home_schedule",
                "items" => array(
                    'schedule'        => array(),
                    'schedulehistory' => array(),
                    'schedulestationhistory' => array(),
                )
            );
//            $this->data['site']['stationtypedataexport'] = array(
//                "label" => "ODSS export",
//                "items" => array(
//                    'stationtypedatahistory'        => array(),
//
//                )
//            );
        }

        $this->data['site']['login'] = array("items"=>array());
    }

    private function __construct($controller, $action)
    {
        $this->current_controller   = strtolower($controller);
        $this->current_action       = strtolower($action);
        $this->init();


        if (is_array($this->data[$this->current_controller])) {
            foreach ($this->data[$this->current_controller] as  $action => $menu) {

                if ($action == $this->current_action) {
                    $this->data[$this->current_controller][$action]['active'] = true;
                }

                if (count($this->data[$this->current_controller][$action]['items'])) {
                    $this->findActiveBranch($this->data[$this->current_controller][$action]['items'],$this->data[$this->current_controller][$action]);
                }
            }
        }

    }

    private function __clone()
    {

    }

    protected function findActiveBranch(&$actionItems, &$parent=null)
    {

        foreach ($actionItems as  $action => $menu) {
            if ($action == $this->current_action) {
                $actionItems[$action]['active'] = true;
                if (!is_null($parent)) {
                    $parent['active'] = true;
                }
            }

            if (count($actionItems[$action]['items'])) {
                $this->findActiveBranch($this->data[$this->current_controller][$action]['items'], $actionItems[$action]);
            }
        }
    }


    protected function getActiveSecondBranch()
    {
        $this->data[$this->current_controller];
    }

    protected function render($view, $data)
    {

        return CBaseController::renderInternal(
            Yii::app()->basePath .'/widgets/MainMenu/views/' .$view.'.php',
            array('data'=>$data, 'controller'=>$this->current_controller),
            true
        );
    }

    public function getFirstMenu()
    {
        return $this->render('top',$this->data[$this->current_controller]);
    }

    public function getSecondMenu()
    {

        foreach ($this->data[$this->current_controller] as $item) {
            if (isset($item['active']) and is_array($item) ) {
                return $this->render('second',$item);
            }
        }

    }




}