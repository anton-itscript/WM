<?php

class m151105_113200_PathesForDockerVolumes extends CDbMigration
{
	protected $table = 'settings';
	public function up()
	{
		$this->update($this->table, array('scheduled_reports_path'=>"/usr/share/nginx/html/www/files/weather_monitor_reports"),'setting_id=1');
		$this->update($this->table, array('xml_messages_path'=>"/usr/share/nginx/html/www/files/xml_messages"),'setting_id=1');
	}

	public function down()
	{
//		$this->update($this->table, array('scheduled_reports_path'=>1),'setting_id=1');
//		$this->update($this->table, array('xml_messages_path'=>1),'setting_id=1');

	}

}