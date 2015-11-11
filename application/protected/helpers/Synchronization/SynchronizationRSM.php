<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 08.05.2015
 * Time: 15:00
 */

class SynchronizationRSM
{
    protected $switch_variant;
    protected $main_role;
    protected $flexibility_role;

    protected $has_answer = true;

    public function __construct($remoteServerMessage)
    {
       if(is_array($remoteServerMessage)
           && isset($remoteServerMessage['switch_variant'])
        //   && isset($remoteServerMessage['main_role'])
           && isset($remoteServerMessage['flexibility_role'])
       ){
           $this->switch_variant        = $remoteServerMessage['switch_variant'];
         //  $this->main_role             = $remoteServerMessage['main_role'];
           $this->flexibility_role      = $remoteServerMessage['flexibility_role'];
       } else {
           $this->has_answer = false;
       }

    }

    public function hasServerAnswer(){
        return $this->has_answer;
    }



    public function isFixedSwitchVariant()
    {

        return $this->switch_variant == 1 ? true: false;
    }

    public function isFlexibilitySwitchVariant()
    {

        return $this->switch_variant == 2 ? true: false;
    }

    public function isMasterMainRole()
    {

        return $this->main_role == 1 ? true: false;
    }

    public function isSlaveMainRole()
    {

        return $this->main_role == 2 ? true: false;
    }

    public function isMaster()
    {

        return $this->flexibility_role == 1 ? true: false;
    }

    public function isSlave()
    {

        return $this->flexibility_role == 2 ? true: false;
    }



}