<?php
	Yii::app()->clientScript->registerScriptFile(It::baseUrl() .'/js/jquery.form.js');
	Yii::app()->clientScript->registerScriptFile(It::baseUrl() .'/js/schedule_work.js');
?>

<div class="data_box speci-report">
	<div class="header"><?php echo It::t('home_aws', 'single__block_speci_report'); ?></div>
	<div class="content">
	
	<?php 
		if (!is_null($report))
		{
	?>
		<table>
			<tr>
				<td>
					<?php echo CHtml::beginForm('#', 'post'); ?>
					<input type="hidden" name="schedule_processed_id" value="<?php echo $report->schedule_processed_id; ?>" />
					<textarea style="width: 300px; height: 60px; font-size: 11px;" id="speciText" name="ScheduleReportProcessed[report_string_initial]" cols="50" rows="50"><?php echo $report->report_string_initial; ?></textarea>
					<div class="action_msg"></div>
					<?php echo CHtml::button(It::t('site_label', 'do_generate'), array('onclick' => 'regenerateScheduledReportWithChangesStart(this)')); ?> &nbsp;
					<?php echo CHtml::button(It::t('site_label', 'do_save'), array('onclick' => 'resaveScheduledReportWithRefreshContinue(this)')); ?> &nbsp;
					<?php echo CHtml::button(It::t('site_label', 'do_cancel'), array('id' => 'cancel-button', 'style' => 'display: none;', 'onclick' => 'cancelScheduledReport(this)')); ?> &nbsp;
					<?php echo CHtml::button(It::t('site_label', 'do_download'), array('onclick' => 'downloadScheduledReportWithRefreshContinue(this)')); ?>
					<?php echo CHtml::endForm(); ?>
	            </td>
			</tr>                       		             
		</table> 
	<?php 
		}
	?>
	</div>
</div>

<script type="text/javascript">
	$('#speciText').bind('input propertychange', function() 
		{
			showCancelButton();
			
			refreshStop();
		}
	);
</script>
	