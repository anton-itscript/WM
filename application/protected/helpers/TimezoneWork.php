<?php

/**
 * Helper class for easire work with timezones
 */

class TimezoneWork
{
    public static function prepareList()
    {
        $return = array();

        $list = DateTimeZone::listIdentifiers();

        if ($list) 
		{
            foreach ($list as $key => $value)
			{
				$return[$value] = $value.' (GMT '. TimezoneWork::getOffsetFromUTC($value, 1) .')';
            }
        }

        return $return;
    }

    public static function getOffsetFromUTC($timezone_id, $return_str = 0)
    {
        $dateTimeZoneUTC = new DateTimeZone("UTC");
        $dateTimeZoneRequested = new DateTimeZone($timezone_id);

        $dateTimeUTC = new DateTime("now", $dateTimeZoneUTC);
        $timeOffset = $dateTimeZoneRequested->getOffset($dateTimeUTC);

        if ($return_str)
		{
            $offset_hour = floor(abs($timeOffset)/3600);
            $offset_minute = (abs($timeOffset) - $offset_hour*3600)/60;

            return ($timeOffset >= 0 ? '+' : '-').str_pad($offset_hour,2,'0',STR_PAD_LEFT).':'.str_pad($offset_minute,2,'0',STR_PAD_LEFT);
        }
		
        return $timeOffset;
    }

    public static function getOffsetFromTZ($timezone_id, $from_tz, $return_str = 0)
    {
        $dateTimeZoneUTC = new DateTimeZone($from_tz);
        $dateTimeZoneRequested = new DateTimeZone($timezone_id);

        $dateTimeUTC = new DateTime("now", $dateTimeZoneUTC);
        $timeOffset = $dateTimeZoneRequested->getOffset($dateTimeUTC);

        if ($return_str) 
		{
            $offset_hour = floor(abs($timeOffset)/3600);
            $offset_minute = (abs($timeOffset) - $offset_hour*3600)/60;

            return ($timeOffset >= 0 ? '+' : '-').str_pad($offset_hour,2,'0',STR_PAD_LEFT).':'.str_pad($offset_minute,2,'0',STR_PAD_LEFT);
        }
		
        return $timeOffset;
    }


    public static function set($tz)
    {
        $res = date_default_timezone_set($tz);
        
		if ($res !== false)
		{
            $sql = "SET time_zone='". TimezoneWork::getOffsetFromUTC($tz, 1) ."';";
            Yii::app()->db->createCommand($sql)->execute();
        }
    }

    public static function getReverseOffsetFromUTC($timezone_id,  $return_str = 0)
    {
        $dateTimeZoneUTC = new DateTimeZone("UTC");
        $dateTimeZoneRequested = new DateTimeZone($timezone_id);

        $dateTimeUTC = new DateTime("now", $dateTimeZoneUTC);
        $timeOffset = $dateTimeZoneRequested->getOffset($dateTimeUTC);

        if ($return_str)
        {
            $offset_hour = floor(abs($timeOffset)/3600);
            $offset_minute = (abs($timeOffset) - $offset_hour*3600)/60;

            return ($timeOffset >= 0 ? '-' : '+').str_pad($offset_hour,2,'0',STR_PAD_LEFT).':'.str_pad($offset_minute,2,'0',STR_PAD_LEFT);
        }

        return $timeOffset;
    }

    public static function findReverseTimeZone($tz)
    {
        $mark = substr($tz,0,1);
        $time = substr($tz,1,5);
        $mark = $mark=='+'? '-'	: "+";
        $newtz = $mark.$time;
        if ($tz == '+00:00') {
            $newtz = $tz;
        }
        $return = array();
        $list = DateTimeZone::listIdentifiers();
        if ($list)
        {
            foreach ($list as $key => $value)
            {
                $findTime =  TimezoneWork::getOffsetFromUTC($value, 1)  ;
                if($findTime == $newtz) {

                    return $value;
                }
            }
        }
        return $return;
    }

    public static function setReverse($tz)
    {
        //$res = date_default_timezone_set($tz);

        $t_tz = TimezoneWork::getReverseOffsetFromUTC($tz, 1);
        $tz = self::findReverseTimeZone($t_tz);
        date_default_timezone_set($tz);
        $sql = "SET time_zone='". $t_tz."';";
        Yii::app()->db->createCommand($sql)->execute();

    }

    public static function setTimeByLocalTz($tz,$time)
    {
        $tz = TimezoneWork::getReverseOffsetFromUTC($tz, 1);
        $mark = substr($tz,0,1);
        $hour = substr($tz,1,2);
        $minutes = substr($tz,4,2);
        $second_diff=0;
        $second_diff = ((int)$hour)*60*60 + ((int)$minutes)*60;
        if ($mark=='-') {
            $time -= $second_diff;
        } elseif($mark=='+') {
            $time += $second_diff;
        }

        return $time;
    }

}
?>