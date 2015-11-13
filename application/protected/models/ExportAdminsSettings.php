<?php

class ExportAdminsSettings extends CFormModel
{

    protected   $process_status;
    public      $user_settings=1;
    protected   $_logger;

    public function __get($property){

        return $this->$property;
    }

    public function init()
    {

        parent::init();
    }

    public function rules()
    {
        return array(
            array("user_settings","safe"),
        );
    }

    public function createExport()
    {
        TimezoneWork::set('UTC');
        $tablesArray = array();
        $settingsArray = array();

        $settingsArray['user_settings']= false;
        $settingsArray['common_settings']= false;


        // users and access tables
        if ($this->user_settings) {
            $userTable = Yii::app()->db->createCommand()
                ->select('
                                            u.user_id,
                                            u.email,
                                            u.username,
                                            u.password,
                                            u.role,
                                            u.timezone_id,
                                            u.created,
                                            u.updated
                                        ')
                ->from('user u')
                ->queryAll();
            $tablesArray['user'] = $userTable;


            $access_userTable = Yii::app()->db->createCommand()
                ->select('
                                            au.id,
                                            au.user_id,
                                            au.action_id,

                                        ')
                ->from('access_user au')
                ->queryAll();
            $tablesArray['access_user'] = $access_userTable;

            $access_globalTable = Yii::app()->db->createCommand()
                ->select('
                                            ag.id,
                                            ag.controller,
                                            ag.action,
                                            ag.enable,
                                            ag.description,

                                        ')
                ->from('access_global ag')
                ->queryAll();
            $tablesArray['access_global'] = $access_globalTable;

            $settingsArray['user_settings']= true;
        }


        $settingsTable = Settings::model()->findAll();
        $tablesArray['settings'] = self::modelDataToArray($settingsTable);

        $configTable = Yii::app()->db->createCommand()
            ->select('c.config_id, c.key, c.label, c.value, c.default, c.type')
            ->from('config c')
            ->queryAll();
        $tablesArray['config'] = $configTable;

        $station = Yii::app()->db->createCommand()
                                                ->select('
                                                            s.station_id,
                                                            s.display_name,
                                                            s.station_id_code,
                                                            s.station_number,
                                                            s.station_type,
                                                            s.logger_type,
                                                            s.communication_type,
                                                            s.communication_port,
                                                            s.communication_esp_ip,
                                                            s.communication_esp_port,
                                                            s.details,
                                                            s.status_message_period,
                                                            s.event_message_period,
                                                            s.timezone_id,
                                                            s.timezone_offset,
                                                            s.wmo_block_number,
                                                            s.wmo_member_state_id,
                                                            s.wmo_station_number,
                                                            s.wmo_originating_centre,
                                                            s.national_aws_number,
                                                            s.lat,
                                                            s.lng,
                                                            s.altitude,
                                                            s.magnetic_north_offset,
                                                            s.country_id,
                                                            s.city_id,
                                                            s.awos_msg_source_folder,
                                                            s.icao_code,
                                                            s.phone_number,
                                                            s.sms_message,
                                                            s.station_gravity,
                                                            s.created,
                                                            s.updated
                                                            ')
                                                ->from('station  s')
                                                ->queryAll();
        $tablesArray['station'] = $station;

        $station_groupTable = Yii::app()->db->createCommand()
                                                            ->select('
                                                                        sg.group_id,
                                                                        sg.name
                                                            ')
                                                            ->from('station_group sg')
                                                            ->queryAll();
        $tablesArray['station_group'] = $station_groupTable;

        $station_group_destinationTable = Yii::app()->db->createCommand()
                                                            ->select('
                                                                        sgd.group_destination_id,
                                                                        sgd.group_id,
                                                                        sgd.station_id
                                                            ')
                                                            ->from('station_group_destination sgd')
                                                            ->queryAll();
        $tablesArray['station_group_destination'] = $station_group_destinationTable;

        $station_calculationTable = Yii::app()->db->createCommand()
                                                            ->select('
                                                                        sc.calculation_id,
                                                                        sc.station_id,
                                                                        sc.handler_id,
                                                                        sc.formula,
                                                                        sc.created,
                                                                        sc.updated
                                                            ')
                                                            ->from('station_calculation sc')
                                                            ->queryAll();
        $tablesArray['station_calculation'] = $station_calculationTable;

        $station_calculation_variableTable = Yii::app()->db->createCommand()
                                                            ->select('
                                                                        scv.calculation_variable_id,
                                                                        scv.calculation_id,
                                                                        scv.variable_name,
                                                                        scv.sensor_feature_id,
                                                                        scv.created,
                                                                        scv.updated
                                                            ')
                                                            ->from('station_calculation_variable scv')
                                                            ->queryAll();
        $tablesArray['station_calculation_variable'] = $station_calculation_variableTable;

        $station_sensorTable = Yii::app()->db->createCommand()
                                                            ->select('
                                                                        ss.station_sensor_id,
                                                                        ss.station_id,
                                                                        ss.sensor_id_code,
                                                                        ss.display_name,
                                                                        ss.created,
                                                                        ss.updated,
                                                                        ss.handler_id
                                                            ')
                                                            ->from('station_sensor ss')
                                                            ->queryAll();
        $tablesArray['station_sensor'] = $station_sensorTable;

        $station_sensor_featureTable = Yii::app()->db->createCommand()
                                                            ->select('
                                                                      ssf.sensor_feature_id,
                                                                      ssf.sensor_id,
                                                                      ssf.feature_code,
                                                                      ssf.feature_display_name,
                                                                      ssf.feature_constant_value,
                                                                      ssf.measurement_type_code,
                                                                      ssf.metric_id,
                                                                      ssf.is_main,
                                                                      ssf.filter_max,
                                                                      ssf.filter_min,
                                                                      ssf.filter_diff,
                                                                      ssf.has_filters,
                                                                      ssf.has_filter_min,
                                                                      ssf.has_filter_max,
                                                                      ssf.has_filter_diff,
                                                                      ssf.is_constant,
                                                                      ssf.is_cumulative,
                                                                      ssf.created,
                                                                      ssf.updated
                                                            ')
                                                            ->from('station_sensor_feature ssf')
                                                            ->queryAll();
        $tablesArray['station_sensor_feature'] = $station_sensor_featureTable;

        $sensor_handler_defaultTable = Yii::app()->db->createCommand()
                                                            ->select('
                                                                      sh.handler_id,
                                                                      sh.handler_id_code,
                                                                      sh.display_name,
                                                                      sh.handler_default_display_name,
                                                                      sh.default_prefix,
                                                                      sh.aws_panel_display_position,
                                                                      sh.aws_panel_show,
                                                                      sh.aws_single_display_position,
                                                                      sh.aws_single_group,
                                                                      sh.aws_station_uses,
                                                                      sh.rain_station_uses,
                                                                      sh.awa_station_uses,
                                                                      sh.flags,
                                                                      sh.start_time,
                                                                      sh.created,
                                                                      sh.updated
                                                            ')
                                                            ->from('sensor_handler sh')
                                                            ->queryAll();
        $tablesArray['sensor_handler'] = $sensor_handler_defaultTable;

        $sensor_handler_default_featureTable = Yii::app()->db->createCommand()
                                                            ->select('
                                                                    shdf.handler_feature_id,
                                                                    shdf.handler_id,
                                                                    shdf.feature_code,
                                                                    shdf.aws_panel_show,
                                                                    shdf.feature_constant_value,
                                                                    shdf.metric_id,
                                                                    shdf.filter_max,
                                                                    shdf.filter_min,
                                                                    shdf.filter_diff,
                                                                    shdf.created,
                                                                    shdf.updated
                                                            ')
                                                            ->from('sensor_handler_default_feature shdf')
                                                            ->queryAll();
        $tablesArray['sensor_handler_default_feature'] = $sensor_handler_default_featureTable;


        $schedule_reportTable = Yii::app()->db->createCommand()
                                                            ->select('
                                                                    sr.schedule_id,
                                                                    sr.report_type,
                                                                    sr.station_id,
                                                                    sr.send_like_attach,
                                                                    sr.send_email_together,
                                                                    sr.period,
                                                                    sr.report_format,
                                                                    sr.last_scheduled_run_fact,
                                                                    sr.last_scheduled_run_planned,
                                                                    sr.created,
                                                                    sr.updated
                                                            ')
                                                            ->from('schedule_report sr')
                                                            ->queryAll();
        $tablesArray['schedule_report'] = $schedule_reportTable;

        $schedule_report_to_stationTable = Yii::app()->db->createCommand()
                                                            ->select('
                                                                    srts.id,
                                                                    srts.schedule_id,
                                                                    srts.station_id
                                                            ')
                                                            ->from('schedule_report_to_station srts')
                                                            ->queryAll();
        $tablesArray['schedule_report_to_station'] = $schedule_report_to_stationTable;

        $schedule_report_destinationTable = Yii::app()->db->createCommand()
                                                            ->select('
                                                                    srd.schedule_destination_id,
                                                                    srd.schedule_id,
                                                                    srd.method,
                                                                    srd.destination_email,
                                                                    srd.destination_local_folder,
                                                                    srd.destination_ip,
                                                                    srd.destination_ip_port,
                                                                    srd.destination_ip_folder,
                                                                    srd.destination_ip_user,
                                                                    srd.destination_ip_password
                                                            ')
                                                            ->from('schedule_report_destination srd')
                                                            ->queryAll();
        $tablesArray['schedule_report_destination'] = $schedule_report_destinationTable;


        $ex_schedule_reportTable = Yii::app()->db->createCommand()
                                                            ->from('ex_schedule_report esr')
                                                            ->queryAll();
        $tablesArray['ex_schedule_report'] = $ex_schedule_reportTable;
        $ex_schedule_report_destinationTable = Yii::app()->db->createCommand()
                                                            ->from('ex_schedule_report_destination esrd')
                                                            ->queryAll();
        $tablesArray['ex_schedule_report_destination'] = $ex_schedule_report_destinationTable;
//        $ex_schedule_report_processedTable = Yii::app()->db->createCommand()
//                                                            ->from('ex_schedule_report_processed esrp')
//                                                            ->queryAll();
//        $tablesArray['ex_schedule_report_processed'] = $ex_schedule_report_processedTable;
//        $ex_schedule_send_logTable = Yii::app()->db->createCommand()
//
//                                                            ->from('ex_schedule_send_log essl')
//                                                            ->queryAll();
//        $tablesArray['ex_schedule_send_log'] = $ex_schedule_send_logTable;


        $settingsArray['common_settings']= true;


        $resultArray['config']=$settingsArray;
        $resultArray['data']=$tablesArray;


        $result = serialize($resultArray);

        $additional="";
        if  ($settingsArray['user_settings'])
            $additional="_users";
        $fileName = 'settings_export'.$additional.'_'.date('Y_m_d_H_i_s').'.conf';

        It::downloadFile($result,$fileName , 'text/conf');
    }


    public function attributeLabels()
    {
        $label = array();
        $label["user_settings"]   = 'Make export with user\'s settings';
        return $label;
    }

    public function save()
    {

    }

    protected static function modelDataToArray($modelData){

        $array = array();
        for ($i=0;$i<count($modelData);$i++) {
            $array[$i] = $modelData[$i]->attributes;
        }

        return $array;
    }

}