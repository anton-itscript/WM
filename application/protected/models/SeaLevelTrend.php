<?php


class SeaLevelTrend extends CStubActiveRecord
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
		return 'sensor_sea_level_trend';
    }

    public function relations(){
        return array(
            'sensor' => array(self::BELONGS_TO, 'StationSensor', 'sensor_id'),
        );
    }

}