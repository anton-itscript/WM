<?php

class m151105_054503_defaultMailSettings extends CDbMigration
{
	protected $table = 'settings';
	public function up()
	{
		$this->update($this->table, array('mail__use_fake_sendmail'=>0),'setting_id=1');
	}

	public function down()
	{
		$this->update($this->table, array('mail__use_fake_sendmail'=>1),'setting_id=1');

	}

}