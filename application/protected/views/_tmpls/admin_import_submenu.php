<?php 
        $cur_route = strtolower(Yii::app()->urlManager->parseUrl(Yii::app()->getRequest()));
        $cur_controller = strtolower($this->getId());
        $cur_controller_action = strtolower($this->getAction()->getId());

?>
<div class="middlewide third_menu">
    <div class="middlenarrow">
        <div id="third_menu">
            <ul>
                <li><a class="<?php echo ($cur_controller_action=='importmsg')?'active':''?>" href="<?php echo $this->createUrl('admin/importmsg')?>"><?php echo It::t('menu_label', 'admin_import_message')?></a></li>
                <li class="delimiter">&nbsp;</li>
                <li><a class="<?php echo ($cur_controller_action=='importxml')?'active':''?>" href="<?php echo $this->createUrl('admin/importxml')?>"><?php echo It::t('menu_label', 'admin_import_xml')?></a></li>
            </ul>
            <div class="clear"></div>
        </div>
    </div>
</div>