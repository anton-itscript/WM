<?php

/**
 * Description of DefaultMetricProvider
 * 
 * It's not used at the time.
 * 
 * @author
 */
class DefaultMetricProvider extends BaseComponent implements IParamProvider
{
	/**
	 * Array of default metric.
	 * Array key is measurement type. E.g. wind_direction_10.
	 * 
	 * @var array
	 * @access protected
	 */
	protected $_metrics = null; 

	/**
	 * Load metric array.
	 * 
	 * @access protected
	 */
	protected function loadMetrics()
	{
		$this->_metrics = array();
		
		// Loading default sensor metrics
		$metricRecords = RefbookMeasurementTypeMetric::model()->with(array('measurement_type', 'metric'))->findByAllAttributes(array('is_main' => 1));
		
		foreach ($metricRecords as $metricRecord) 
		{
			$this->_metrics[$metricRecord->measurement_type->code] = $metricRecord->metric->metric_code;
		}
		
		
		// Loading default calculations' metrics
		// ToDo: implement this, when need will appear.
		
//		$calculationRecords = CalculationDBHandler::model()->findAll($criteria);
//		
//		foreach ($calculationRecords as $calculationRecord) 
//		{
//			$this->_metrics[$metricRecord->measurement_type->code] = $metricRecord->metric->metric_code;
//		}
	}	

	/**
	 * Returns metrics. Initialize at first call.
	 * 
	 * @access protected
	 * @return array
	 */
	protected function getMetrics()
	{
		if (is_null($this->_metrics))
		{
			$this->loadMetrics();
		}
		
		return $this->_metrics;
	}

	/**
	 * Returns metric by key.
	 * For sensor key is a name of sensor feature, e.g. wind_speed_10.
	 * For calculations - <calculation_handler_name>_<calculation_name>, e.g DewPoint_pressure.
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function getParam($name)
	{
		$this->_logger->log(__METHOD__ .': name= '. $name);
		
		$metric = $this->getMetrics();
		
		return $this->returnResult(__METHOD__, array_key_exists($name, $metric) ? $metric[$name] : null);
	}
}

?>
