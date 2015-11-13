<?php

class m150907_094330_ODSS_part2 extends CDbMigration
{
	public function up()
	{

        $sql = "

        SET FOREIGN_KEY_CHECKS=0;
        -- ----------------------------
        -- Table structure for ex_schedule_report
        -- ----------------------------
        DROP TABLE IF EXISTS `ex_schedule_report`;
        CREATE TABLE `ex_schedule_report` (
          `ex_schedule_id` int(11) NOT NULL AUTO_INCREMENT,
          `station_type` varchar(255) NOT NULL,
          `report_type` varchar(50) NOT NULL DEFAULT 'synop' COMMENT 'synop, bufr',
          `period` smallint(6) NOT NULL DEFAULT '60' COMMENT 'in minutes',
          `report_format` varchar(20) NOT NULL DEFAULT 'csv' COMMENT 'txt, csv',
          `start_datetime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `next_run_planned` timestamp NULL DEFAULT '0000-00-00 00:00:00',
          `ex_schedule_ident` varchar(255) DEFAULT NULL,
          `generation_delay` int(11) DEFAULT '0',
          `aging_time_delay` int(11) DEFAULT '10',
          `active` int(1) DEFAULT '1',
          `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`ex_schedule_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

        -- ----------------------------
        -- Table structure for ex_schedule_report_destination
        -- ----------------------------
        DROP TABLE IF EXISTS `ex_schedule_report_destination`;
        CREATE TABLE `ex_schedule_report_destination` (
          `ex_schedule_destination_id` int(11) NOT NULL AUTO_INCREMENT,
          `ex_schedule_id` int(11) NOT NULL,
          `method` varchar(20) NOT NULL DEFAULT 'mail',
          `destination_email` varchar(255) NOT NULL,
          `destination_local_folder` varchar(255) NOT NULL,
          `destination_ip` varchar(15) NOT NULL,
          `destination_ip_port` smallint(5) NOT NULL DEFAULT '21',
          `destination_ip_folder` varchar(255) NOT NULL DEFAULT '',
          `destination_ip_user` varchar(255) NOT NULL,
          `destination_ip_password` varchar(255) NOT NULL,
          PRIMARY KEY (`ex_schedule_destination_id`),
          KEY `ex_schedule_id` (`ex_schedule_id`) USING BTREE,
          CONSTRAINT `ex_schedule_report_destination_fk` FOREIGN KEY (`ex_schedule_id`) REFERENCES `ex_schedule_report` (`ex_schedule_id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

        -- ----------------------------
        -- Table structure for ex_schedule_report_processed
        -- ----------------------------
        DROP TABLE IF EXISTS `ex_schedule_report_processed`;
        CREATE TABLE `ex_schedule_report_processed` (
          `ex_schedule_processed_id` int(11) NOT NULL AUTO_INCREMENT,
          `ex_schedule_id` int(11) NOT NULL,
          `is_synchronized` int(1) DEFAULT '0',
          `aging_time` int(50) DEFAULT NULL,
          `current_role` varchar(255) NOT NULL DEFAULT 'none',
          `check_period_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `check_period_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`ex_schedule_processed_id`),
          KEY `sch_rep_processed__created` (`created`),
          KEY `schedule_report_processed_sr_fk` (`ex_schedule_id`),
          CONSTRAINT `proc_ex_schedule_id` FOREIGN KEY (`ex_schedule_id`) REFERENCES `ex_schedule_report` (`ex_schedule_id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

        -- ----------------------------
        -- Table structure for ex_schedule_send_log
        -- ----------------------------
        DROP TABLE IF EXISTS `ex_schedule_send_log`;
        CREATE TABLE `ex_schedule_send_log` (
          `ex_schedulte_send_log_id` int(11) NOT NULL AUTO_INCREMENT,
          `ex_schedule_processed_id` int(11) NOT NULL,
          `ex_schedule_destination_id` int(11) NOT NULL,
          `sent` int(1) DEFAULT '0',
          `send_logs` longblob,
          `updated` timestamp NULL DEFAULT NULL,
          `created` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`ex_schedulte_send_log_id`),
          KEY `log_ex_schedule_processed_id` (`ex_schedule_processed_id`),
          KEY `log_ex_schedule_destination_id` (`ex_schedule_destination_id`),
          CONSTRAINT `log_ex_schedule_destination_id` FOREIGN KEY (`ex_schedule_destination_id`) REFERENCES `ex_schedule_report_destination` (`ex_schedule_destination_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
          CONSTRAINT `log_ex_schedule_processed_id` FOREIGN KEY (`ex_schedule_processed_id`) REFERENCES `ex_schedule_report_processed` (`ex_schedule_processed_id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
        ";
        $connection=Yii::app()->db;
        $command=$connection->createCommand($sql);
        $command->execute();

        $sql = "

        SET FOREIGN_KEY_CHECKS=0;

        -- ----------------------------
        -- Table structure for ex_schedule_report
        -- ----------------------------
        DROP TABLE IF EXISTS `ex_schedule_report`;
        CREATE TABLE `ex_schedule_report` (
          `ex_schedule_id` int(11) NOT NULL,
          `station_type` varchar(255) NOT NULL,
          `report_type` varchar(50) NOT NULL DEFAULT 'synop' COMMENT 'synop, bufr',
          `period` smallint(6) NOT NULL DEFAULT '60' COMMENT 'in minutes',
          `report_format` varchar(20) NOT NULL DEFAULT 'csv' COMMENT 'txt, csv',
          `start_datetime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `next_run_planned` timestamp NULL DEFAULT '0000-00-00 00:00:00',
          `ex_schedule_ident` varchar(255) DEFAULT NULL,
          `generation_delay` int(11) DEFAULT '0',
          `aging_time_delay` int(11) DEFAULT '10',
          `active` int(1) DEFAULT '1',
          `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`ex_schedule_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- ----------------------------
        -- Table structure for ex_schedule_report_destination
        -- ----------------------------
        DROP TABLE IF EXISTS `ex_schedule_report_destination`;
        CREATE TABLE `ex_schedule_report_destination` (
          `ex_schedule_destination_id` int(11) NOT NULL,
          `ex_schedule_id` int(11) NOT NULL,
          `method` varchar(20) NOT NULL DEFAULT 'mail',
          `destination_email` varchar(255) NOT NULL,
          `destination_local_folder` varchar(255) NOT NULL,
          `destination_ip` varchar(15) NOT NULL,
          `destination_ip_port` smallint(5) NOT NULL DEFAULT '21',
          `destination_ip_folder` varchar(255) NOT NULL DEFAULT '',
          `destination_ip_user` varchar(255) NOT NULL,
          `destination_ip_password` varchar(255) NOT NULL,
          PRIMARY KEY (`ex_schedule_destination_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

        -- ----------------------------
        -- Table structure for ex_schedule_report_processed
        -- ----------------------------
        DROP TABLE IF EXISTS `ex_schedule_report_processed`;
        CREATE TABLE `ex_schedule_report_processed` (
          `ex_schedule_processed_id` int(11) NOT NULL,
          `ex_schedule_id` int(11) NOT NULL,
          `is_synchronized` int(1) DEFAULT '0',
          `aging_time` int(50) DEFAULT NULL,
          `current_role` varchar(255) NOT NULL DEFAULT 'none',
          `check_period_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `check_period_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`ex_schedule_processed_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

        -- ----------------------------
        -- Table structure for ex_schedule_send_log
        -- ----------------------------
        DROP TABLE IF EXISTS `ex_schedule_send_log`;
        CREATE TABLE `ex_schedule_send_log` (
          `ex_schedulte_send_log_id` int(11) NOT NULL,
          `ex_schedule_processed_id` int(11) NOT NULL,
          `ex_schedule_destination_id` int(11) NOT NULL,
          `sent` int(1) DEFAULT '0',
          `send_logs` longblob,
          `updated` timestamp NULL DEFAULT NULL,
          `created` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`ex_schedulte_send_log_id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ";

        $connection=Yii::app()->db_long;
        $command=$connection->createCommand($sql);
        $command->execute();

	}

	public function down()
	{

        $sql = "
        SET FOREIGN_KEY_CHECKS=0;

            DROP TABLE IF EXISTS `ex_schedule_report`;
            DROP TABLE IF EXISTS `ex_schedule_report_destination`;
            DROP TABLE IF EXISTS `ex_schedule_report_processed`;
            DROP TABLE IF EXISTS `ex_schedule_send_log`;
        ";

        $connection=Yii::app()->db_long;
        $command=$connection->createCommand($sql);
        $command->execute();

        $connection=Yii::app()->db;
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