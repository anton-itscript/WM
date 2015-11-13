<?php

/**
 * This class contains owm implementation of generate() and prepareReportComplete() functions of WeatherReport class.
 * 
 */
class LesothoODSSWeatherTypeReport extends WeatherTypeReport
{
    protected $formatted_data;
    public function transformData()
    {


        $this->data = $this->dataSort($this->data, 'measuring_timestamp', 'desc' );



        $this->_logger->log(__METHOD__ . ' START');
        $this->_logger->log(__METHOD__ . ' COUNT:' . count($this->data));

        $metricHeaders= array();
        $formattedData = array();

        if (count($this->data)>0) {
            $i=0;

            foreach ($this->data as $listenerLogItem) {
                $row = array();
                if ($i==0) {
                    $row[$listenerLogItem->Station->station_type] = $listenerLogItem->Station->station_id_code;
                    $row['Time'] = $listenerLogItem->measuring_timestamp;
                    $metricHeaders[$listenerLogItem->Station->station_type] = '';
                    $metricHeaders['Time'] = '';
                }

                foreach ($listenerLogItem->sensor_data as $sensor_data) {
                    if ($sensor_data->sensor_feature->is_main) {

                        $row[$sensor_data->Sensor->sensor_id_code] =  $sensor_data->sensor_feature_value;
                        $metricHeaders[$sensor_data->Sensor->sensor_id_code] = $sensor_data->sensor_feature->metric->short_name;
                    }
                }

                $formattedData[] = $row;
            }
        }
        array_unshift ($formattedData, $metricHeaders);
        $this->formatted_data = $formattedData;

        return $formattedData;
    }


    public function prepareReportComplete()
    {
        $this->report_complete = '';

        if(count($this->formatted_data))
        {
//            $headers = $this->createCSVHeaders();
//
//            $this->report_complete .= implode(';',$headers);
//            $this->report_complete .= "\n";
//
//            foreach ($this->formatted_data as $data)
//            {
//                    if(is_array($data))
//                $this->report_complete .= implode(';', $data);
//                $this->report_complete .= "\n";
//            }

            $this->_logger->log(__METHOD__ . print_r($this->formatted_data,1));
            $this->csv_exporter_object = new ECSVExporter($this->formatted_data);

            $this->_logger->log(__METHOD__. ' formatted_data '. print_r($this->csv_exporter_object,1));
        }




    }

    protected function createCSVHeaders()
    {
        $headerArray=array();
        foreach ($this->formatted_data as $item){
            if(is_array($item))
            $headerArray = array_merge($headerArray, $item);
        }
        return array_keys($headerArray);
    }
}

?>
