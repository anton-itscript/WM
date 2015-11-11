<?php

/**
 * @author
 */


class SynchronizationSwitcher
{
    /**
     * @var Synchronization
     * */
    protected $_synchronization;
    /**
     * @var SynchronizationRSM
     * */
    protected $_synchronizationRSM;

    protected $_logger;
    protected $_this_server_switch_variant;

    public function __construct($synchronizationObject, $synchronizationRSMObject)
    {
        $this->_logger = LoggerFactory::getFileLogger('SynchronizationSwitcher');

        $this->_synchronization = $synchronizationObject;
        $this->_synchronizationRSM = $synchronizationRSMObject;



        if($this->_synchronizationRSM->hasServerAnswer()) {

            if ($this->_synchronization->isFixedSwitchVariant()) {
                $this->_fixedSwitchLogic();
            }

            if ($this->_synchronization->isFlexibilitySwitchVariant()) {
                $this->_flexibilitySwitchLogic();
            }

        } else {
            $this->_synchronization->setInMaster();
        }

    }


    protected function _fixedSwitchLogic()
    {

        if ($this->_synchronization->isMaster() && $this->_synchronization->isMasterMainRole()
            && ($this->_synchronizationRSM->isSlave())) {

                //and that's ok
        }

        if ($this->_synchronization->isMaster() && $this->_synchronization->isSlaveMainRole()
            && ($this->_synchronizationRSM->isMaster())) {

            $this->_synchronization->setInSlave();
        }

        if ($this->_synchronization->isSlave() && $this->_synchronization->isSlaveMainRole()
            && ($this->_synchronizationRSM->isMaster())) {

                //and that's ok
        }

        if (
            $this->_synchronization->isSlave() && $this->_synchronization->isMasterMainRole()
            && $this->_synchronizationRSM->isSlave()
        ) {

            $this->_synchronization->setInMaster();
        }
    }

    protected function _flexibilitySwitchLogic()
    {


        //master vs slave slaveRole fixed
        if ($this->_synchronization->isMaster()
            && ( $this->_synchronizationRSM->isSlave()
                && ( $this->_synchronizationRSM->isSlaveMainRole() && $this->_synchronizationRSM->isFixedSwitchVariant() ))) {



        }

        // master vs master masterRole flexibility
        if ($this->_synchronization->isMaster()
            && ( $this->_synchronizationRSM->isMaster()
                && ( $this->_synchronizationRSM->isMasterMainRole() && $this->_synchronizationRSM->isFlexibilitySwitchVariant() ))) {

            $this->_synchronization->setInSlave();

        }

        // master vs master maserRole Fixed
        if ($this->_synchronization->isMaster()
            && ( $this->_synchronizationRSM->isMaster()
                && ( $this->_synchronizationRSM->isMasterMainRole() && $this->_synchronizationRSM->isFixedSwitchVariant() ))) {

            $this->_synchronization->setInSlave();

        }

        //master vs master SlaveRole Flexibility
        if ($this->_synchronization->isMaster()
            && ( $this->_synchronizationRSM->isMaster()
                && ( $this->_synchronizationRSM->isSlaveMainRole() && $this->_synchronizationRSM->isFlexibilitySwitchVariant() ))) {

            // do nothing

        }

        // master vs master SlaveRole Fixed
        if ($this->_synchronization->isMaster()
            && ( $this->_synchronizationRSM->isMaster()
                && ( $this->_synchronizationRSM->isSlaveMainRole() && $this->_synchronizationRSM->isFixedSwitchVariant() ))) {

            // do nothing

        }



        // master  vs slave masterRole fixed
        if ($this->_synchronization->isMaster()
            && ( $this->_synchronizationRSM->isSlave()
                && ( $this->_synchronizationRSM->isMasterMainRole() && $this->_synchronizationRSM->isFixedSwitchVariant() ))) {

            $this->_synchronization->setInSlave();

        }

        // master  vs slave masterRole Flexibility
        if ($this->_synchronization->isMaster()
            && ( $this->_synchronizationRSM->isSlave()
                && ( $this->_synchronizationRSM->isMasterMainRole() && $this->_synchronizationRSM->isFlexibilitySwitchVariant() ))) {

            // do nothing

        }

        // master  vs slave slaveRole fixed
        if ($this->_synchronization->isMaster()
            && ( $this->_synchronizationRSM->isSlave()
                && ( $this->_synchronizationRSM->isSlaveMainRole() && $this->_synchronizationRSM->isFixedSwitchVariant() ))) {

            // do nothing

        }

        // master  vs slave slaveRole Flexibility
        if ($this->_synchronization->isMaster()
            && ( $this->_synchronizationRSM->isSlave()
                && ( $this->_synchronizationRSM->isSlaveMainRole() && $this->_synchronizationRSM->isFixedSwitchVariant() ))) {

            // do nothing

        }

    }
}
?>