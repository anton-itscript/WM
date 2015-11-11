<?php

class m150818_072745_ODSS_reports extends CDbMigration
{
    protected $table="access_global";
    public function up()
    {
        $this->insert($this->table, array('controller'=>'Site','action'=>'StationTypeDataExport', 'enable'=>'1', 'description'=>''));
        $this->insert($this->table, array('controller'=>'Site','action'=>'StationTypeDataHistory', 'enable'=>'1', 'description'=>''));
        $this->insert($this->table, array('controller'=>'Site','action'=>'ScheduleTypeDownload', 'enable'=>'1', 'description'=>''));


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
          `start_datetime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
          `next_run_planned` timestamp NULL DEFAULT '0000-00-00 00:00:00',
          `ex_schedule_ident` varchar(255) DEFAULT NULL,
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
          `destination_ip_folder` varchar(255) NOT NULL DEFAULT '/',
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
          `sent` int(1) NOT NULL DEFAULT '0',
          `current_role` varchar(255) NOT NULL DEFAULT 'none',
          `check_period_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `check_period_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`ex_schedule_processed_id`)

        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;";
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
          `ex_schedule_id` int(11) NOT NULL  ,
          `station_type` varchar(255) NOT NULL,
          `report_type` varchar(50) NOT NULL DEFAULT 'synop' COMMENT 'synop, bufr',
          `period` smallint(6) NOT NULL DEFAULT '60' COMMENT 'in minutes',
          `report_format` varchar(20) NOT NULL DEFAULT 'csv' COMMENT 'txt, csv',
          `start_datetime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
          `next_run_planned` timestamp NULL DEFAULT '0000-00-00 00:00:00',
          `ex_schedule_ident` varchar(255) DEFAULT NULL,
          `active` int(1) DEFAULT '1',
          `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`ex_schedule_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


        -- ----------------------------
        -- Table structure for ex_schedule_report_destination
        -- ----------------------------
        DROP TABLE IF EXISTS `ex_schedule_report_destination`;
        CREATE TABLE `ex_schedule_report_destination` (
          `ex_schedule_destination_id` int(11) NOT NULL ,
          `ex_schedule_id` int(11) NOT NULL,
          `method` varchar(20) NOT NULL DEFAULT 'mail',
          `destination_email` varchar(255) NOT NULL,
          `destination_local_folder` varchar(255) NOT NULL,
          `destination_ip` varchar(15) NOT NULL,
          `destination_ip_port` smallint(5) NOT NULL DEFAULT '21',
          `destination_ip_folder` varchar(255) NOT NULL DEFAULT '/',
          `destination_ip_user` varchar(255) NOT NULL,
          `destination_ip_password` varchar(255) NOT NULL,
          PRIMARY KEY (`ex_schedule_destination_id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



        -- ----------------------------
        -- Table structure for ex_schedule_report_processed
        -- ----------------------------
        DROP TABLE IF EXISTS `ex_schedule_report_processed`;
        CREATE TABLE `ex_schedule_report_processed` (
          `ex_schedule_processed_id` int(11) NOT NULL ,
          `ex_schedule_id` int(11) NOT NULL,
          `sent` int(1) NOT NULL DEFAULT '0',
          `current_role` varchar(255) NOT NULL DEFAULT 'none',
          `check_period_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `check_period_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`ex_schedule_processed_id`)

        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        $connection=Yii::app()->db_long;
        $command=$connection->createCommand($sql);
        $command->execute();

    }

    public function down()
    {
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `action`="StationTypeDataExport" and `controller` = "Site" ');

        $sql = "
        SET FOREIGN_KEY_CHECKS=0;

        DROP TABLE IF EXISTS `ex_schedule_report`;
        DROP TABLE IF EXISTS `ex_schedule_report_destination`;
        DROP TABLE IF EXISTS `ex_schedule_report_processed`;";

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