<?php 
        $cur_route = strtolower(Yii::app()->urlManager->parseUrl(Yii::app()->getRequest()));
        $cur_controller = strtolower($this->getId());
        $cur_controller_action = strtolower($this->getAction()->getId());

?>
<div class="middlewide third_menu">
    <div class="middlenarrow">
        <div id="third_menu">
            <ul>
                <li><a class="<?php echo ($cur_controller_action=='access')?'active':''?>" href="<?php echo $this->createUrl('superadmin/access')?>"><?php echo It::t('menu_label', 'superadmin_access')?></a></li>
                <li class="delimiter">&nbsp;</li>
                <li><a class="<?php echo ($cur_controller_action=='accessedit')?'active':''?>" href="<?php echo $this->createUrl('superadmin/accessedit')?>"><?php echo It::t('menu_label', 'superadmin_accessedit')?></a></li>
            </ul>
            <div class="clear"></div>
        </div>
    </div>
</div>