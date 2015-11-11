<div class="middlewide">
    <div class="middlenarrow">
        <div class="small" style="text-align: right;"><?php echo It::t('site_label', 'page_is_autorefreshing')?></div>
        <div id="autorefreshedPageError"></div>
    </div>

    <?php $this->widget('TwoDatesFilter', array('block_path' => '#filterparams', 'date_from_name' => 'RgTableForm[date_from]', 'date_to_name' => 'RgTableForm[date_to]'));?>

    <div class="middlenarrow" style=" margin-top: -20px">
        
        <?php $current_url = $this->createUrl('site/rgtable')?>
        <div id="filterparams" style="margin-bottom: 10px;">
        <form action="<?php echo $current_url?>" method="post">

            <table width="100%" class="formtable">
            <tr>
                <th><?php echo CHtml::activeLabel($render_data['form'], 'station_id')?></th>
                <th>&nbsp;</th>
                <th class="date"><?php echo CHtml::activeLabel($render_data['form'], 'date_from')?></th>
                <th class="time" style="min-width: 70px"><?php echo CHtml::activeLabel($render_data['form'], 'time_from')?></th>
                <th class="empty">&nbsp;</th>
                <th class="date"><?php echo CHtml::activeLabel($render_data['form'], 'date_to')?></th>
                <th class="time" style="min-width: 70px"><?php echo CHtml::activeLabel($render_data['form'], 'time_to')?></th>
                <th>&nbsp;</th>
                <th><?php echo CHtml::activeLabel($render_data['form'], 'rate_volume')?></th>
                <th>&nbsp;</th>
            </tr>
            <tr>
                <td>
                    <?php if ($render_data['form']->getStationsList()) {?>
                        <?php echo CHtml::activeDropDownList($render_data['form'], 'station_id', $render_data['form']->getStationsList(), array('style' => 'width: 130px;', 'empty' => array(0 => 'All Stations')))?>
                    <?php } else {?>
                        No stations.
                    <?php }?>
                    <?php echo CHtml::error($render_data['form'],'station_id'); ?>     

                </td>
                <td>&nbsp;</td>
                <td class="date">
                    <?php echo CHtml::activeTextField($render_data['form'], 'date_from', array('class' => 'date-pick input-calendar'))?>
                    <?php echo CHtml::error($render_data['form'],'date_from'); ?> 
                </td>
                <td class="time">
                    <?php echo CHtml::activeTextField($render_data['form'], 'time_from', array('style' => 'width: 50px;'))?>
                    <?php echo CHtml::error($render_data['form'],'time_from'); ?> 
                </td>
                <td>&nbsp;</td>
                <td class="date">
                    <?php echo CHtml::activeTextField($render_data['form'], 'date_to', array('class' => 'date-pick input-calendar'))?>
                    <?php echo CHtml::error($render_data['form'],'date_to'); ?> 
                </td>
                <td class="time">
                    <?php echo CHtml::activeTextField($render_data['form'], 'time_to', array('style' => 'width: 50px;'))?>
                    <?php echo CHtml::error($render_data['form'],'time_to'); ?> 
                </td>
                <td>&nbsp;</td>
                <td>
                    <?php echo CHtml::activeDropDownList($render_data['form'], 'rate_volume', $render_data['form']->getGroupSumsList(), array('style' => 'width: 50px;'))?>
                    <?php echo CHtml::error($render_data['form'],'rate_volume'); ?> 
                </td>
                <td class="buttons">
                    <input type="submit" name="filter" value="<?php echo It::t('site_label', 'do_filter')?>" />
                    <input type="submit" name="clear" value="<?php echo It::t('site_label', 'do_reset')?>" />
                    <?php if ($render_data['listing']) {?>
                    <input type="submit" name="export" value="<?php echo It::t('site_label', 'do_export')?>" />
                    <?php } ?>
                </td> 
            </tr>

            
            </table>


        </form>


    </div>
        
</div>    
</div>

<div id="autorefreshedPpage_5min">
   <?php $this->renderPartial($template, array('render_data' => $render_data)); ?>
</div>
