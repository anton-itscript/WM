<?php

class m151105_064906_defaultMails extends CDbMigration
{
	protected $table = 'user';
	public function up()
	{
		$this->update($this->table, array( 'email'=>'hello@itscript.com'), 'user_id=1');
		$this->update($this->table, array( 'email'=>'hello@itscript.com'), 'user_id=2');
	}

	public function down()
	{

	}
}