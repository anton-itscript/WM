<?php Yii::app()->clientScript->registerScriptFile(It::baseUrl() . '/js/schedule_work.js'); ?>
<div class="middlewide">
    <div class="middlenarrow">
    <h1>
        <a href="<?php echo $this->createUrl('site/StationTypeDataExport') ?>"> Station Type Report Generation </a> /
        <a href="<?php echo $this->createUrl('site/StationTypeDataHistory',array('ex_schedule_id'=>$report->ex_schedule_id)) ?>">Report <?=$report->ex_schedule_id?>  </a> /
        <?=$stationInfo['station_id_code']?>
    </h1>

 <table class="formtable">
        <tr>
            <th style="text-align: right;">Station type:</th>
            <td><?php echo $report->station_type?></td>
            <td>&nbsp;&nbsp;</td>

            <th style="text-align: right;">Report type:</th>
            <td><?php echo $report->report_type?></td>
            <td>&nbsp;&nbsp;</td>

            <th style="text-align: right;">Report format:</th>
            <td><?php echo $report->report_format?></td>
            <td>&nbsp;&nbsp;</td>

            <th style="text-align: right;">Period:</th>
            <td><?php echo Yii::app()->params['schedule_generation_period'][$report->period]?></td>

            <th style="text-align: right;">Next run in:</th>
            <td><?php echo $report->next_run_planned_delayed?> (UTC)</td>
            <td>&nbsp;&nbsp;</td>
        </tr>
        </table>
    </div>
</div>


<div class="middlenarrow">
    <br/>
    <?php if (!count($history['result'])) {?>
        No reports were generated.
    
    <?php } else {?>

        <?php foreach ($history['result'] as $key => $item) {?>
            <table class="tablelist " style="width:100%;">
                <tr>
                    <td style="width: 5px; vertical-align:middle" rowspan="5"><?=$item->ex_schedule_processed_id?></td>
                </tr>
                <tr>
                    <th style="width: 100px;">Messages Start Time (UTC): <?=$item->check_period_start?></th>
                    <th style="width: 100px;">Messages End Time (UTC): <?=$item->check_period_end?></th>
                </tr>
                <tr>
                    <td style="width: 100px;">
                        <?php echo CHtml::beginForm('#', 'post'); ?>
                        <input type="hidden" name="ex_schedule_processed_id" value="<?php echo $item->ex_schedule_processed_id?>" />
                        <?php echo CHtml::button('Download', array('onclick' => 'downloadTypeScheduledReport(this)'))?>
                        <?php echo CHtml::endForm(); ?>
                    </td>
                    <td style="width: 100px;"></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <textarea  disabled="disabled" rows="10" style="width:960px"><?=$item->file_content?></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <ul>
                            <?php if ($item->is_synchronized == 0) { ?>
                                <li>Waiting for synchronization.</li>
                            <?php } ?>

                            <?php if ($item->is_synchronized == 1) { ?>
                                <li>Synchronization was successful.</li>
                            <?php } ?>

                            <?php if ($item->is_synchronized == 2) { ?>
                                <li><b>Synchronization is not performed.</b> The waiting time for synchronization has ended. </li>
                            <?php } ?>
                        </ul>
                        <?php if ($item->is_synchronized != 0) { ?>
                            <ul>
                                <?php foreach ($item->send_log as $send_log) {?>
                                    <li>
                                        <?php if ($send_log->sent) {?>
                                            Report was sent <?=$send_log->destination->address_name;?>
                                        <?php } else { ?>
                                            <b>Report does not sent</b> <?=$send_log->destination->address_name;?> <br>
                                            <?php if (count($send_log->send_logs_array)) {?>
                                                <b>Errors:</b><br>
                                                <ul>
                                                    <?php foreach ($send_log->send_logs_array as $logItem) {?>
                                                        <li> <?=$logItem;?></li>
                                                    <?php }?>
                                                </ul>
                                            <?php } ?>
                                        <?php } ?>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    </td>
                </tr>

            </table>
        <?php }?>

    <div class="spacer"></div>
        <?php if ($history['pages']->getPageCount() > 1){?>
            <div class="paginator" style="margin-top: 10px;">
                <?php $this->widget('CLinkPager',
                    array(
                        'pages' => $history['pages'],
                        'header' => '',
                        'firstPageLabel' => '&nbsp;',
                        'lastPageLabel' => '&nbsp;',
                        'nextPageLabel' => '&rarr;',
                        'prevPageLabel' => '&larr;'
                    ));
                ?>
                <div class="clear"></div>
            </div>
        <?php }?>
    <?php }?>

</div>

