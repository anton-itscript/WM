<?php 
	$this->widget('TwoDatesFilter', array(
			'block_path' => '#filterparams',
			'date_from_name' => 'ConnectionsLogForm[date_from]',
			'date_to_name' => 'ConnectionsLogForm[date_to]'
		)
	);

?>

<div class="middlewide">
	<div class="middlenarrow">

		<div class="spacer"></div>

		<?php $current_url = $this->createUrl('admin/connectionslog')?>
		<div id="filterparams" style="margin-bottom: 10px;">

			<form action="<?php echo $current_url?>" method="post">

				<table width="100%" class="formtable">
					<tr>
						<th><?php echo CHtml::activeLabel($form, 'source')?></th>
						<th>&nbsp;</th>
						<th class="date"><?php echo CHtml::activeLabel($form, 'date_from')?></th>
						<th class="time"><?php echo CHtml::activeLabel($form, 'time_from')?></th>
						<th class="empty">&nbsp;</th>
						<th class="date"><?php echo CHtml::activeLabel($form, 'date_to')?></th>
						<th class="time"><?php echo CHtml::activeLabel($form, 'time_to')?></th>
						<th>&nbsp;</th>
					</tr>
					<tr>
						<td>
							<?php if ($sources) {?>
								<?php echo CHtml::activeDropDownList($form, 'source', $sources, array('style' => 'width: 130px;'))?>
							<?php } else {?>
								No Sources.
							<?php }?>
							<?php echo CHtml::error($form,'source'); ?>     

						</td>
						<td>&nbsp;</td>
						<td class="date">
							<?php echo CHtml::activeTextField($form, 'date_from', array('class' => 'date-pick input-calendar'))?>
							<?php echo CHtml::error($form,'date_from'); ?> 
						</td>
						<td class="time">
							<?php echo CHtml::activeTextField($form, 'time_from', array('style' => 'width: 50px;'))?>
							<?php echo CHtml::error($form,'time_from'); ?> 
						</td>
						<td>&nbsp;</td>
						<td class="date">
							<?php echo CHtml::activeTextField($form, 'date_to', array('class' => 'date-pick input-calendar'))?>
							<?php echo CHtml::error($form,'date_to'); ?> 
						</td>
						<td class="time">
							<?php echo CHtml::activeTextField($form, 'time_to', array('style' => 'width: 50px;'))?>
							<?php echo CHtml::error($form,'time_to'); ?> 
						</td>
						<td>&nbsp;</td>
						<td class="buttons">
							<input type="submit" name="filter" value="<?php echo It::t('site_label', 'do_filter')?>" />
							<input type="submit" name="clear" value="<?php echo It::t('site_label', 'do_reset')?>" />
							<input type="submit" name="export" value="<?php echo It::t('site_label', 'do_export')?>" />
						</td> 
					</tr>
				</table>
			</form>     

		<div class="clear"></div>
		</div>
	</div><!-- div.middlenarrow -->
	<div class="spacer"></div>
</div><!-- div.middlewide -->

<div class="middlenarrow">
    <?php
		$this->widget('zii.widgets.grid.CGridView', array(
				'id' => 'connection-log-grid',
				
				'dataProvider' => $provider,

				'template' => '{items}{pager}',

				'itemsCssClass' => 'tablelist',
				'emptyText' => 'This log is empty.',
			
				'pager' => array(
					'class' => 'CLinkPager',
					
					'header' => '',
					
					'firstPageLabel' => '&nbsp;',
					'lastPageLabel' => '&nbsp;',
					'nextPageLabel' => '&rarr;',
					'prevPageLabel' => '&larr;',	
				),


				'columns' => array(
					array(
						'class' => 'SpanColumn',
						
						'name' => 'created',
						'type' => 'raw',
						
						'isSpan' => true,
						'spanSize' => 3,
						'spanCondition' => 'is_null($prev) ? true : ($prev->listener_id != $data->listener_id)',
						'spanValue' => '"<b>Connection #". $data->listener_id . "</b> (". date("m/d/Y H:i:s", $data->listener->started) ." - ". ($data->listener->stopped ? date("m/d/Y H:i:s", $data->listener->stopped) : "still connected") .") </td></tr><tr><td style=\"padding-left:15px\">"',
						'value' => 'date("m/d/Y H:i", strtotime($data->created))',
						
						'htmlOptions' => array(
							 'style' => 'padding-left:15px; min-width: 120px;',
						),
					),
					
					array(
						'class' => 'SpanColumn',
						'name' => 'status',
						
						'spanCondition' => 'is_null($prev) ? true : ($prev->listener_id != $data->listener_id)',
                        'value'         => 'ucfirst($data->status)',
						
						'htmlOptions' => array(
							'style' => 'width: 100px;'
						),
					),
					
					array(
						'class' => 'SpanColumn',
						'name' => 'comment',
						
						'spanCondition' => 'is_null($prev) ? true : ($prev->listener_id != $data->listener_id)',
						
						'htmlOptions' => array(
							'style' => 'width: 700px;'
						),
					),
				),
			)
		); 
	?>    
</div>