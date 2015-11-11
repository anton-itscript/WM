<?php $this->widget('TwoDatesFilter', array('block_path' => '#filterparams', 'date_from_name' => 'ExportForm[date_from]', 'date_to_name' => 'ExportForm[date_to]'));?>

<div class="middlewide">
<div class="middlenarrow">
    
    <?php if (!$form->all_stations) {?>
        <?php echo It::t('site_label', 'no_stations')?>
    
    <?php } else {?>
        <?php echo CHtml::beginForm($this->createUrl('site/export'), 'post'); ?>
        
        
            <div class="spacer"></div>
            
            <table class="formtable left" style="margin-right: 10px;">
            <tr>
                <th>
                    <input type="checkbox" id="check_all_stations" onclick="$('div.checkboxes_list.stid input').attr('checked', ($(this).attr('checked') == 'checked' ? true : false));">
                    <?php echo CHtml::activeLabel($form, 'station_id')?>        
                </th>
            </tr>
            <tr>
                <td>
                    <div class="checkboxes_list stid">
                        <?php echo CHtml::activeCheckBoxList($form, 'station_id', $form->all_stations)?>
                    </div>            
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
                <td colspan="3"><?php echo CHtml::submitButton(It::t('site_label', 'do_export'), array('name' => 'make_export'))?></td>
            </tr>
            </table>   
            
            
            <div class="clear"></div>
            
        <?php echo CHtml::endForm(); ?>
    
    <?php } ?>
    
    
</div>  
</div>