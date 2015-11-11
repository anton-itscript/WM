<?php

class StationGroup extends CStubActiveRecord
{
    /** @var string */
    public $name;

    public static function model($className=__CLASS__){
		return parent::model($className);
	}

	public function tableName(){
		return 'station_group';
	}
    public function relations(){
        return array(
            'stations'=>array(self::HAS_MANY, 'StationGroupDestination','group_id'),
        );
    }
    public function rules() {
        return array(
            array('name', 'length', 'max' => 8,'min' => 3, 'allowEmpty' => false),
            array('name', 'unique'),
        );
    }
    public function attributeLabels() {
        return array(
            'group_name' => Yii::t('project', 'Group Name: '),
        );
    }
    public static function getGroupName(){
        $criteria = new CDbCriteria();
            $criteria->index = "group_id";
        return self::model()->findAll($criteria);
    }
    public static function deleteGroupId($group_id){
        self::model()->deleteByPk($group_id);
    }
    public static function getStationArrFromGroup($group_id){
        if(isset($group_id)){
            $criteria = new CDbCriteria();
            $criteria->with = array(
                'stations' => array(
                    'index'=>'station_id'
                )
            );
            $res = self::model()->findByPk($group_id,$criteria);
            return $res?array_keys($res->stations):array();
        }else{
            return array();
        }
    }
}