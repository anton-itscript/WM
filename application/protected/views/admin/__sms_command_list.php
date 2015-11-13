<?php
/**
 * @var CActiveDataProvider $dataProvider
 */
?>

<h1> SMS Log </h1>

<form action="<?=Yii::app()->createUrl('admin/sendsmscommand')?>" method="POST" >
    <input type="text" name="SMSCommand[updated_from]" value="<?=$dateFrom?>" class="date-pick" style="display: inline-block; width: 100px;" >
    <input type="text" name="SMSCommand[updated_to]" value="<?=$dateTo?>" class="date-pick" style="display: inline-block; width: 100px;">
    <input type="hidden" name="date_range"/>
    <input type="submit"/>

</form>
<a href="<?=Yii::app()->createUrl('admin/sendsmscommand', $params = array('reset'=>'1'))?>">
    <button>Reset</button>
</a>
<?php

$this->widget('zii.widgets.grid.CGridView', [
    'dataProvider' => $dataProvider->model->search(),
    'enablePagination' => true,

    'itemsCssClass' => 'tablelist',
    'ajaxUpdate'        => false,
    'pager' => [
        'class' => 'CLinkPager',
        'header' => '',
        'pageSize' => 15,
        'firstPageLabel' => '&nbsp;',
        'lastPageLabel' => '&nbsp;',
        'nextPageLabel' => '&rarr;',
        'prevPageLabel' => '&larr;',
    ],

    'columns' => [
        'created',
        [
            'name' => 'updated',
            'htmlOptions' => ['style' => 'width: 250px;'],
        ],
        [
            'name' => 'sms_command_status',
            'htmlOptions' => ['style' => 'width: 30px;'],
        ],
        [
            'name' => 'station_id',
            'htmlOptions' => ['style' => 'width: 30px;'],
        ],
        'sms_command_code',
        'sms_command_message',
        'sms_command_response',

        [
            'class' => 'CButtonColumn',
            'template' => '{delete}{view}',

            'buttons' => [
                'delete' => [
                    'url' => 'Yii::app()->createUrl("admin/sendsmscommand", array("delete_id" => $data->sms_command_id))',
                ],
                'view' => [
                    'url' => 'Yii::app()->createUrl("admin/sendsmscommand", array("view_id" => $data->sms_command_id))',
                ]
            ]
        ],
    ],
]);
?>

<a href="<?=Yii::app()->createUrl('admin/sendsmscommand', $params = array('getcsv'=>'1'))?>">
    <button>Get CSV</button>
</a>

<script>
    var block_path = '';
    var date_from_name ='SMSCommand[updated_from]';
    var date_to_name ='SMSCommand[updated_to]';
</script>