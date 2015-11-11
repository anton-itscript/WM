<?php

class StationCalculationVariable extends CStubActiveRecord
{

    public static function model($className=__CLASS__){
        return parent::model($className);
    }
    public function beforeSave(){
        if(!$this->getUseLong()){
            if ($this->isNewRecord){
                $this->created = new CDbExpression('NOW()');
            }
            $this->updated = new CDbExpression('NOW()');
        }
        return parent::beforeSave();
    }

	public function tableName()
	{
		return 'station_calculation_variable';
	}

	public function rules()
	{
		return array(
			array('calculation_id,variable_name', 'required'),
			array('sensor_feature_id', 'numerical', 'allowEmpty' => true)
		);
	}    
	
	public function relations()
    {
        return array(
            'calculation' => array(self::BELONGS_TO, 'StationCalculation', 'calculation_id'),
        );
    }


}

?>