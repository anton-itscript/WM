-- MySQL dump 10.13  Distrib 5.5.31, for Linux (x86_64)
--
-- Host: localhost    Database: delairco_wm
-- ------------------------------------------------------
-- Server version	5.5.31-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access_global`
--

DROP TABLE IF EXISTS `access_global`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access_global` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `controller` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `enable` enum('0','1') NOT NULL DEFAULT '0',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_action_controller` (`controller`,`action`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COMMENT='Global access, seting onli SuperAdmin';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_global`
--

LOCK TABLES `access_global` WRITE;
/*!40000 ALTER TABLE `access_global` DISABLE KEYS */;
INSERT INTO `access_global` VALUES (1,'Site','Index','1',NULL),(2,'Site','AwsPanel','1',''),(3,'Site','AwsSingle','1',NULL),(4,'Site','AwsGraph','1',NULL),(5,'Site','AwsTable','1',NULL),(6,'Site','RgPanel','1',''),(7,'Site','RgTable','1',''),(8,'Site','RgGraph','1',''),(9,'Site','MsgHistory','1',NULL),(10,'Site','Export','1',NULL),(11,'Site','Schedule','1',NULL),(12,'Site','Schedulehistory','1',NULL),(13,'Site','ScheduleDownload','1',NULL),(14,'Site','Login','1','Login page.'),(15,'Site','Logout','1',NULL),(16,'Admin','Index','1',NULL),(17,'Admin','Stations','1',NULL),(18,'Admin','StationSave','1',NULL),(19,'Admin','StationDelete','1',NULL),(20,'Admin','Sensors','1',NULL),(21,'Admin','CalculationSave','1',NULL),(22,'Admin','CalculationDelete','1',NULL),(23,'Admin','DeleteSensor','1',NULL),(24,'Admin','Sensor','1',NULL),(25,'Admin','Connections','1',NULL),(26,'Admin','ConnectionsLog','1',NULL),(27,'Admin','Xmllog','1',NULL),(28,'Admin','StartListening','1',NULL),(29,'Admin','StopListening','1',NULL),(30,'Admin','GetStatus','1',NULL),(31,'Admin','Setup','1',NULL),(32,'Admin','SetupOther','1',NULL),(33,'Admin','Dbsetup','1',NULL),(34,'Admin','SetupSensors','1',NULL),(35,'Admin','SetupSensor','1',''),(36,'Admin','Mailsetup','1',NULL),(37,'Admin','Importmsg','1',NULL),(38,'Admin','Importxml','1',NULL),(39,'Admin','MsgGeneration','1',NULL),(40,'Admin','AwsFiltered','1',''),(41,'Admin','DeleteForwardInfo','1',NULL),(42,'Admin','Users','1',NULL),(43,'Admin','User','1',NULL),(44,'Admin','UserDelete','1',NULL),(45,'Admin','UserAccessChange','1',NULL),(46,'Admin','ForwardList','1',NULL),(48,'Admin','StationGroups','1',''),(49,'Admin','HeartbeatReports','1','Heartbeat Reports List'),(50,'Admin','HeartbeatReport','1','View Heartbeat Report'),(51,'Admin','Coefficients','1','Setting Coefficients for Calculation'),(52,'Admin','EditSensor','1','Edit Station Sensor');
/*!40000 ALTER TABLE `access_global` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `access_user`
--

DROP TABLE IF EXISTS `access_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access_user` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action_id` int(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_action_user` (`user_id`,`action_id`),
  KEY `action_id` (`action_id`),
  CONSTRAINT `access_user_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `access_user_ibfk_2` FOREIGN KEY (`action_id`) REFERENCES `access_global` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Access user, seting Admin, SuperAdmin';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_user`
--

LOCK TABLES `access_user` WRITE;
/*!40000 ALTER TABLE `access_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `access_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_old_data`
--

DROP TABLE IF EXISTS `backup_old_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_old_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_timestamp_limit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `completed` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_old_data`
--

LOCK TABLES `backup_old_data` WRITE;
/*!40000 ALTER TABLE `backup_old_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_old_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_old_data_log`
--

DROP TABLE IF EXISTS `backup_old_data_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_old_data_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_id` int(11) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`log_id`),
  KEY `backup_id` (`backup_id`),
  CONSTRAINT `backup_old_data_log_fk` FOREIGN KEY (`backup_id`) REFERENCES `backup_old_data` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_old_data_log`
--

LOCK TABLES `backup_old_data_log` WRITE;
/*!40000 ALTER TABLE `backup_old_data_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_old_data_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calculation_handler`
--

DROP TABLE IF EXISTS `calculation_handler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calculation_handler` (
  `handler_id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `handler_id_code` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `metric_id` tinyint(4) NOT NULL,
  `default_prefix` varchar(2) NOT NULL,
  `aws_panel_display_position` tinyint(4) NOT NULL DEFAULT '0',
  `aws_panel_show` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`handler_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calculation_handler`
--

LOCK TABLES `calculation_handler` WRITE;
/*!40000 ALTER TABLE `calculation_handler` DISABLE KEYS */;
INSERT INTO `calculation_handler` VALUES (1,'DewPoint','Dew Point',2,'DP',0,1),(2,'PressureSeaLevel','Pressure MSL',20,'PS',0,1);
/*!40000 ALTER TABLE `calculation_handler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `config_id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `value` text,
  `default` text NOT NULL,
  `type` varchar(128) NOT NULL,
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES (1,'HEARTBEAT_REPORT_STATUS','status','0','0','bool'),(2,'HEARTBEAT_REPORT_PERIOD','period','T','d','char'),(3,'HEARTBEAT_REPORT_EMAIL','report to','alexandr.vysotsky@itscript.com','','array'),(4,'HEARTBEAT_REPORT_CLIENT_NAME','client name','Vas9','CLIENT','string'),(5,'SITE_AWSGRAPH_GAPSIZE','Site AWSGraph gapSize','3000','5','integer');
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `heartbeat_report`
--

DROP TABLE IF EXISTS `heartbeat_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `heartbeat_report` (
  `report_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(10) NOT NULL,
  `period` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `heartbeat_report`
--

LOCK TABLES `heartbeat_report` WRITE;
/*!40000 ALTER TABLE `heartbeat_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `heartbeat_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `heartbeat_report_data`
--

DROP TABLE IF EXISTS `heartbeat_report_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `heartbeat_report_data` (
  `report_data_id` int(13) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` int(11) unsigned NOT NULL,
  `handler` varchar(255) NOT NULL,
  `keys` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`report_data_id`),
  KEY `report_fk` (`report_id`),
  CONSTRAINT `report_fk` FOREIGN KEY (`report_id`) REFERENCES `heartbeat_report` (`report_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `heartbeat_report_data`
--

LOCK TABLES `heartbeat_report_data` WRITE;
/*!40000 ALTER TABLE `heartbeat_report_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `heartbeat_report_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listener`
--

DROP TABLE IF EXISTS `listener`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `listener` (
  `listener_id` int(11) NOT NULL AUTO_INCREMENT,
  `process_pid` mediumint(9) unsigned NOT NULL,
  `source` varchar(50) NOT NULL,
  `started` int(11) NOT NULL,
  `stopped` int(11) NOT NULL,
  `connection_result_code` varchar(20) NOT NULL,
  `connection_result_description` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`listener_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `listener`
--

LOCK TABLES `listener` WRITE;
/*!40000 ALTER TABLE `listener` DISABLE KEYS */;
/*!40000 ALTER TABLE `listener` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listener_log`
--

DROP TABLE IF EXISTS `listener_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `listener_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `listener_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `station_type` varchar(10) DEFAULT NULL,
  `station_id` smallint(6) NOT NULL DEFAULT '0',
  `measuring_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_processed` tinyint(1) NOT NULL DEFAULT '0',
  `is_processing` tinyint(1) NOT NULL DEFAULT '0',
  `is_last` tinyint(1) NOT NULL DEFAULT '0',
  `is_actual` tinyint(1) NOT NULL DEFAULT '0',
  `rewrite_prev_values` tinyint(1) NOT NULL DEFAULT '0',
  `source` varchar(15) NOT NULL DEFAULT '',
  `source_info` varchar(30) DEFAULT '',
  `failed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 - failed',
  `fail_description` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`log_id`),
  KEY `listener_id` (`listener_id`),
  KEY `listener_log__last_station_failed` (`is_last`,`station_id`,`failed`),
  KEY `listener_log__measuring_timestamp` (`measuring_timestamp`),
  KEY `listener_log__is_indexes` (`is_actual`,`is_last`,`is_processed`,`is_processing`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `listener_log`
--

LOCK TABLES `listener_log` WRITE;
/*!40000 ALTER TABLE `listener_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `listener_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listener_log_process_error`
--

DROP TABLE IF EXISTS `listener_log_process_error`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `listener_log_process_error` (
  `process_error_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` int(11) NOT NULL,
  `type` enum('error','warning') NOT NULL DEFAULT 'error',
  `code` varchar(255) NOT NULL,
  `description` text,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`process_error_id`),
  KEY `log_id` (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `listener_log_process_error`
--

LOCK TABLES `listener_log_process_error` WRITE;
/*!40000 ALTER TABLE `listener_log_process_error` DISABLE KEYS */;
/*!40000 ALTER TABLE `listener_log_process_error` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listener_process`
--

DROP TABLE IF EXISTS `listener_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `listener_process` (
  `listener_process_id` int(11) NOT NULL AUTO_INCREMENT,
  `listener_id` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`listener_process_id`),
  KEY `listener_id` (`listener_id`),
  CONSTRAINT `listener_process_fk` FOREIGN KEY (`listener_id`) REFERENCES `listener` (`listener_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `listener_process`
--

LOCK TABLES `listener_process` WRITE;
/*!40000 ALTER TABLE `listener_process` DISABLE KEYS */;
/*!40000 ALTER TABLE `listener_process` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refbook_measurement_type`
--

DROP TABLE IF EXISTS `refbook_measurement_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refbook_measurement_type` (
  `measurement_type_id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `display_name` varchar(255) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `ord` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`measurement_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refbook_measurement_type`
--

LOCK TABLES `refbook_measurement_type` WRITE;
/*!40000 ALTER TABLE `refbook_measurement_type` DISABLE KEYS */;
INSERT INTO `refbook_measurement_type` VALUES (1,'Temperature','temperature',1),(2,'Rain fall','rain',2),(3,'Humidity','humidity',4),(4,'Wind Speed','wind_speed',5),(5,'Wind Direction','wind_direction',6),(6,'Pressure','pressure',7),(8,'Sea Level','sea_level',8),(9,'Solar Radiation','solar_radiation',9),(10,'Sunshine duration','sun_duration',10),(11,'Visibility','visibility',11),(12,'Battery Voltage','battery_voltage',0),(13,'Depth','depth',12),(14,'Rain Gauge Bucket Size','bucket_size',3),(15,'Height','height',13),(16,'Cloud Vertical Visibility','cloud_vertical_visibility',14),(17,'Cloud Height','cloud_height',15),(18,'Sea Level (Mean, Sigma)','sea_level',16),(19,'Treshold Period','treshold_period',0),
(20,'Sea Level (Wave Height)','sea_level_wave_height',17),
(21, 'Cloud Measuring Range', 'cloud_measuring_range', 18),
(22, 'Snow Depth', 'snow_depth', 19);
/*!40000 ALTER TABLE `refbook_measurement_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refbook_measurement_type_metric`
--

DROP TABLE IF EXISTS `refbook_measurement_type_metric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refbook_measurement_type_metric` (
  `measurement_type_metric_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `measurement_type_id` tinyint(4) NOT NULL,
  `metric_id` tinyint(4) NOT NULL,
  `is_main` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`measurement_type_metric_id`),
  KEY `measurement_type_id` (`measurement_type_id`),
  KEY `metric_id` (`metric_id`),
  CONSTRAINT `refbook_measurement_type_metric_fk` FOREIGN KEY (`measurement_type_id`) REFERENCES `refbook_measurement_type` (`measurement_type_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `refbook_measurement_type_metric_fk1` FOREIGN KEY (`metric_id`) REFERENCES `refbook_metric` (`metric_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refbook_measurement_type_metric`
--

LOCK TABLES `refbook_measurement_type_metric` WRITE;
/*!40000 ALTER TABLE `refbook_measurement_type_metric` DISABLE KEYS */;
INSERT INTO `refbook_measurement_type_metric` VALUES (1,12,1,1),(2,1,2,1),(3,1,3,0),(4,2,15,0),(6,2,4,1),(7,3,7,1),(8,4,8,0),(9,4,9,1),(10,5,14,1),(11,6,10,0),(12,8,11,1),(13,9,12,1),(14,10,13,1),(16,13,5,1),(17,1,16,0),(18,4,17,0),(19,4,18,0),(21,15,11,1),(22,6,20,1),(23,9,21,0),(24,16,22,1),(25,16,11,0),(26,17,22,1),(27,17,11,0),(28,11,11,1),(29,18,11,1),(30,19,13,1),(31,20,5,1),(32,21,11,1),(33,21,22,0),(34,22,11,1),(35,14,4,1),
(36,14,4,1);

/*!40000 ALTER TABLE `refbook_measurement_type_metric` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refbook_metric`
--

DROP TABLE IF EXISTS `refbook_metric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refbook_metric` (
  `metric_id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `html_code` varchar(50) NOT NULL,
  `short_name` varchar(10) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `code` varchar(30) NOT NULL,
  PRIMARY KEY (`metric_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refbook_metric`
--

LOCK TABLES `refbook_metric` WRITE;
/*!40000 ALTER TABLE `refbook_metric` DISABLE KEYS */;
INSERT INTO `refbook_metric` VALUES (1,'V','V','Volt','volt'),(2,'&deg;C',' C degree','Celsius degree','celsius'),(3,'F','F','Fahrenheit','farenheit'),(4,'mm','mm','Milimeter','millimeter'),(5,'cm','cm','Centimeter','centimeter'),(6,'G','G','Gallon','gallon'),(7,'%','%','Percent','percent'),(8,'Knot','Knot','Knot','knot'),(9,'m/s','m/s','Meter per second','meter_per_second'),(10,'Pa','Pa','Pascal','pascal'),(11,'m','m','Meter','meter'),(12,'J/sq.m','J/sq.m','Joule per square meter','joule_per_sq_meter'),(13,'min','min','Minute','minute'),(14,'&deg;','degree','Degree','degree'),(15,'inch','inch','Inch','inch'),(16,'K','K','Kelvin','kelvin'),(17,'mph','mph','Miles per hour','miles_per_hour'),(18,'km/h','km/h','Kilometers per hour','kilometers_per_hour'),(20,'hPa','hPa','100 Pascal','hpascal'),(21,'kJ/sq.m','kJ/sq.m','Kilo Joule per square meter','kjoule_per_sq_meter'),(22,'ft','ft','Feet','feet'),(23,'km','km','Kilometer','kilometer'),(24,'inHg','inHg','inHg','inHg');
/*!40000 ALTER TABLE `refbook_metric` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_report`
--

DROP TABLE IF EXISTS `schedule_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_report` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` varchar(50) NOT NULL DEFAULT 'synop' COMMENT 'synop, bufr',
  `station_id` smallint(6) NOT NULL,
  `period` smallint(6) NOT NULL DEFAULT '60' COMMENT 'in minutes',
  `report_format` varchar(20) NOT NULL DEFAULT 'csv' COMMENT 'txt, csv',
  `last_scheduled_run_fact` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_scheduled_run_planned` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`schedule_id`),
  KEY `fk_schedule_report__station_id` (`station_id`),
  CONSTRAINT `fk_schedule_report__station_id` FOREIGN KEY (`station_id`) REFERENCES `station` (`station_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_report`
--

LOCK TABLES `schedule_report` WRITE;
/*!40000 ALTER TABLE `schedule_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_report_destination`
--

DROP TABLE IF EXISTS `schedule_report_destination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_report_destination` (
  `schedule_destination_id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL,
  `method` varchar(20) NOT NULL DEFAULT 'mail',
  `destination_email` varchar(255) NOT NULL,
  `destination_local_folder` varchar(255) NOT NULL,
  `destination_ip` varchar(15) NOT NULL,
  `destination_ip_port` smallint(5) NOT NULL DEFAULT '21',
  `destination_ip_folder` varchar(255) NOT NULL DEFAULT '/',
  `destination_ip_user` varchar(255) NOT NULL,
  `destination_ip_password` varchar(255) NOT NULL,
  PRIMARY KEY (`schedule_destination_id`),
  KEY `schedule_id` (`schedule_id`),
  CONSTRAINT `schedule_report_destination_fk` FOREIGN KEY (`schedule_id`) REFERENCES `schedule_report` (`schedule_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_report_destination`
--

LOCK TABLES `schedule_report_destination` WRITE;
/*!40000 ALTER TABLE `schedule_report_destination` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_report_destination` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_report_processed`
--

DROP TABLE IF EXISTS `schedule_report_processed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_report_processed` (
  `schedule_processed_id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL,
  `check_period_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `check_period_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `listener_log_id` int(11) NOT NULL,
  `listener_log_ids` text NOT NULL,
  `is_processed` tinyint(1) NOT NULL DEFAULT '0',
  `serialized_report_errors` text NOT NULL,
  `serialized_report_explanations` text NOT NULL,
  `is_last` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`schedule_processed_id`),
  KEY `schedule_id` (`schedule_id`),
  KEY `listener_log_id` (`listener_log_id`),
  KEY `sch_rep_processed__is_indexes` (`is_last`,`is_processed`),
  KEY `sch_rep_processed__created` (`created`),
  CONSTRAINT `schedule_report_processed_fk` FOREIGN KEY (`schedule_id`) REFERENCES `schedule_report` (`schedule_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_report_processed`
--

LOCK TABLES `schedule_report_processed` WRITE;
/*!40000 ALTER TABLE `schedule_report_processed` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_report_processed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensor_data`
--

DROP TABLE IF EXISTS `sensor_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sensor_data` (
  `sensor_data_id` int(11) NOT NULL AUTO_INCREMENT,
  `station_id` smallint(6) NOT NULL,
  `sensor_id` int(11) NOT NULL,
  `sensor_feature_id` int(11) NOT NULL,
  `sensor_feature_value` tinytext NOT NULL,
  `is_m` enum('1','0') NOT NULL DEFAULT '0',
  `metric_id` tinyint(4) NOT NULL,
  `sensor_feature_normalized_value` decimal(15,4) NOT NULL,
  `sensor_feature_exp_value` smallint(6) NOT NULL DEFAULT '0',
  `period` smallint(6) NOT NULL COMMENT 'in minutes',
  `listener_log_id` int(11) NOT NULL,
  `measuring_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`sensor_data_id`),
  KEY `sensor_feature_id` (`sensor_feature_id`),
  KEY `listener_log_id` (`listener_log_id`),
  KEY `sensor_data_sensor_id_index` (`sensor_id`),
  KEY `sensor_data_station_id_index` (`station_id`),
  KEY `sensor_data__feature_measuuring_index` (`sensor_feature_id`,`measuring_timestamp`,`is_m`),
  KEY `sensor_data__log_feature_index` (`listener_log_id`,`sensor_feature_id`,`measuring_timestamp`),
  KEY `sensor_data__measuring_timestamp` (`measuring_timestamp`),
  CONSTRAINT `sensor_data_fk` FOREIGN KEY (`sensor_feature_id`) REFERENCES `station_sensor_feature` (`sensor_feature_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sensor_data_fk1` FOREIGN KEY (`listener_log_id`) REFERENCES `listener_log` (`log_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensor_data`
--

LOCK TABLES `sensor_data` WRITE;
/*!40000 ALTER TABLE `sensor_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `sensor_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensor_data_minute`
--

DROP TABLE IF EXISTS `sensor_data_minute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sensor_data_minute` (
  `sensor_data_id` int(11) NOT NULL AUTO_INCREMENT,
  `sensor_id` int(11) NOT NULL,
  `station_id` smallint(6) NOT NULL,
  `sensor_value` smallint(6) NOT NULL DEFAULT '0',
  `metric_id` tinyint(4) NOT NULL,
  `sensor_feature_normalized_value` int(11) NOT NULL,
  `5min_sum` smallint(6) NOT NULL DEFAULT '0',
  `10min_sum` smallint(6) NOT NULL DEFAULT '0',
  `20min_sum` smallint(6) NOT NULL DEFAULT '0',
  `30min_sum` smallint(6) NOT NULL DEFAULT '0',
  `60min_sum` smallint(6) NOT NULL DEFAULT '0',
  `1day_sum` mediumint(9) NOT NULL DEFAULT '0',
  `bucket_size` decimal(11,2) NOT NULL DEFAULT '0.00',
  `listener_log_id` int(11) NOT NULL COMMENT 'id of message received',
  `measuring_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `battery_voltage` decimal(11,2) NOT NULL DEFAULT '0.00',
  `is_tmp` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`sensor_data_id`),
  KEY `listener_log_id` (`listener_log_id`),
  KEY `sensor_id` (`sensor_id`),
  CONSTRAINT `sensor_data_minute_fk` FOREIGN KEY (`listener_log_id`) REFERENCES `listener_log` (`log_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sensor_data_minute_fk1` FOREIGN KEY (`sensor_id`) REFERENCES `station_sensor` (`station_sensor_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensor_data_minute`
--

LOCK TABLES `sensor_data_minute` WRITE;
/*!40000 ALTER TABLE `sensor_data_minute` DISABLE KEYS */;
/*!40000 ALTER TABLE `sensor_data_minute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensor_handler`
--

DROP TABLE IF EXISTS `sensor_handler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sensor_handler` (
  `handler_id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `handler_id_code` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `handler_default_display_name` varchar(255) DEFAULT NULL,
  `default_prefix` varchar(2) NOT NULL,
  `aws_panel_display_position` tinyint(4) NOT NULL,
  `aws_panel_show` tinyint(1) NOT NULL DEFAULT '0',
  `aws_single_display_position` tinyint(4) NOT NULL DEFAULT '0',
  `aws_single_group` varchar(255) NOT NULL,
  `aws_station_uses` tinyint(1) NOT NULL DEFAULT '0',
  `rain_station_uses` tinyint(1) NOT NULL DEFAULT '0',
  `awa_station_uses` tinyint(1) NOT NULL DEFAULT '0',
  `flags` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Bit flags',
  `start_time` tinyint(2) NOT NULL DEFAULT '-1',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`handler_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensor_handler`
--

LOCK TABLES `sensor_handler` WRITE;
/*!40000 ALTER TABLE `sensor_handler` DISABLE KEYS */;
-- INSERT INTO `sensor_handler` VALUES (1,'BatteryVoltage','Battery Voltage','BV',0,1,0,'battery_voltage',1,0,0,25,-1,'0000-00-00 00:00:00','2014-10-22 02:46:01'),(2,'Humidity','Humidity','HU',0,1,2,'temperature_and_humidity',1,0,0,25,0,'0000-00-00 00:00:00','2014-10-22 02:38:43'),(3,'Pressure','Pressure','PR',0,1,3,'pressure',1,0,0,25,0,'0000-00-00 00:00:00','2014-10-22 02:46:01'),(4,'RainAws','Rain AWS','RN',0,1,4,'rain',1,0,0,25,0,'0000-00-00 00:00:00','2014-10-22 09:23:23'),(5,'RainRg','Rain RG','RN',0,1,0,'',0,1,0,26,7,'0000-00-00 00:00:00','2014-10-22 02:38:43'),(6,'SolarRadiation','Solar Radiation','SR',0,1,8,'sun',1,0,0,25,-1,'0000-00-00 00:00:00','2014-10-22 02:38:43'),(7,'SunshineDuration','Sunshine Duration','SD',0,1,8,'sun',1,0,0,25,4,'0000-00-00 00:00:00','2014-10-24 13:34:22'),(8,'Temperature','Temperature','TP',0,1,2,'temperature_and_humidity',1,0,0,25,5,'0000-00-00 00:00:00','2014-10-23 09:43:08'),(9,'TemperatureSoil','Temperature Soil','TP',0,1,5,'temperature_soil',1,0,0,25,12,'0000-00-00 00:00:00','2014-10-23 09:43:08'),(10,'WindDirection','Wind Direction','WD',0,1,1,'wind',1,0,0,25,0,'0000-00-00 00:00:00','2014-10-22 02:38:43'),(11,'WindSpeed','Wind Speed','WS',0,1,1,'wind',1,0,0,25,-1,'0000-00-00 00:00:00','2014-10-22 02:38:43'),(12,'DewPoint','Dew Point','',0,1,2,'temperature_and_humidity',0,0,0,24,-1,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(13,'SeaLevelAWS','Sea Level and Tide Data','SL',0,0,6,'sea_level',1,0,0,25,-1,'0000-00-00 00:00:00','2014-10-24 08:30:02'),(14,'VisibilityAWS','Visibility without extinction','VI',0,0,7,'visibility',1,0,0,25,-1,'0000-00-00 00:00:00','2014-10-24 08:29:12'),(15,'CloudHeightAWS','Cloud Depth','CH',0,0,9,'clouds',1,0,0,25,-1,'0000-00-00 00:00:00','2014-10-24 08:29:12'),(16,'TemperatureWater','Temperature Water','TP',0,1,5,'temperature_soil',1,0,0,25,0,'0000-00-00 00:00:00','2014-10-23 09:43:08'),(17,'VisibilityAwsDlm13m','Visibility with extinction','VI',0,0,7,'visibility',1,0,0,17,-1,'0000-00-00 00:00:00','2014-10-24 08:29:12'),(18,'CloudHeightAwsDlm13m','Cloud Amount','CH',0,1,9,'clouds',1,0,0,17,-1,'0000-00-00 00:00:00','2014-10-28 21:09:01'),(19,'SnowDepthAwsDlm13m','Snow Depth','SN',0,0,9,'snow_depth',1,0,0,17,0,'0000-00-00 00:00:00','2014-10-24 08:29:12');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (1,'BatteryVoltage','Battery Voltage','Battery Voltage','BV',0,1,0,'battery_voltage',1,0,0,25,-1,'0000-00-00 00:00:00','2015-03-13 15:37:08');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (2,'Humidity','Humidity','Humidity','HU',0,1,2,'temperature_and_humidity',1,0,0,25,7,'0000-00-00 00:00:00','2015-03-13 15:35:07');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (3,'Pressure','Pressure','Pressure','PR',0,1,3,'pressure',1,0,0,25,3,'0000-00-00 00:00:00','2015-02-19 16:43:51');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (4,'RainAws','Rain AWS','Rain AWS','RN',0,1,4,'rain',1,0,0,25,9,'0000-00-00 00:00:00','2015-03-16 10:31:51');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (5,'RainRg','Rain RG','Rain RG','RN',0,1,0,'',0,1,0,26,7,'0000-00-00 00:00:00','2014-10-22 09:38:43');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (6,'SolarRadiation','Solar Radiation','Solar Radiation','SR',0,1,8,'sun',1,0,0,25,4,'0000-00-00 00:00:00','2015-02-08 15:36:53');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (7,'SunshineDuration','Sunshine Duration','Sunshine Duration','SD',0,1,8,'sun',1,0,0,25,4,'0000-00-00 00:00:00','2015-01-25 13:11:41');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (8,'Temperature','Temperature','Temperature','TA',0,1,2,'temperature_and_humidity',1,0,0,25,10,'0000-00-00 00:00:00','2015-03-16 10:36:15');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (9,'TemperatureSoil','Temperature Soil','Temperature Soil','TS',0,1,5,'temperature_soil',1,0,0,25,12,'0000-00-00 00:00:00','2015-03-02 13:57:36');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (10,'WindDirection','Wind Direction','Wind Direction','WD',0,1,1,'wind',1,0,0,25,0,'0000-00-00 00:00:00','2015-03-16 10:36:04');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (11,'WindSpeed','Wind Speed','Wind Speedy','WS',0,1,1,'wind',1,0,0,25,0,'0000-00-00 00:00:00','2015-03-17 09:40:47');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (12,'DewPoint','Dew Point','Dew Point','',0,1,2,'temperature_and_humidity',0,0,0,24,-1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (13,'SeaLevelAWS','Sea Level and Tide Data','Sea Level and Tide Data','SL',0,0,6,'sea_level',1,0,0,25,-1,'0000-00-00 00:00:00','2014-10-24 15:30:02');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (14,'VisibilityAWS','Visibility without Extinction','Visibility without Extinction','VI',0,0,7,'visibility',1,0,0,25,-1,'0000-00-00 00:00:00','2014-10-24 15:29:12');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (15,'CloudHeightAWS','Cloud Depth','Cloud Depth','CH',0,0,9,'clouds',1,0,0,25,-1,'0000-00-00 00:00:00','2014-10-24 15:29:12');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (16,'TemperatureWater','Temperature Water','Temperature Water','TW',0,1,5,'temperature_soil',1,0,0,25,0,'0000-00-00 00:00:00','2014-10-23 16:43:08');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (17,'VisibilityAwsDlm13m','Visibility with Extinction','Visibility with Extinction','VI',0,0,7,'visibility',1,0,0,17,-1,'0000-00-00 00:00:00','2014-10-24 15:29:12');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (18,'CloudHeightAwsDlm13m','Cloud Amount','Cloud Amount','CH',0,1,9,'clouds',1,0,0,17,-1,'0000-00-00 00:00:00','2014-10-29 04:09:01');
insert  into `sensor_handler`(`handler_id`,`handler_id_code`,`display_name`,`handler_default_display_name`,`default_prefix`,`aws_panel_display_position`,`aws_panel_show`,`aws_single_display_position`,`aws_single_group`,`aws_station_uses`,`rain_station_uses`,`awa_station_uses`,`flags`,`start_time`,`created`,`updated`) values (19,'SnowDepthAwsDlm13m','Snow Depth','Snow Depth','SN',0,0,9,'snow_depth',1,0,0,17,0,'0000-00-00 00:00:00','2015-02-08 16:05:48');

/*!40000 ALTER TABLE `sensor_handler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensor_handler_default_feature`
--

DROP TABLE IF EXISTS `sensor_handler_default_feature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sensor_handler_default_feature` (
  `handler_feature_id` int(11) NOT NULL AUTO_INCREMENT,
  `handler_id` tinyint(4) NOT NULL,
  `feature_code` varchar(50) NOT NULL,
  `aws_panel_show` tinyint(1) NOT NULL DEFAULT '0',
  `feature_constant_value` decimal(11,3) NOT NULL,
  `metric_id` tinyint(4) NOT NULL,
  `filter_max` decimal(11,2) NOT NULL,
  `filter_min` decimal(11,2) NOT NULL,
  `filter_diff` decimal(11,2) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`handler_feature_id`),
  KEY `handler_id` (`handler_id`),
  CONSTRAINT `sensor_handler_default_feature_fk` FOREIGN KEY (`handler_id`) REFERENCES `sensor_handler` (`handler_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensor_handler_default_feature`
--

LOCK TABLES `sensor_handler_default_feature` WRITE;
/*!40000 ALTER TABLE `sensor_handler_default_feature` DISABLE KEYS */;
INSERT INTO `sensor_handler_default_feature` VALUES (84,1,'battery_voltage',1,0.000,1,14.00,10.00,0.00,'2014-10-17 08:26:35','2014-10-23 11:07:50'),
(85,18,'cloud_vertical_visibility',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:05'),
(86,18,'cloud_measuring_range',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:05'),
(87,18,'cloud_height_height_1',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(88,18,'cloud_height_depth_1',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(89,18,'cloud_height_height_2',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(90,18,'cloud_height_depth_2',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(91,18,'cloud_height_height_3',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(92,18,'cloud_height_depth_3',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(93,18,'status',0,0.000,0,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(94,18,'cloud_amount_amount_1',1,0.000,0,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(95,18,'cloud_amount_height_1',1,0.000,22,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(96,18,'cloud_amount_amount_2',0,0.000,0,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(97,18,'cloud_amount_height_2',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(98,18,'cloud_amount_amount_3',0,0.000,0,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(99,18,'cloud_amount_height_3',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(100,18,'cloud_amount_amount_4',0,0.000,0,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(101,18,'cloud_amount_height_4',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(102,18,'cloud_amount_amount_total',0,0.000,0,0.00,0.00,0.00,'2014-10-17 08:26:52','2014-10-24 02:35:06'),
(103,15,'cloud_vertical_visibility',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:27:03','2014-10-24 02:37:30'),
(104,15,'cloud_measuring_range',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:27:03','2014-10-24 02:37:30'),
(105,15,'cloud_height_height_1',1,0.000,22,0.00,0.00,0.00,'2014-10-17 08:27:03','2014-10-24 02:37:30'),
(106,15,'cloud_height_depth_1',1,0.000,22,0.00,0.00,0.00,'2014-10-17 08:27:03','2014-10-24 02:37:30'),
(107,15,'cloud_height_height_2',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:27:03','2014-10-24 02:37:30'),
(108,15,'cloud_height_depth_2',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:27:03','2014-10-24 02:37:30'),
(109,15,'cloud_height_height_3',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:27:03','2014-10-24 02:37:30'),
(110,15,'cloud_height_depth_3',0,0.000,22,0.00,0.00,0.00,'2014-10-17 08:27:03','2014-10-24 02:37:30'),
(111,2,'humidity',1,0.000,7,80.00,0.00,0.00,'2014-10-17 08:27:46','2014-10-17 09:38:44'),
(112,3,'pressure',1,0.000,20,0.00,0.00,0.00,'2014-10-17 08:27:51','2014-10-21 05:55:16'),
(113,3,'height',0,0.000,11,0.00,0.00,0.00,'2014-10-17 08:27:51','2014-10-21 05:55:16'),
(114,4,'rain_in_period',1,0.000,15,0.00,0.00,0.00,'2014-10-17 08:27:55','2014-10-20 08:27:23'),
(115,4,'rain_in_day',0,0.000,15,0.00,0.00,0.00,'2014-10-17 08:27:55','2014-10-20 08:27:23'),
(116,5,'rain',0,0.000,15,2.00,0.00,2.00,'2014-10-17 08:27:58','2014-10-20 06:22:54'),
(117,5,'bucket_size',0,0.000,0,0.00,0.00,0.00,'2014-10-17 08:27:58','2014-10-20 06:22:54'),
(118,13,'sea_level_mean',1,0.000,11,0.00,0.00,0.00,'2014-10-17 08:28:01','2014-10-17 08:28:01'),
(119,13,'sea_level_sigma',0,0.000,11,0.00,0.00,0.00,'2014-10-17 08:28:01','2014-10-17 08:28:01'),
(120,13,'sea_level_wave_height',0,0.000,5,0.00,0.00,0.00,'2014-10-17 08:28:01','2014-10-17 08:28:01'),
(121,13,'sl_baseline',0,0.000,0,0.00,0.00,0.00,'2014-10-17 08:28:01','2014-10-17 08:28:01'),
(122,13,'sl_trend_treshold',0,0.000,0,0.00,0.00,0.00,'2014-10-17 08:28:01','2014-10-17 08:28:01'),
(123,13,'sl_trend_avg_calculate_period',0,30.000,13,0.00,0.00,0.00,'2014-10-17 08:28:01','2014-10-17 08:28:01'),
(124,19,'snow_depth',1,0.000,11,0.00,0.00,0.00,'2014-10-17 08:28:06','2014-10-17 08:28:06'),
(125,19,'error_code',0,0.000,0,0.00,0.00,0.00,'2014-10-17 08:28:06','2014-10-17 08:28:06'),
(126,6,'solar_radiation_in_period',1,0.000,12,0.00,0.00,0.00,'2014-10-17 08:28:12','2014-10-17 08:28:12'),
(127,6,'solar_radiation_in_day',0,0.000,12,0.00,0.00,0.00,'2014-10-17 08:28:12','2014-10-17 08:28:12'),
(128,7,'sun_duration_in_period',1,0.000,13,0.00,0.00,0.00,'2014-10-17 08:28:16','2014-10-24 06:15:40'),
(129,7,'sun_duration_in_day',0,0.000,13,0.00,0.00,0.00,'2014-10-17 08:28:16','2014-10-24 06:15:40'),
(130,8,'temperature',1,0.000,2,0.00,0.00,0.00,'2014-10-17 08:28:20','2014-10-17 08:28:45'),
(131,9,'temperature',1,0.000,2,0.00,0.00,0.00,'2014-10-17 08:28:24','2014-10-17 09:31:30'),
(132,9,'depth',0,0.000,5,0.00,0.00,0.00,'2014-10-17 08:28:24','2014-10-17 09:31:30'),
(133,16,'temperature',1,0.000,2,0.00,0.00,0.00,'2014-10-17 08:28:33','2014-10-17 08:28:33'),
(134,16,'depth',0,0.000,5,0.00,0.00,0.00,'2014-10-17 08:28:33','2014-10-17 08:28:33'),
(135,17,'visibility_1',1,0.000,11,0.00,0.00,0.00,'2014-10-17 08:29:07','2014-10-17 08:29:07'),
(136,17,'visibility_10',0,0.000,11,0.00,0.00,0.00,'2014-10-17 08:29:07','2014-10-17 08:29:07'),
(137,17,'extinction',0,0.000,0,0.00,0.00,0.00,'2014-10-17 08:29:07','2014-10-17 08:29:07'),
(138,17,'status',0,0.000,0,0.00,0.00,0.00,'2014-10-17 08:29:07','2014-10-17 08:29:07'),
(139,14,'visibility_1',1,0.000,11,0.00,0.00,0.00,'2014-10-17 08:29:11','2014-10-17 08:29:11'),
(140,14,'visibility_10',0,0.000,11,0.00,0.00,0.00,'2014-10-17 08:29:11','2014-10-17 08:29:11'),
(141,10,'wind_direction_1',1,0.000,14,303.00,0.00,300.00,'2014-10-17 08:29:13','2014-10-20 04:26:18'),
(142,10,'wind_direction_2',0,0.000,14,300.00,0.00,300.00,'2014-10-17 08:29:13','2014-10-20 04:26:18'),
(143,10,'wind_direction_10',0,0.000,14,300.00,0.00,300.00,'2014-10-17 08:29:13','2014-10-20 04:26:18'),
(144,11,'wind_speed_1',1,0.000,18,0.00,0.00,1.00,'2014-10-17 08:29:16','2014-10-21 05:33:02'),
(145,11,'wind_speed_2',0,0.000,18,0.00,0.00,1.00,'2014-10-17 08:29:16','2014-10-21 05:33:02'),
(146,11,'wind_speed_10',0,0.000,18,0.00,0.00,1.00,'2014-10-17 08:29:16','2014-10-21 05:33:02');
/*!40000 ALTER TABLE `sensor_handler_default_feature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensor_sea_level_trend`
--

DROP TABLE IF EXISTS `sensor_sea_level_trend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sensor_sea_level_trend` (
  `trend_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` int(11) NOT NULL,
  `sensor_id` int(11) NOT NULL,
  `trend` varchar(20) NOT NULL COMMENT 'up / down / no_change / unknown',
  `is_significant` enum('0','1') NOT NULL DEFAULT '0',
  `last_high` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `last_high_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_low` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `last_low_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `measuring_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`trend_id`),
  KEY `sensor_sea_level_trend_fk` (`log_id`),
  KEY `sensor_id` (`sensor_id`),
  CONSTRAINT `sensor_sea_level_trend_fk` FOREIGN KEY (`log_id`) REFERENCES `listener_log` (`log_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sensor_sea_level_trend_fk1` FOREIGN KEY (`sensor_id`) REFERENCES `station_sensor` (`station_sensor_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensor_sea_level_trend`
--

LOCK TABLES `sensor_sea_level_trend` WRITE;
/*!40000 ALTER TABLE `sensor_sea_level_trend` DISABLE KEYS */;
/*!40000 ALTER TABLE `sensor_sea_level_trend` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `database_version` varchar(10) NOT NULL DEFAULT '0.4.27',
  `overwrite_data_on_import` tinyint(1) NOT NULL DEFAULT '1',
  `overwrite_data_on_listening` tinyint(1) NOT NULL DEFAULT '0',
  `process_message_pid` int(11) NOT NULL,
  `current_company_name` varchar(255) DEFAULT NULL,
  `scheduled_reports_path` varchar(255) NOT NULL DEFAULT 'C:\\weather_monitor_reports',
  `xml_messages_path` varchar(255) NOT NULL DEFAULT 'C:\\ftp\\xml_messages',
  `xml_check_frequency` tinyint(2) NOT NULL DEFAULT '15',
  `mail__use_fake_sendmail` tinyint(1) NOT NULL DEFAULT '1',
  `mail__sender_address` varchar(255) NOT NULL DEFAULT 'delairco@gmail.com',
  `mail__sender_name` varchar(255) NOT NULL DEFAULT 'Delairco',
  `mail__sender_password` varchar(255) NOT NULL DEFAULT 'delaircoweathermonitor',
  `mail__smtp_server` varchar(255) NOT NULL DEFAULT 'smtp.gmail.com',
  `mail__smtp_port` mediumint(9) NOT NULL DEFAULT '587',
  `mail__smtps_support` enum('auto','ssl','tls','none') NOT NULL DEFAULT 'tls',
  `local_timezone_id` varchar(100) NOT NULL DEFAULT 'UTC',
  `local_timezone_offset` varchar(20) NOT NULL DEFAULT '+00:00',
  `db_exp_enabled` enum('0','1') NOT NULL DEFAULT '0',
  `db_exp_period` enum('1','10','30','60','90') NOT NULL DEFAULT '90',
  `db_exp_frequency` enum('1','5','10','30') NOT NULL DEFAULT '30',
  `db_exp_sql_host` varchar(255) NOT NULL,
  `db_exp_sql_port` smallint(5) unsigned NOT NULL DEFAULT '3306',
  `db_exp_sql_dbname` varchar(255) NOT NULL,
  `db_exp_sql_login` varchar(255) NOT NULL,
  `db_exp_sql_password` varchar(255) NOT NULL,
  PRIMARY KEY (`setting_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'0.06.00',1,0,0,'Your company name','C:\\weather_monitor_reports','C:\\weather_monitor_ftp\\xml_messages',5,1,'delairco@gmail.com','Delairco','delaircoweathermonitor','smtp.gmail.com',587,'tls','UTC','+00:00','0','10','1','localhost',3306,'','','');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station`
--

DROP TABLE IF EXISTS `station`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station` (
  `station_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `display_name` varchar(255) NOT NULL,
  `station_id_code` varchar(5) NOT NULL,
  `station_number` varchar(3) NOT NULL,
  `station_type` enum('rain','aws','awos') NOT NULL DEFAULT 'rain',
  `logger_type` enum('DLM11','DLM13M') NOT NULL DEFAULT 'DLM11' COMMENT 'Data logger type',
  `communication_type` enum('direct','sms','tcpip','gprs','server') NOT NULL DEFAULT 'direct',
  `communication_port` varchar(5) NOT NULL,
  `communication_esp_ip` varchar(15) NOT NULL DEFAULT '',
  `communication_esp_port` int(11) NOT NULL DEFAULT '0',
  `details` text,
  `status_message_period` smallint(6) NOT NULL DEFAULT '60' COMMENT 'minutes',
  `event_message_period` smallint(6) NOT NULL DEFAULT '10' COMMENT 'minutes',
  `timezone_id` varchar(255) NOT NULL DEFAULT 'UTC',
  `timezone_offset` varchar(20) NOT NULL,
  `wmo_block_number` varchar(3) DEFAULT NULL,
  `wmo_member_state_id` varchar(3) DEFAULT NULL,
  `wmo_station_number` int(11) NOT NULL DEFAULT '0',
  `wmo_originating_centre` int(11) NOT NULL DEFAULT '202',
  `national_aws_number` int(11) DEFAULT '0',
  `lat` decimal(20,10) NOT NULL DEFAULT '0.0000000000',
  `lng` decimal(20,10) NOT NULL DEFAULT '0.0000000000',
  `altitude` int(9) NOT NULL DEFAULT '0',
  `magnetic_north_offset` tinyint(3) NOT NULL DEFAULT '0',
  `country_id` int(11) NOT NULL DEFAULT '0',
  `city_id` int(11) NOT NULL DEFAULT '0',
  `awos_msg_source_folder` text CHARACTER SET ucs2 NOT NULL,
  `icao_code` varchar(4) DEFAULT NULL COMMENT 'ICAO code. Station name for METAR/SPECI reports.',
  `phone_number` tinytext COMMENT 'Phone number of modem for reset by SMS',
  `sms_message` tinytext COMMENT 'Reset message for modem',
  `station_gravity` decimal(20,10) DEFAULT '0.0000000000',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`station_id`),
  KEY `i_station__type` (`station_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station`
--

LOCK TABLES `station` WRITE;
/*!40000 ALTER TABLE `station` DISABLE KEYS */;
/*!40000 ALTER TABLE `station` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_calculation`
--

DROP TABLE IF EXISTS `station_calculation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_calculation` (
  `calculation_id` int(11) NOT NULL AUTO_INCREMENT,
  `station_id` smallint(6) NOT NULL,
  `handler_id` tinyint(4) NOT NULL,
  `formula` varchar(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`calculation_id`),
  KEY `station_id` (`station_id`),
  KEY `handler_id` (`handler_id`),
  CONSTRAINT `station_calculation_fk` FOREIGN KEY (`station_id`) REFERENCES `station` (`station_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `station_calculation_fk1` FOREIGN KEY (`handler_id`) REFERENCES `calculation_handler` (`handler_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_calculation`
--

LOCK TABLES `station_calculation` WRITE;
/*!40000 ALTER TABLE `station_calculation` DISABLE KEYS */;
/*!40000 ALTER TABLE `station_calculation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_calculation_data`
--

DROP TABLE IF EXISTS `station_calculation_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_calculation_data` (
  `calculation_data_id` int(11) NOT NULL AUTO_INCREMENT,
  `calculation_id` int(11) NOT NULL,
  `listener_log_id` int(11) NOT NULL,
  `value` decimal(15,4) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`calculation_data_id`),
  KEY `calculation_id` (`calculation_id`),
  KEY `listener_log_id` (`listener_log_id`),
  CONSTRAINT `station_calculation_data_fk` FOREIGN KEY (`calculation_id`) REFERENCES `station_calculation` (`calculation_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `station_calculation_data_fk1` FOREIGN KEY (`listener_log_id`) REFERENCES `listener_log` (`log_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_calculation_data`
--

LOCK TABLES `station_calculation_data` WRITE;
/*!40000 ALTER TABLE `station_calculation_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `station_calculation_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_calculation_variable`
--

DROP TABLE IF EXISTS `station_calculation_variable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_calculation_variable` (
  `calculation_variable_id` int(11) NOT NULL AUTO_INCREMENT,
  `calculation_id` int(11) NOT NULL,
  `variable_name` varchar(20) NOT NULL,
  `sensor_feature_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`calculation_variable_id`),
  KEY `calculation_id` (`calculation_id`),
  KEY `fk_station_cal_var__sensor_feature_id` (`sensor_feature_id`),
  CONSTRAINT `fk_station_cal_var__sensor_feature_id` FOREIGN KEY (`sensor_feature_id`) REFERENCES `station_sensor_feature` (`sensor_feature_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `station_calculation_variable_fk` FOREIGN KEY (`calculation_id`) REFERENCES `station_calculation` (`calculation_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_calculation_variable`
--

LOCK TABLES `station_calculation_variable` WRITE;
/*!40000 ALTER TABLE `station_calculation_variable` DISABLE KEYS */;
/*!40000 ALTER TABLE `station_calculation_variable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_group`
--

DROP TABLE IF EXISTS `station_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_group` (
  `group_id` smallint(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(8) NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_group`
--

LOCK TABLES `station_group` WRITE;
/*!40000 ALTER TABLE `station_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `station_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_group_destination`
--

DROP TABLE IF EXISTS `station_group_destination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_group_destination` (
  `group_destination_id` smallint(8) NOT NULL AUTO_INCREMENT,
  `group_id` smallint(2) NOT NULL,
  `station_id` smallint(6) NOT NULL,
  PRIMARY KEY (`group_destination_id`),
  KEY `station_group_fk` (`group_id`),
  KEY `station_fk` (`station_id`),
  CONSTRAINT `station_fk` FOREIGN KEY (`station_id`) REFERENCES `station` (`station_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `station_group_fk` FOREIGN KEY (`group_id`) REFERENCES `station_group` (`group_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_group_destination`
--

LOCK TABLES `station_group_destination` WRITE;
/*!40000 ALTER TABLE `station_group_destination` DISABLE KEYS */;
/*!40000 ALTER TABLE `station_group_destination` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_sensor`
--

DROP TABLE IF EXISTS `station_sensor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_sensor` (
  `station_sensor_id` int(11) NOT NULL AUTO_INCREMENT,
  `station_id` smallint(6) NOT NULL,
  `sensor_id_code` varchar(3) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `handler_id` tinyint(4) NOT NULL,
  PRIMARY KEY (`station_sensor_id`),
  KEY `station_id` (`station_id`),
  KEY `handler_id` (`handler_id`),
  CONSTRAINT `station_sensor_fk` FOREIGN KEY (`station_id`) REFERENCES `station` (`station_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_sensor`
--

LOCK TABLES `station_sensor` WRITE;
/*!40000 ALTER TABLE `station_sensor` DISABLE KEYS */;
/*!40000 ALTER TABLE `station_sensor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station_sensor_feature`
--

DROP TABLE IF EXISTS `station_sensor_feature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_sensor_feature` (
  `sensor_feature_id` int(11) NOT NULL AUTO_INCREMENT,
  `sensor_id` int(11) NOT NULL,
  `feature_code` varchar(50) NOT NULL,
  `feature_display_name` varchar(255) NOT NULL,
  `feature_constant_value` decimal(11,3) NOT NULL DEFAULT '0.000',
  `measurement_type_code` varchar(50) NOT NULL,
  `metric_id` tinyint(4) NOT NULL,
  `is_main` tinyint(1) NOT NULL,
  `filter_max` decimal(11,2) NOT NULL DEFAULT '0.00',
  `filter_min` decimal(11,2) NOT NULL DEFAULT '0.00',
  `filter_diff` decimal(11,2) NOT NULL DEFAULT '0.00',
  `has_filters` tinyint(1) NOT NULL DEFAULT '0',
  `has_filter_min` tinyint(1) NOT NULL DEFAULT '0',
  `has_filter_max` tinyint(1) NOT NULL DEFAULT '0',
  `has_filter_diff` tinyint(1) NOT NULL DEFAULT '0',
  `is_constant` tinyint(1) NOT NULL DEFAULT '0',
  `is_cumulative` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`sensor_feature_id`),
  KEY `sensor_id` (`sensor_id`),
  KEY `metric_id` (`metric_id`),
  KEY `sensor_feature__id_code_index` (`sensor_id`,`feature_code`),
  CONSTRAINT `station_sensor_feature_fk` FOREIGN KEY (`sensor_id`) REFERENCES `station_sensor` (`station_sensor_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station_sensor_feature`
--

LOCK TABLES `station_sensor_feature` WRITE;
/*!40000 ALTER TABLE `station_sensor_feature` DISABLE KEYS */;
/*!40000 ALTER TABLE `station_sensor_feature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_forwarded_message`
--

DROP TABLE IF EXISTS `tbl_forwarded_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_forwarded_message` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL COMMENT 'Id of message in listener_log',
  `client_id` int(10) unsigned NOT NULL COMMENT 'Id of message forwarding info',
  `status` enum('new','sent') NOT NULL DEFAULT 'new',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `i_forwarded_message__status` (`status`),
  KEY `fk_forwarded_message__log_id` (`message_id`),
  KEY `fk_forwarded_message__client_id` (`client_id`),
  CONSTRAINT `fk_forwarded_message__client_id` FOREIGN KEY (`client_id`) REFERENCES `tbl_message_forwarding_info` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_forwarded_message__log_id` FOREIGN KEY (`message_id`) REFERENCES `listener_log` (`log_id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table with info about forwarded messages';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_forwarded_message`
--

LOCK TABLES `tbl_forwarded_message` WRITE;
/*!40000 ALTER TABLE `tbl_forwarded_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_forwarded_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_message_forwarding_info`
--

DROP TABLE IF EXISTS `tbl_message_forwarding_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_message_forwarding_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source` text NOT NULL COMMENT 'Connection source string',
  `description` text COMMENT 'Description of forward info',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Provides info about WMs for message forwarding.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_message_forwarding_info`
--

LOCK TABLES `tbl_message_forwarding_info` WRITE;
/*!40000 ALTER TABLE `tbl_message_forwarding_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_message_forwarding_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_migration`
--

DROP TABLE IF EXISTS `tbl_migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_migration` (
  `version` varchar(255) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_migration`
--

LOCK TABLES `tbl_migration` WRITE;
/*!40000 ALTER TABLE `tbl_migration` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_migration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','superadmin') NOT NULL DEFAULT 'user',
  `timezone_id` varchar(255) NOT NULL DEFAULT 'UTC',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'marina.ivanov@itscript.com','admin','$2a$13$ewquN3ejT47STTvC4khIr.wave5vQF1mlIDXqteLQLt/LgymOs3uG','admin','UTC','0000-00-00 00:00:00','2014-09-15 09:59:18'),(2,'alexandr.vysotsky@itscript.com','superadmin','$2a$13$ewquN3ejT47STTvC4khIr.wave5vQF1mlIDXqteLQLt/LgymOs3uG','superadmin','UTC','0000-00-00 00:00:00','2014-09-12 09:28:27');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_command`
--

DROP TABLE IF EXISTS `sms_command`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_command` (
  `sms_command_id` int(11) NOT NULL AUTO_INCREMENT,
  `sms_command_status` enum('new','sent','processed') NOT NULL DEFAULT 'new',
  `station_id` smallint(6) NOT NULL,
  `sms_command_code` varchar(255) NOT NULL,
  `sms_command_message` varchar(255) NOT NULL,
  `sms_command_response` varchar(255) DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`sms_command_id`),
  KEY `fk_station` (`station_id`),
  CONSTRAINT `fk_station` FOREIGN KEY (`station_id`) REFERENCES `station` (`station_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `xml_process_log`
--

DROP TABLE IF EXISTS `xml_process_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xml_process_log` (
  `xml_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`xml_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xml_process_log`
--

LOCK TABLES `xml_process_log` WRITE;
/*!40000 ALTER TABLE `xml_process_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `xml_process_log` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-19 14:40:30
