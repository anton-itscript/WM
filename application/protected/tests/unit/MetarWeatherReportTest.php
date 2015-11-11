<?php

/**
 * Wrapper of MetarWeatherReport for opening protected methods.
 */
class MetarWeatherReportWrapper extends MetarWeatherReport
{
	public function setMeasuringTimestamp($value)
	{
		$this->_measuring_timestamp = $value;
	}
	
	public function setMeasuringDay($value)
	{
		$this->_measuring_day = $value;
	}
	
	public function setMeasuringHour($value)
	{
		$this->_measuring_hour = $value;
	}
	
	public function setMeasuringMinute($value)
	{
		$this->_measuring_minute = $value;
	}
	
	
	public function setSection($value)
	{
		$this->_section = $value;
	}
	
	public function setSubSection($value)
	{
		$this->_subsection = $value;
	}
	
	public static function formatWindDirectionPublic($value)
	{
		return self::formatWindDirection($value);
	}
	
	public static function formatMetricPublic($value)
	{
		return self::formatMetric($value);
	}
	
	public static function formatVisibilityPublic($value)
	{
		return self::formatVisibility($value);
	}
	
	public static function formatTemperaturePublic($value)
	{
		return self::formatTemperature($value);
	}
	
	public static function formatPressurePublic($value)
	{
		return self::formatPressure($value);
	}
	
	public static function formatDewPointPublic($value)
	{
		return self::formatDewPoint($value);
	}
	
	public static function formatCloudHeightPublic($value, $metric)
	{
		return self::formatCloudHeight($value, $metric);
	}
	
	public static function formatCloudAmountPublic($value)
	{
		return self::formatCloudAmount($value);
	}
	
	public static function getCloudAmountLimitForGroupPublic($group)
	{
		return self::getCloudAmountLimitForGroup($group);
	}
	
	public static function formatCloudVerticalVisibilityPublic($value)
	{
		return self::formatCloudVerticalVisibility($value);
	}
	
	
	
	public function getSectionsPublic()
	{
		return $this->getSections();
	}
	
	public function getReportTypePublic()
	{
		return $this->getReportType();
	}

	public function getWindDirectionPublic()
	{
		return $this->getWindDirection();
	}
	
	public function getWindSpeedPublic()
	{
		return $this->getWindSpeed();
	}
	
	public function getWindGustPublic()
	{
		return $this->getWindGust();
	}
	
	public function getPrevailingVisibilityPublic()
	{
		return $this->getPrevailingVisibility();
	}
	
	public function hasDirectionalVisibilityPublic()
	{
		return $this->hasDirectionalVisibility();
	}
	
	public function getDirectionalVisibilityPublic()
	{
		return $this->getDirectionalVisibility();
	}
	
	public function getRunwayVisualRangePublic()
	{
		return $this->getRunwayVisualRange();
	}
	
	public function getPresentWeatherPublic()
	{
		return $this->getPresentWeather();
	}
	
	public function getCloudAmountPublic($number)
	{
		return $this->getCloudAmount($number);
	}
	
	public function getCloudHeightPublic($number)
	{
		return $this->getCloudHeight($number);
	}
	
	public function getCloudVerticalVisibilityPublic()
	{
		return $this->getCloudVerticalVisibility();
	}
	
	public function getTemperaturePublic()
	{
		return $this->getTemperature();
	}
	
	public function getDewPointPublic()
	{
		return $this->getDewPoint();
	}
	
	public function getPressurePublic()
	{
		return $this->getPressure();
	}
	
	
	
	public function writeReportAutoModifierPublic()
	{
		return $this->writeReportAutoModifier();
	}
	
	public function writeReportTypePublic()
	{
		$this->writeReportType();
	}
	
	public function writeStationIdentifierPublic()
	{
		$this->writeStationIdentifier();
	}
	
	public function writeReportTimePublic()
	{
		$this->writeReportTime();
	}
	
	public function writeReportModifierPublic()
	{
		$this->writeReportModifier();
	}
	
	public function writeWindPublic()
	{
		$this->writeWind();
	}
	
	public function writeVisibilityPublic()
	{
		$this->writeVisibility();
	}
	
	public function writeRunwayVisualRangePublic()
	{
		$this->writeRunwayVisualRange();
	}
	
	public function writePresentWeatherPublic()
	{
		$this->writePresentWeather();
	}
	
	public function writeSkyConditionPublic()
	{
		$this->writeSkyCodition();
	}
	
	public function writeTemperatureAndDewPointPublic()
	{
		$this->writeTemperatureAndDewPoint();
	}
	
	public function writeAltimeterPublic()
	{
		$this->writeAltimeter();
	}
	
	public function writeManualAndPlainLanguagePublic()
	{
		$this->writeManualAndPlainLanguage();
	}
	
	public function writeAdditiveDataPublic()
	{
		$this->writeAdditiveData();
	}
}

/**
 * Description of MetarWeatherReportTest
 *
 * @author
 */
class MetarWeatherReportTest extends CTestCase
{
	public function test_FormatWindDirection_RoundDown()
	{
		$result = MetarWeatherReportWrapper::formatWindDirectionPublic(113);
		$expected = 110;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatWindDirection_RoundUp()
	{
		$result = MetarWeatherReportWrapper::formatWindDirectionPublic(279);
		$expected = 280;
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_FormatMetric_MetersPerSecond()
	{
		$result = MetarWeatherReportWrapper::formatMetricPublic('meter_per_second');
		$expected = 'MPS';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatMetric_KilometersPerHour()
	{
		$result = MetarWeatherReportWrapper::formatMetricPublic('kilometers_per_hour');
		$expected = 'KMH';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatMetric_Knots()
	{
		$result = MetarWeatherReportWrapper::formatMetricPublic('knot');
		$expected = 'KT';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatMetric_NonConvertableMetric()
	{
		$result = MetarWeatherReportWrapper::formatMetricPublic('metric1');
		$expected = 'metric1';
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_FormatVisibility_ZeroValue()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(0);
		$expected = 0;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatVisibility_Below800_1()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(100);
		$expected = 100;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatVisibility_Below800_2()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(129);
		$expected = 100;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatVisibility_Below800_3()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(250);
		$expected = 250;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatVisibility_Below800_4()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(357);
		$expected = 350;
		
		$this->assertEquals($expected, $result);
	}	
	
	public function test_FormatVisibility_Between800and5000_1()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(800);
		$expected = 800;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatVisibility_Between800and5000_2()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(925);
		$expected = 900;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatVisibility_Between800and5000_3()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(1050);
		$expected = 1000;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatVisibility_Between800and5000_4()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(4999);
		$expected = 4900;
		
		$this->assertEquals($expected, $result);
	}	
	
	public function test_FormatVisibility_Between5000and9999_1()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(5000);
		$expected = 5000;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatVisibility_Between5000and9999_2()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(5466);
		$expected = 5000;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatVisibility_Between5000and9999_3()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(7999);
		$expected = 7000;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatVisibility_Between5000and9999_4()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(9999);
		$expected = 9000;
		
		$this->assertEquals($expected, $result);
	}	
	
	public function test_FormatVisibility_Over9999_1()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(10000);
		$expected = 9999;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatVisibility_Over9999_2()
	{
		$result = MetarWeatherReportWrapper::formatVisibilityPublic(12345);
		$expected = 9999;
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_FormatTemperature_BelowZero_TwoDigits()
	{
		$result = MetarWeatherReportWrapper::formatTemperaturePublic(-20);
		$expected = 'M20';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatTemperature_BelowZero_OneDigit()
	{
		$result = MetarWeatherReportWrapper::formatTemperaturePublic(-2);
		$expected = 'M02';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatTemperature_BelowZero_HalfZero()
	{
		$result = MetarWeatherReportWrapper::formatTemperaturePublic(-0.5);
		$expected = 'M00';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatTemperature_Zero()
	{
		$result = MetarWeatherReportWrapper::formatTemperaturePublic(0);
		$expected = '00';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatTemperature_AboveZero_HalfZero()
	{
		$result = MetarWeatherReportWrapper::formatTemperaturePublic(0.5);
		$expected = '01';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatTemperature_AboveZero_OneDigit()
	{
		$result = MetarWeatherReportWrapper::formatTemperaturePublic(9);
		$expected = '09';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatTemperature_AboveZero_TwoDigits()
	{
		$result = MetarWeatherReportWrapper::formatTemperaturePublic(45);
		$expected = '45';
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_FormatPressure_RoundDown()
	{
		$result = MetarWeatherReportWrapper::formatPressurePublic(1.9);
		$expected = 'Q0001';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatPressure_OneDigit()
	{
		$result = MetarWeatherReportWrapper::formatPressurePublic(1);
		$expected = 'Q0001';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatPressure_TwoDigits()
	{
		$result = MetarWeatherReportWrapper::formatPressurePublic(41);
		$expected = 'Q0041';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatPressure_ThreeDigits()
	{
		$result = MetarWeatherReportWrapper::formatPressurePublic(541);
		$expected = 'Q0541';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatPressure_FourDigits()
	{
		$result = MetarWeatherReportWrapper::formatPressurePublic(1541);
		$expected = 'Q1541';
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_FormatDewPoint_BelowZero_TwoDigits()
	{
		$result = MetarWeatherReportWrapper::formatDewPointPublic(-20);
		$expected = 'M20';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatDewPoint_BelowZero_OneDigit()
	{
		$result = MetarWeatherReportWrapper::formatDewPointPublic(-2);
		$expected = 'M02';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatDewPoint_BelowZero_HalfZero()
	{
		$result = MetarWeatherReportWrapper::formatDewPointPublic(-0.5);
		$expected = 'M00';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatDewPoint_Zero()
	{
		$result = MetarWeatherReportWrapper::formatDewPointPublic(0);
		$expected = '00';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatDewPoint_AboveZero_HalfZero()
	{
		$result = MetarWeatherReportWrapper::formatDewPointPublic(0.5);
		$expected = '01';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatDewPoint_AboveZero_OneDigit()
	{
		$result = MetarWeatherReportWrapper::formatDewPointPublic(9);
		$expected = '09';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatDewPoint_AboveZero_TwoDigits()
	{
		$result = MetarWeatherReportWrapper::formatDewPointPublic(45);
		$expected = '45';
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_FormatCloudHeight_ZeroValue_Meters()
	{
		$result = MetarWeatherReportWrapper::formatCloudHeightPublic(0, 'meters');
		$expected = '0';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudHeight_RoundDown1_Meters()
	{
		$result = MetarWeatherReportWrapper::formatCloudHeightPublic(1234, 'meters');
		$expected = '041';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudHeight_RoundDown2_Meters()
	{
		$result = MetarWeatherReportWrapper::formatCloudHeightPublic(1254, 'meters');
		$expected = '041';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudHeight_3000Value_Meters()
	{
		$result = MetarWeatherReportWrapper::formatCloudHeightPublic(3000, 'meters');
		$expected = '100';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudHeight_Over3000Value_Meters()
	{
		$result = MetarWeatherReportWrapper::formatCloudHeightPublic(3244, 'meters');
		$expected = '100';
		
		$this->assertEquals($expected, $result);
	}	
	
	public function test_FormatCloudHeight_ZeroValue_Feet()
	{
		$result = MetarWeatherReportWrapper::formatCloudHeightPublic(0, 'feet');
		$expected = '0';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudHeight_RoundDown1_Feet()
	{
		$result = MetarWeatherReportWrapper::formatCloudHeightPublic(175, 'feet');
		$expected = '001';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudHeight_RoundDown2_Feet()
	{
		$result = MetarWeatherReportWrapper::formatCloudHeightPublic(343, 'feet');
		$expected = '003';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudHeight_10000Value_Feet()
	{
		$result = MetarWeatherReportWrapper::formatCloudHeightPublic(10000, 'feet');
		$expected = '100';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudHeight_Over10000Value_Feet()
	{
		$result = MetarWeatherReportWrapper::formatCloudHeightPublic(11432, 'feet');
		$expected = '100';
		
		$this->assertEquals($expected, $result);
	}	
	
	
	
	public function test_FormatCloudAmount_1okta()
	{
		$result = MetarWeatherReportWrapper::formatCloudAmountPublic(1);
		$expected = 'FEW';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudAmount_2okta()
	{
		$result = MetarWeatherReportWrapper::formatCloudAmountPublic(2);
		$expected = 'FEW';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudAmount_3okta()
	{
		$result = MetarWeatherReportWrapper::formatCloudAmountPublic(3);
		$expected = 'SCT';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudAmount_4okta()
	{
		$result = MetarWeatherReportWrapper::formatCloudAmountPublic(4);
		$expected = 'SCT';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudAmount_5okta()
	{
		$result = MetarWeatherReportWrapper::formatCloudAmountPublic(5);
		$expected = 'BKN';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudAmount_6okta()
	{
		$result = MetarWeatherReportWrapper::formatCloudAmountPublic(6);
		$expected = 'BKN';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudAmount_7okta()
	{
		$result = MetarWeatherReportWrapper::formatCloudAmountPublic(7);
		$expected = 'OVC';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudAmount_8okta()
	{
		$result = MetarWeatherReportWrapper::formatCloudAmountPublic(8);
		$expected = 'OVC';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_FormatCloudAmount_UnknownValue()
	{
		$result = MetarWeatherReportWrapper::formatCloudAmountPublic(12);
		$expected = null;
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_GetCloudAmountLimitForGroup_1stGroup()
	{
		$result = MetarWeatherReportWrapper::getCloudAmountLimitForGroupPublic(1);
		$expected = 0;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudAmountLimitForGroup_2ndGroup()
	{
		$result = MetarWeatherReportWrapper::getCloudAmountLimitForGroupPublic(2);
		$expected = 2;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudAmountLimitForGroup_3rdGroup()
	{
		$result = MetarWeatherReportWrapper::getCloudAmountLimitForGroupPublic(3);
		$expected = 4;
		
		$this->assertEquals($expected, $result);
	}	
	
	
	
	public function test_GetSections()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$result = $report->getSectionsPublic();
		
		$expected = array(
			'Row1' => array(
				'ReportType',
				'StationIdentifier',

				'ReportTime',
				'ReportAutoModifier',
				
				'Wind',
			),
			'Row2' => array(
				'Visibility',
				'RunwayVisualRange',
				'PresentWeather',
				'SkyCodition',
			),
			'Row3' => array(
				'TemperatureAndDewPoint',
				'Altimeter',
			),
		);
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_PreapreReportComplete_NoParts()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->report_parts = array();
		
		$report->prepareReportComplete();
		
		$expected = '';
		
		$this->assertEquals($expected, $report->report_complete);
	}
	
	public function test_PreapreReportComplete_EmptySubsetions()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->report_parts = array(
			'a' => array(
				'1' => 'test1',
				'2' => 'test2',
				'3' => '',
				'4' => '',
				'5' => 'test5',
			),
			'b' => array(
				'1' => '',
				'2' => 'b_test2',
				'3' => '',
				'4' => 'b_test4',
			),
		);
		
		$report->prepareReportComplete();
		
		$expected = "test1 test2 test5 \nb_test2 b_test4 \n";
		
		$this->assertEquals($expected, $report->report_complete);
	}
	
	
	
	public function test_GetReportType()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$result = $report->getReportTypePublic();
		$expected = 'METAR';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetWindDirection_NoWindDirectionSensor()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array();
		
		$result = $report->getWindDirectionPublic();
		
		$expected = array(
			'value' => '///',
			'source_value' => null,
			'description' => 'Wind Direction: <span>///</span> => <span>No Wind Direction sensor</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetWindDirection_IsMissing()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'wind_direction_10' => array(
				'sensor_feature_value' => null,
				'metric_code' => 'degree',
				'is_m' => 1,
			)
		);
		
		$result = $report->getWindDirectionPublic();
		
		$expected = array(
			'value' => '///',
			'source_value' => 'M',
			'description' => 'Wind Direction: <span>///</span> => <span>Last data about Wind Direction is unavailable</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetWindDirection_ZeroDegree()
	{
		$stationInfo = new Station();
		
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->station_info = $stationInfo;
		
		$report->sensors = array(
			'wind_direction_10' => array(
				'sensor_feature_value' => 0,
				'metric_code' => 'degree',
				'is_m' => 0,
				'handler_id_code' => 'WindDirection',
			)
		);
		
		$result = $report->getWindDirectionPublic();
		
		$expected = array(
			'value' => '360',
			'source_value' => '0',
			'description' => 'Wind Direction: <span>360</span> => <span>0 degree</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetWindDirection_LessThen10degree_RoundToZero()
	{
		$stationInfo = new Station();
		
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->station_info = $stationInfo;
		
		$report->sensors = array(
			'wind_direction_10' => array(
				'sensor_feature_value' => 3,
				'metric_code' => 'degree',
				'is_m' => 0,
				'handler_id_code' => 'WindDirection',
			)
		);
		
		$result = $report->getWindDirectionPublic();
		
		$expected = array(
			'value' => '360',
			'source_value' => 3,
			'description' => 'Wind Direction: <span>360</span> => <span>3 degree</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetWindDirection_TwoDigits()
	{
		$stationInfo = new Station();
		
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->station_info = $stationInfo;
		
		$report->sensors = array(
			'wind_direction_10' => array(
				'sensor_feature_value' => 15,
				'metric_code' => 'degree',
				'is_m' => 0,
				'handler_id_code' => 'WindDirection',
			)
		);
		
		$result = $report->getWindDirectionPublic();
		
		$expected = array(
			'value' => '020',
			'source_value' => '15',
			'description' => 'Wind Direction: <span>020</span> => <span>15 degree</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetWindDirection_ThreeDigits()
	{
		$stationInfo = new Station();
		
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->station_info = $stationInfo;
		
		$report->sensors = array(
			'wind_direction_10' => array(
				'sensor_feature_value' => 155,
				'metric_code' => 'degree',
				'is_m' => 0,
				'handler_id_code' => 'WindDirection',
			)
		);
		
		$result = $report->getWindDirectionPublic();
		
		$expected = array(
			'value' => '160',
			'source_value' => '155',
			'description' => 'Wind Direction: <span>160</span> => <span>155 degree</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetWindDirection_360degree()
	{
		$stationInfo = new Station();
		
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->station_info = $stationInfo;
		
		$report->sensors = array(
			'wind_direction_10' => array(
				'sensor_feature_value' => 360,
				'metric_code' => 'degree',
				'is_m' => 0,
				'handler_id_code' => 'WindDirection',
			)
		);
		
		$result = $report->getWindDirectionPublic();
		
		$expected = array(
			'value' => '360',
			'source_value' => '360',
			'description' => 'Wind Direction: <span>360</span> => <span>360 degree</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetWindSpeed_NoWindSpeedSensor()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array();
		
		$result = $report->getWindSpeedPublic();
		
		$expected = array(
			'value' => '00',
			'source_value' => null,
			'metric' => 'MPS',
			'description' => 'Wind Speed: <span>00</span> => <span>No Wind Speed sensor</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetWindSpeed_IsMissing()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'wind_speed_10' => array(
				'sensor_feature_value' => null,
				'metric_code' => 'meter_per_second',
				'is_m' => 1,
			)
		);
		
		$result = $report->getWindSpeedPublic();
		
		$expected = array(
			'value' => '00',
			'source_value' => 'M',
			'metric' => 'MPS',
			'description' => 'Wind Speed: <span>00</span> => <span>Last data about Visibility is unavailable</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetWindSpeed_OneDigit()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'wind_speed_10' => array(
				'sensor_feature_value' => 2,
				'metric_code' => 'meter_per_second',
				'is_m' => 0,
			)
		);
		
		$result = $report->getWindSpeedPublic();
		
		$expected = array(
			'value' => '02',
			'source_value' => 2,
			'metric' => 'MPS',
			'description' => 'Wind Speed: <span>02</span> => <span>2 meter_per_second</span>',
		);
		
		$this->assertEquals($expected, $result);
	}

	public function test_GetWindSpeed_TwoDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'wind_speed_10' => array(
				'sensor_feature_value' => 25,
				'metric_code' => 'meter_per_second',
				'is_m' => 0,
			)
		);
		
		$result = $report->getWindSpeedPublic();
		
		$expected = array(
			'value' => '25',
			'source_value' => 25,
			'metric' => 'MPS',
			'description' => 'Wind Speed: <span>25</span> => <span>25 meter_per_second</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetWindSpeed_DifferentMetrics()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'wind_speed_10' => array(
				'sensor_feature_value' => 25,
				'metric_code' => 'kilometers_per_hour',
				'is_m' => 0,
			)
		);
		
		$result = $report->getWindSpeedPublic();
		
		$expected = array(
			'value' => '90',
			'source_value' => 25,
			'metric' => 'MPS',
			'description' => 'Wind Speed: <span>90</span> => <span>25 kilometers_per_hour = 90 meter_per_second</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetWindGust_NoWindGustSensor()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array();
		
		$result = $report->getWindGustPublic();
		
		$expected = array(
			'value' => '',
			'source_value' => null,
			'description' => 'Wind Gust: <span></span> => <span>Not available</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetPrevailingVisibility_NoVisibilitySensor()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array();
		
		$result = $report->getPrevailingVisibilityPublic();
		
		$expected = array(
			'value' => '////',
			'source_value' => null,
			'description' => 'Prevailing visibility: <span>////</span> => <span>No Visibility sensor</span>',
			'metric' => 'meter',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetPrevailingVisibility_IsMissing()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'visibility_1' => array(
				'sensor_feature_value' => null,
				'metric_code' => 'meter',
				'is_m' => 1,
			)
		);
		
		$result = $report->getPrevailingVisibilityPublic();
		
		$expected = array(
			'value' => '////',
			'source_value' => 'M',
			'description' => 'Prevailing visibility: <span>////</span> => <span>Last data about Visibility is unavailable</span>',
			'metric' => 'meter',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetPrevailingVisibility_OneDigit()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'visibility_1' => array(
				'sensor_feature_value' => 2,
				'metric_code' => 'meter',
				'is_m' => 0,
			)
		);
		
		$result = $report->getPrevailingVisibilityPublic();
		
		$expected = array(
			'value' => '0',
			'source_value' => 2,
			'description' => 'Prevailing visibility: <span>0</span> => <span>2 meter</span>',
			'metric' => 'meter',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetPrevailingVisibility_TwoDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'visibility_1' => array(
				'sensor_feature_value' => 54,
				'metric_code' => 'meter',
				'is_m' => 0,
			)
		);
		
		$result = $report->getPrevailingVisibilityPublic();
		
		$expected = array(
			'value' => '50',
			'source_value' => 54,
			'description' => 'Prevailing visibility: <span>50</span> => <span>54 meter</span>',
			'metric' => 'meter',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetPrevailingVisibility_ThreeDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'visibility_1' => array(
				'sensor_feature_value' => 953,
				'metric_code' => 'meter',
				'is_m' => 0,
			)
		);
		
		$result = $report->getPrevailingVisibilityPublic();
		
		$expected = array(
			'value' => '900',
			'source_value' => 953,
			'description' => 'Prevailing visibility: <span>900</span> => <span>953 meter</span>',
			'metric' => 'meter',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetPrevailingVisibility_FourDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'visibility_1' => array(
				'sensor_feature_value' => 7953,
				'metric_code' => 'meter',
				'is_m' => 0,
			)
		);
		
		$result = $report->getPrevailingVisibilityPublic();
		
		$expected = array(
			'value' => '7000',
			'source_value' => 7953,
			'description' => 'Prevailing visibility: <span>7000</span> => <span>7953 meter</span>',
			'metric' => 'meter',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetPrevailingVisibility_DifferentMetrics()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'visibility_1' => array(
				'sensor_feature_value' => 5,
				'metric_code' => 'kilometer',
				'is_m' => 0,
			)
		);
		
		$result = $report->getPrevailingVisibilityPublic();
		
		$expected = array(
			'value' => '5000',
			'source_value' => 5,
			'description' => 'Prevailing visibility: <span>5000</span> => <span>5 kilometer = 5000 meter</span>',
			'metric' => 'meter',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_HasDirectionalVisibility()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$result = $report->hasDirectionalVisibilityPublic();
		
		$this->assertFalse($result);
	}
	
	
	
	public function test_GetDirectionalVisibility_NotAvailable()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array();
		
		$result = $report->getDirectionalVisibilityPublic();
		
		$expected = array(
			'value' => 'NDV',
			'source_value' => null,
			'description' => 'Directional visibility: <span>NDV</span> => <span>Not available</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_GetRunwayVisualRange_NotAvailable()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array();
		
		$result = $report->getRunwayVisualRangePublic();
		
		$expected = array(
			'value' => '',
			'source_value' => null,
			'description' => 'Runaway Visual Range: <span></span> => <span>Not available</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_GetCloudVerticalVisibility_NoSensor()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array();
		
		$result = $report->getCloudVerticalVisibilityPublic();
		
		$expected = array(
			'source_value' => null,
			'value' => '////',
			'description' => 'Cloud Vertical Visibility: <span>////</span> => <span>No Cloud Vertical Visibility sensor</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudVerticalVisibility_IsMissing()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_vertical_visibility' => array(
				'is_m' => 1,
			)
		);
		
		$result = $report->getCloudVerticalVisibilityPublic();
		
		$expected = array(
			'source_value' => 'M',
			'value' => '////',
			'description' => 'Cloud Vertical Visibility: <span>////</span> => <span>Last data about Cloud Vertical Visibility is unavailable</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudVerticalVisibility_OneDigit()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_vertical_visibility' => array(
				'sensor_feature_value' => 4,
				'is_m' => 0,
				'metric_code' => 'meter',
			)
		);
		
		$result = $report->getCloudVerticalVisibilityPublic();
		
		$expected = array(
			'source_value' => 4,
			'value' => '0004',
			'description' => 'Cloud Vertical Visibility: <span>0004</span> => <span>4 meter</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudVerticalVisibility_TwoDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_vertical_visibility' => array(
				'sensor_feature_value' => 47,
				'is_m' => 0,
				'metric_code' => 'meter',
			)
		);
		
		$result = $report->getCloudVerticalVisibilityPublic();
		
		$expected = array(
			'source_value' => 47,
			'value' => '0047',
			'description' => 'Cloud Vertical Visibility: <span>0047</span> => <span>47 meter</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudVerticalVisibility_ThreeDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_vertical_visibility' => array(
				'sensor_feature_value' => 478,
				'is_m' => 0,
				'metric_code' => 'meter',
			)
		);
		
		$result = $report->getCloudVerticalVisibilityPublic();
		
		$expected = array(
			'source_value' => 478,
			'value' => '0478',
			'description' => 'Cloud Vertical Visibility: <span>0478</span> => <span>478 meter</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudVerticalVisibility_FourDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_vertical_visibility' => array(
				'sensor_feature_value' => 1478,
				'is_m' => 0,
				'metric_code' => 'meter',
			)
		);
		
		$result = $report->getCloudVerticalVisibilityPublic();
		
		$expected = array(
			'source_value' => 1478,
			'value' => '1478',
			'description' => 'Cloud Vertical Visibility: <span>1478</span> => <span>1478 meter</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudVerticalVisibility_Feets()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_vertical_visibility' => array(
				'sensor_feature_value' => 1478,
				'is_m' => 0,
				'metric_code' => 'feet',
			)
		);
		
		$result = $report->getCloudVerticalVisibilityPublic();
		
		$expected = array(
			'source_value' => 1478,
			'value' => '0450',
			'description' => 'Cloud Vertical Visibility: <span>0450</span> => <span>1478 feet = 450.4944 meter</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_GetPresentWeather_NotAvailable()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array();
		
		$result = $report->getPresentWeatherPublic();
		
		$expected = array(
			'value' => '',
			'source_value' => null,
			'description' => 'Present Weather: <span></span> => <span>Not available</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	
	public function test_GetTemperature_NoTemperatureSensor()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array();
		
		$result = $report->getTemperaturePublic();
		
		$expected = array(
			'value' => '//',
			'source_value' => null,
			'description' => 'Temperature: <span>//</span> => <span>No Temperature sensor</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetTemperature_IsMissing()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'temperature' => array(
				'sensor_feature_value' => null,
				'metric_code' => 'celsius',
				'is_m' => 1,
			)
		);
		
		$result = $report->getTemperaturePublic();
		
		$expected = array(
			'value' => '//',
			'source_value' => 'M',
			'description' => 'Temperature: <span>//</span> => <span>Last data about Temperature is unavailable</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetTemperature_OneDigit()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'temperature' => array(
				'sensor_feature_value' => 5,
				'metric_code' => 'celsius',
				'is_m' => 0,
			)
		);
		
		$result = $report->getTemperaturePublic();
		
		$expected = array(
			'value' => '05',
			'source_value' => 5,
			'description' => 'Temperature: <span>05</span> => <span>5 celsius</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetTemperature_TwoDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'temperature' => array(
				'sensor_feature_value' => 56,
				'metric_code' => 'celsius',
				'is_m' => 0,
			)
		);
		
		$result = $report->getTemperaturePublic();
		
		$expected = array(
			'value' => '56',
			'source_value' => 56,
			'description' => 'Temperature: <span>56</span> => <span>56 celsius</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetTemperature_DifferentMetric()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'temperature' => array(
				'sensor_feature_value' => 298,
				'metric_code' => 'kelvin',
				'is_m' => 0,
			)
		);
		
		$result = $report->getTemperaturePublic();
		
		$expected = array(
			'value' => '25',
			'source_value' => 298,
			'description' => 'Temperature: <span>25</span> => <span>298 kelvin = 24.85 celsius</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_GetDewPoint_NoDewPointCalculation()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->calculations = array();
		
		$result = $report->getDewPointPublic();
		
		$expected = array(
			'value' => '//',
			'source_value' => null,
			'description' => 'Dew Point: <span>//</span> => <span>No Dew Point calculation</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetDewPoint_OneDigit()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->calculations = array(
			'DewPoint' => array(
				'value' => 5,
			)
		);
		
		$result = $report->getDewPointPublic();
		
		$expected = array(
			'value' => '05',
			'source_value' => 5,
			'description' => 'Dew Point: <span>05</span> => <span>5 celsius</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetDewPoint_TwoDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->calculations = array(
			'DewPoint' => array(
				'value' => 56,
			)
		);
		
		$result = $report->getDewPointPublic();
		
		$expected = array(
			'value' => '56',
			'source_value' => 56,
			'description' => 'Dew Point: <span>56</span> => <span>56 celsius</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetPressure_NoPressureSensor()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->calculations = array();
		
		$result = $report->getPressurePublic();
		
		$expected = array(
			'value' => 'Q////',
			'source_value' => null,
			'description' => 'Pressure Sea Level: <span>Q////</span> => <span>No Pressure Sea Level calculation</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetPressure_OneDigit()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->calculations = array(
			'PressureSeaLevel' => array(
				'value' => 5,
			)
		);
		
		$result = $report->getPressurePublic();
		
		$expected = array(
			'value' => 'Q0005',
			'source_value' => 5,
			'description' => 'Pressure Sea Level: <span>Q0005</span> => <span>5 hPascal</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetPressure_TwoDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->calculations = array(
			'PressureSeaLevel' => array(
				'value' => 56,
			)
		);
		
		$result = $report->getPressurePublic();
		
		$expected = array(
			'value' => 'Q0056',
			'source_value' => 56,
			'description' => 'Pressure Sea Level: <span>Q0056</span> => <span>56 hPascal</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetPressure_ThreeDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->calculations = array(
			'PressureSeaLevel' => array(
				'value' => 567,
			)
		);
		
		$result = $report->getPressurePublic();
		
		$expected = array(
			'value' => 'Q0567',
			'source_value' => 567,
			'description' => 'Pressure Sea Level: <span>Q0567</span> => <span>567 hPascal</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetPressure_FourDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->calculations = array(
			'PressureSeaLevel' => array(
				'value' => 1567,
			)
		);
		
		$result = $report->getPressurePublic();
		
		$expected = array(
			'value' => 'Q1567',
			'source_value' => 1567,
			'description' => 'Pressure Sea Level: <span>Q1567</span> => <span>1567 hPascal</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	

    
    
	public function test_GetCloudAmount_NoSensor()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
		);
		
		$result = $report->getCloudAmountPublic(1);
		
		$expected = array(
			'value' => '///',
			'source_value' => null,
			'description' => 'Cloud Amount #1: <span>///</span> => <span>No Cloud Amount sensor</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudAmount_IsMissing()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_amount_amount_1' => array(
				'sensor_feature_value' => null,
				'is_m' => 1,
			)
		);
		
		$result = $report->getCloudAmountPublic(1);
		
		$expected = array(
			'value' => '///',
			'source_value' => 'M',
			'description' => 'Cloud Amount #1: <span>///</span> => <span>Last data about Cloud Amount #1 is unavailable</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudAmount_1stFeature_1okta()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_amount_amount_1' => array(
				'sensor_feature_value' => 1,
				'is_m' => 0,
			)
		);
		
		$result = $report->getCloudAmountPublic(1);
		
		$expected = array(
			'value' => 'FEW',
			'source_value' => 1,
			'description' => 'Cloud Amount #1: <span>FEW</span> => <span>1/8</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudAmount_2ndFeature_8oktas()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_amount_amount_2' => array(
				'sensor_feature_value' => 8,
				'is_m' => 0,
			)
		);
		
		$result = $report->getCloudAmountPublic(2);
		
		$expected = array(
			'value' => 'OVC',
			'source_value' => 8,
			'description' => 'Cloud Amount #2: <span>OVC</span> => <span>8/8</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudAmount_3rdFeature_4oktas()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_amount_amount_3' => array(
				'sensor_feature_value' => 4,
				'is_m' => 0,
			)
		);
		
		$result = $report->getCloudAmountPublic(3);
		
		$expected = array(
			'value' => 'SCT',
			'source_value' => 4,
			'description' => 'Cloud Amount #3: <span>SCT</span> => <span>4/8</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudAmount_TotalFeature_6oktas()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_amount_amount_total' => array(
				'sensor_feature_value' => 6,
				'is_m' => 0,
			)
		);
		
		$result = $report->getCloudAmountPublic('total');
		
		$expected = array(
			'value' => 'BKN',
			'source_value' => 6,
			'description' => 'Cloud Amount #total: <span>BKN</span> => <span>6/8</span>',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	
	
	public function test_GetCloudHeight_NoSensor()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
		);
		
		$result = $report->getCloudHeightPublic(1);
		
		$expected = array(
			'value' => '///',
			'source_value' => null,
			'description' => 'Cloud Height #1: <span>///</span> => <span>No Cloud Height sensor</span>',
			'metric' => 'feet',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudHeight_IsMissing()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_amount_height_1' => array(
				'is_m' => 1,
			)
		);
		
		$result = $report->getCloudHeightPublic(1);
		
		$expected = array(
			'value' => '///',
			'source_value' => 'M',
			'description' => 'Cloud Height #1: <span>///</span> => <span>Last data about Cloud Height #1 is unavailable</span>',
			'metric' => 'feet',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudHeight_1stFeature_OneDigit()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_amount_height_1' => array(
				'sensor_feature_value' => 39,
				'metric_code' => 'meter',
				'is_m' => 0,
			)
		);
		
		$result = $report->getCloudHeightPublic(1);
		
		$expected = array(
			'value' => '001',
			'source_value' => 39,
			'description' => 'Cloud Height #1: <span>001</span> => <span>39 meter = 127.95275590551 feet</span>',
			'metric' => 'feet',
			'source_metric' => 'meter',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudHeight_2ndFeature_TwoDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_amount_height_2' => array(
				'sensor_feature_value' => 361,
				'metric_code' => 'meter',
				'is_m' => 0,
			)
		);
		
		$result = $report->getCloudHeightPublic(2);
		
		$expected = array(
			'value' => '011',
			'source_value' => 361,
			'description' => 'Cloud Height #2: <span>011</span> => <span>361 meter = 1184.3832020997 feet</span>',
			'metric' => 'feet',
			'source_metric' => 'meter',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudHeight_3rdFeature_ThreeDigits()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_amount_height_3' => array(
				'sensor_feature_value' => 39,
				'metric_code' => 'meter',
				'is_m' => 0,
			)
		);
		
		$result = $report->getCloudHeightPublic(3);
		
		$expected = array(
			'value' => '001',
			'source_value' => 39,
			'description' => 'Cloud Height #3: <span>001</span> => <span>39 meter = 127.95275590551 feet</span>',
			'metric' => 'feet',
			'source_metric' => 'meter'
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_GetCloudHeight_DifferentMetrics()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'cloud_amount_height_1' => array(
				'sensor_feature_value' => 1000,
				'metric_code' => 'meter',
				'is_m' => 0,
			)
		);
		
		$result = $report->getCloudHeightPublic(1);
		
		$expected = array(
			'value' => '032',
			'source_value' => 1000,
			'description' =>  'Cloud Height #1: <span>032</span> => <span>1000 meter = 3280.8398950131 feet</span>',
			'metric' => 'feet',
			'source_metric' => 'meter',
		);
		
		$this->assertEquals($expected, $result);
	}	
	
	
	
	public function test_WriteReportAutoModifier()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->setSection(0);
		$report->setSubSection(0);
		
		$report->writeReportAutoModifierPublic();
		
		$parts = array(
			array(
				'XXXX'
			),
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Fully automated generation: <span>XXXX</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	
	
	public function test_WriteReportType()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->setSection(0);
		$report->setSubSection(0);
		
		$report->writeReportTypePublic();
		
		$parts = array(
			array(
				'METAR'
			),
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Report Type: <span>METAR</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	public function test_WriteStationIdentifier()
	{
		$station = new Station();
		
		$station->icao_code = 'TST1';
        $station->station_id_code = 'TST01';
		$station->display_name = 'TestStation';
		
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->station_info = $station;
		
		$report->writeStationIdentifierPublic();
		
		$parts = array(
			array(
				'TST1'
			),
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Station Identifier: <span>TST1</span> => TestStation [TST01]'
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	public function test_WriteReportTime_OneDigitDayHourMinute()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->setMeasuringTimestamp('measure_time');
		
		$report->setMeasuringDay(1);
		$report->setMeasuringHour(3);
		$report->setMeasuringMinute(4);
		
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeReportTimePublic();
		
		$parts = array(
			array(
				'010304Z',
			),
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Report timestamp: <span>measure_time</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	public function test_WriteReportTime_TwoDigitsDayHourMinute()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->setMeasuringTimestamp('measure_time1');
		
		$report->setMeasuringDay(12);
		$report->setMeasuringHour(10);
		$report->setMeasuringMinute(45);
		
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeReportTimePublic();
		
		$parts = array(
			array(
				'121045Z',
			),
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Report timestamp: <span>measure_time1</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	
	public function test_WriteReportModifier()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeReportModifierPublic();
		
		$parts = array(
			array(
				'NIL',
			),
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Report modifier: <span>NIL</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	
	public function test_WriteWind()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeWindPublic();
		
		$parts = array(
			array(
				'///00MPS',
			),
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Wind Direction: <span>///</span> => <span>No Wind Direction sensor</span>',
					'Wind Speed: <span>00</span> => <span>No Wind Speed sensor</span>',
					'Wind Gust: <span></span> => <span>Not available</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	public function test_WriteVisibility_Empty()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeVisibilityPublic();
		
		$parts = array(
			array(
				'////NDV',
			),
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Prevailing visibility: <span>////</span> => <span>No Visibility sensor</span>',
					'Directional visibility: <span>NDV</span> => <span>Not available</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	public function test_WriteVisibility_Cavok()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->sensors = array(
			'visibility_1' => array(
				'sensor_feature_value' => 10000,
				'metric_code' => 'meter',
				'is_m' => 0,
			),
			
			'cloud_amount_height_1' => array(
				'sensor_feature_value' => 1600,
				'metric_code' => 'meter',
				'is_m' => 0,
			),
			
			'cloud_vertical_visibility' => array(
				'is_m' => 1,
			),
		);
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeVisibilityPublic();
		
		$parts = array(
			array(
				'CAVOK',
			),
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Visibility: <span>CAVOK</span>',
					'Cloud Height #1: <span>052</span> => <span>1600 meter = 5249.343832021 feet</span>',
					'Cloud Vertical Visibility: <span>////</span> => <span>Last data about Cloud Vertical Visibility is unavailable</span>',
					'Prevailing visibility: <span>9999</span> => <span>10000 meter</span>',
					'Directional visibility: <span>NDV</span> => <span>Not available</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	public function test_WriteRunwayVisualRange()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeRunwayVisualRangePublic();
		
		$parts = array(
			array(
				'',
			),
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Runaway Visual Range: <span></span> => <span>Not available</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	public function test_WritePresentWeather()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
				
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writePresentWeatherPublic();
		
		$parts = array(
			array(
				'',
			),
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Present Weather: <span></span> => <span>Not available</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	public function test_WriteSkyConditions()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
				
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeSkyConditionPublic();
		
		$this->assertNull($report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Cloud Amount #1: <span>///</span> => <span>No Cloud Amount sensor</span>',
					'Cloud Height #1: <span>///</span> => <span>No Cloud Height sensor</span>',
                    'Cloud Amount #2: <span>///</span> => <span>No Cloud Amount sensor</span>',
					'Cloud Height #2: <span>///</span> => <span>No Cloud Height sensor</span>',
                    'Cloud Amount #3: <span>///</span> => <span>No Cloud Amount sensor</span>',
					'Cloud Height #3: <span>///</span> => <span>No Cloud Height sensor</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	public function test_WriteSkyConditions_NCDandNSC()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
				
		$report->sensors = array(
			'cloud_amount_amount_1' => array(
				'sensor_feature_value' => 0,
				'is_m' => 0,
			),
			
			'cloud_amount_amount_2' => array(
				'sensor_feature_value' => 4,
				'is_m' => 0,
			),
			
			'cloud_amount_amount_3' => array(
				'sensor_feature_value' => '',
				'is_m' => 1,
			),
			
			
			'cloud_amount_height_1' => array(
				'sensor_feature_value' => 2000,
				'metric_code' => 'meter',
				'is_m' => 0,
			),
			
			'cloud_amount_height_2' => array(
				'sensor_feature_value' => 1700,
				'metric_code' => 'meter',
				'is_m' => 0,
			),
			
			'cloud_amount_height_3' => array(
				'sensor_feature_value' => 400,
				'metric_code' => 'meter',
				'is_m' => 0,
			),
		);
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeSkyConditionPublic();
		
		$parts = array(
			array(
				'NCD NSC',
			)
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Cloud Amount #1: <span></span> => <span>0/8</span>',
					'Cloud Height #1: <span>065</span> => <span>2000 meter = 6561.6797900262 feet</span>',
                    'Cloud Amount #2: <span>SCT</span> => <span>4/8</span>',
					'Cloud Height #2: <span>055</span> => <span>1700 meter = 5577.4278215223 feet</span>',
                    'Cloud Amount #3: <span>///</span> => <span>Last data about Cloud Amount #3 is unavailable</span>',
					'Cloud Height #3: <span>013</span> => <span>400 meter = 1312.3359580052 feet</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	public function test_WriteSkyConditions_Common()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
				
		$report->sensors = array(
			'cloud_amount_amount_1' => array(
				'sensor_feature_value' => 1,
				'is_m' => 0,
			),
			
			'cloud_amount_amount_2' => array(
				'sensor_feature_value' => 3,
				'is_m' => 0,
			),
			
			'cloud_amount_amount_3' => array(
				'sensor_feature_value' => 7,
				'is_m' => 0,
			),
			
			
			'cloud_amount_height_1' => array(
				'sensor_feature_value' => 1200,
				'metric_code' => 'meter',
				'is_m' => 0,
			),
			
			'cloud_amount_height_2' => array(
				'sensor_feature_value' => 300,
				'metric_code' => 'meter',
				'is_m' => 0,
			),
			
			'cloud_amount_height_3' => array(
				'sensor_feature_value' => 400,
				'metric_code' => 'meter',
				'is_m' => 0,
			),
		);
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeSkyConditionPublic();
		
		$parts = array(
			array(
				'FEW039 SCT009 OVC013',
			)
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Cloud Amount #1: <span>FEW</span> => <span>1/8</span>',
					'Cloud Height #1: <span>039</span> => <span>1200 meter = 3937.0078740157 feet</span>',
                    'Cloud Amount #2: <span>SCT</span> => <span>3/8</span>',
					'Cloud Height #2: <span>009</span> => <span>300 meter = 984.25196850394 feet</span>',
                    'Cloud Amount #3: <span>OVC</span> => <span>7/8</span>',
					'Cloud Height #3: <span>013</span> => <span>400 meter = 1312.3359580052 feet</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	public function test_WriteTemperatureAndDewPoint()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeTemperatureAndDewPointPublic();
		
		$parts = array(
			array(
				'/////',
			),
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Temperature: <span>//</span> => <span>No Temperature sensor</span>',
					'Dew Point: <span>//</span> => <span>No Dew Point calculation</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}

	public function test_WriteAltimeter()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeAltimeterPublic();
		
		$parts = array(
			array(
				'Q////',
			),
		);
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = array(
			array(
				array(
					'Pressure Sea Level: <span>Q////</span> => <span>No Pressure Sea Level calculation</span>',
				),
			),
		);
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	public function test_WriteManualAndPlainLanguage()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeManualAndPlainLanguagePublic();
		
		$parts = null;
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = null;
		
		$this->assertEquals($explanations, $report->explanations);
	}
	
	public function test_WriteAdditiveData()
	{
		$report = new MetarWeatherReportWrapper(LoggerFactory::getTestLogger());
		
		$report->setSection(0);
		$report->setSubSection(0);
		$report->writeAdditiveDataPublic();
		
		$parts = null;
		
		$this->assertEquals($parts, $report->report_parts);
		
		$explanations = null;
		
		$this->assertEquals($explanations, $report->explanations);
	}
}

?>
