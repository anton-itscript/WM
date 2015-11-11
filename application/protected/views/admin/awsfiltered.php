<?php  Yii::app()->clientScript->registerCssFile(It::baseUrl().'/css/datePicker.css') ?>
<?php  Yii::app()->clientScript->registerScriptFile(It::baseUrl().'/js/date.js');?>
<?php  Yii::app()->clientScript->registerScriptFile(It::baseUrl().'/js/jquery.datePicker.js');?>
<div class="middlewide">
    <div class="middlenarrow">
        <div class="spacer"></div>
        <?php $current_url = $this->createUrl('admin/awsfiltered'); ?>
        <div id="filterparams">
            <form action="<?php echo $current_url?>" method="post">
                <table width="100%" class="formtable">
                    <tr>
                        <th>Select Station:</th>
                        <th>&nbsp;</th>
                        <th class="date">Start Date</th>
                        <th class="time">UTC Time:</th>
                        <th class="empty">&nbsp;</th>
                        <th class="date">End Date</th>
                        <th class="time">UTC Time:</th>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <td>
                            <select name="search[station_id]">
                                <?php foreach ($stations as $key => $value) {?>
                                    <option value="<?php echo $value['station_id']; ?>" <?php echo  ($value['station_id'] == $fparams['station_id'] ? 'selected' : ''); ?> ><?php echo $value['station_id_code']; ?> - <?php echo $value['display_name']; ?></option>
                                <?php }?>
                            </select>
                        </td>
                        <td>&nbsp;</td>
                        <td class="date"><input type="text" class="date-pick input-calendar" name="search[date_from]" value="<?php echo $fparams['date_from']; ?>" /></td>
                        <td class="time"><input type="text" class="" name="search[time_from]" value="<?php echo $fparams['time_from']; ?>" maxlength="5" /></td>
                        <td>&nbsp;</td>
                        <td class="date"><input type="text" class="date-pick input-calendar" name="search[date_to]" value="<?php echo $fparams['date_to']; ?>" /></td>
                        <td class="time"><input type="text" class="" name="search[time_to]" value="<?php echo $fparams['time_to']; ?>" maxlength="5" /></td>
                        <td class="buttons">
                            <input type="submit" name="filter" value="<?php echo It::t('site_label', 'do_filter'); ?>" />
                            <input type="submit" name="clear" value="<?php echo It::t('site_label', 'do_reset'); ?>" />
                            <input type="hidden" name="showdata" value="1" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="spacer"></div>
    </div></div>

<div class="middlenarrow">
    <div class="spacer"></div>
    <?php if ($clean_page) {

    } else {?>
    <?php if (!$list) {?>

        There is no filtered data.
    <?php  } else {?>
        <table class="tablelist" width="100%">
            <tr>
                <th>No.</th>
                <th>
                    <a href="<?php echo $current_url?>?of=stationid" class="<?php echo ($fparams['order_field'] == 'stationid' && $fparams['order_direction'] == 'DESC')?'desc':'asc'?> <?php echo $fparams['order_field'] == 'stationid'?'selected':''?>">Station ID</a>
                </th>
                <th>
                    <a href="<?php echo $current_url?>?of=date" class="<?php echo ($fparams['order_field'] == 'date' && $fparams['order_direction'] == 'DESC')?'desc':'asc'?> <?php echo $fparams['order_field'] == 'date'?'selected':''?>">Date</a>
                </th>
                <th>
                    <a href="<?php echo $current_url?>?of=sensorid" class="<?php echo ($fparams['order_field'] == 'sensorid' && $fparams['order_direction'] == 'DESC')?'desc':'asc'?> <?php echo $fparams['order_field'] == 'sensorid'?'selected':''?>">Sensor Id</a>
                </th>
                <th>
                    <a href="<?php echo $current_url?>?of=value" class="<?php echo ($fparams['order_field'] == 'value' && $fparams['order_direction'] == 'DESC')?'desc':'asc'?> <?php echo $fparams['order_field'] == 'value'?'selected':''?>">Value</a>
                </th>
                <th>Period<br/>(minutes)</th>
                <th>Problem</th>
                <th>&nbsp;</th>
            </tr>
            <?php  foreach($list as $key => $value) { ?>
                <?php  $class = (fmod($key,2) == 0 ? 'c' : '');?>
                <tr class="<?php echo $class?>">
                    <td><?php echo ($pages->currentPage*$pages->pageSize + $key + 1); ?></td>
                    <td><?php echo $value['station_id_code']; ?></td>
                    <td><?php echo $value['measuring_timestamp']; ?></td>
                    <td><?php echo $value['sensor_id_code']; ?></td>
                    <td><?php echo $value['sensor_feature_value']; ?>
                        <?php echo ($value['is_cumulative'] ? ' (cumulated)':''); ?>
                    </td>
                    <td>
                        <?php echo $value['period']; ?>
                    </td>
                    <td>
                        <?php if (isset($value['filter_reason'])) {?>
                            <?php foreach ($value['filter_reason'] as $k1 => $reason_data) {?>
                                <font color="red"><?php echo $reason_data['main']; ?></font>
                                <?php if ($reason_data['extra']) {?>
                                    <br/><?php echo $reason_data['extra']; ?>
                                <?php }?>
                                <br/>
                            <?php }?>
                        <?php } ?>
                    </td>
                    <td><a href="<?php echo $this->createUrl('admin/AwsFiltered', array('delete' => $value['sensor_data_id'])); ?>" onclick="return confirm('Are you sure you want to delete this value?');">Delete</a></td>
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

            </div>

        <?php }?>

    <?php  } ?>
    <?php  } ?>

    <script type="text/javascript">

        $(document).ready(function(){
            $('#filterparams .date-pick').datePicker({startDate:'01/01/1996', clickInput:true, imgCreateButton: true})
            $('#filterparams input[name=date_from]').bind(
                'dpClosed',
                function(e, selectedDates)
                {
                    var d = selectedDates[0];
                    if (d) {
                        d = new Date(d);
                        $('#filterparams input[name=date_to]').dpSetStartDate(d.addDays(1).asString());
                    }
                }
            );
            $('#filterparams input[name=date_to]').bind(
                'dpClosed',
                function(e, selectedDates)
                {
                    var d = selectedDates[0];
                    if (d) {
                        d = new Date(d);
                        $('#filterparams input[name=date_from]').dpSetEndDate(d.addDays(-1).asString());
                    }
                }
            );
        });
    </script>
    <br/><br/>
</div>