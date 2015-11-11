<div class="middlenarrow">
<h1>Forward messages to other WM servers</h1>
<?php
	$this->widget('zii.widgets.grid.CGridView', array(
		'id' => 'forward-info-grid',

		'dataProvider' => $model->search(),
		'template' => '{pager}<br />{items}<br />{pager}',

		'itemsCssClass' => 'tablelist',
		'emptyText' => 'No forward info found.',
		
		'ajaxUpdate' => true,
		'enablePagination' => true,
		
		'htmlOptions' => array(
			'class' => 'tablelist',
		),

		'pager' => array(
			'class' => 'CLinkPager',

			'header' => '',

			'firstPageLabel' => '&nbsp;',
			'lastPageLabel' => '&nbsp;',
			'nextPageLabel' => '&rarr;',
			'prevPageLabel' => '&larr;',	
		),

		'columns' => array(
			'address',
			'port',
			array(
				'class' => 'CButtonColumn',
				'template' => '{delete}',

				'buttons' => array(
					'delete' => array(
						'url' => 'Yii::app()->createUrl("admin/DeleteForwardInfo", array("id" => $data->id))',
					)
				)
			),
		),
	));
?>

<?php
	$ipAddressAddForm = $this->beginWidget('CActiveForm', array(
		'id' => 'ip-address-add-form',
		'enableAjaxValidation' => false,
		'action' => $this->createAbsoluteUrl('admin/ForwardList'),
		'method' => 'post',	
	));
?>

	<table class="formtable">
		<thead>
			<tr>
				<th colspan="3"><?php echo $ipAddressAddForm->errorSummary($createModel); ?></th>
			</tr>
			<tr>
				<th><?php echo $ipAddressAddForm->labelEx($createModel, 'address'); ?></th>
				<th><?php echo $ipAddressAddForm->labelEx($createModel, 'port'); ?></th>
				<th>&nbsp;</th>
			</tr>	
		</thead>
		<tbody>
			<tr>
				<td><?php echo $ipAddressAddForm->textField($createModel, 'address', array('size' => 50)); ?></td>
				<td><?php echo $ipAddressAddForm->textField($createModel, 'port', array('size' => 6,'maxlength' => 6)); ?></td>
				<td><?php echo CHtml::submitButton('Add'); ?></td>
			</tr>	
		</tbody>
	</table>

<?php $this->endWidget(); ?>

</div>