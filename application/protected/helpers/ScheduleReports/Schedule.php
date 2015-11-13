<?php

/*
 * Is called using command: "php console.php schedule", doesn't requires arguements
 * Is called every minute using schtasks
 * 
 * This console script is looking for scheduled reports that 
 * have to be generated at the moment.
 */

abstract class Schedule extends BaseComponent
{

	public function __construct($logger,$args)
	{
	    parent::__construct($logger);

        $this->run($args);
	}

    public abstract function run($args);

    /***
     * returns array(
     *  0 => min timestamp for searching last message
     *  1 => fact timestamp of last schedule running (can be different (+/-) then HH:00) (max timestamp for searching last message)
     *  2 => timestamp of fact schedule run  
     *  3 => min timestamp for data export
     *  4 => max timestamp for data export
     * )
     ***/
    public  function getCheckPeriod($generationTime, $report_period)
	{
        $check_period = false;
        
		$cur_hour = date('H', $generationTime);
        $cur_min  = date('i', $generationTime);
		
        $month = date('m', $generationTime);
        $day   = date('d', $generationTime);
        $year  = date('Y', $generationTime);
        
        if (($cur_min >= 1) && ($cur_min <= 10))
		{
            $check_period = array(
                date('Y-m-d H:i:s', mktime($cur_hour - 1, 50, 0,     $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, $cur_min, 0, $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, 0, 0,        $month, $day, $year))
            );
        }
        
		if (($cur_min >= 31) && ($cur_min <= 40))
		{
            $check_period = array(
                date('Y-m-d H:i:s', mktime($cur_hour, 20, 0,       $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, $cur_min, 0, $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, 30, 0,       $month, $day, $year))
            );            
        }
        
        if (($cur_min >= 16) && ($cur_min <= 25))
		{
            $check_period = array(
                date('Y-m-d H:i:s', mktime($cur_hour, 05, 0,       $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, $cur_min, 0, $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, 15, 0,       $month, $day, $year))
            );            
        }     
        
        if (($cur_min >= 46) && ($cur_min <= 55))
		{
            $check_period = array(
                date('Y-m-d H:i:s', mktime($cur_hour, 35, 0,       $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, $cur_min, 0, $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, 45, 0,       $month, $day, $year))
            );            
        }        
        
        if ($check_period === false)
		{
            $now =  mktime($cur_hour, $cur_min, 0, $month, $day, $year);
            
			$check_period = array(
                date('Y-m-d H:i:s', $now - $report_period * 60),
                date('Y-m-d H:i:s', $now),
                date('Y-m-d H:i:s', $now),
            );
        }        
        
        $max_timestamp_for_data_export = $check_period[2];
        $min_timestamp_for_data_export = date('Y-m-d H:i:s', strtotime($max_timestamp_for_data_export) - $report_period * 60);
        
		$this->_logger->log(__METHOD__ .' Prepare check period', array(
				'cur_hour' => $cur_hour, 
				'cur_min' => $cur_min, 
				'period' => $report_period, 
				'max_timestamp' => $max_timestamp_for_data_export, 
				'min_timestamp' => $min_timestamp_for_data_export
			)
		);
		
        $check_period[] = $min_timestamp_for_data_export;
        $check_period[] = $max_timestamp_for_data_export;        
       
        return $check_period;
    }

    /**
     * @param $minutesCount int
     * @return array or intervals
     */
    public static function generatePeriodArray($minutesCount){

        $periodInSeconds = $minutesCount * 60;

        $minutes = array();
        $seconds = mktime(0, 0, 0, 1, 1, 1970);
        $seconds2 = mktime(0, 0, 0, 1, 2, 1970);
        $daySeconds = $seconds2 - $seconds;
        for($i=0;$i<$daySeconds;$i+=$periodInSeconds){
            $minutes[] =date('H:i', $seconds);
            $seconds+= $periodInSeconds;
        }

        return $minutes;
    }

    /**
     * @param $timestamp int
     * @param $interval int seconds
     * @return false | int
     */
//    public static function getNextPeriodTime($timestamp,$interval=300)
//    {
//        if($interval<=0)
//            return false;
//
//        $currentTime 	= mktime(date('H',$timestamp), date('i',$timestamp), 0, date('m',$timestamp), date('d',$timestamp), date('Y',$timestamp) );
//        $currentTimeH 	= mktime(date('H',$timestamp), 0, 0, date('m',$timestamp), date('d',$timestamp), date('Y',$timestamp) );
//        $seconds        = mktime(0, 0, 0, 1, 1, date('Y'));
//        $seconds2       = mktime(0, 0, 0, 2, 1, date('Y'));
//        $monthSeconds   = $seconds2 - $seconds;
//
//        $sec = 0;
//        $timeIntervalArray = array();
//        for ($i = 0; $i < $monthSeconds; $i += $interval) {
//            $timeIntervalArray[] = $currentTimeH + $sec;
//            $sec+= $interval;
//        }
//
//        for ($i=0;$i<count($timeIntervalArray);$i++) {
//            if ($timeIntervalArray[$i] > $currentTime and $timeIntervalArray[$i - 1] <= $currentTime and isset($timeIntervalArray[$i - 1])) {
//                return $timeIntervalArray[$i];
//            }
//        }
//    }

    /**
     * @param $interval int seconds
     * @return false | int
     */
//    public static function getNextPeriodFromCurrentTime($interval=300)
//    {
//        if($interval<=0)
//            return false;
//
//        $currentTime 	= mktime(date('H'), date('i'), 0, date('m'), date('d'), date('Y') );
//        $currentTimeH 	= mktime(date('H'), 0, 0, date('m'), date('d'), date('Y') );
//
//        $seconds        = mktime(0, 0, 0, 1, 1, date('Y'));
//        $seconds2       = mktime(0, 0, 0, 2, 1, date('Y'));
//        $monthSeconds   = $seconds2 - $seconds;
//
//        $sec = 0;
//        $timeIntervalArray = array();
//        for ($i = 0; $i < $monthSeconds; $i += $interval) {
//
//            $timeIntervalArray[] = $currentTimeH + $sec;
//            $sec+= $interval;
//        }
//        for ($i=0;$i<count($timeIntervalArray);$i++) {
//            if ($timeIntervalArray[$i] > $currentTime and $timeIntervalArray[$i - 1] <= $currentTime and isset($timeIntervalArray[$i - 1])) {
//                return $timeIntervalArray[$i];
//            }
//        }
//    }

    /**
     * @param int $interval
     * @return mixed
     */
//    public static function getCurrentPeriodFromCurrentTime($interval=300)
//    {
//        if($interval<=0)
//            return false;
//
//        $currentTime 	= mktime(date('H'), date('i'), 0, date('m'), date('d'), date('Y') );
//        $currentTimeH 	= mktime(date('H'), 0, 0, date('m'), date('d'), date('Y') );
//
//        $seconds        = mktime(0, 0, 0, 1, 1, date('Y'));
//        $seconds2       = mktime(0, 0, 0, 2, 1, date('Y'));
//        $monthSeconds   = $seconds2 - $seconds;
//
//        $sec = 0;
//        $timeIntervalArray = array();
//        for ($i = 0; $i < $monthSeconds; $i += $interval) {
//
//            $timeIntervalArray[] = $currentTimeH + $sec;
//            $sec+= $interval;
//        }
//
//        for ($i=0;$i<count($timeIntervalArray);$i++) {
//            if (
//                $timeIntervalArray[$i] >= $currentTime
//                and
//                (
//                    ($timeIntervalArray[$i - 1] < $currentTime and isset($timeIntervalArray[$i - 1]))
//                     xor
//                    $i == 0
//                )
//            ) {
//                return $timeIntervalArray[$i];
//            }
//        }
//    }

    /**
     * Function returns array of periods. Script is able to generate schedule for these periods only.
	 * 
     * @param int $generationTime
     * @return array 
     */
    public  function getProperPeriods($generationTime)
	{
		$this->_logger->log(__METHOD__, array('generationTime' => $generationTime));
		
		$compare_min = null;
		$cur_hour = date('H', $generationTime);
        $cur_min  = date('i', $generationTime);
		
		if ($cur_min >= 0 && $cur_min <= 14) {
			$compare_min = '00';
		} elseif ($cur_min >= 15 && $cur_min <= 29) {
			$compare_min = '15';
		} elseif ($cur_min >= 30 && $cur_min <= 44) {
			$compare_min = '30';
		}

        if ($cur_min >= 45 && $cur_min <= 59) {
			$compare_min = '45';
		}         

		$cur_time = $cur_hour .':'. $compare_min;
		$proper_periods = array();


		$scheduler = [
            '1'     => self::generatePeriodArray(1),
            '2'     => self::generatePeriodArray(2),
            '5'     => self::generatePeriodArray(5),
			'15'    => self::generatePeriodArray(15),
			'30'    => self::generatePeriodArray(30),
			'60'    => self::generatePeriodArray(60),
			'120'   => self::generatePeriodArray(120),
			'180'   => self::generatePeriodArray(180),
			'360'   => self::generatePeriodArray(360),
			'540'   => self::generatePeriodArray(540),
			'720'   => self::generatePeriodArray(720),
			'900'   => self::generatePeriodArray(900),
			'1080'  => self::generatePeriodArray(1080),
			'1440'  => self::generatePeriodArray(1440),
		];

		foreach ($scheduler as $key => $value) {
			if (in_array($cur_time, $value)) {
				$proper_periods[] = $key;
			}
		}

		$this->_logger->log(__METHOD__, array('proper_periods' => implode(',', $proper_periods)));
		
		return $proper_periods;
    }

    public static function getNextPeriodTime($interval=0, $unix_timestamp = false)
    {
        if($unix_timestamp == false)
            $unix_timestamp = time();
        $unix_timestamp_res = self::getLastPeriodTime($interval, $unix_timestamp);
        if($unix_timestamp == ($unix_timestamp_res-$interval))
            $unix_timestamp_res += $interval;
        return $unix_timestamp_res + $interval;
    }

    public static function getLastPeriodTime($interval=0, $unix_timestamp = false)
    {
        if($unix_timestamp == false)
            $unix_timestamp = time();
        $res = $unix_timestamp/$interval;
        $res = (int)$res;
        $res = $interval*$res;
        return $res;
    }

    public static function getPeriodsArray($interval, $unix_timestamp_from, $unix_timestamp_to = false, $debug=false)
    {
        if($unix_timestamp_to == false)
            $unix_timestamp_to = time();

        $timestamp_from = self::getNextPeriodTime($interval,$unix_timestamp_from);
        $timestamp_to = self::getLastPeriodTime($interval,$unix_timestamp_to);
        $result_array = array();
        if ($debug == false) {
            while ($timestamp_from<$timestamp_to) {
                $result_array[$timestamp_from] = $timestamp_from += $interval;
            }
        } else {
            while ($timestamp_from<$timestamp_to) {
                $result_array[date('Y-m-d H:i:s',$timestamp_from)] = date('Y-m-d H:i:s',$timestamp_from += $interval);
            }
        }
        return $result_array;
    }
}

?>