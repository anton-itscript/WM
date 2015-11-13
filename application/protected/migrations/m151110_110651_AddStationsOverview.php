<?php

class m151110_110651_AddStationsOverview extends CDbMigration
{
	protected $table="access_global";
	public function up()
	{
		$this->insert($this->table, array('controller'=>'Admin','action'=>'StationsOverview', 'enable'=>'1', 'description'=>''));

	}

	public function down()
	{
		$this->execute('DELETE FROM ' . $this->table . ' WHERE `action`="StationsOverview" and `controller` = "Admin" ');


	}
}