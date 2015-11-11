<?php

class m150709_092613_aws_format extends CDbMigration
{
    protected $table="config";
	public function up()
	{

        $this->insert($this->table, array('key'=>'AWS_FORMAT', 'label'=>'AWS Format', 'value'=>'1', 'default'=>'1', 'type'=>'int'));
	}

	public function down()
	{
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="AWS_FORMAT"');

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