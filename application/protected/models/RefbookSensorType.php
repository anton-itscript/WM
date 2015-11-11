<?php

class RefbookSensorType extends CStubActiveRecord {

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'refbook_sensor_type';
	}


	public function getMetricDisplayName($sensor_type)
	{
	    $sql = "SELECT `metric` FROM `".RefbookSensorType::model()->tableName()."` WHERE `code` = '".$sensor_type."'";
	    return Yii::app()->db->createCommand($sql)->queryScalar();
	}

}