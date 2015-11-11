<?php

class StationCalculation extends CStubActiveRecord
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
        return 'station_calculation';
    }

    public function rules()
	{
        return array(
            array('station_id,formula,handler_id', 'required'),
        );
    }    
	
	public function relations()
    {
        return array(
            'handler' => array(self::BELONGS_TO, 'CalculationDBHandler', 'handler_id'),        
            'Station' => array(self::BELONGS_TO, 'Station', 'station_id'),
        );
    }

    public static function getStationCalculationHandlers($station_ids)
	{
		$criteria = new CDbCriteria();
		$criteria->with = array('handler');
		
		$criteria->compare('station_id', $station_ids);
		
		$records = StationCalculation::model()->findAll($criteria);
		
		$result = array();
		
		foreach ($records as $record){
			$result[$record->station_id][] = $record;
		}
		
        return $result;
    }
}

?>