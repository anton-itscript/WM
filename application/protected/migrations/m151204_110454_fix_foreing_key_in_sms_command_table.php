<?php

class m151204_110454_fix_foreing_key_in_sms_command_table extends CDbMigration
{
	public function up()
	{
		$sql = "
           	SET FOREIGN_KEY_CHECKS=0;
            ALTER TABLE `sms_command` DROP  FOREIGN KEY  `fk_station`;
            ALTER TABLE `sms_command` ADD  CONSTRAINT `fk_station` FOREIGN KEY (`station_id`) REFERENCES `station` (`station_id`) ON DELETE CASCADE;
            SET FOREIGN_KEY_CHECKS=1;
";
		$connection=Yii::app()->db;
		$command=$connection->createCommand($sql);
		$command->execute();
	}


	public function down()
	{
		echo "m151204_110454_fix_foreing_key_in_sms_command_table does not support migration down.\n";
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