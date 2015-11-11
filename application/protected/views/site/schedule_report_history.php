<?php Yii::app()->clientScript->registerScriptFile(It::baseUrl() . '/js/schedule_work.js'); ?>
<div class="middlewide">
    <div class="middlenarrow">
    <h1>
        <a href="<?php echo $this->createUrl('site/schedule') ?>">Schedule Report Generation</a> /
        <a href="<?php echo $this->createUrl('site/schedulehistory',array('schedule_id'=>$reportInfo['schedule_id'])) ?>">Report <?=$reportInfo['schedule_id']?>  </a> /
        <?=$stationInfo['station_id_code']?>
    </h1>

 <table class="formtable">
        <tr>
            <th style="text-align: right;">Station:</th>
            <td><?php echo $stationInfo['station_id_code']?></td>

            <td>&nbsp;&nbsp;</td>

            <th style="text-align: right;">Report:</th>
            <td><?php echo strtoupper($reportInfo['report_type'])?></td>

           <td>&nbsp;&nbsp;</td>   

            <th style="text-align: right;">Period:</th>
            <td><?php echo Yii::app()->params['schedule_generation_period'][$reportInfo['period']]?></td>
        </tr> 
        <tr>
            <th style="text-align: right;">Station Timezone:</th>
            <td><?php echo $stationInfo['timezone_id']?> (GMT <?php echo $stationInfo['timezone_offset']?>)</td>

            <td>&nbsp;&nbsp;</td>

            <th style="text-align: right;">Format:</th>
            <td><?php echo $reportInfo['report_format']?></td>

            <td>&nbsp;&nbsp;</td>
        </tr>
        </table>
    </div>
</div>


<div class="middlenarrow">
    <?php if ($pages->getPageCount() > 1){?>

        <div class="paginator" style="margin-top: 10px;">
            <?php $this->widget('CLinkPager',
                array(
                    'pages' => $pages,
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

    
    <br/>
    
    <?php if (!$scheduleProcessed) {?>
        No reports were generated.
    
    <?php } else {?>
    
        
        <?php foreach ($scheduleProcessed as $key => $value) {?>
        <table class="tablelist " style="width:100%;">
        <tr>
            <th style="width: 100px;">Measuring time:</th> 
            <td>
                <?php if (isset($value['listener_log_id']))
					{
						echo gmdate('Y-m-d H:i', strtotime($value['measuring_timestamp'])); ?> UTC
                <?php 
					} 
					else 
					{
						echo gmdate('Y-m-d H:i', strtotime($value['check_period_start'])); ?> - <?php echo gmdate('Y-m-d H:i', strtotime($value['check_period_end'])); ?> UTC
                <?php 
					}
				?>    
            </td>
        </tr>    
        <tr>
            <th>Message:</th>
            <td>
                <?php if ($value['report_type'] == 'data_export') {?>
                    <?php if (!$value['logs']) {?>
                        No logs found for checked period.
                    <?php } else {?>
                        <div style="width: 850px; overflow-x: scroll;">
                        <?php foreach ($value['logs'] as $k1 => $v1) {?>
                            <b>#<?php echo $v1['log_id']?>:</b> 
                            <a href="<?php echo $this->createUrl('site/awssingle', array('station_id' => $value['station_id'], 'log_id' => $v1['log_id']))?>" target="_blank" >At AWS Single View</a>

                            <div class="small" style="margin-top: 5px;">
                                <?php echo $v1['message']?>
                            </div>                  
                        <?php } ?>                
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <b>#<?php echo $value['listener_log_id']?>:</b> 
                    <a href="<?php echo $this->createUrl('site/awssingle', array('station_id' => $value['station_id'], 'log_id' => $value['listener_log_id']))?>" target="_blank" >At AWS Single View</a>

                    <div class="small" style="margin-top: 5px; width: 850px; overflow-x: scroll;">
                        <?php echo $value['message']?>
                    </div>                              
                <?php } ?>
                
            </td>
        </tr>
        
        <?php if ($value['report_type'] === 'data_export' && !isset($value['logs'])) {?>
        
        <?php } else {?>
        <tr>
            <th>Report:<br/><br/>
                <?php echo CHtml::beginForm('#', 'post'); ?>
                <input type="hidden" name="schedule_processed_id" value="<?php echo $value['schedule_processed_id']?>" />
                <?php echo CHtml::button('Download', array('onclick' => 'downloadScheduledReport(this)'))?>
                <?php echo CHtml::endForm(); ?>            
            </th>
            <td>
                <?php if (in_array($value['report_type'], array('synop', 'metar', 'speci'))) {?>
                
                    <?php echo nl2br($value['report_string_initial'])?>
                
                <?php }?>
            </td>
        </tr>
        
        <?php if (isset($value['report_errors'])) : ?>
        <tr >
            <th>Errors during generation:</th>
            <td><?php echo implode('<br/>', $value['report_errors'])?></td>
        </tr>
        <?php endif; ?>
        
        <tr class="bottomdouble">
            <th>Explanations:</th>
            <td class="synop_explanations">
                <?php if (isset($value['report_explanations'])) {?>
                    <?php foreach($value['report_explanations'] as $k1 => $v1) {?>
                        <?php if ($value['report_type'] == 'bufr') {?>
                            <b>Section #<?php echo $k1?>:</b>
                        <?php } else {?>
                            <b>Report Line #<?php echo ($k1+1)?></b>
                        <?php }?>
                        <br/>
                        <?php foreach ($v1 as $k2 => $v2) {?>
                        <?php echo ($v2 ? implode('<br/> ', $v2) : '') ?><br/>
                        <?php }?>
                        <br/>
                    <?php }?>
                <?php }?>
            </td>
        </tr>

        <?php }?>

        </table>
        <br/><br/>
        <?php }?>
       
        
    <div class="spacer"></div>
    
    <?php if ($pages->getPageCount() > 1){?>

	<div class="paginator" style="margin-top: 10px;">
            <?php $this->widget('CLinkPager',
                array(
                    'pages' => $pages,
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

