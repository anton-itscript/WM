<?php

class ImportAdminsSettings extends CFormModel
{


    public $imported_file;
    protected $data_after_validor;
    protected $required_tables;
    protected $not_critical_tables;
    protected $depends_tables;

    protected $result_success;

    public function __get($property){

        return $this->$property;
    }

    public function init()
    {
        $this->required_tables['settings'] = array(
           // 'config',
            'settings',
            'station',
            'station_group',
            'station_group_destination',
            'station_calculation',
            'station_calculation_variable',
            'station_sensor',
            'station_sensor_feature',
            'sensor_handler',
            'sensor_handler_default_feature',

        );

        $this->not_critical_tables['settings'] = array(
            'schedule_report',
            'schedule_report_to_station',
            'schedule_report_destination',
            'ex_schedule_report',
            'ex_schedule_report_destination',
        );

        $this->depends_tables = array(
            'schedule_report_to_station' =>array('schedule_report', 'schedule_report_destination')
        );

        $this->required_tables["user_settings"] = array("user","access_user","access_global");

//        $this->_logger = LoggerFactory::getFileLogger('exportAdminSettings');
        parent::init();
    }

    public function rules()
    {
        return array(
            array('imported_file', 'file',
                'types'=>'conf',
            ),
            array('imported_file', 'dataValidator'),
        );
    }


    public function attributeLabels()
    {
        $label = array();
        $label["imported_file"]   = 'File';
        return $label;
    }
    public function dataValidator(){

        if(!is_object($this->imported_file) )
            return false;


        $conf = file_get_contents($this->imported_file->getTempName());
        $conf = unserialize($conf);
        if ($conf===false) {
            $this->addError('imported_file', 'Wrong Data');
            return false;
        }

        if (isset($conf['data']['config'])) {
            unset($conf['data']['config']);
        }
        foreach ($this->required_tables['settings'] as $table) {
            if (!array_key_exists($table,$conf['data'])) {
                $this->addError('imported_file', 'Table "'.$table.'" missing');
                $has_error = true;
            }
        }
        if ($conf['config']['user_settings']==true) {
            foreach ($this->required_tables['user_settings'] as $table) {
                if (!array_key_exists($table, $conf['data'])) {
                    $this->addError('imported_file', 'Table "' . $table . '" missing');
                    $has_error = true;
                }
            }
        }

        foreach ($this->depends_tables as $table => $tablesArray) {
            if (!array_key_exists($table, $conf['data'])) {

                foreach ($tablesArray as $t) {
                    if (isset($conf['data'][$t])) {
                        unset($conf['data'][$t]);
                    }
                }
            }
        }

        if ($has_error)
            return false;


        $this->data_after_validor   = $conf['data'];
        return true;
    }

    public function save()
    {

        $sql = "SET FOREIGN_KEY_CHECKS = 0;\n";
        $sql .= "";
        foreach ($this->data_after_validor as $key => $value) {
            $sql .= sqlBuilder::createTruncateTableCommand($key);
            $sql .= sqlBuilder::createInsertFromArray($value, $key);
        }
        $sql .= "\n\nSET FOREIGN_KEY_CHECKS = 1;\n";


        $connection=Yii::app()->db;
        $transaction=$connection->beginTransaction();

        try {
            TimezoneWork::set('UTC');
            $command = $connection->createCommand($sql);
            $command->execute();
            $transaction->commit();
            $this->result_success =  true;

            return true;
        }
        catch(Exeption $e) {
            $transaction->rollback();
            $this->result_success =  false;
        }

        return false;

//            try{
//                $count = $command->execute();
//                $this->_logger->log(__METHOD__, array('count' => $count));
//            } catch (Exception $e) {
//                $this->_logger->log(__METHOD__,array('err' => $e->getMessage()));
//            }

    }

}