<?php
class DefaultSensorsForm extends CFormModel {
    public $handlers;
    public $calculations;
    public $defListBox;
    public $test;

    protected function setHandlers()
    {
        $criteria = new CDbCriteria();
        // 1 or 2: AWS or Rain station
        $criteria->addCondition('flags & 3 > 0');
        $criteria->order = "display_name";
        $criteria->index = 'handler_id';

        $this->handlers = SensorDBHandler::model()->findAll($criteria);
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

        $this->calculations = CalculationDBHandler::model()->findAll($criteria);
    }

    public function init()
    {
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
}