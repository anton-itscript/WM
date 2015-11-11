<?php 
        $cur_route = strtolower(Yii::app()->urlManager->parseUrl(Yii::app()->getRequest()));
        $cur_controller = strtolower($this->getId());
        $cur_controller_action = strtolower($this->getAction()->getId());

?>
<div class="middlewide third_menu">
    <div class="middlenarrow">
        <div id="third_menu">
            <ul>
                <li><a class="<?php echo ($cur_controller_action=='connections')?'active':''?>" href="<?php echo $this->createUrl('admin/connections')?>"><?php echo It::t('menu_label', 'admin_connections_connections')?></a></li>
                <li class="delimiter">&nbsp;</li>
                <li><a class="<?php echo ($cur_controller_action=='connectionslog')?'active':''?>" href="<?php echo $this->createUrl('admin/connectionslog')?>"><?php echo It::t('menu_label', 'admin_connections_log')?></a></li>
                <li class="delimiter">&nbsp;</li>
                <li><a class="<?php echo ($cur_controller_action=='xmllog')?'active':''?>" href="<?php echo $this->createUrl('admin/xmllog')?>"><?php echo It::t('menu_label', 'admin_connections_xmllog')?></a></li>
                
            </ul>
            <div class="clear"></div>
        </div>
    </div>
</div>