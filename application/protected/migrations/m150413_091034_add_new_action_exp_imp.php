<?php

class m150413_091034_add_new_action_exp_imp extends CDbMigration
{
    protected $table="access_global";
	public function up()
	{
        $this->insert($this->table, array('controller'=>'Admin','action'=>'ExportAdminsSettings', 'enable'=>'1', 'description'=>'Export Admins Settings'));
    }

	public function down()
	{
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `action`="ExportAdminsSettings" and `controller` = "Admin" ');


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