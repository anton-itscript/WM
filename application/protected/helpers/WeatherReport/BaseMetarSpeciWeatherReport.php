<?php

/**
 * Description of BaseMetarSpeciWeatherReport
 *
 * @author
 */
abstract class BaseMetarSpeciWeatherReport extends WeatherReport
{
	/**
	 * Current number of report section
	 * 
	 * @access protected  
	 * @var int 
	 */
	protected $_section = 0;
	
	/**
	 * Current number of subsection
	 * 
	 * @access protected
	 * @var int 
	 */
	protected $_subsection = 0;
	
	/**
	 * Timestamp of measurement (from message).
	 * @access protected
	 * @var int 
	 */
	protected $_measuring_timestamp;
	
	/**
	 * Day part of timestamp of measurement (from message).
	 * @access protected
	 * @var int 
	 */
	protected $_measuring_day;
	/**
	 * Hour part of timestamp of measurement (from message).
	 * @access protected
	 * @var int 
	 */
	protected $_measuring_hour;
	/**
	 * Minute part of timestamp of measurement (from message).
	 * @access protected
	 * @var int 
	 */
	protected $_measuring_minute;
	
//	/**
//	 * Provides metric for METAR/SPECI reports which is set default for sensors.
//	 * @var IParamProvider 
//	 */
//	protected $_defaultMetricProvider = null;
	
//	/**
//	 * Ctor.
//	 * 
//	 * @param ILogger $logger
//	 * @param IParamProvider $defaultMetricProvider
//	 */
//	public function __construct($logger, $defaultMetricProvider)
//	{
//		parent::__construct($logger);
//		
//		$this->_defaultMetricProvider = $defaultMetricProvider;
//	}
	
	/**
	 * Format Wind Direction according to report format.
	 * 
	 * @param int Wind Direction in degrees
	 * @return int 
	 */
	protected static function formatWindDirection($value)
	{
		$value = (int)round($value, -1);
		
		if ($value == 0)
		{
			$value = 360;
		}
		
		return str_pad($value, 3, '0', STR_PAD_LEFT);
	}
	
	/**
	 * Format Wind Speed according to report format.
	 * 
	 * @param int
	 * @return int 
	 */
	protected static function formatWindSpeed($value)
	{
		return str_pad((int)round($value), 2, '0', STR_PAD_LEFT);
	}
	
	/**
	 * Format metric according to report format.
	 * 
	 * @param string Metric code
	 * @return string 
	 */
	protected static function formatMetric($metric)
	{
		switch($metric)
		{
			case 'meter_per_second':
				return 'MPS';
				
			case 'kilometers_per_hour':
				return 'KMH';
				
			case 'knot':
				return 'KT';
				
			default:
				return $metric;
		}
	}	
	
	/**
	 * 
	 *	(a) Up to 800 metres rounded down to the nearest 50 metres;
	 *	(b) Between 800 and 5 000 metres rounded down to the nearest 100 metres;
	 *	(c) Between 5 000 metres up to 9 999 metres rounded down to the nearest 1 000 metres;
	 *	(d) With 9999 indicating 10 km and above. 
	 */
	protected static function formatVisibility($value)
	{
		if ($value < 800)
		{
			return (int)(floor($value / 50) * 50);
		}
		else if (($value >= 800) && ($value < 5000))
		{
			return (int)(floor($value / 100) * 100);
		}
		else if (($value >= 5000) && ($value < 10000))
		{
			return (int)(floor($value / 1000) * 1000);
		}
		
		return 9999;
	}
	
	/**
	 * Format Temperature according to report format.
	 * 
	 * @param int Temperature in celcius
	 * @return string 
	 */
	protected static function formatTemperature($value)
	{
		$roundMode = ($value < 0) ? PHP_ROUND_HALF_DOWN : PHP_ROUND_HALF_UP;
		$prefix = ($value < 0) ? 'M' : '';
		
		return  $prefix . str_pad((int)abs(round($value, 0, $roundMode)), 2, '0', STR_PAD_LEFT);
	}
	
	/**
	 * Format Pressure according to report format.
	 * 
	 * @param int 
	 * @return string 
	 */
	protected static function formatPressure($value)
	{
		return  'Q' . str_pad((int)floor($value), 4, '0', STR_PAD_LEFT);
	}
	
	/**
	 * Format Dew Point according to report format (at this moment, same formatting as formatTemperature).
	 * 
	 * @param int
	 * @return int 
	 */
	protected static function formatDewPoint($value)
	{
		$roundMode = ($value < 0) ? PHP_ROUND_HALF_DOWN : PHP_ROUND_HALF_UP;
		$prefix = ($value < 0) ? 'M' : '';
		
		return  $prefix . str_pad((int)abs(round($value, 0, $roundMode)), 2, '0', STR_PAD_LEFT);
	}
	
	/**
	 *	The height of cloud base shall be reported in steps of 30 m (100 ft) up to 3 000 m (10 000 ft). 
	 *	Any observed value which does not fit the reporting scale in use shall be rounded down to the nearest lower step in the scale.
	 * 
	 * @access protected
	 * @param int Cloud Height value
	 * @oaram string $metric Feet or meter
	 * @return int Formatted Cloud Height value
	 */
	protected static function formatCloudHeight($value, $metric)
	{
		$compareValue = null;
		$divider = null;
		
		switch($metric)
		{
			case 'feet':
				$compareValue = 10000;
				$divider = 100;
				
				break;
			
			// Meters
			default:
				$compareValue = 3000;
				$divider = 30;
				
				break;
		}
		
		if ($value > $compareValue)
		{
			$value = $compareValue;
		}
		
		return str_pad((int)floor($value / $divider), 3, '0', STR_PAD_LEFT);
	}
	
	/**
	 *	The cloud amount NsNsNs shall be reported as few (1 to 2 oktas), scattered (3 to 4 oktas), 
	 *	broken (5 to 7 oktas) or overcast (8 oktas), using the three-letter abbreviations FEW, SCT, BKN and OVC
	 * 
	 * @access protected
	 * @param int Cloud Amount oktas count
	 * @return int Formatted Cloud Amount value
	 */
	protected static function formatCloudAmount($value)
	{
		switch ($value)
		{
			case 1:
			case 2:
				return 'FEW';
				
			case 3:
			case 4:
				return 'SCT';
				
			case 5:
			case 6:
			case 7:
				return 'BKN';
				
			case 8:
				return 'OVC';
		}
		
		return null;
	}
	
    /**
     * Returns cloud amount limits for groups.
     * 
     * @param int $group
     * @return int
     */
    protected static function getCloudAmountLimitForGroup($group)
    {
        switch ($group)
		{
			case 2:
                return 2;
            
            case 3:
                return 4;
                
			default:
				return 0;
		}
    }


    /**
	 * Format Cloud Vertical Visibility according to report format.
	 * 
	 * @param int 
	 * @return string 
	 */
	protected static function formatCloudVerticalVisibility($value)
	{
		return  str_pad((int)round($value), 4, '0', STR_PAD_LEFT);
	}
	
	protected function getSections()
	{
		return array(
			'Body' => array(
				'ReportType',
				'StationIdentifier',

				'ReportTime',
				'ReportModifier',

				'Wind',
				'Visibility',
				'RunwayVisualRange',
				'PresentWeather',
				'SkyCodition',
				'TemperatureAndDewPoint',
				'Altimeter',
			),
			
			'Remarks' => array(
				'ManualAndPlainLanguage',
				'AdditiveData',
			),
		);
	}
	
	/**
	 *	Compiles "space"-separated string.
	 */
	public function prepareReportComplete()
	{
        $this->_logger->log(__METHOD__, array('parts' => $this->report_parts));
		
        $this->report_complete = '';
        
        if ($this->report_parts)
		{
            foreach ($this->report_parts as $section)
			{
				if (is_array($section))
				{
					foreach ($section as $subsection)
					{
						if (!empty($subsection))
						{
							$this->report_complete .= $subsection .' ';
						}
					}
				
					if (!empty($this->report_complete))
					{
						$this->report_complete .= "\n";
					}
				}				
            }             
        }
    }
	
	/**
	 * 
	 */
	public function load($schedule_processed_id)
	{
        parent::load($schedule_processed_id);
        
		$this->_logger->log(__METHOD__);
		
        if ($this->schedule_process_info->schedule_processed_id)
		{
            $file_path = dirname(Yii::app()->request->scriptFile) .
							DIRECTORY_SEPARATOR ."files".
							DIRECTORY_SEPARATOR ."schedule_reports".
							DIRECTORY_SEPARATOR . $this->schedule_process_info->schedule_processed_id;
			
            if (file_exists($file_path))
			{
				$this->report_complete = file_get_contents($file_path);
            }
        }
        
        if ($this->schedule_process_info->serialized_report_errors)
		{
			$this->errors = unserialize($this->schedule_process_info->serialized_report_errors);
        }
    }
    
    /**
	 *
	 * @return boolean 
	 */
    public function generate()
	{
        $this->_logger->log(__METHOD__);
        
		if ($this->errors)
		{
			$this->_logger->log(__METHOD__, array('errors' => $this->errors));
            
            return false;
        }
		
        $current_user_timezone = date_default_timezone_get();
        
		$timezone_id = 'UTC';
        
		if ($timezone_id !=  $current_user_timezone)
		{
			TimezoneWork::set($timezone_id);
        } 
        
        $this->sensors      = $this->prepareSensorsInfo($this->listener_log_info->log_id);
        $this->calculations = $this->_prepareCalculationsInfo($this->listener_log_info->log_id);
        
        if ($timezone_id != $current_user_timezone)
		{
			TimezoneWork::set($current_user_timezone);
        } 
        
        if ($this->errors)
		{
			$this->_logger->log(__METHOD__, array('errors' => $this->errors));
            
            return false;
        }
        
        $this->report_parts = array();
        $this->explanations = array();
        
        $this->_measuring_timestamp = strtotime($this->listener_log_info->measuring_timestamp);
        
		$this->_measuring_day       = gmdate('d', $this->_measuring_timestamp);
        $this->_measuring_hour      = gmdate('H', $this->_measuring_timestamp);
        $this->_measuring_minute	= gmdate('i', $this->_measuring_timestamp);
		
		$this->_logger->log(__METHOD__ .' Prepare sections.');
		
		foreach ($this->getSections() as $sectionNumber => $section)
		{
			$this->_section = $sectionNumber;
			
			$this->_logger->log(__METHOD__, array('section' => $section));
			
			foreach ($section as $subsectionNumber => $subsection)
			{
				$this->_subsection = $subsectionNumber;
				
				$this->_logger->log(__METHOD__, array('subsection' => $subsection));
				
				$this->{'write'. $subsection}();
			}
		}
		
		return true;
	}
	
	/**
	 * Returns METAR or SPECI 
	 * @return string METAR or SPECI
	 */
	protected abstract function getReportType();
	
	/**
	 *  
	 */
	protected function writeReportType()
	{
		$reportType = $this->getReportType();
		
		$this->report_parts[$this->_section][$this->_subsection] = $reportType;
		$this->explanations[$this->_section][$this->_subsection][] = 
				'Report Type: <span>'. $reportType .'</span>';
	}
	
	/**
	 *	Format: CCCC 
	 * 
	 *	The second element of the transmitted coded aviation weather report is the Station Identifier:
	 *	This is entered on MF1M-10 in the heading block labeled SID.
	 *	The METAR/SPECI uses the International Civil Aviation Organization (ICAO) four-letter station
	 *	identifier. All airports in the 48 contiguous states begin with the letter “K” followed by the three-letter
	 *	identifier for the airport. Alaskan stations all begin with “PA” for Pacific-Alaskan, Hawaiian stations
	 *	begin with “PH” for Pacific-Hawaiian. The “PA” or “PH” is followed by the international two-letter
	 *	identifier for that station.
	 *	Stations in the Eastern Caribbean begin with the letter “T”; Western Caribbean stations begin with the
	 *	letter “M”; Guam stations begin with the letters “PG.”
	 * 
	 *	Examples: 
	 *		New Iberia, LA - KARA Alexandria, LA - KAEX
	 *		Sugar Land, TX - KSGR Anchorage, AK - PANC
	 *		Nome, AK - PAOM Honolulu, HI - PHNL
	 *		Keahole Point, HI - PHKO
	 */
	protected function writeStationIdentifier()
	{
		$this->report_parts[$this->_section][$this->_subsection] = $this->station_info->icao_code;
		$this->explanations[$this->_section][$this->_subsection][] = 
				'Station Identifier: <span>'. $this->station_info->icao_code .'</span> => '. $this->station_info->display_name .' ['. $this->station_info->station_id_code .']';
	}
	
	/**
	 *	Format: YYGGggZ 
	 * 
	 *	The third element of the coded aviation weather observation is the Date/Time group.
	 *	YY - two-digit date GG - two-digit hour gg - two-digit minutes Z - letter indicator for UTC
	 *	In the METAR/SPECI, the day and time of observation is a six-character field plus the letter “Z.” The
	 *	first two digits “YY” are the day of the month and the next four digits “GGgg” are the time. The times
	 *	entered are in reference to the 24-hour clock. The letter “Z” is added to the end of the group to indicate
	 *	the date and time are in Coordinated Universal Time.
	 *	The date and time are included in all reports. The actual time of a METAR report is the time the last
	 *	element of the observation was observed. The actual time of a SPECI report is when the criterion for
	 *	a SPECI is met or noted. If the report is a correction to a previously disseminated report, the time of the
	 *	corrected report shall be the same time used in the report being corrected.
	 *	
	 * Examples: 
	 *		An observation taken on the 23rd of the month at 1955 UTC
	 *			METAR KARA 231955Z
	 *		An observation taken on the 1st of the month at 0550 UTC
	 *			METAR KAEX 010550Z
	 *		An observation taken on the 10th of the month at 0005 UTC
	 *			SPECI PAOM 100005Z
	 *		An observation taken on the 20th of the month at 4:35 PM UTC
	 *			SPECI PHKO 201635Z
	 * 
	 */
	protected function writeReportTime()
	{
		$this->report_parts[$this->_section][$this->_subsection] = sprintf("%'02s%'02s%'02sZ", $this->_measuring_day, $this->_measuring_hour, $this->_measuring_minute);
		$this->explanations[$this->_section][$this->_subsection][] = 'Report timestamp: <span>'. $this->_measuring_timestamp .'</span>';
	}
	
	/**
	 *	Format: COR 
	 * 
	 *	The only modifier for the report will be COR. COR indicates the report is a correction to a previously
	 *	transmitted report. Corrections transmitted shall consist of the entire corrected report. The original date
	 *	and time of the report shall be used as the date and time in the corrected report.
	 *
	 *	Example of a Transmitted Corrected Report:
	 *		METAR KOKC 011955Z COR 22015G25KT 3/4SM TSRA BR OVC010CB 18/16 A2992 RMK
	 *		FRQ LTGIC TS OHD MOV E
	 *	
	 *	Corrections to a still valid observation should be given to everyone who received the erroneous data.
	 * 
	 *  Possible values:
	 *		COR - correted report
	 *		NIL - missing report (new one?)
	 */
	protected function writeReportModifier()
	{
		$modifier = 'NIL';
		
		$this->report_parts[$this->_section][$this->_subsection] = $modifier;
		$this->explanations[$this->_section][$this->_subsection][] = 
				'Report modifier: <span>'. $modifier .'</span>';
	}
	
	/**
	 * Format: dddff(f)KT_DnDnDnVDxDxDx
	 * 
	 *	Wind Direction - ddd
	 *		The direction is reported using three characters. When using direct reading dials, you determine the
	 *		wind direction by averaging the direction over a 2-minute period. The true wind direction is reported
	 *		in tens of degrees using three digits. The third character will always be a zero. 
	 *		See Table 2-1, Wind	Direction in Tens of Degrees. 
	 *		
	 *		Direct - ### (average for last 20-minute period).
	 *		True direction - ##0
	 * 
	 * 
	 *	Wind Speed - ff(f)
	 *		Wind speed is reported using two digits or three when necessary. If the direction was determined to be
	 *		variable (VRB) and the wind speed is 6 knots or less, the speed is appended to the VRB, e.g., VRB05.
	 *		However, wind directions should be reported whenever they can be determined even when the wind
	 *		speed is 6 knots or less, e.g., 14004. A calm wind (less than 1 knot) is coded with five zeros, e.g.,
	 *		00000. The transmitted coded group ends with the letters “KT” to indicate the unit of measurement is
	 *		in knots; however, it is not recorded on MF1M-10.
	 * 
	 *		Examples of Transmitted/Coded Data: 
	 *			31015KT VRB04KT 040112KT 14004KT 00000KT
	 * 
	 *	Wind Gusts - Gfmfm(fm)
	 *		Reporting gusts is a bit more difficult. The wind gust is coded in two or three digits immediately
	 *		following the wind speed. The wind data for the most recent 10 minutes are evaluated. Gusts are
	 *		indicated by rapid fluctuations in wind speed with a variation of 10 knots or more between peaks and
	 *		lulls. The speed of the gust shall be the maximum instantaneous wind speed. The letter “G” is placed
	 *		right before the wind gust speed in the transmitted coded report; however, it is not recorded in column
	 *		5 of MF1M-10.
	 * 
	 *		Examples of Transmitted/Coded Data: 
	 *			31015G25KT 090115G125KT
	 *		
	 *		Squalls are reported in Present Weather and are also part of what makes up the wind character. A Squall
	 *		is a sudden increase in average wind speed of at least 16 knots and sustained at 22 knots or more and
	 *		lasting for at least 1 minute. The difference between Gust and Squall is duration and intensity of the
	 *		increase.
	 */
	protected function writeWind()
	{
		$windDirection = $this->getWindDirection();
		$windSpeed = $this->getWindSpeed();
		$windGust = $this->getWindGust();
		
		if ((!is_null($windDirection['source_value']) && ($windDirection['source_value'] != 'M')) && 
			(!is_null($windSpeed['source_value']) && ($windSpeed['source_value'] != 'M'))
			)
		{
			$this->report_parts[$this->_section][$this->_subsection] = $windDirection['value'] . $windSpeed['value'] . $windGust['value'] . $windSpeed['metric'];
		}
		else
		{
			$this->report_parts[$this->_section][$this->_subsection] = '';
		}
		
		$this->explanations[$this->_section][$this->_subsection][] = $windDirection['description'];
		$this->explanations[$this->_section][$this->_subsection][] = $windSpeed['description'];
		$this->explanations[$this->_section][$this->_subsection][] = $windGust['description'];
	}
	
	protected abstract function getWindDirection();
	protected abstract function getWindSpeed();
	protected abstract function getWindGust();
	
	/**
	 * Format: VVVVVSM 
	 */
	protected function writeVisibility()
	{
		$prevailingVisibility = $this->getPrevailingVisibility();
		$directionalVisibility = $this->getDirectionalVisibility();
		
		$cloudHeight1 = $this->getCloudHeight(1);
		
		$cloudVerticalVisibility = $this->getCloudVerticalVisibility();
		
		// CAVOK shall be issued for visibility when visibility is ≥10km AND no cloud below 1500m AND no Vertical Visibility value
		$isCavok = ($prevailingVisibility['source_value'] != 'M') && (It::convertMetric($prevailingVisibility['source_value'], $prevailingVisibility['metric'], 'meter') >= 10000) && 
					($cloudHeight1['source_value'] != 'M') && (It::convertMetric($cloudHeight1['source_value'], $cloudHeight1['source_metric'], 'meter') > 1500) &&
					(($cloudVerticalVisibility['source_value'] == 'M') || is_null($cloudVerticalVisibility['source_value']));
		
		
		if ($isCavok)
		{
			$this->report_parts[$this->_section][$this->_subsection] = 'CAVOK';
			
			$this->explanations[$this->_section][$this->_subsection][] = 'Visibility: <span>CAVOK</span>';
			$this->explanations[$this->_section][$this->_subsection][] = $cloudHeight1['description'];
			$this->explanations[$this->_section][$this->_subsection][] = $cloudVerticalVisibility['description'];
		}
		else
		{
			if ($this->hasDirectionalVisibility())
			{
				$this->report_parts[$this->_section][$this->_subsection] = $prevailingVisibility['value'] .' '. $directionalVisibility['value'];
			}
			else
			{
				$this->report_parts[$this->_section][$this->_subsection] = $prevailingVisibility['value'] . $directionalVisibility['value'];
			}
		}
		
		$this->explanations[$this->_section][$this->_subsection][] = $prevailingVisibility['description'];
		$this->explanations[$this->_section][$this->_subsection][] = $directionalVisibility['description'];
	}
	
	protected abstract function getPrevailingVisibility();
	protected abstract function hasDirectionalVisibility();
	protected abstract function getDirectionalVisibility();
	
	/**
	 * Format: RDrDr/VrVrVrVrFT or RDrDr/VnVnVnVnVVxVxVxVxFT 
	 */
	protected function writeRunwayVisualRange()
	{
		$runwayVisualRange = $this->getRunwayVisualRange();
		
		
		$this->report_parts[$this->_section][$this->_subsection] = $runwayVisualRange['value'];
		
		$this->explanations[$this->_section][$this->_subsection][] = $runwayVisualRange['description'];
	}
	
	protected abstract function getRunwayVisualRange();
	
	/**
	 * Format: w'w'
	 */
	protected function writePresentWeather()
	{
		$presentWeather = $this->getPresentWeather();
		
		
		$this->report_parts[$this->_section][$this->_subsection] = $presentWeather['value'];
		
		$this->explanations[$this->_section][$this->_subsection][] = $presentWeather['description'];
	}
	
	protected abstract function getPresentWeather();
	
	/**
	 * Format: NsNsNsHsHsHs or VVHsHsHs or SKC
	 */
	protected function writeSkyCodition()
	{
		$exitLoop = false;
		$needToCheckLayer4 = false;
		
		// for 1 to 4
        for($group = 1; $group < 5; $group++)
        {
            $cloudAmount = $this->getCloudAmount($group);
            $cloudHeight = $this->getCloudHeight($group);

			if (($group === 4) && ($needToCheckLayer4 === false))
			{
				break;
			}
			
			if (!is_null($cloudAmount['source_value']) && ($cloudAmount['source_value'] !== 'M'))
            {
				$cloudInfo = null;

				// Layer 1 is lower than Layer 2 etc. So, check only 1st layer.
				if (($group === 1) && ($cloudAmount['source_value'] === 0))
				{
					$cloudInfo = 'NCD';
					
					$exitLoop = true;
				}
				// Layer 1 is lower than Layer 2 etc. So, check only 1st layer.
				else if (($group === 1) && (It::convertMetric($cloudHeight['source_value'], $cloudHeight['source_metric'], 'meter') > 1500))
				{
					$cloudInfo = 'NSC';
					
					$exitLoop = true;
				}
				else if ($this->getCloudAmountLimitForGroup($group) < $cloudAmount['source_value'])
				{
					$cloudInfo = $cloudAmount['value'] . $cloudHeight['value'];
				}
				else if ($group === 3)
				{
					$needToCheckLayer4 = true;
				}

				if (!is_null($cloudInfo))
				{
					if (isset($this->report_parts[$this->_section][$this->_subsection]))
					{
						$this->report_parts[$this->_section][$this->_subsection] .= ' '. $cloudInfo;
					}
					else
					{
						$this->report_parts[$this->_section][$this->_subsection] = $cloudInfo;
					}	
					
					if ($exitLoop)
						break;
				}
            }
            
            $this->explanations[$this->_section][$this->_subsection][] = $cloudAmount['description'];
            $this->explanations[$this->_section][$this->_subsection][] = $cloudHeight['description'];
        }
	}
	
	protected abstract function getCloudVerticalVisibility();
	protected abstract function getCloudAmount($number);
	protected abstract function getCloudHeight($number);
	
	/**
	 * Format: T'T'/T'dT'd
	 */
	protected function writeTemperatureAndDewPoint()
	{
		$temperature = $this->getTemperature();
		$dewPoint = $this->getDewPoint();
		
		$this->report_parts[$this->_section][$this->_subsection] = $temperature['value'] .'/'. $dewPoint['value'];
		
		$this->explanations[$this->_section][$this->_subsection][] = $temperature['description'];
		$this->explanations[$this->_section][$this->_subsection][] = $dewPoint['description'];
	}
	
	protected abstract function getTemperature();
	protected abstract function getDewPoint();
	
	/**
	 * Format: APhPhPhPh
	 */
	protected function writeAltimeter()
	{
		$pressure = $this->getPressure();
		
		$this->report_parts[$this->_section][$this->_subsection] = $pressure['value'];
		
		$this->explanations[$this->_section][$this->_subsection][] = $pressure['description'];
	}
	
	protected abstract function getPressure();
	
	/**
	 * 
	 */
	protected function writeManualAndPlainLanguage()
	{
		
	}
	
	/**
	 * 
	 */
	protected function writeAdditiveData()
	{
		
	}
}

?>
