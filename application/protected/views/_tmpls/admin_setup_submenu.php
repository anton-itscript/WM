<?php 
        $cur_route = strtolower(Yii::app()->urlManager->parseUrl(Yii::app()->getRequest()));
        $cur_controller = strtolower($this->getId());
        $cur_controller_action = strtolower($this->getAction()->getId());

?>
<div class="middlewide third_menu">
    <div class="middlenarrow">
        <div id="third_menu">
            <ul>
                <li><a class="<?php echo ($cur_controller_action=='setupother')?'active':''?>" href="<?php echo $this->createUrl('admin/setupother')?>"><?php echo It::t('menu_label', 'admin_setup_other_settings')?></a></li>
                <li class="delimiter">&nbsp;</li>
                <li><a class="<?php echo (in_array($cur_controller_action, array('setupsensor','setupsensors'))?'active':'')?>" href="<?php echo $this->createUrl('admin/setupsensors')?>"><?php echo It::t('menu_label', 'admin_setup_setup_sensors')?></a></li>
                <li class="delimiter">&nbsp;</li>
                <li><a class="<?php echo ($cur_controller_action=='dbsetup')?'active':''?>" href="<?php echo $this->createUrl('admin/dbsetup')?>"><?php echo It::t('menu_label', 'admin_setup_db_setup')?></a></li>
                <li class="delimiter">&nbsp;</li>
                <li><a class="<?php echo ($cur_controller_action=='mailsetup')?'active':''?>" href="<?php echo $this->createUrl('admin/mailsetup')?>"><?php echo It::t('menu_label', 'admin_setup_mail_setup')?></a></li>
            </ul>
            <div class="clear"></div>
        </div>
    </div>
</div>