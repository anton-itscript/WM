<?php 
        $cur_route = strtolower(Yii::app()->urlManager->parseUrl(Yii::app()->getRequest()));
        $cur_controller = strtolower($this->getId());
        $cur_controller_action = strtolower($this->getAction()->getId());

?>
<div class="middlewide third_menu">
    <div class="middlenarrow">
        <div id="third_menu">
            <ul>
                <li><a class="<?php echo ($cur_controller_action=='users')?'active':''?>" href="<?php echo $this->createUrl('superadmin/users')?>"><?php echo It::t('menu_label', 'superadmin_users')?></a></li>
                <li class="delimiter">&nbsp;</li>
                <li><a class="<?php echo ($cur_controller_action=='user')?'active':''?>" href="<?php echo $this->createUrl('superadmin/user')?>"><?php echo It::t('menu_label', 'superadmin_user')?></a></li>
            </ul>
            <div class="clear"></div>
        </div>
    </div>
</div>