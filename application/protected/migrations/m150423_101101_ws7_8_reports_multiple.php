<?php

class m150423_101101_ws7_8_reports_multiple extends CDbMigration
{
	public function up()
	{


        $sql = "
            SET FOREIGN_KEY_CHECKS=0;

            -- ----------------------------
            -- Table structure for schedule_report_to_station
            -- ----------------------------
            DROP TABLE IF EXISTS `schedule_report_to_station`;
            CREATE TABLE `schedule_report_to_station` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `schedule_id` int(11) NOT NULL,
              `station_id` smallint(7) NOT NULL,
              `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              `created` timestamp NULL DEFAULT '0000-00-00 00:00:00',
              PRIMARY KEY (`id`),
              KEY `schedule_report_fk` (`schedule_id`),
              KEY `id` (`id`,`station_id`),
              KEY `station_id` (`station_id`,`id`),
              KEY `station_id_2` (`station_id`),
              CONSTRAINT `schedule_report_fk` FOREIGN KEY (`schedule_id`) REFERENCES `schedule_report` (`schedule_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `station_schedule_report_fk` FOREIGN KEY (`station_id`) REFERENCES `station` (`station_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


            ALTER TABLE `schedule_report_processed` DROP  FOREIGN KEY  `schedule_report_processed_fk`;

            ALTER TABLE `schedule_report_processed` DROP KEY `schedule_id`;
            ALTER TABLE `schedule_report_processed` DROP COLUMN  `schedule_id`;
            ALTER TABLE `schedule_report_processed` ADD COLUMN `sr_to_s_id`  int(11) NOT NULL AFTER `schedule_processed_id`;
            ALTER TABLE `schedule_report_processed` ADD  CONSTRAINT `schedule_report_processed_sr_fk` FOREIGN KEY (`sr_to_s_id`) REFERENCES `schedule_report_to_station` (`id`) ON DELETE CASCADE;

            ALTER TABLE  `schedule_report` DROP  KEY `fk_schedule_report__station_id`;
            ALTER TABLE  `schedule_report` DROP FOREIGN KEY `fk_schedule_report__station_id`;
            ALTER TABLE  `schedule_report` ADD COLUMN `send_like_attach` int(1) DEFAULT '1';
            ALTER TABLE  `schedule_report` ADD COLUMN `send_email_together` int(1) DEFAULT '0';

            SET FOREIGN_KEY_CHECKS=1;
";
        $connection=Yii::app()->db;
        $command=$connection->createCommand($sql);
        $command->execute();


        $sql = "
            SET FOREIGN_KEY_CHECKS=0;
            DROP TABLE IF EXISTS `schedule_report_to_station`;
            CREATE TABLE `schedule_report_to_station` (
              `id` int(11) NOT NULL,
              `schedule_id` int(11) NOT NULL,
              `station_id` smallint(7) NOT NULL,
              `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              `created` timestamp NULL DEFAULT '0000-00-00 00:00:00',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;



            ALTER TABLE `schedule_report_processed` DROP  FOREIGN KEY  `schedule_report_processed_fk`;

            ALTER TABLE `schedule_report_processed` DROP KEY `schedule_id`;
            ALTER TABLE `schedule_report_processed` DROP COLUMN  `schedule_id`;
            ALTER TABLE `schedule_report_processed` ADD COLUMN `sr_to_s_id`  int(11) NOT NULL AFTER `schedule_processed_id`;

            ALTER TABLE  `schedule_report` DROP  KEY `fk_schedule_report__station_id`;
            ALTER TABLE  `schedule_report` DROP FOREIGN KEY `fk_schedule_report__station_id`;
            ALTER TABLE  `schedule_report` ADD COLUMN `send_like_attach` int(1) DEFAULT '1';
            ALTER TABLE  `schedule_report` ADD COLUMN `send_email_together` int(1) DEFAULT '0';

            SET FOREIGN_KEY_CHECKS=1;

";


        $connection=Yii::app()->db_long;
        $command=$connection->createCommand($sql);
        $command->execute();
    }

	public function down()
	{

        $sql = "
            SET FOREIGN_KEY_CHECKS=0;
            DROP TABLE IF EXISTS `schedule_report_to_station`;


            ALTER TABLE   `schedule_report`                     DROP COLUMN `send_like_attach`;
            ALTER TABLE   `schedule_report`                     DROP COLUMN`send_email_together`;
            ALTER TABLE   `schedule_report_processed`           DROP FOREIGN KEY   `schedule_report_processed_sr_fk` ;
            ALTER TABLE   `schedule_report_processed`           DROP COLUMN    `sr_to_s_id` ;

			ALTER TABLE `schedule_report_processed` ADD COLUMN   `schedule_id`  int(11) NOT NULL;

            ALTER TABLE `schedule_report_processed`
            ADD  CONSTRAINT
              `schedule_report_processed_fk`
            FOREIGN KEY (`schedule_id`)
            REFERENCES `schedule_report` (`schedule_id`)
            ON DELETE
                CASCADE
            ON UPDATE
                NO ACTION;

            ALTER TABLE `schedule_report_processed` ADD KEY `schedule_id` (`schedule_id`);
            ALTER TABLE  `schedule_report` ADD  KEY `fk_schedule_report__station_id` (`station_id`);
            ALTER TABLE  `schedule_report`
                                        ADD  CONSTRAINT
                                            `fk_schedule_report__station_id`
                                                                FOREIGN KEY (`station_id`)
                            REFERENCES `station` (`station_id`)
                                                    ON DELETE CASCADE
                                                            ON UPDATE CASCADE;
            SET FOREIGN_KEY_CHECKS=1;
";
        $connection=Yii::app()->db;
        $command=$connection->createCommand($sql);
        $command->execute();


        $connection=Yii::app()->db_long;
        $command=$connection->createCommand($sql);
        $command->execute();
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