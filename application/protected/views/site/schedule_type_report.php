<?php $dest_types = ScheduleTypeReportDestination::getTypes(); ?>


<div class="middlewide">
    <div class="middlenarrow">
        <h1><?php // echo It::t('home_schedule', 'title');  ?> Station Type Report Generation </h1>

        <?php echo CHtml::beginForm($this->createUrl('site/StationTypeDataExport'), 'post'); ?>
        <input type="hidden" name="ex_schedule_id" value="<?php echo $scheduleTypeReportForm->ex_schedule_id?>" />
        <?php echo CHtml::errorSummary($scheduleTypeReportForm); ?>

        <table class="formtable">
            <tr>
                <td>
                    <?php echo CHtml::activeDropDownList($scheduleTypeReportForm, 'report_type', ScheduleTypeReport::getReportType(), array('style' => 'width: 50px;')); ?>
                </td>
                <td>
                    <?php echo CHtml::activeDropDownList($scheduleTypeReportForm, 'period', ScheduleTypeReport::getPeriod(), array('style' => 'width: 50px;')); ?>
                </td>
                <td>
                    <?php echo CHtml::activeDropDownList($scheduleTypeReportForm, 'report_format', ScheduleTypeReport::getReportFormat(), array('style' => 'width: 50px;')); ?>
                </td>
                <td>
                    <?php echo CHtml::activeDropDownList($scheduleTypeReportForm, "station_type", ScheduleTypeReport::getStationTypes()); ?>
                </td>
                <td>
                    <div>
                        <div style="float:left"><b>Date of next run: &nbsp;&nbsp;</b></div>
                        <?php echo CHtml::activeTextField($scheduleTypeReportForm, 'start_date', array('class' => 'date-pick input-calendar', 'style' => 'width: 80px'))?>
                    </div>
                </td>
                <td>
                    <div>
                        <div style="float:left"><b>Time: &nbsp;&nbsp;</b></div>
                        <?php echo CHtml::activeTextField($scheduleTypeReportForm, 'start_time', array('style' => 'width: 50px;'))?>
                    </div>
                </td>
                <td>
                    <div>
                        <div style="float:left"><b>Identifier: &nbsp;&nbsp;</b></div>
                        <?php echo CHtml::activeTextField($scheduleTypeReportForm, 'ex_schedule_ident', array('style' => 'width: 60px;'))?>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <div>
                        <div style="float:left"><b>Generation Delay (min): &nbsp;&nbsp;</b></div>
                        <?php echo CHtml::activeTextField($scheduleTypeReportForm, 'generation_delay', array('style' => 'width: 60px;'))?>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <div>
                        <div style="float:left"><b>Aging time (min): &nbsp;&nbsp;</b></div>
                        <?php echo CHtml::activeTextField($scheduleTypeReportForm, 'aging_time_delay', array('style' => 'width: 60px;'))?>
                    </div>
                </td>
            </tr>
        </table>

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

                <?php echo CHtml::activeHiddenField($forms_d[$dest_key], '['.$dest_key.']ex_schedule_destination_id'); ?>
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
                <?php } /*else if ($forms_d[$dest_key]->method == 'local_folder') { ?>
                    <table class="formtable">
                        <tr>
                            <td><?php echo CHtml::activeLabel($forms_d[$dest_key], '['.$dest_key.']destination_local_folder'); ?>:</td>
                            <td>
                                <b><?php echo $scheduleTypeReportProcessed->getFileDir().DIRECTORY_SEPARATOR ?></b>
                                <?php echo CHtml::activeTextField($forms_d[$dest_key], '['.$dest_key.']destination_local_folder', array('style' => 'width: 300px;')); ?>
                                <?php echo It::t('home_schedule', 'local_folder_notice'); ?>
                            </td>
                            <td>
                            </td>
                        </tr>
                    </table>
                <?php }*/ ?>

            </div>
            <?php $i++; ?>
        <?php } ?>

    <?php }?>
</div>
<br/><br/>

        <table>
            <tr>
                <td>
                    <?php echo CHtml::submitButton($scheduleTypeReportForm->ex_schedule_id ? It::t('site_label', 'do_update') : It::t('site_label', 'do_add')); ?>
                    <?php echo CHtml::button(It::t('site_label', 'do_cancel'), array('onclick' => 'document.location.href="'.$this->createUrl('site/StationTypeDataExport').'"')); ?>
                </td>
            </tr>
        </table>

        <?php echo CHtml::endForm(); ?>
        <br/>
    </div>
</div>


<br/>

<?php echo CHtml::endForm(); ?>
<br/>

<div class="">
    <div class="middlenarrow schedule-type-report">
        <h1><?php //echo It::t('home_schedule', 'list_title'); ?>Station types reports</h1>
        <br/>
        <table class="tablelist schedulelist">
            <tbody>
            <tr >
                <th  style="vertical-align:middle" width="10%"> Id</th>
                <th  style="vertical-align:middle" width="70%"></th>
                <th  style="vertical-align:middle" >Station type</th>
                <th  style="vertical-align:middle" ><?php echo It::t('home_schedule', 'col_destinations'); ?></th>
                <th  style="vertical-align:middle" width="10%"><?php echo It::t('home_schedule', 'col_tools'); ?></th>
            </tr>
            <?php foreach ($scheduleTypesReports['result'] as $item) {?>
                <tr <?php echo  $item->active ? 'class="enabled"' : 'class="disabled"'?>>
                    <td  class="cell-vm-ac">
                        <a href="<?php echo $this->createUrl('site/StationTypeDataExport', array('ex_schedule_id' => $item->ex_schedule_id)); ?>"><?=$item->ex_schedule_id?></a>
                    </td>
                    <td>
                        <ul>
                            <li><b>Report type: </b><?=$item->report_type?></li>
                            <li><b>Format: </b><?=$item->report_format?></li>
                            <li><b>Period: </b><?php echo Yii::app()->params['schedule_generation_period'][$item->period]?></li>
                            <li><b>Run time last (UTC): </b><?php echo  $item->start_datetime_delayed?></li>
                            <li><b>Run time next (UTC): </b><?php echo  $item->next_run_planned_delayed?></li>
                            <li><b>Identifier: </b><?php echo  $item->ex_schedule_ident?></li>
                            <li><b>Generation delay: </b><?php echo  $item->generation_delay?> Min</li>
                        </ul>
                    </td>
                    <td class="cell-vm-ac">
                        <?=$item->station_type?>
                    </td>
                    <td>
                        <?php
                        if (count($item->destinations) === 0) {
                            echo 'Just in History';
                        } else {
                            foreach ($item->destinations as $key => $destination) {
                                echo ($key + 1) . '.';

                                if ($destination['method'] === 'mail') {
                                    echo $destination['destination_email'];
                                } else if ($destination['method'] === 'ftp') {
                                    echo $destination['destination_ip'] . ':' . $destination['destination_ip_port'];
                                } else {
                                    echo $scheduleTypeReportProcessed->getFileDir() . DIRECTORY_SEPARATOR . $destination['destination_local_folder'];
                                }

                                echo '<br />';
                            }
                        }
                        ?>
                    </td>
                    <td  class="cell-vm-al">
                        <div class="spacer"></div>
                        <a href="<?php echo $this->createUrl('site/StationTypeDataExport', array('ex_schedule_id' => $item->ex_schedule_id)); ?>">Edit</a>
                        <div class="spacer"></div>
                        <a href="<?php echo $this->createUrl('site/StationTypeDataExport', array('ex_schedule_id' => $item->ex_schedule_id, 'active'=> $item->active ? 0 : 1)); ?>"><?php echo  $item->active ? 'Enabled' : 'Disabled'?></a>
                        <div class="spacer"></div>
                        <a href="<?php echo $this->createUrl('site/StationTypeDataHistory', array('ex_schedule_id' => $item->ex_schedule_id)); ?>"><?php echo It::t('site_label', 'do_history'); ?></a>
                        <div class="spacer"></div>
                        <a href="<?php echo $this->createUrl('site/StationTypeDataExport', array('ex_delete_id' => $item->ex_schedule_id)); ?>" onclick="return confirm('Are you sure you want to delete schedule?')"><?php echo It::t('site_label', 'do_delete'); ?></a>
                        <div class="spacer"></div>

                    </td>
                </tr>
            <?php }?>
            </tbody>
        </table>
        <?php   if ($scheduleTypesReports['pages']->getPageCount() > 1){?>
            <div class="paginator" style="margin-top: 10px;">
                <?php $this->widget('CLinkPager',
                    array(
                        'pages' => $scheduleTypesReports['pages'],
                        'header' => '',
                        'firstPageLabel' => '&nbsp;',
                        'lastPageLabel' => '&nbsp;',
                        'nextPageLabel' => '&rarr;',
                        'prevPageLabel' => '&larr;'
                    ));
                ?>
                <div class="clear"></div>
            </div>
        <?php }   ?>
    </div>

</div>


<script>
    var dest_key = <?php echo $dest_key ? $dest_key : 0 ;?>,
        report_format = '<?php echo ($scheduleTypeReportForm->report_format); ?>',
        do_delete = '<?php echo It::t('site_label', 'do_delete'); ?>',
        ex_schedule_id = <?php echo $scheduleTypeReportForm->ex_schedule_id ? $scheduleTypeReportForm->ex_schedule_id : 0 ?>;

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
    var scheduled_reports_path = '<?php echo addslashes($scheduleTypeReportProcessed->getFileDir()).DIRECTORY_SEPARATOR ; ?>';


</script>
