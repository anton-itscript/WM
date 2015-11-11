<?php $this->widget('TwoDatesFilter', array('block_path' => '#filterparams', 'date_from_name' => 'MessagesLogFilterForm[date_from]', 'date_to_name' => 'MessagesLogFilterForm[date_to]'));?>

<form action="<?php echo $current_url?>" method="post">
<div class="middlewide">
    <div class="middlenarrow">
        <div class="right_align"><a class="refresh" href=""><?php echo It::t('site_label', 'do_refresh')?></a></div>
        <div style="margin-top: -20px">
            <?php $current_url = $this->createUrl('site/msghistory')?>

            <table class="formtable left" style="margin-right: 20px;">
            <tr>
                <th><?php echo CHtml::activeLabel($form, 'type')?></th>
            </tr>
            <tr>
                <td>
                    <?php echo CHtml::activeCheckBoxList($form, 'types',  $form->getTypes())?>
                    <?php echo CHtml::error($form,'types'); ?> 
                </td>
            </tr>
            </table>        
        
            <table class="formtable left middlecolumns" id="filterparams" >
            <tr>
                <th><?php echo CHtml::activeLabel($form, 'date_from')?></th>
                <td>
                    <?php echo CHtml::activeTextField($form, 'date_from', array('class' => 'date-pick input-calendar'))?>
                    <div class="clear"></div>
                    <?php echo CHtml::error($form,'date_from'); ?>
                </td>    
                <td>
                    <?php echo CHtml::activeLabel($form, 'time_from')?>
                    <?php echo CHtml::activeTextField($form, 'time_from', array('style' => 'width: 50px;'))?>
                    <?php echo CHtml::error($form,'time_from'); ?>
                </td>
            </tr>
            <tr>
                <th><?php echo CHtml::activeLabel($form, 'date_to')?></th>
                <td>
                    <?php echo CHtml::activeTextField($form, 'date_to', array('class' => 'date-pick input-calendar'))?>
                    <div class="clear"></div>
                    <?php echo CHtml::error($form,'date_to'); ?>                
                </td>
                <td>
                    <?php echo CHtml::activeLabel($form, 'time_to')?>
                    <?php echo CHtml::activeTextField($form, 'time_to', array('style' => 'width: 50px;'))?>
                    <?php echo CHtml::error($form,'time_to'); ?>
                </td>            
            </tr>  
            <tr>
                <td colspan="3">
                    <?php echo CHtml::submitButton(It::t('site_label', 'do_filter'), array('name' => 'filter'))?>
                    <?php echo CHtml::submitButton(It::t('site_label', 'do_reset'), array('name' => 'clear'))?>
                    &nbsp;&nbsp;
                    <?php echo CHtml::submitButton(It::t('site_label', 'do_export'), array('name' => 'export'))?>
                    &nbsp;&nbsp;
                    <?php if (Yii::app()->user->isSuperAdmin()) {?>
                        <?php echo CHtml::submitButton(It::t('site_label', 'do_delete_checked'), array('name' => 'delete_checked', 'onclick' => "return confirm('".It::t('site_label', 'msg_history__confirm_delete')."');"))?>
                    <?php } ?>
                </td>
            </tr>
            </table>
        </div>
        <div class='clear'></div>
    </div>
</div>

<div class="middlenarrow">



<br/><br/>

<?php if (!$list) {?>
    <?php echo It::t('site_label', 'msg_history__empty_result')?>
    
<?php } else {?>
    <table class="tablelist messageslog" width="100%">
    <tr>
        <?php if (Yii::app()->user->isSuperAdmin()) {?>
        <th rowspan="2" class="bottomdouble chk"><input type="checkbox" name="check_all" value="" /></th>
        <?php }?>
        <th colspan="3">
            <div style="width: 70px;">
                <a href="<?php echo $current_url?>?of=message" class="<?php echo ($form->order_field == 'message' && $form->order_direction == 'DESC')?'desc':'asc'?> <?php echo $form->order_field == 'message'?'selected':''?>"><?php echo It::t('site_label', 'msg_history__col_message')?></a>        
            </div>
        </th>
        <?php if (Yii::app()->user->isSuperAdmin()) {?>
        <th rowspan="2" class="bottomdouble tools"><?php echo It::t('site_label', 'msg_history__col_tools')?></th>
        <?php }?>        
    </tr>    
    <tr class="bottomdouble">
        <th class="date">
            <a href="<?php echo $current_url?>?of=date" class="<?php echo ($form->order_field == 'date' && $form->order_direction == 'DESC')?'desc':'asc'?> <?php echo $form->order_field == 'date'?'selected':''?>"><?php echo It::t('site_label', 'msg_history__col_added')?></a>        
        </th>
        <th class="info"><?php echo It::t('site_label', 'msg_history__col_info')?></th>
        <th><?php echo It::t('site_label', 'msg_history__col_errors')?></th>

    </tr>
    <?php $i=0 ?>
    <?php foreach ($list as $key => $value) {?>
    <tr  class="<?php echo (fmod($i,2) == 0 ? 'c' : '')?>">
        <?php if (Yii::app()->user->isSuperAdmin()) {?>
        <td rowspan="2" class="bottomdouble chk"><input type="checkbox" name="log_id[]" value="<?php echo $value['log_id']?>" /></td>
        <?php } ?>
        <td colspan="3" class="message">
            <?php echo $value['message']?>
        </td>
        <?php if (Yii::app()->user->isSuperAdmin()) {?>
        <td rowspan="2"  class="bottomdouble">
            <a href="<?php echo $this->createUrl('site/msghistory')?>?delete=<?php echo $value['log_id']?>" onclick="return confirm('Are you sure you want to delete this log?')"><?php echo It::t('site_label', 'do_delete')?></a>
        </td>
        <?php }?>        
    </tr>    
    <tr class="<?php echo (fmod($i,2) == 0 ? 'c' : '')?> bottomdouble">
        <td style="width:200px">
            <ul>
                <li style="margin-bottom:3px"><b>Message time UTC:</b><br>&nbsp&nbsp&nbsp&nbsp<?php echo $value['measuring_timestamp']?></li>
                <li style="margin-bottom:3px"><b>Message created:</b><br>&nbsp&nbsp&nbsp&nbsp<?php echo $value['created']?></li>
                <li><b>Message updated:</b><br>&nbsp&nbsp&nbsp&nbsp<?php echo $value['updated']?></li>
            </ul>
        </td>
        <td class="info">
            
            <?php if (!$value['is_processed']) {?>
                <?php echo It::t('site_label', 'msg_history__message_was_not_processed_yet')?>
            <?php } else {?>
                <?php if ($value['station_id_code']) {?>
                    <?php echo It::t('site_label', 'msg_history__station_recognized')?><br/>
                    <?php echo $value['display_name'].' (ID: '.$value['station_id_code'].', type: '.$value['station_type'].') ' ?>
                <?php } else {?>
                    <?php echo It::t('site_label', 'msg_history__station_not_recognized')?>
                <?php }?>

                <?php if ($value['errors']) {?>
                    <div class="message_history_red"><?php echo It::t('site_label', 'msg_history__message_fatal_errors')?></div>
                <?php } else if ($value['warnings']) {?>
                    <div class="message_history_green"><?php echo It::t('site_label', 'msg_history__some_sensors_were_not_processed')?></div>
                <?php }?>
                <?php if (!$value['errors'] && !$value['warnings']) {?>
                    <div  class="message_history_green"><?php echo It::t('site_label', 'msg_history__message_successfull')?></div>
                <?php }?>
            <?php }?>
        </td>
        <td>
            <?php if ($value['errors']) {?>
                <div class="message_history_errors">
                    <?php echo It::t('site_label', 'msg_history__fatal_errors_list')?><br/>
                    <?php echo implode("<br/>",$value['errors'])?>
                </div>
            <?php }?>
            <?php if ($value['warnings']) {?>
                <div class="message_history_warnings <?php if (count($value['warnings']) > 5) {?>long<?php }?>">
                    <b><?php echo It::t('site_label', 'msg_history__warnings_errors_list')?></b><br/>
                    <?php echo implode("<br/>",$value['warnings'])?>
                </div>
            <?php }?>
            &nbsp;
        </td>

    </tr>

    <?php $i++;?>

    <?php }?>
    </table>
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
</form>