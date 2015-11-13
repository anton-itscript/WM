<?php

class m150820_160653_WL_sensor_long_db extends CDbMigration
{
    protected $table_sensor_handler="sensor_handler";
    protected $table_sensor_handler_default_feature="sensor_handler_default_feature";
    protected $_db;
    public function getDbConnection()
    {

        $this->_db= Yii::app()->db_long;
        return $this->_db;
    }
    public function setDbConnection($db)
    {
        $this->_db=$db;
    }
    public function insert($table, $columns)
    {
        echo "    > insert into $table ...";
        $time=microtime(true);
        $this->getDbConnection()->createCommand()->insert($table, $columns);
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
    }

	public function up()
	{


        $this->insert($this->table_sensor_handler, array(
            'handler_id_code'                   => 'WaterLevel',
            'display_name'                      => 'Water Level',
            'handler_default_display_name'      => 'Water Level',
            'default_prefix'                    => 'WL',
            'aws_panel_display_position'        => '0',
            'aws_panel_show'                    => '1',
            'aws_single_display_position'       => '1',
            'aws_single_group'                  => 'water',
            'aws_station_uses'                  => '1',
            'rain_station_uses'                 => '0',
            'awa_station_uses'                  => '0',
            'flags'                             => '25',
            'start_time'                        => '-1',
            'created'                           => '0000-00-00 00:00:00',
            'updated'                           => '0000-00-00 00:00:00',

        ));


        $this->insert($this->table_sensor_handler_default_feature, array(
            'handler_feature_id'    =>'151',
            'handler_id'            =>'20',
            'feature_code'          =>'water_level',
            'aws_panel_show'        =>'1',
            'feature_constant_value'=>'0.00',
            'metric_id'             =>'11',
            'filter_max'            =>'0.00',
            'filter_min'            =>'0.00',
            'filter_diff'           =>'0.00',
            'created'               =>'0000-00-00 00:00:00',
            'updated'               =>'0000-00-00 00:00:00',
        ));
        $this->insert($this->table_sensor_handler_default_feature, array(
            'handler_feature_id'    =>'152',
            'handler_id'            =>'20',
            'feature_code'          =>'level_offset',
            'aws_panel_show'        =>'0',
            'feature_constant_value'=>'0.00',
            'metric_id'             =>'11',
            'filter_max'            =>'0.00',
            'filter_min'            =>'0.00',
            'filter_diff'           =>'0.00',
            'created'               =>'0000-00-00 00:00:00',
            'updated'               =>'0000-00-00 00:00:00',
        ));
	}

	public function down()
	{
		echo "m150820_160653_WL_sensor_long_db does not support migration down.\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}