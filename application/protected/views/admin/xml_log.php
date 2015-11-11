<?php $this->widget('TwoDatesFilter', array('block_path' => '#filterparams', 'date_from_name' => 'XmlLogForm[date_from]', 'date_to_name' => 'XmlLogForm[date_to]'));?>


<div class="middlewide">
        <div class="middlenarrow">
            
                <div class="spacer"></div>
                <?php $current_url = $this->createUrl('admin/xmllog')?>
                <div id="filterparams" style="margin-bottom: 10px;">
                <form action="<?php echo $current_url?>" method="post">

                    <table width="100%" class="formtable">
                    <tr>
                        <th class="date"><?php echo CHtml::activeLabel($form, 'date_from')?></th>
                        <th class="time"><?php echo CHtml::activeLabel($form, 'time_from')?></th>
                        <th class="empty">&nbsp;</th>
                        <th class="date"><?php echo CHtml::activeLabel($form, 'date_to')?></th>
                        <th class="time"><?php echo CHtml::activeLabel($form, 'time_to')?></th>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
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
    
    <?php if ($list) {?>
    
        
        <table class="tablelist" width="100%">
        <?php foreach ($list as $key => $value) { ?>
            
            <tr>
                <td style="padding-top: 10px; width: 150px;">
                    <b><?php echo date('m/d/Y H:i:s', strtotime($value['created'])) ?></b>
                </td>
                <td style="padding-left:15px;"><?php echo nl2br($value['comment'])?></td>
            </tr>    
        <?php }?>
        </table>
    
        <?php if ($pages->getPageCount() > 1){?>

            <div class="paginator" style="margin-top: 10px;">
                <?php  $this->widget(
                    'CLinkPager',
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
    <?php } else {?>
        This log is empty.
    <?php }?>
    
</div>