<?php $dest_types = ScheduleReportDestination::getTypes(); ?>


<div class="middlewide">
    <div class="middlenarrow">
        <h1><?php echo It::t('home_schedule', 'title');  ?></h1>

        <?php echo CHtml::beginForm($this->createUrl('site/schedule'), 'post'); ?>
        <input type="hidden" name="schedule_id" value="<?php echo $form->schedule_id?>" />
        <?php echo CHtml::errorSummary($form); ?>

        
        <table class="formtable">
            <tr>
                <td>
                    <?php echo CHtml::activeDropDownList($form, 'report_type', Yii::app()->params['schedule_report_type'], array('style' => 'width: 50px;')); ?>
                </td>
                <td>
                    <?php echo CHtml::activeDropDownList($form, 'period', Yii::app()->params['schedule_generation_period'], array('style' => 'width: 50px;')); ?>
                </td>
                <td>
                    <?php echo CHtml::activeDropDownList($form, 'report_format', Yii::app()->params['schedule_report_format'], array('style' => 'width: 50px;')); ?>
                </td>
            </tr>
            <tr>
               <td colspan="2" >
                    <?php echo CHtml::activeRadioButtonList($form, 'send_like_attach',array('1'=>'attachments to the report', '0'=>'write to the message body'), array('style' => 'width: 50px;')); ?>
                </td>

                <td >
                    <?php echo CHtml::activeCheckbox($form, 'send_email_together', array('style' => 'width: 50px;')); ?>  &nbsp;&nbsp;<span>Send messages together</span>
                </td>

            </tr>
        </table>

        <table class="formtable station_table">
            <?php foreach ($forms_s as $key => $value):?>
                <tr>
                    <td>
                        <?php echo CHtml::activeHiddenField($forms_s[$key], "[$key]id"); ?>
                        <?php echo CHtml::activeDropDownList($forms_s[$key], "[$key]station_id", ScheduleReport::getStationsList()); ?>
                    </td>
                    <td>
                        &nbsp;&nbsp;<a class="remove_button">[remove]</a>
                    </td>
                </tr>
            <?php endforeach ?>
        </table>
        <br>
        <a href="javascript:void(0)" data-item-count="<?=count($forms_s)?>" class="add_button">[Add new]</a>
        </br>




        <br/>
        <b><?php echo It::t('home_schedule', 'form_destinations'); ?></b> 
        [Add new:
        <?php
            $total_dest = count($dest_types);
            $i = 1;
        
			foreach ($dest_types as $key => $value) 
			{
		?>
            <a href="#" onclick="addDestination('<?php echo $key?>');"><?php echo $value?></a> 
        <?php 
				if ($i < $total_dest) {?>&nbsp;|&nbsp;<?php }
				$i++; 
			} 
		?>
        ]
        <?php echo It::t('home_schedule', 'form_if_no_destination_selected'); ?>


        <div id="destinations_container">
        <?php if ($forms_d) {?>
        
            <?php $dest_key = 0;  $i = 1;?>
            <?php foreach ($forms_d as $dest_key => $value) { ?>

                
                <div class="destination_block" >
                        
                        <?php echo CHtml::activeHiddenField($forms_d[$dest_key], '['.$dest_key.']schedule_destination_id'); ?>
                        <?php echo CHtml::activeHiddenField($forms_d[$dest_key], '['.$dest_key.']method'); ?>
                        
                        <b><?php echo ($i).'. '.  $dest_types[$forms_d[$dest_key]->method] ?></b>
                        &nbsp;&nbsp;[ <a href="#" class="delete_destination"><?php echo It::t('site_label', 'do_delete'); ?></a> ]

                        <?php echo CHtml::errorSummary($forms_d[$dest_key]); ?>
                        <?php if ($forms_d[$dest_key]->method == 'mail') {?>
                            
                            <table class="formtable">
                                <tr>
                                    <td><?php echo CHtml::activeLabel($forms_d[$dest_key], '['.$dest_key.']destination_email'); ?>:</td>
                                    <td><?php echo CHtml::activeTextField($forms_d[$dest_key], '['.$dest_key.']destination_email', array('style' => 'width: 300px;')); ?></td>
                                </tr>    
                            </table>  
                        <?php } else if ($forms_d[$dest_key]->method == 'ftp') { ?>
                            <table class="formtable">
                            <tr>
                                <td><?php echo CHtml::activeLabel($forms_d[$dest_key], '['.$dest_key.']destination_ip'); ?>:</td>
                                <td><?php echo CHtml::activeTextField($forms_d[$dest_key], '['.$dest_key.']destination_ip', array('style' => 'width: 110px;')); ?></td>
                                <td><?php echo CHtml::activeLabel($forms_d[$dest_key], '['.$dest_key.']destination_ip_port'); ?>:</td>
                                <td><?php echo CHtml::activeTextField($forms_d[$dest_key], '['.$dest_key.']destination_ip_port', array('style' => 'width: 50px;')); ?></td>
                                <td><?php echo CHtml::activeLabel($forms_d[$dest_key], '['.$dest_key.']destination_ip_folder'); ?>:</td>
                                <td><?php echo CHtml::activeTextField($forms_d[$dest_key], '['.$dest_key.']destination_ip_folder'); ?></td>
                                <td><?php echo CHtml::activeLabel($forms_d[$dest_key], '['.$dest_key.']destination_ip_user'); ?>:</td>
                                <td><?php echo CHtml::activeTextField($forms_d[$dest_key], '['.$dest_key.']destination_ip_user', array('style' => 'width: 110px;')); ?></td>
                                <td><?php echo CHtml::activeLabel($forms_d[$dest_key], '['.$dest_key.']destination_ip_password'); ?>:</td>
                                <td><?php echo CHtml::activeTextField($forms_d[$dest_key], '['.$dest_key.']destination_ip_password', array('style' => 'width: 110px;')); ?></td>   
                                <td colspan="2"></td>          
                            </tr>
                            </table>
                        <?php } else if ($forms_d[$dest_key]->method == 'local_folder') { ?>
                            <table class="formtable">
                            <tr>
                                <td><?php echo CHtml::activeLabel($forms_d[$dest_key], '['.$dest_key.']destination_local_folder'); ?>:</td>
                                <td>
                                    <b><?php echo Yii::app()->user->getSetting('scheduled_reports_path').DIRECTORY_SEPARATOR ?></b>
                                    <?php echo CHtml::activeTextField($forms_d[$dest_key], '['.$dest_key.']destination_local_folder', array('style' => 'width: 300px;')); ?>
                                    <?php echo It::t('home_schedule', 'local_folder_notice'); ?>
                                </td>
                                <td>
                                </td>        
                            </tr>    
                            </table>                
                        <?php } ?>
                    
                </div>  
                <?php $i++; ?>
            <?php } ?>
           
        <?php }?>
        </div> 
        <br/><br/>


        
        <table>
        <tr>
            <td>
                <?php echo CHtml::submitButton($form->schedule_id ? It::t('site_label', 'do_update') : It::t('site_label', 'do_add')); ?>
                <?php echo CHtml::button(It::t('site_label', 'do_cancel'), array('onclick' => 'document.location.href="'.$this->createUrl('site/schedule').'"')); ?>                
            </td>
        </tr>    
        </table>
        <?php echo CHtml::endForm(); ?>    
        <br/>
    </div>
</div>


    <br/>
    <?php if ($reportsList) {?>
<div class="">
    <div class="middlenarrow">
        <h1><?php echo It::t('home_schedule', 'list_title'); ?></h1>
        <?php foreach($reportsList as $report ):?>

            <br/>
            <table class="tablelist schedulelist">
                <tbody>
                <tr >
                    <th rowspan="2" style="vertical-align:middle" width="10%"><?=It::t('home_schedule', 'col_report'); ?>  <?=$report['schedule_id']; ?></th>
                    <th  width="15%"><?php echo It::t('home_schedule', 'col_format'); ?></th>


                    <th colspan="2"></th>
                    <th  ><?php echo It::t('home_schedule', 'col_destinations'); ?></th>
                    <th  width="10%"><?php echo It::t('home_schedule', 'col_tools'); ?></th>
                </tr>
                <tr>

                    <td style="vertical-align: middle; text-align: center;">
                      <b><?php echo Yii::app()->params['schedule_report_type'][$report['report_type']]; ?></b> (*.<?php echo $report['report_format']; ?>)
                        <div style="padding-top: 10px;"><?php echo Yii::app()->params['schedule_generation_period'][$report['period']] ?></div>
                        <div class="small" style="padding-top: 10px;">
                            <?php echo ($report['last_scheduled_run_planned'] != '0000-00-00 00:00:00' ? gmdate('m/d/Y H:i', strtotime($report['last_scheduled_run_planned'])).' UTC' : '-' ); ?>
                        </div>
                    </td>
                    <td style="vertical-align: middle; text-align: center;">
                        <?php if ($report['send_email_together']) :?>
                            Send messages together


                                <?php echo CHtml::beginForm('#', 'post'); ?>
                                <?php foreach($report['station'] as $stantionReports ):?>
                                <input type="hidden" name="schedule_processed_id[]" value="<?php echo $stantionReports['processed'][0]['schedule_processed_id']; ?>" />
                                <?php endforeach;?>
                                <?php echo CHtml::button('Re-Send', array('onclick' => 'resendAllScheduledReport(this)')); ?>
                                <div class="action_msg"></div>
                                <?php echo CHtml::endForm(); ?>

                        <?php else:?>
                            Send messages separately
                        <?php endif;?>

                    </td>
                    <td style="vertical-align: middle; text-align: center;">

                        <?php if ($report['send_like_attach']) :?>
                            attachments to the report
                        <?php else:?>
                            write to the message body
                        <?php endif;?>
                    </td>
                    <td style="vertical-align: middle; font-size:11px; line-height: 13px;">
                        <?php  if (!$report['destinations']) {?>
                            Just History
                        <?php } else {?>
                            <?php foreach ($report['destinations'] as $k1 => $v1) {?>
                                <?php echo ($k1+1); ?>.
                                <?php if ($v1['method'] == 'mail' ) { ?>
                                   Email: <?php echo $v1['destination_email'] ?>
                                <?php } else if ($v1['method'] == 'ftp' ) { ?>
                                   FTP: <?php echo ($v1['destination_ip'].':'.$v1['destination_ip_port']); ?>
                                <?php } else if ($v1['method'] == 'local_folder') { ?>
                                   Folder:  <?php echo Yii::app()->user->getSetting('scheduled_reports_path').DIRECTORY_SEPARATOR.$v1['destination_local_folder'] ?>
                                <?php } ?>
                                <br/>
                            <?php }?>


                        <?php }?>
                    </td>

                    <td>
                        <a href="<?php echo $this->createUrl('site/schedule', array('schedule_id' => $report['schedule_id'])); ?>"><?php echo It::t('site_label', 'do_edit'); ?></a>
                        <div class="spacer"></div>
                        <a href="<?php echo $this->createUrl('site/schedule', array('delete_id' => $report['schedule_id'])); ?>" onclick="return confirm('Are you sure you want to delete schedule?')"><?php echo It::t('site_label', 'do_delete'); ?></a>
                        <div class="spacer"></div>
                        <a href="<?php echo $this->createUrl('site/schedulehistory', array('schedule_id' => $report['schedule_id'])); ?>"><?php echo It::t('site_label', 'do_history'); ?></a>
                    </td>
                </tr>
                </tbody>
            </table>
            <br/>
                <table class="tablelist schedulelist" width="100%">
                <tr>
                    <th class="f1"><?php echo It::t('home_schedule', 'col_station'); ?></th>
                    <th class="f2"><?php echo It::t('home_schedule', 'col_time'); ?></th>

                    <th class="f4"><?php echo It::t('home_schedule', 'col_last'); ?></th>
                </tr>

                <?php foreach ($report['station'] as $key => $station) :?>

                <tr class="<?php echo fmod($key,2) == 0 ? 'c' : ''?>">
                    <td style="vertical-align: middle;">

                        <div class="small">

                                <a href="<?php echo $this->createUrl('site/awssingle', array('station_id' => $station['realStation']['station_id'])); ?>" title="Go to AWS Single View"><?php echo $station['realStation']['station_id_code']; ?></a> <br>
                                (GMT <?php echo $station['realStation']['timezone_offset']; ?>)

                        </div>
                    </td>


                    <td class="small"  style="vertical-align: middle; ">
                        <?php if ($report['report_type'] == 'data_export') {?>

                            <?php if ($station['processed'][0]['schedule_processed_id']) {?>
                                <?php echo count($station['processed'][0]); ?> messages <br/>in period <br/><?php echo gmdate('m/d/Y H:i', strtotime($station['processed'][0]['check_period_start'])); ?> -  <?php echo gmdate('m/d/Y H:i', strtotime($station['processed'][0]['check_period_end'])); ?>
                            <?php } else {?>
                                -
                            <?php } ?>
                        <?php } else {?>
                            <?php if ($station['processed'][0]['schedule_processed_id']) { ?>
                                <?php echo gmdate('m/d/Y H:i', strtotime($station['processed'][0]['listenerLog']['measuring_timestamp'])); ?> UTC
                            <?php } else {?>
                                  -
                            <?php } ?>
                        <?php }  ?>
                    </td>

                    <td style="text-align:right" >

                        <?php if ( $station['processed'][0]['schedule_processed_id']) { ?>
                        <?php echo CHtml::beginForm('#', 'post'); ?>
                            <input type="hidden" name="schedule_processed_id" value="<?php echo $station['processed'][0]['schedule_processed_id']; ?>" />
                            <?php  if ($report['report_type'] == 'bufr' || $report['report_type'] == 'data_export') { ?>
                                <br/>
                                <a href="#" onclick="downloadScheduledReport(this); return false;"><?php echo $station['realStation']['file_name']; ?></a>
                                <br/><br/>
                            <?php } else { ?>
                                <textarea style="width: 300px; height: 60px; font-size: 11px;" name="ScheduleReportProcessed[report_string_initial]"  COLS="50" ROWS="50" ><?php echo $station['realStation']['report_string_initial'] ?></textarea>
                            <?php } ?>

                            <div class="action_msg"></div>
                            <?php echo CHtml::button(It::t('site_label', 'do_regenerate'), array('onclick' => 'regenerateScheduledReport(this)')); ?>
                            &nbsp;
                            <?php if ($report['report_type'] != 'bufr' && $report['report_type'] != 'data_export') { ?>
                                <?php echo CHtml::button(It::t('site_label', 'do_save'), array('onclick' => 'resaveScheduledReport(this)')); ?>
                                &nbsp;
                            <?php } ?>
                            <?php if ( $station['processed'][0]['schedule_processed_id'] and $report['send_email_together']==0) { ?>



                                <input type="hidden" name="schedule_processed_id" value="<?php echo $station['processed'][0]['schedule_processed_id']; ?>" />
                                <?php echo CHtml::button('Re-Send', array('onclick' => 'resendScheduledReport(this)')); ?>

                            <?php }?>
                            <?php echo CHtml::button(It::t('site_label', 'do_download'), array('onclick' => 'downloadScheduledReport(this)')); ?>
                            <?php echo CHtml::endForm(); ?>
                        <?php } else {?>
                            &nbsp;
                        <?php }?>
                    </td>
                </tr>
                    <?php endforeach?>
                </table>
        <?php endforeach?>
    </div>
</div>
    <?php }?>
</div>
<script>
    var dest_key = <?php echo $dest_key ? $dest_key : 0?>,
        report_format = '<?php echo ($form->report_format); ?>',
        do_delete = '<?php echo It::t('site_label', 'do_delete'); ?>',
        schedule_id = <?php echo $form->schedule_id ? $form->schedule_id : 0 ?>;

    var dest_name_mail = '<?php echo $dest_types['mail'] ?>';
    var dest_email_mail = '<?php echo It::t('home_schedule', 'dest_param_email'); ?>';

    var dest_name_ftp   = '<?php echo $dest_types['ftp'] ?>';
    var dest_ip_ftp     = '<?php echo It::t('home_schedule', 'dest_param_ftp_ip'); ?>';
    var dest_port_ftp   = '<?php echo It::t('home_schedule', 'dest_param_ftp_port'); ?>';
    var dest_folder_ftp = '<?php echo It::t('home_schedule', 'dest_param_ftp_folder'); ?>';
    var dest_user_ftp   = '<?php echo It::t('home_schedule', 'dest_param_ftp_user'); ?>';
    var dest_pwd_ftp    = '<?php echo It::t('home_schedule', 'dest_param_ftp_password'); ?>';

    var dest_name_local = '<?php echo $dest_types['local_folder'] ?>';
    var dest_fld_local  = '<?php echo It::t('home_schedule', 'dest_param_local_folder'); ?>';
    var dest_note_local = '<?php echo It::t('home_schedule', 'local_folder_notice'); ?>';
    var scheduled_reports_path = '<?php echo addslashes(Yii::app()->user->getSetting('scheduled_reports_path').DIRECTORY_SEPARATOR); ?>';


</script>

