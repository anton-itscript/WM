<?php
class DefaultSensorsForm extends CFormModel {
    public $handlers;
    public $calculations;
    public $defListBox;
    public $test;

    public $arrh = array();

    protected $sensor_handlers = array();

    protected function setHandlers()
    {
        $criteria = new CDbCriteria();
        // 1 or 2: AWS or Rain station
        $criteria->addCondition('flags & 3 > 0');
        $criteria->order = "display_name";
        $criteria->index = 'handler_id';

        $this->handlers = SensorDBHandler::model()->with('features.metric')->findAll($criteria);

        foreach ($this->handlers as $handler) {
            $this->sensor_handlers[$handler->handler_id_code] = SensorHandler::create($handler->handler_id_code);
        }
//        echo "<pre>";
//        print_r($this->sensor_handlers);
//        print_r($this->handlers);
//        echo "</pre>";
//        exit;
    }

    protected function setDefListBox()
    {
        $var = array();
        $var[0] = 'Hide';
        for($i=1;$i<=9;$i++)
            $var[$i]=$i;

        $this->defListBox=$var;
    }

    protected function setCalculation()
    {
        $criteria = new CDbCriteria();
        $criteria->index = 'handler_id';

        $this->calculations = CalculationDBHandler::model()->with('metric')->findAll($criteria);
    }

    public function init()
    {
        $this->arrh[-1]='now';
        for($i=0;$i<24;$i++)
            $this->arrh[$i]=($i<10 ? '0'.$i : $i).':00';

        $this->setHandlers();
        $this->setDefListBox();
        $this->setCalculation();
    }

    public function updateData($data)
    {
        foreach($data['handlers'] as $handler_id => $handlerData){
            if($this->handlers[$handler_id]->aws_panel_show != $handlerData['aws_panel_show']){
                $this->handlers[$handler_id]->aws_panel_show = $handlerData['aws_panel_show'];
                $this->handlers[$handler_id]->save();
            }
        }

        foreach($data['calculations'] as $handler_id => $handlerData){
            if($this->calculations[$handler_id]->aws_panel_show != $handlerData['aws_panel_show']){
                $this->calculations[$handler_id]->aws_panel_show = $handlerData['aws_panel_show'];
                $this->calculations[$handler_id]->save();
            }
        }
    }


    public function getFeatureByFeatureCodeAndHandlerIdCode($handler_id_code, $feature_code)
    {
        foreach ($this->sensor_handlers[$handler_id_code]->features as $feature) {
            if ($feature['feature_code'] == $feature_code) {
                $feature['exstra_features'] = false;
                return $feature;
            }
        }
        foreach ($this->sensor_handlers[$handler_id_code]->extra_features as $feature) {
            if ($feature['feature_code'] == $feature_code) {
                $feature['exstra_features'] = true;
                return $feature;
            }
        }


        return $this->getFeature($handler_id_code, $feature_code);

    }


    public function getFeature($handler_id_code, $feature_code)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->handler_id_code == $handler_id_code) {
                if (is_object($handler['features'][$feature_code])) {
                    return $handler['features'][$feature_code]->getAttributes();
                }
            }
        }
        return array();
    }




}