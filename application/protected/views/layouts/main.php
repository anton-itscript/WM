<?php
    $cur_route = strtolower(Yii::app()->urlManager->parseUrl(Yii::app()->getRequest()));

	$cur_controller = strtolower($this->getId());
    $cur_controller_action = strtolower($this->getAction()->getId());

    Yii::app()->clientScript->registerPackage('js');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" >

	<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="<?php echo Yii::app()->getBaseUrl(true); ?>/js/jqplot/excanvas.js"></script><![endif]-->


    <script type="text/javascript">
        var BaseUrl = "<?php echo Yii::app()->getBaseUrl(true); ?>";
        var CurrentTZOffset = <?php echo Yii::app()->user->getTZOffset('h'); ?>;
        window['Context'] = {domain:'<?php echo Yii::app()->getBaseUrl(true); ?>', controller:'<?php echo $cur_controller ?>', action:'<?php echo $cur_controller_action ?>'};
    </script>

    <title><?php echo $this->getPageTitle(); ?></title>
</head>

<body>
<div id="headerwrap">
    <div id="header">
        <div id="header_top">
            <div id="first_menu">
                <ul>
                    <?php if (!Yii::app()->user->isGuest) {?>
                        <li><a href="<?php echo $this->createUrl('site/logout'); ?>"><?php echo It::t('menu_label', 'top_logout'); ?></a></li>
                        <?php if (Yii::app()->user->role == 'superadmin') {?>
                            <li><a href="<?php echo $this->createUrl('superadmin/'); ?>" class="<?php echo (($cur_controller == 'superadmin') ? 'active' : ''); ?>"><?php echo It::t('menu_label', 'top_super_admin'); ?></a></li>
                        <?php }?>
                        <?php if (Yii::app()->user->role == 'admin' or Yii::app()->user->role == 'superadmin') {?>
                            <li><a href="<?php echo $this->createUrl('admin/'); ?>" class="<?php echo (($cur_controller == 'admin' || $cur_route == 'site/login') ? 'active' : ''); ?>"><?php echo It::t('menu_label', 'top_admin'); ?></a></li>
                        <?php }?>
                        <li><a href="<?php echo $this->createUrl('site/'); ?>" class="<?php echo (($cur_controller == 'site' && $cur_controller_action != 'login') ? 'active' : ''); ?>"><?php echo It::t('menu_label', 'top_home'); ?></a></li>
                    <?php }?>
                </ul>
                <br/><br/>
                <div id="header_time">
                    <form action="<?php echo $this->createUrl($cur_route); ?>" method="post" id="TZChangeForm">
                        <div class="time text"><?php echo Yii::app()->user->getTZ(); ?> (<?php echo TimezoneWork::getOffsetFromUTC(Yii::app()->user->getTZ(),1); ?>)</div>
                        <div class="text">
                            <?php echo date('M d'); ?>,
                            <span id="jclock2"></span>
                        </div>
                        <div class="delimiter time text">UTC (GMT +00:00)</div> 
                        <div class="text">
                            <?php echo gmdate('M d'); ?>,
                            <span id="jclock1"></span>
                        </div> 
                        <div class="clear"></div>
                    </form>
                </div>                 
            </div>

            <div id="header_company">
                <div id="header_soft_name"><a href="<?php echo $this->createUrl('site/index'); ?>"><b>Weather</b> Monitor</a></div>
                <div id="header_company_name"><?php echo Yii::app()->user->getSetting('current_company_name');?></div>
            </div>
            

            <div class="clear"></div>
        </div>
<?php /*
        <div id="second_menu" >
             <?php if ($cur_controller == 'admin') {?>
             <ul>
                 <?php if(Yii::app()->user->ckAct('Admin','Stations')): ?>
                    <li><a class="<?php echo (in_array($cur_controller_action,array('sensor','sensors','stations','stationgroups')) || ($cur_controller_action == 'stationsave' && isset($_REQUEST['station_id'])) )?'active':''?>" href="<?php echo $this->createUrl('admin/stations'); ?>"><?php echo It::t('menu_label', 'admin_stations'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                 <?php endif; if(Yii::app()->user->ckAct('Admin','StationSave')): ?>
                    <li><a class="<?php echo ($cur_controller_action =='stationsave' && !isset($_REQUEST['station_id'])) ? 'active' : ''?>" href="<?php echo $this->createUrl('admin/stationsave'); ?>"><?php echo It::t('menu_label', 'admin_create_station'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                 <?php endif; if(Yii::app()->user->ckAct('Admin','Connections')): ?>
                    <li><a class="<?php echo (in_array($cur_controller_action, array('connections', 'connectionslog', 'xmllog'))?'active':''); ?>" href="<?php echo $this->createUrl('admin/connections'); ?>"><?php echo It::t('menu_label', 'admin_connections'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                 <?php endif; if(Yii::app()->user->ckAct('Admin','Importmsg')): ?>
                    <li><a class="<?php echo (in_array($cur_controller_action, array('importmsg', 'importxml'))) ? 'active' : ''?>" href="<?php echo $this->createUrl('admin/importmsg'); ?>"><?php echo It::t('menu_label', 'admin_import_data'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                 <?php endif; if(Yii::app()->user->ckAct('Admin','MsgGeneration')): ?>
                    <?php if (Yii::app()->params['show_msg_generation']) {?>
                        <li><a class="<?php echo ($cur_controller_action == 'msggeneration') ? 'active' : ''?>" href="<?php echo $this->createUrl('admin/msggeneration'); ?>"><?php echo It::t('menu_label', 'admin_msg_generation'); ?></a></li>
                        <li class="delimiter">&nbsp;</li>
                    <?php }?>
                 <?php endif; if(Yii::app()->user->ckAct('Admin','AwsFiltered')): ?>
                    <li><a class="<?php echo ($cur_controller_action == 'awsfiltered') ? 'active' : ''?>" href="<?php echo $this->createUrl('admin/awsfiltered'); ?>"><?php echo It::t('menu_label', 'admin_aws_filtered_data'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                 <?php endif; if(Yii::app()->user->ckAct('Admin','ForwardList')): ?>
                    <li><a class="<?php echo (in_array($cur_controller_action,array('forwardlist'))?'active':''); ?>" href="<?php echo $this->createUrl('admin/ForwardList'); ?>"><?php echo It::t('menu_label', 'message_forwarding_list'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                 <?php endif; if(Yii::app()->user->ckAct('Admin','Users')): ?>
                     <li><a class="<?php echo (in_array($cur_controller_action,array('users'))?'active':''); ?>" href="<?php echo $this->createUrl('admin/users'); ?>"><?php echo It::t('menu_label', 'superadmin_users'); ?></a></li>
                     <li class="delimiter">&nbsp;</li>
                 <?php endif; if(Yii::app()->user->ckAct('Admin','HeartbeatReports')): ?>
                     <li><a class="<?php echo (in_array($cur_controller_action,array('heartbeatreports','heartbeatreport'))?'active':''); ?>" href="<?php echo $this->createUrl('admin/HeartbeatReports'); ?>"><?php echo It::t('menu_label', 'superadmin_HeartbeatReport'); ?></a></li>
                     <li class="delimiter">&nbsp;</li>
                 <?php endif; if(Yii::app()->user->ckAct('Admin','Setup')): ?>
				    <li><a class="<?php echo (in_array($cur_controller_action, array('setupother','setupsensor','setupsensors','dbbackup','checkmailing', 'checkcomstatus', 'dbexport', 'dbexporthistory','dbsetup','mailsetup'))?'active':''); ?>" href="<?php echo $this->createUrl('admin/setupother'); ?>"><?php echo It::t('menu_label', 'admin_setup'); ?></a></li>
                 <?php endif;?>
             </ul>
            <?php }?>

            <?php if ($cur_controller == 'superadmin') {?>
                <ul>
                    <li><a class="<?php echo (in_array($cur_controller_action,array('users','user')))?'active':''?>" href="<?php echo $this->createUrl('superadmin/users'); ?>"><?php echo It::t('menu_label', 'superadmin_users'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                    <li><a class="<?php echo (in_array($cur_controller_action,array('access','accessedit')))?'active':''?>" href="<?php echo $this->createUrl('superadmin/access'); ?>"><?php echo It::t('menu_label', 'superadmin_access'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                    <li><a class="<?php echo (in_array($cur_controller_action,array('syncsettings','syncsettings')))?'active':''?>" href="<?php echo $this->createUrl('superadmin/syncsettings'); ?>"><?php echo It::t('menu_label', 'superadmin_syncsettings'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                    <li><a class="<?php echo (in_array($cur_controller_action,array('longdbsetup','longdbtask')))?'active':''?>" href="<?php echo $this->createUrl('superadmin/LongDbSetup'); ?>"><?php echo It::t('menu_label', 'superadmin_LongDb'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                    <li><a class="<?php echo (in_array($cur_controller_action,array('heartbeatreport')))?'active':''?>" href="<?php echo $this->createUrl('superadmin/HeartbeatReport'); ?>"><?php echo It::t('menu_label', 'superadmin_HeartbeatReport'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                    <li><a class="<?php echo (in_array($cur_controller_action,array('sendsmscommand')))?'active':''?>" href="<?php echo $this->createUrl('superadmin/sendsmscommand'); ?>"><?php echo It::t('menu_label', 'superadmin_sendsmscommand'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                    <li><a class="<?php echo (in_array($cur_controller_action,array('config')))?'active':''?>" href="<?php echo $this->createUrl('superadmin/config'); ?>"><?php echo It::t('menu_label', 'superadmin_config'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                    <li><a class="<?php echo (in_array($cur_controller_action,array('metrics')))?'active':''?>" href="<?php echo $this->createUrl('superadmin/metrics'); ?>"><?php echo It::t('menu_label', 'superadmin_metrics'); ?></a></li>
                    <li class="delimiter">&nbsp;</li>
                    <li><a class="<?php echo (in_array($cur_controller_action,array('awsformat')))?'active':''?>" href="<?php echo $this->createUrl('superadmin/awsformat'); ?>">AWS Format</a></li>
                    <li class="delimiter">&nbsp;</li>
                    <li><a class="<?php echo (in_array($cur_controller_action,array('exportadminssettings')))?'active':''?>" href="<?php echo $this->createUrl('superadmin/exportadminssettings'); ?>"><?php echo It::t('menu_label', 'admin_import_export_admins_settings')?></a></li>
                    <li class="delimiter">&nbsp;</li>
                </ul>
            <?php }?>
            
            <?php 
				if ($cur_controller == 'site' && $cur_controller_action != 'login') 
				{
					$total_rain_stations = Station::getTotal('rain');
					$total_aws_stations  = Station::getTotal('aws');
			?>
            <ul>
                <?php if ($total_aws_stations) {?>
                    <?php if(Yii::app()->user->ckAct('Site','AwsPanel')): ?>
                        <li><a class="<?php echo ($cur_controller_action == 'awspanel')   ? 'active' : ''?>" href="<?php echo $this->createUrl('site/awspanel'); ?>"><?php echo It::t('menu_label', 'home_aws_panel'); ?></a></li>
                        <li class="delimiter">&nbsp;</li>
                    <?php endif; if(Yii::app()->user->ckAct('Site','AwsPanelOld')): ?>
                        <li><a class="<?php echo ($cur_controller_action == 'awspanelold')  ? 'active' : ''?>" href="<?php echo $this->createUrl('site/awspanelold'); ?>"><?php echo It::t('menu_label', 'home_aws_panel').' old'; ?></a></li>
                        <li class="delimiter">&nbsp;</li>
                    <?php endif; if(Yii::app()->user->ckAct('Site','AwsSingle')): ?>
                        <li><a class="<?php echo ($cur_controller_action == 'awssingle')  ? 'active' : ''?>" href="<?php echo $this->createUrl('site/awssingle'); ?>"><?php echo It::t('menu_label', 'home_aws_single'); ?></a></li>
                        <li class="delimiter">&nbsp;</li>
                    <?php endif; if(Yii::app()->user->ckAct('Site','AwsGraph')): ?>
                        <li><a class="<?php echo ($cur_controller_action == 'awsgraph')   ? 'active' : ''?>" href="<?php echo $this->createUrl('site/awsgraph'); ?>"><?php echo It::t('menu_label', 'home_aws_graph'); ?></a></li>
                        <li class="delimiter">&nbsp;</li>
                    <?php endif; if(Yii::app()->user->ckAct('Site','AwsTable')): ?>
                        <li><a class="<?php echo ($cur_controller_action == 'awstable')   ? 'active' : ''?>" href="<?php echo $this->createUrl('site/awstable'); ?>"><?php echo It::t('menu_label', 'home_aws_table'); ?></a></li>
                        <li class="delimiter">&nbsp;</li>
                    <?php endif; ?>
                <?php }?>
                
                <?php if ($total_rain_stations) {?>
                    <?php if(Yii::app()->user->ckAct('Site','RgPanel')): ?>
                        <li><a class="<?php echo ($cur_controller_action == 'rgpanel')      ? 'active' : ''?>" href="<?php echo $this->createUrl('site/rgpanel'); ?>"><?php echo It::t('menu_label', 'home_rg_panel'); ?></a></li>
                        <li class="delimiter">&nbsp;</li>
                    <?php endif; if(Yii::app()->user->ckAct('Site','RgTable')): ?>
                        <li><a class="<?php echo ($cur_controller_action == 'rgtable')    ? 'active' : ''?>" href="<?php echo $this->createUrl('site/rgtable').'?clear=1'; ?>"><?php echo It::t('menu_label', 'home_rg_table'); ?></a></li>
                        <li class="delimiter">&nbsp;</li>
                    <?php endif; if(Yii::app()->user->ckAct('Site','RgGraph')): ?>
                        <li><a class="<?php echo ($cur_controller_action == 'rggraph')    ? 'active' : ''?>" href="<?php echo $this->createUrl('site/rggraph').'?clear=1'; ?>"><?php echo It::t('menu_label', 'home_rg_graph'); ?></a></li>
                        <li class="delimiter">&nbsp;</li>
                    <?php endif;?>
                <?php } ?>
                
                <?php if ($total_aws_stations || $total_rain_stations) {?>
                    <?php if(Yii::app()->user->ckAct('Site','MsgHistory')): ?>
                        <li><a class="<?php echo ($cur_controller_action == 'msghistory') ? 'active' : ''?>" href="<?php echo $this->createUrl('site/msghistory').'?clear=1'; ?>"><?php echo It::t('menu_label', 'home_msg_history'); ?></a></li>
                        <li class="delimiter">&nbsp;</li>
                    <?php endif; if(Yii::app()->user->ckAct('Site','Export')): ?>
                        <li><a class="<?php echo ($cur_controller_action == 'export')     ? 'active' : ''?>" href="<?php echo $this->createUrl('site/export'); ?>"><?php echo It::t('menu_label', 'home_export'); ?></a></li>
                        <li class="delimiter">&nbsp;</li>
                    <?php endif;?>
                <?php } ?>
                
                <?php if ($total_aws_stations) {?>
                    <?php if(Yii::app()->user->ckAct('Site','Schedule')): ?>
                        <li><a class="<?php echo (in_array($cur_controller_action, array('schedule', 'schedulehistory')))?'active':''?>" href="<?php echo $this->createUrl('site/schedule'); ?>"><?php echo It::t('menu_label', 'home_schedule'); ?></a></li>
                    <?php endif;?>
                    <?php  if(Yii::app()->user->ckAct('Site','StationTypeDataExport')): ?>
                        <li><a class="<?php echo (in_array($cur_controller_action, array('stationtypedataexport', 'stationtypedatahistory')))?'active':''?>" href="<?php echo $this->createUrl('site/StationTypeDataExport'); ?>"> ODSS export</a></li>
                    <?php endif; ?>
                <?php } ?>
            </ul>
            <?php }?>                
            
            <div class="clear"></div>
        </div>
*/?>
        <?php $mainMenu =  MainMenu::getInstance($cur_controller,$cur_controller_action);
        echo $mainMenu->getFirstMenu();
        ?>
    </div>
</div><!-- div#headerwrap-->    



<div id="middlewrap">
    <?php echo $mainMenu->getSecondMenu()?>
    <?php $this->widget('ThrowStatus'); ?>
    <?php echo $content; ?>
</div>

<div id="footerwrap">
    <div id="footer">

            <div id="logo">
                <a href="<?php echo $this->createUrl('update/index') ?>">v <?php Yii::app()->params['version']['sprint']?></a>
            </div>
            <div id="copyright">
                <?php echo It::t('site_label', 'main_layout__footer_year'); ?>
                <?php echo It::t('site_label', 'main_layout__footer_copyright'); ?>
            </div>
            <div style="clear:both"></div>
        <?php     $synchronization = new Synchronization;       ?>
        <div class="status" >
            Main role: <?=$synchronization->getMainRole()?>.
            Current role: <?=$synchronization->getRole()?>.

            <?php if ($synchronization->isProcessed()){?>
                Synchronization is in process.
            <?php } else {?>
                Synchronization stopped.
            <?php }?>
        </div>
    </div>
</div><!-- div#footerwrap -->   

</body>
</html>
