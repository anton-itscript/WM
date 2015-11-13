<?php

class m150817_075236_shedule_history_action extends CDbMigration
{
    protected $table="access_global";
	public function up()
	{
        $this->insert($this->table, array('controller'=>'Site','action'=>'ScheduleStationHistory', 'enable'=>'1', 'description'=>''));
	}

	public function down()
	{
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `action`="ScheduleStationHistory" and `controller` = "Site" ');

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