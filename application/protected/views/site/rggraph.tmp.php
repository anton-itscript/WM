<?php $this->widget('TwoDatesFilter', array('block_path' => '#filterparams', 'date_from_name' => 'RgGraphForm[date_from]', 'date_to_name' => 'RgGraphForm[date_to]'));?>

<!-- BEGIN: load jqplot -->
<?php  Yii::app()->clientScript->registerCssFile(It::baseUrl().'/css/jquery.jqplot.css') ?>
<?php  Yii::app()->clientScript->registerScriptFile(It::baseUrl().'/js/jqplot/jquery.jqplot.js');?>
<?php  Yii::app()->clientScript->registerScriptFile(It::baseUrl().'/js/jqplot/plugins/jqplot.dateAxisRenderer.js');?>
<?php  Yii::app()->clientScript->registerScriptFile(It::baseUrl().'/js/jqplot/plugins/jqplot.highlighter.js');?>
<?php  Yii::app()->clientScript->registerScriptFile(It::baseUrl().'/js/jqplot/plugins/jqplot.cursor.js');?>

<?php 
    $stations = $form->getStationsList(); 
?>

  <!-- END: load jqplot -->
<div class="middlewide">
<div class="middlenarrow">    
    <?php if ($stations) {?>
    <div class="right_align"><a class="refresh" href=""><?php echo It::t('site_label', 'do_refresh')?></a></div>
    <?php }?> 


    <div id="filterparams" style="margin-bottom: 10px;">
        <?php echo CHtml::beginForm($this->createUrl('site/rggraph'), 'post'); ?>

            <table width="100%" class="formtable">
            <tr>
                <th><?php echo CHtml::activeLabel($form, 'station_id')?></th>
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
                    <?php if ($stations) {?>
                        <?php echo CHtml::activeDropDownList($form, 'station_id', $stations)?>
                    <?php } else {?>
                        No RG stations
                    <?php }?>
                </td>
                <td>&nbsp;</td>
                <td class="date" style="vertical-align: top;">
                    <?php echo CHtml::activeTextField($form, 'date_from', array('class' => 'date-pick input-calendar', 'style' => 'width: 80px'))?>
                    <div style="clear: both;"></div>
                    <?php echo CHtml::error($form,'date_from'); ?>                    
                </td>
                <td class="time" style="vertical-align: top;">
                    <?php echo CHtml::activeTextField($form, 'time_from', array('style' => 'width: 50px;'))?>
                    <?php echo CHtml::error($form,'time_from'); ?>
                </td>
                <td>&nbsp;</td>
                <td class="date" style="vertical-align: top;">
                    <?php echo CHtml::activeTextField($form, 'date_to', array('class' => 'date-pick input-calendar', 'style' => 'width: 80px'))?>
                    <div style="clear: both;"></div>
                    <?php echo CHtml::error($form,'date_to'); ?> 
                </td>
                <td class="time" style="vertical-align: top;">
                    <?php echo CHtml::activeTextField($form, 'time_to', array('style' => 'width: 50px;'))?>
                    <?php echo CHtml::error($form,'time_to'); ?>
                </td>
                <td>&nbsp;</td>

                <td class="buttons">
                    <?php echo CHtml::submitButton(It::t('site_label', 'do_filter'), array('name' => 'filter'))?>
                    <?php echo CHtml::submitButton(It::t('site_label', 'do_reset'), array('name' => 'clear'))?>
                </td>
            </tr>
            </table>
        <?php echo CHtml::endForm(); ?>
    </div>
</div></div>
  
<div class="middlenarrow">  

    
<?php  if (!$form->station_id) { ?>
    <h1><?php echo It::t('home_rg', 'graph__no_station_selected')?></h1>    
<?php } else if (!$series_data) {?>
    <h1><?php echo It::t('home_rg', 'graph__no_data_in_selected_period')?></h1>
<?php } else {?>

        
<script language="javascript" type="text/javascript">
$(document).ready(function(){

    $.jqplot.config.enablePlugins = true;

        series = <?php echo json_encode($series_data);?>;
        series_names = <?php echo json_encode($series_names);?>;

        var plot1 = $.jqplot('chart1', series, {

            axes: {
                xaxis: {
                    renderer: $.jqplot.DateAxisRenderer,
                    tickOptions: { formatString: '%y/%m/%d<br/>%H:%M'},
                    ticks : <?php echo $total_ticks?>,
                    min   : '<?php echo $min_tick?>',
                    max   : '<?php echo $max_tick?>'
                },
                yaxis: {
                    tickOptions: {formatString: '%.2f'},
                    min   : 0
                }
            },
            grid: { hoverable: true, clickable: true },
            highlighter:{show:true},
            cursor:{zoom:true},
            legend: { show: true, placement: 'outside' },
            series: series_names
        });

        $('#resetZoom').click(function(){
            plot1.resetZoom();
        });

});


</script>

<div id="chart1" class="plot" style="width:1000px;height:300px;"></div>

<div style="padding-top:20px"><?php echo It::t('home_rg', 'graph__select_area_to_zoom')?> <button value="reset" id="resetZoom" type="button" ><?php echo It::t('home_rg', 'graph__do_soom_out')?></button></div>

<?php }?>


</div>