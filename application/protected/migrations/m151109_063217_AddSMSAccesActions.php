<?php

class m151109_063217_AddSMSAccesActions extends CDbMigration
{
	protected $table="access_global";
	public function up()
	{
		$this->insert($this->table, array('controller'=>'Admin','action'=>'SendSmsCommand', 'enable'=>'1', 'description'=>''));
		$this->insert($this->table, array('controller'=>'Admin','action'=>'SmsCommandSetup', 'enable'=>'1', 'description'=>''));
		$this->insert($this->table, array('controller'=>'Admin','action'=>'GenerateSmsCommand', 'enable'=>'1', 'description'=>''));
	}

	public function down()
	{
		$this->execute('DELETE FROM ' . $this->table . ' WHERE `action`="SendSmsCommand" and `controller` = "Admin" ');
		$this->execute('DELETE FROM ' . $this->table . ' WHERE `action`="SmsCommandSetup" and `controller` = "Admin" ');
		$this->execute('DELETE FROM ' . $this->table . ' WHERE `action`="GenerateSmsCommand" and `controller` = "Admin" ');

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