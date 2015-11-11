<?php
class StationGroupsForm extends CFormModel {
    public $stations;
    public $groups;
    public $data;

    public function init(){
        $this->stations   = Station::getStationName();
        $this->groups     = StationGroup::getGroupName();
        $this->data       = StationGroupDestination::getStationGroupArray();
    }

}