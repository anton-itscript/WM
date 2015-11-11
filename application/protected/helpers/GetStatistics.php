<?php

/*
 * Get statistics
 * Save in DB
 * Load from DB
 *
 * Create / Save .xls
 */

class GetStatistics
{
    const SPLITTER = ', ';
    /**
     * save data */
    public $db_stat;
    public $db_long_stat;

    public $db_tables_size;
    public $db_long_tables_size;
    public $db_tables_rows;
    public $db_long_tables_rows;

    public $stations; // station_id => station_name
    public $stations_logger;
    public $stations_communication_type;
    public $stations_message_interval;
    public $stations_message_count;
    public $stations_message_expected;
    public $stations_message_error;
    public $stations_message_is_processing;
    public $stations_message_last;
    public $stations_sensor_bv;
    public $stations_schedule_synop;
    public $stations_schedule_bufr;
    public $stations_schedule_speci;
    public $stations_schedule_metar;
    /**
     * calculated data */
    public $db_small_stat;
    public $db_long_small_stat;
    /**
     * system */
    public $system;

    public function __construct($report = null)
    {
        if (is_null($report) || is_object($report)) {
            $this->ini($report);
            $this->calculateData();
        } elseif (is_numeric($report)) {
            $this->load($report);
            $this->calculateData();
        }
    }

    /**
     * load data from report_id
     * @param $report_id
     */
    private function load($report_id)
    {
        $data = HeartbeatReportData::get($report_id);
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     * create statistics now
     * @param null $report
     */
    private function ini($report = null)
    {
        date_default_timezone_set("UTC");

        if (is_null($report)) {
            $report = new HeartbeatReport();
            $report->created = date("Y-m-d H:i:s");
            $report->period = date("Y-m-d H:i:s", time() - 24 * 60 * 60);
        }
        $minute_in_period = (strtotime($report->created) - strtotime($report->period)) / 60;

        $databases = array(
            'db' => false,
            'db_long' => true
        );
        $config_file = array(
            'db' => 'db_params',
            'db_long' => 'db_long_params'
        );
        $sql = "SELECT TABLE_NAME, `DATA_LENGTH`+`INDEX_LENGTH` as 'TABLE_SIZE', TABLE_ROWS
                    FROM information_schema.TABLES
                    WHERE `table_schema` LIKE :db;" . "";

        foreach ($databases as $database => $use_long) {
            /*
             * $db_stat
             * $db_long_stat
             */
            $res = CStubActiveRecord::getDbConnect($use_long)->createCommand("SHOW STATUS;")->queryAll();
            foreach ($res as $val)
                $this->{$database . '_stat'}[$val['Variable_name']] = $val['Value'];
            /*
             * $db_tables_size
             * $db_long_tables_size
             * $db_tables_rows
             * $db_long_tables_rows
             */
//            $db_conf = new ConfigManager($config_file[$database]);
//            $db_name = $db_conf::getConfigSection('database', 'dbname');

            $db_name = Yii::app()->params[$config_file[$database]]['dbname'];


            $res = CStubActiveRecord::getDbConnect($use_long)->createCommand($sql)->bindValue(':db', $db_name)->queryAll();
            $allSize = $allCount = 0;
            foreach ($res as $table) {
                $allSize += $this->{$database . '_tables_size'}[$table['TABLE_NAME']] = number_format(($table['TABLE_SIZE']) / 1024 / 1024, 2);
                $allCount += $this->{$database . '_tables_rows'}[$table['TABLE_NAME']] = $table['TABLE_ROWS'];
            }
            $this->{$database . '_tables_size'}['All'] = $allSize;
            $this->{$database . '_tables_rows'}['All'] = $allCount;
            if ($use_long) {
                /*
                 * $stations
                 * $stations_logger
                 * $stations_communication_type
                 * $stations_message_interval
                 * $stations_message_count
                 * $stations_message_expected
                 * $stations_message_error
                 * $stations_message_is_processing
                 * $stations_message_last
                 * $stations_schedule_synop
                 * $stations_schedule_bufr
                 * $stations_schedule_speci
                 * $stations_schedule_metar
                 * $stations_schedule_data_export
                 */
                $criteria = new CDbCriteria();
                $criteria->index = 'station_id';
                $criteria->with = array(
                    'messages' => array(
                        'together' => false,
                        'select' => 'messages.measuring_timestamp, messages.failed, is_processing',
                        'condition' => 'messages.measuring_timestamp > \'' . $report->period . '\'',
                        'order' => 'messages.measuring_timestamp DESC'
                    ),
                    'schedule' => array(
                        'select' => 'schedule.report_type, schedule.period',
                        'together' => false,
                        'with' => array(
                            'processed' => array(
                                'select' => 'processed.updated',
                                'together' => false,
                                'condition' => 'processed.updated > \'' . $report->period . '\''
                            )
                        )
                    )
                );
                $stations = Station::model()->long()->findAll($criteria);
                foreach ($stations as $station_id => $station) {
                    $this->stations[$station_id] = $station['display_name'];
                    $this->stations_logger[$station_id] = $station['logger_type'];
                    $this->stations_communication_type[$station_id] = $station['communication_type'];
                    $this->stations_message_interval[$station_id] = $station['event_message_period'];
                    $this->stations_message_count[$station_id] = count($station->messages);
                    $this->stations_message_expected[$station_id] = $minute_in_period / $station['event_message_period'];
                    foreach ($station->messages as $message) {
                        $this->stations_message_error[$station_id] += $message['failed'];
                        $this->stations_message_is_processing[$station_id] += $message['is_processing'];
                    }
                    $this->stations_message_last[$station_id] = $station->messages[0]['measuring_timestamp'];
                    $logIds[] = $station->messages[0]['log_id'];

                    foreach ($station->schedule as $schedule) {
                        $schedule_count = count($schedule->processed);
                        if ($schedule_count) {
                            if ($schedule->report_type == 'speci') {
                                $this->stations_schedule_speci[$station_id]['gen'] += $schedule_count;
                            } else {
                                $this->{'stations_schedule_' . $schedule->report_type}[$station_id]['gen']
                                    += $schedule_count;
                                $this->{'stations_schedule_' . $schedule->report_type}[$station_id]['sch']
                                    += number_format($minute_in_period / $schedule->period);
                            }
                        }
                    }
                }
                foreach($this as $key => $arr){
                    if ( stripos($key,'stations_schedule_') !== false && !is_null($arr) ){
                        foreach($arr as $station_id => $val)
                            if(!is_null($val['gen']) && !is_null($val['sch'])){
                                $this->{$key}[$station_id] = $val['sch'] . ' / ' . $val['gen'] . ' = ' . number_format($val['gen'] / $val['sch'] * 100) . '%';
                            } elseif( stripos($key,'speci') === false ) {
                                $this->{$key}[$station_id] = $val['sch'] . ' / ' . $val['gen'];
                            } else {
                                $this->{$key}[$station_id] = $val['gen'];
                            }
                    }
                }
                /*
                 * $stations_bv_last
                 */
                $criteria = new CDbCriteria();
                $criteria->with = array(
                    'sensor_feature' => array(
                        'select' => false,
                        'joinType' => 'INNER JOIN',
                        'condition' => 'sensor_feature.feature_code = \'battery_voltage\''
                    )
                );
                $criteria->index = 'station_id';
                $criteria->compare('listener_log_id', $logIds);
                $bv_data = SensorData::model()->long()->findAll($criteria);

                foreach ($stations as $station_id => $station) {
                    $this->stations_sensor_bv[$station_id] = $bv_data[$station_id]['sensor_feature_value'];
                }
            }
        }
        /*
         * $system
         */
        $this->system['disk_free_space'] = number_format(disk_free_space('/') / pow(10, 9), 2) . 'Gb';
        $this->system['disk_total_space'] = number_format(disk_total_space('/') / pow(10, 9), 2) . 'Gb';
    }

    /**
     * Calculated data
     */
    private function calculateData()
    {
        $databases = array(
            'db' => false,
            'db_long' => true
        );
        /**
         * for last backups
         */
        $backups_path = Yii::app()->params['backups_path'];
        $outputs = null;
        if (It::isLinux()) {
            $cmd = 'ls -1At ' . $backups_path . ' | egrep -i *.sql';
            exec($cmd, $outputs);
        } else if (It::isWindows()) {
            exec('dir ' . $backups_path . DIRECTORY_SEPARATOR . '*.sql /B /4 /T:C /O:D', $outputs);
            array_slice($outputs, 0, -1);
        }
        /*
         * $db_small_stat
         * $db_long_small_stat
         */
        foreach ($databases as $database => $var) {
            $this->{$database . '_small_stat'} = array(
                'Uptime' => number_format($this->{$database . '_stat'}['Uptime'] / 60 / 60) . 'h' . $this->{$database . '_stat'}['Uptime'] % 60 . 'm',
                'Queries' => $this->{$database . '_stat'}['Queries']
            );
            foreach ($outputs as $output) {
                if (strpos($output, '_long.sql')) {
                    if ($var) {
                        $this->{$database . '_small_stat'}['Last_backup'] = date("y-m-d H:i", filemtime($backups_path . DIRECTORY_SEPARATOR . $output));
                        break;
                    }
                } else {
                    if (!$var) {
                        $this->{$database . '_small_stat'}['Last_backup'] = date("y-m-d H:i", filemtime($backups_path . DIRECTORY_SEPARATOR . $output));
                        break;
                    }
                }
            }
            $this->{$database . '_small_stat'} += array(
                'Size' => $this->{$database . '_tables_size'}['All'] . 'Mb',
                'Data_read' => number_format($this->{$database . '_stat'}['Innodb_data_read'] / 1024) . 'Mb',
                'Data_writes' => number_format($this->{$database . '_stat'}['Innodb_data_writes'] / 1024) . 'Mb',
                'Rows' => $this->{$database . '_tables_rows'}['All'],
                'Rows_deleted' => $this->{$database . '_stat'}['Innodb_rows_deleted'],
                'Rows_inserted' => $this->{$database . '_stat'}['Innodb_rows_inserted'],
                'Rows_read' => $this->{$database . '_stat'}['Innodb_rows_read'],
                'Rows_updated' => $this->{$database . '_stat'}['Innodb_rows_updated'],
                'Connections' => $this->{$database . '_stat'}['Connections'],
            );

        }
    }

    /**
     * Return Excel for save or download
     * @param null $report
     * @return PHPExcel
     */
    private function createExcel($report = null)
    {
        /* Get description */
        $description = $this->getDescription();

        /* Create new PHPExcel object */
        include(Yii::getPathOfAlias('ext.phpexcel.Classes') . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $xx = new PHPExcel();

        $xx->getProperties()->setCreator("Delairco")
            ->setLastModifiedBy("Delairco")
            ->setTitle("Heartbeat Report")
            ->setSubject("Heartbeat Report")
            ->setDescription("Was generated with Weather Monitor software.");

        /* Input data */
        ////////
        // STYLE
        $style = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM
                )
            )
        );
        ////////
        // SHEET Report info
        // Report info
        if ($report or $this->system) {
            $sh = $xx->getActiveSheet();
            $sh->setTitle('Report and system info');
            $col = 1;
            $row = 2;
            if ($report) {
                $sh->mergeCellsByColumnAndRow($col, $row, $col + 1, $row);
                $sh->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sh->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
                $sh->setCellValueByColumnAndRow($col, $row++, 'Report info');

                foreach ($report as $key => $val) {
                    $sh->setCellValueByColumnAndRow($col, $row, str_replace('_', ' ', ucfirst($key)));
                    $sh->setCellValueByColumnAndRow($col + 1, $row++, $val);
                }

                $sh->getStyle('B2:C7')->applyFromArray($style);
                $sh->getStyle('B2:B7')->applyFromArray($style);
                $sh->getStyle('B2')->applyFromArray($style);
                $row++;
            }

            if ($this->system) {
                $tmp_row = $row;
                $sh->mergeCellsByColumnAndRow($col, $row, $col + 1, $row);
                $sh->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sh->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
                $sh->setCellValueByColumnAndRow($col, $row++, 'System info');

                foreach ($this->system as $key => $val) {
                    $sh->setCellValueByColumnAndRow($col, $row, str_replace('_', ' ', ucfirst($key)));
                    $sh->setCellValueByColumnAndRow($col + 1, $row++, $val);
                }
                $row--;
                $sh->getStyle('B' . $tmp_row . ':C' . $row)->applyFromArray($style);
                $sh->getStyle('B' . $tmp_row . ':B' . $row)->applyFromArray($style);
                $sh->getStyle('B' . $tmp_row)->applyFromArray($style);
            }

            for ($i = 1; $i < 3; $i++)
                $sh->getColumnDimensionByColumn($i)->setAutoSize(true);

            $xx->createSheet(1);
            $xx->setActiveSheetIndex(1);

        }
        ////////
        // SHEET DB Status
        // DB Status
        $sh = $xx->getActiveSheet();
        $sh->setTitle('DB Status');
        $col = 1;
        $row = 2;
        $sh->mergeCellsByColumnAndRow($col, $row, $col + 2, $row);
        $sh->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sh->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
        $sh->setCellValueByColumnAndRow($col, $row++, 'DB Status');
        $sh->setCellValueByColumnAndRow($col, $row, 'DB');
        $sh->setCellValueByColumnAndRow($col + 1, $row, 'Short');
        $sh->setCellValueByColumnAndRow($col + 2, $row, 'Long');
        foreach ($this->db_small_stat as $stat => $val) {
            $row++;
            $sh->setCellValueByColumnAndRow($col, $row, str_replace('_', ' ', ucfirst($stat)));
            $sh->setCellValueByColumnAndRow($col + 1, $row, $val);
            $sh->setCellValueByColumnAndRow($col + 2, $row, $this->db_long_small_stat[$stat]);
        }
        $sh->getStyle('B2:D' . $row)->applyFromArray($style);
        $sh->getStyle('B3:B' . $row)->applyFromArray($style);
        $sh->getStyle('B3:D3')->applyFromArray($style);

        // DB Full Status
        $row += 2;
        $tmp_row = $row;
        $sh->mergeCellsByColumnAndRow($col, $row, $col + 2, $row);
        $sh->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sh->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
        $sh->setCellValueByColumnAndRow($col, $row++, 'DB Full Status');
        $sh->setCellValueByColumnAndRow($col, $row, 'DB');
        $sh->setCellValueByColumnAndRow($col + 1, $row, 'Short');
        $sh->setCellValueByColumnAndRow($col + 2, $row, 'Long');
        foreach ($this->db_stat as $stat => $val) {
            $row++;
            $sh->setCellValueByColumnAndRow($col, $row, $stat);
            $sh->setCellValueByColumnAndRow($col + 1, $row, $val);
            $sh->setCellValueByColumnAndRow($col + 2, $row, $this->db_long_stat[$stat]);
        }
        $sh->getStyle('B' . $tmp_row . ':D' . $row)->applyFromArray($style);
        $sh->getStyle('B' . ($tmp_row + 1) . ':B' . $row)->applyFromArray($style);
        $sh->getStyle('B' . ($tmp_row + 1) . ':D' . ($tmp_row + 1))->applyFromArray($style);
        // DB Tables
        $col = 5;
        $row = 2;
        $sh->mergeCellsByColumnAndRow($col, $row, $col + 2, $row);
        $sh->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sh->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
        $sh->setCellValueByColumnAndRow($col, $row++, 'DB Short Tables');
        $sh->setCellValueByColumnAndRow($col, $row, 'Table name');
        $sh->setCellValueByColumnAndRow($col + 1, $row, 'Rows');
        $sh->setCellValueByColumnAndRow($col + 2, $row, 'Size(Mb)');
        foreach ($this->db_tables_rows as $stat => $val) {
            $row++;
            $sh->setCellValueByColumnAndRow($col, $row, $stat);
            $sh->setCellValueByColumnAndRow($col + 1, $row, $val);
            $sh->setCellValueByColumnAndRow($col + 2, $row, $this->db_tables_size[$stat]);
        }
        $sh->getStyle('F2:H' . $row)->applyFromArray($style);
        $sh->getStyle('F3:F' . $row)->applyFromArray($style);
        $sh->getStyle('F3:H3')->applyFromArray($style);

        $row += 2;
        $tmp_row = $row;
        $sh->mergeCellsByColumnAndRow($col, $row, $col + 2, $row);
        $sh->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sh->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
        $sh->setCellValueByColumnAndRow($col, $row++, 'DB Long Tables');
        $sh->setCellValueByColumnAndRow($col, $row, 'Table name');
        $sh->setCellValueByColumnAndRow($col + 1, $row, 'Rows');
        $sh->setCellValueByColumnAndRow($col + 2, $row, 'Size(Mb)');
        foreach ($this->db_long_tables_rows as $stat => $val) {
            $row++;
            $sh->setCellValueByColumnAndRow($col, $row, $stat);
            $sh->setCellValueByColumnAndRow($col + 1, $row, $val);
            $sh->setCellValueByColumnAndRow($col + 2, $row, $this->db_long_tables_size[$stat]);
        }
        $sh->getStyle('F' . $tmp_row . ':H' . $row)->applyFromArray($style);
        $sh->getStyle('F' . ($tmp_row + 1) . ':F' . $row)->applyFromArray($style);
        $sh->getStyle('F' . ($tmp_row + 1) . ':H' . ($tmp_row + 1))->applyFromArray($style);

        for ($i = 1; $i < 15; $i++)
            $sh->getColumnDimensionByColumn($i)->setAutoSize(true);

        ////////
        // SHEET Stations
        // Stations
        $countStation = count($this->stations);
        $sh = $xx->createSheet(2);
        $sh->setTitle('Stations');
        $col = 1;
        $row = 2;
        $sh->mergeCellsByColumnAndRow($col + 1, $row, $col + $countStation, $row);
        $sh->getStyleByColumnAndRow($col + 1, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sh->getStyleByColumnAndRow($col + 1, $row)->getFont()->setBold(true);
        $sh->setCellValueByColumnAndRow($col + 1, $row++, 'Stations');
        $pole = array(
            'ID',
            'Name',
            'Logger type',
            'Communication type',
            'Message interval',
            'Messages:',
            'expected - received',
            'error / not processed',
            'processed',
            'percentage processed',
            'Schedule:',
            'Synop | scheduled vs generated',
            'BUFR | scheduled vs generated',
            'METAR | scheduled vs generated',
            'SPECI generated',
            'Last BV (V)',
            'Last message',
        );
        $tmp_row = $row;
        foreach($pole as $val){
            $sh->setCellValueByColumnAndRow($col, $row++, $val);
        }
        foreach ($this->stations as $station_id => $station) {
            $row = $tmp_row;
            $col++;
            $sh->setCellValueByColumnAndRow($col, $row++, $station_id);
            $sh->setCellValueByColumnAndRow($col, $row++, $station);
            $sh->setCellValueByColumnAndRow($col, $row++, $this->stations_logger[$station_id]);
            $sh->setCellValueByColumnAndRow($col, $row++, $this->stations_communication_type[$station_id]);
            $sh->setCellValueByColumnAndRow($col, $row++, $this->stations_message_interval[$station_id]);
            $row++;
            $sh->setCellValueByColumnAndRow($col, $row++, $this->stations_message_expected[$station_id] . ' - ' . $this->stations_message_count[$station_id].' = '.($this->stations_message_expected[$station_id] - $this->stations_message_count[$station_id]));
            $sh->setCellValueByColumnAndRow($col, $row++, implode(array($this->stations_message_error[$station_id],$this->stations_message_is_processing[$station_id]), ' / '));
            $sh->setCellValueByColumnAndRow($col, $row++, ($processed = $this->stations_message_count[$station_id] - $this->stations_message_error[$station_id] - $this->stations_message_is_processing[$station_id]));
            $sh->setCellValueByColumnAndRow($col, $row++, ($this->stations_message_count[$station_id] ? ($processed . ' / ' . $this->stations_message_count[$station_id] . ' = ' . $processed / $this->stations_message_count[$station_id] * 100) .'%': 100 ));
            $row++;
            $sh->setCellValueByColumnAndRow($col, $row++, $this->stations_schedule_synop[$station_id]);
            $sh->setCellValueByColumnAndRow($col, $row++, $this->stations_schedule_bufr[$station_id]);
            $sh->setCellValueByColumnAndRow($col, $row++, $this->stations_schedule_metar[$station_id]);
            $sh->setCellValueByColumnAndRow($col, $row++, $this->stations_schedule_speci[$station_id]);
            $sh->setCellValueByColumnAndRow($col, $row++, $this->stations_sensor_bv[$station_id]);
            $sh->setCellValueByColumnAndRow($col, $row,   $this->stations_message_last[$station_id]);
        }

        $tmp_cell = $sh->getColumnDimensionByColumn(1 + $countStation)->getColumnIndex();

        $sh->getStyle('B2:' . $tmp_cell . $row)->applyFromArray($style);
        $sh->getStyle('B2:B' . $row)->applyFromArray($style);
        $sh->getStyle('B3:' . $tmp_cell . '4')->applyFromArray($style);
        for ($i = 1; $i < $countStation + 2; $i++)
            $sh->getColumnDimensionByColumn($i)->setAutoSize(true);

        /////////
        // Return
        $xx->setActiveSheetIndex(0);
        return $xx;
    }

    /**
     * Download excel from page Admin/HeartbeatReport
     * @param null $report
     */
    public function downloadExcel($report = null)
    {
        $excel = $this->createExcel($report);

        header('Content-Type: application/xls');
        header('Content-Disposition: attachment;filename="HeartbeatReport' . ($report ? $report->report_id : '') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     * Save excel in server for attach mail report
     * @param null $report
     * @return string
     */
    public function saveExcel($report = null)
    {
        try {
            $excel = $this->createExcel($report);
            $dir = dirname(Yii::app()->request->scriptFile) .
                DIRECTORY_SEPARATOR . "files" .
                DIRECTORY_SEPARATOR . "hbr";

            $file_path = $dir . DIRECTORY_SEPARATOR . "HeartbeatReport.xls";

            if(!is_writeable($dir)){
                return false;
            }

            if (file_exists($file_path))
                unlink($file_path);

            $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $objWriter->save($file_path);
        } catch (Exception $e) {
            return false;
        }
        return $file_path;
    }

    /**
     * push data in db
     * @param $report_id
     * @return bool flag
     */
    public function push($report_id)
    {
        // use array key from other
        $use_key = array(
            'db_tables_rows' => 'db_tables_size',
            'db_long_tables_rows' => 'db_long_tables_size',
            'stations_logger' => 'stations',
            'stations_communication_type' => 'stations',
            'stations_message_interval' => 'stations',
            'stations_message_count' => 'stations',
            'stations_message_expected' => 'stations',
            'stations_message_error' => 'stations',
            'stations_message_is_processing' => 'stations',
            'stations_message_last' => 'stations',
            'stations_sensor_bv' => 'stations',
            'stations_schedule_synop' => 'stations',
            'stations_schedule_bufr' => 'stations',
            'stations_schedule_speci' => 'stations',
            'stations_schedule_metar' => 'stations',
            'stations_schedule_data_export' => 'stations',
        );
        //miss_key
        $miss_key = array(
            'db_small_stat',
            'db_long_small_stat',
        );
        $flag = true;
        try {
            foreach ($this as $var => $val) {
                if (!is_null($val) && !in_array($var, $miss_key)) {
                    $flag = $flag && HeartbeatReportData::set($report_id, $var, $val, $use_key[$var]);
                }
            }
        } catch (Exception $e) {
            return false;
        }
        return $flag;
    }

    /**
     * Return description of property
     * @return array
     */
    private function getDescription()
    {
        return array(
//                'db_stat'                           => '',
//                'db_long_stat'                      => '',
//                'db_tables_size'                    => '',
//                'db_long_tables_size'               => '',
//                'db_tables_rows'                    => '',
//                'db_long_tables_rows'               => '',
            'stations' => 'Stations',
            'stations_logger' => 'Logger type',
            'stations_communication_type' => 'Communication type',
            'stations_message_interval' => 'Message interval',
            'stations_message_count' => 'Messages received',
            'stations_message_expected' => 'Messages expected',
            'stations_message_error' => 'Error messages',
            'stations_message_is_processing' => 'Not processed',
            'stations_message_last' => 'Last message',
            'stations_sensor_bv' => 'Last Battery value',
            'stations_schedule_synop' => 'Synop',
            'stations_schedule_bufr' => 'BUFR',
            'stations_schedule_speci' => 'SPECI',
            'stations_schedule_metar' => 'METAR',
            'stations_schedule_data_export' => 'Data_export',
//                'db_small_stat'                     => '',
//                'db_long_small_stat'                => '',
        );
    }
}