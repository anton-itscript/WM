<?php 
        $cur_route = strtolower(Yii::app()->urlManager->parseUrl(Yii::app()->getRequest()));
        $cur_controller = strtolower($this->getId());
        $cur_controller_action = strtolower($this->getAction()->getId());

?>
<div class="middlewide third_menu">
    <div class="middlenarrow">
        <div id="third_menu">
            <ul>
                <li><a class="<?php echo ($cur_controller_action=='Stations')?'active':''?>" href="<?php echo $this->createUrl('admin/Stations')?>"><?php echo It::t('menu_label', 'admin_stations')?></a></li>
                <li class="delimiter">&nbsp;</li>
                <li><a class="<?php echo ($cur_controller_action=='StationGroups')?'active':''?>" href="<?php echo $this->createUrl('admin/StationGroups')?>"><?php echo It::t('menu_label', 'admin_station_groups')?></a></li>
            </ul>
            <div class="clear"></div>
        </div>
    </div>
</div>