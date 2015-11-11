<?php


class StationGroupDestination extends CStubActiveRecord{

	public static function model($className=__CLASS__){
		return parent::model($className);
	}

	public function tableName(){
		return 'station_group_destination';
	}
    public function relations(){
        return array(
            'group' => array(self::HAS_ONE, 'StationGroup', 'group_id'),
            'station' => array(self::HAS_ONE, 'Station', 'station_id')
        );
    }
    public static function getStationGroupArray(){
        /*
         * [station_id]
         *      [group_id]
         */
        $data = self::model()->findAll();
        $return = array();
        foreach($data as $val){
            $return[$val->station_id][$val->group_id] = 1;
        }
        return $return;
    }
    public static function setStationGroupArray($data){
        self::model()->deleteAll();
        foreach($data as $station_id => $groups){
            foreach($groups as $group_id => $check){
                if($check==1){
                    $item = new StationGroupDestination;
                    $item->station_id = $station_id;
                    $item->group_id = $group_id;
                    $item->save(false);
                }
            }
        }

    }

}