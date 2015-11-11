<?php
$f = 0; // flag
$tableColumn = 3;
$station_in_row = 6;
?>

<div class="middlewide">
    <div class="middlenarrow">
        <div class="breadcrumbs">
            <a href="<?php echo $this->createUrl('admin/heartbeatreports'); ?>">Heartbeat Reports</a> &gt;
            <?php echo $report ? $report->created : 'NOW'; ?>
        </div>
    </div>
</div>


<div class="middlenarrow heartbeatreport">
<div class="download">
    <?php echo CHtml::link('Download', array(
        'HeartbeatReport', 'report_id' => $report ? $report->report_id: null, 'download' => 1
    ));?>
</div>
<h1>Heartbeat Report</h1>

<!--REPORT STAT-->
<?php if ($report): ?>
    <div class="report">
        <table class="tablelist">
            <tr>
                <th class="th_hidden" colspan="<?php echo 6 ?>">Report info</th>
            </tr>
            <tr>
                <?php foreach ($report as $key => $value) { ?>
                    <td>
                        <?php echo str_replace('_',' ',ucfirst($key)); ?>
                        <div class="value_format"><?php echo $value; ?></div>
                    </td>
                <?php } ?>
            </tr>
        </table>
    </div>
<?php endif; ?>

<!--SYSTEM-->
<?php if ($data->system): ?>
    <div class="system">
        <table class="tablelist">
            <tr>
                <th class="th_hidden" colspan="<?php echo count($data->system); ?>">System Info</th>
            </tr>
            <tr style="display: none;">
                <?php foreach ($data->system as $key => $value) { ?>
                    <td>
                        <?php echo ucwords(str_replace('_',' ',$key)); ?>
                        <div class="value_format"><?php echo $value; ?></div></td>
                <?php } ?>
            </tr>
        </table>
    </div>
<?php endif; ?>

<!--DB SMALL STAT-->
<div class="db_small_stat">
    <?php if ($data->db_small_stat): ?>
        <table class="tablelist">
            <tr>
                <th class="th_hidden" colspan="6">DB Status</th>
            </tr>
            <?php $f = 0; ?>
            <?php foreach ($data->db_small_stat as $key => $value) { ?>
                <?php if ($f % 3 == 0) { ?>
                    <tr style="display: none;">
                <?php } ?>
                <td><?php echo str_replace('_',' ',ucfirst($key)); ?>
                    <div class="value_format"><?php echo $value; ?></div>
                </td>
                <?php if ($f % 3 == 2) { ?>
                    </tr>
                <?php } ?>
                <?php $f++; ?>
            <?php } ?>
        </table>
    <?php endif; ?>
    <?php if ($data->db_long_small_stat): ?>
        <table class="tablelist stat">
            <tr>
                <th class="th_hidden" colspan="6">DB Long Status</th>
            </tr>
            <?php $f = 0; ?>
            <?php foreach ($data->db_long_small_stat as $key => $value) { ?>
                <?php if ($f % 3 == 0) { ?>
                    <tr style="display: none;">
                <?php } ?>
                <td><?php echo str_replace('_',' ',ucfirst($key)); ?>
                    <div class="value_format"><?php echo $value; ?></div>
                </td>
                <?php if ($f % 3 == 2) { ?>
                    </tr>
                <?php } ?>
                <?php $f++; ?>
            <?php } ?>
        </table>
    <?php endif; ?>
    <div style="clear:both"></div>
</div>

<!--DB STAT-->
<div class="db_stat">
    <?php if ($data->db_stat): ?>
        <table class="tablelist stat">
            <tr>
                <th class="th_hidden" colspan="6">DB Full Status</th>
            </tr>
            <?php $f = 0; ?>
            <?php foreach ($data->db_stat as $key => $value) { ?>
                <?php if ($f % 3 == 0) { ?>
                    <tr style="display: none;">
                <?php } ?>
                <td><?php echo str_replace('_','_',ucfirst($key)); ?>
                    <div class="value_format"><?php echo $value; ?></div>
                </td>
                <?php if ($f % 3 == 2) { ?>
                    </tr>
                <?php } ?>
                <?php $f++; ?>
            <?php } ?>
        </table>
    <?php endif; ?>
    <?php if ($data->db_long_stat): ?>
        <table class="tablelist stat">
            <tr>
                <th class="th_hidden" colspan="6">DB Long Full Status</th>
            </tr>
            <?php $f = 0; ?>
            <?php foreach ($data->db_long_stat as $key => $value) { ?>
                <?php if ($f % 3 == 0) { ?>
                    <tr style="display: none;">
                <?php } ?>
                <td><?php echo str_replace('_','_',ucfirst($key)); ?>
                    <div class="value_format"><?php echo $value; ?></div>
                </td>
                <?php if ($f % 3 == 2) { ?>
                    </tr>
                <?php } ?>
                <?php $f++; ?>
            <?php } ?>
        </table>
    <?php endif; ?>
</div>

<!--DB TABLE STAT-->
<div class="db_table_stat">
    <?php if ($data->db_tables_size): ?>
        <table class="tablelist stat">
            <tr>
                <th class="th_hidden" colspan="<?php echo $tableColumn; ?>">DB Table Size(Mb) / Rows</th>
            </tr>
            <?php $f = 0; ?>
            <?php foreach ($data->db_tables_size as $key => $value) { ?>
                <?php if ($f % $tableColumn == 0) { ?>
                    <tr style="display: none;">
                <?php } ?>
                <td><?php echo $key; ?>
                    <div class="value_format"><?php echo $value . ' / ' . $data->db_tables_rows[$key]; ?></div>
                </td>
                <?php if ($f % $tableColumn == $tableColumn - 1) { ?>
                    </tr>
                <?php } ?>
                <?php $f++; ?>
            <?php } ?>
        </table>
    <?php endif; ?>
    <?php if ($data->db_long_tables_size): ?>
        <table class="tablelist stat">
            <tr>
                <th class="th_hidden" colspan="<?php echo $tableColumn; ?>">DB Long Table Size(Mb) / Rows</th>
            </tr>

            <?php $f = 0; ?>
            <?php foreach ($data->db_long_tables_size as $key => $value) { ?>
                <?php if ($f % $tableColumn == 0) { ?>
                    <tr style="display: none;">
                <?php } ?>
                <td><?php echo $key; ?>
                    <div class="value_format"><?php echo $value . ' / ' . $data->db_long_tables_rows[$key]; ?></div>
                </td>
                <?php if ($f % $tableColumn == $tableColumn - 1) { ?>
                    </tr>
                <?php } ?>
                <?php $f++; ?>
            <?php } ?>
        </table>
    <?php endif; ?>
</div>

<!--REPORT STATIONS-->
<div class="stations">
    <?php if ($data->stations): ?>
        <table class="tablelist stat">
            <tr>
                <th class="th_hidden" colspan="<?php echo $station_in_row+1; ?>">Stations</th>
            </tr>

            <?php $f = 0; ?>
            <?php foreach ($data->stations as $station_id => $station) { ?>
                <?php if ($f % $station_in_row == 0) { ?>
                    <tr style="display: none;">
                    <td>
                        <div class="station_name">Station name</div>
                        <div class="station_stat">
                            Station ID
                            <br> Logger Type
                            <br> Communication Type
                            <br> Message Interval
                            <br> Messages:
                            <br> &nbsp Expected - Received
                            <br> &nbsp Error / Not Processed
                            <br> &nbsp Processed
                            <br> &nbsp Percentage Processed
                            <br> Schedule
                            <br> &nbsp Synop | Scheduled vs Generated
                            <br> &nbsp BUFR | Scheduled vs Generated
                            <br> &nbsp METAR | Scheduled vs Generated
                            <br> &nbsp SPECI Generated
                            <br> Last BV (V)
                            <br> Last Message
                        </div>
                    </td>
                <?php } ?>
                <td>
                    <div class="station_name"><?php echo $station; ?></div>
                    <div class="station_stat">
                        <?php
                        echo
                            $station_id
                            .'<br>'. $data->stations_logger[$station_id]
                            .'<br>'. $data->stations_communication_type[$station_id]
                            .'<br>'. $data->stations_message_interval[$station_id]
                            .'<br>'
                            .'<br>'. $data->stations_message_expected[$station_id]
                            . ' - ' . $data->stations_message_count[$station_id] . ' = '
                                    .($data->stations_message_expected[$station_id]
                                        - $data->stations_message_count[$station_id])
                            .'<br>'. implode(array($data->stations_message_error[$station_id],$data->stations_message_is_processing[$station_id]), ' / ')
                            .'<br>'. ($processed = $data->stations_message_count[$station_id]
                                - $data->stations_message_error[$station_id]
                                - $data->stations_message_is_processing[$station_id])
                            .'<br>'. ($data->stations_message_count[$station_id] ?
                                ($processed . ' / ' . $data->stations_message_count[$station_id] . ' = ' .
                                    $processed / $data->stations_message_count[$station_id] * 100) .'%'
                                : 100 )
                            .'<br>'
                            .'<br>'. $data->stations_schedule_synop[$station_id]
                            .'<br>'. $data->stations_schedule_bufr[$station_id]
                            .'<br>'. $data->stations_schedule_metar[$station_id]
                            .'<br>'. $data->stations_schedule_speci[$station_id]
                            .'<br>'. $data->stations_sensor_bv[$station_id]
                            .'<br>'. $data->stations_message_last[$station_id]
                        ;
                        ?>
                    </div>
                </td>
                <?php if ($f % $station_in_row == $station_in_row - 1) { ?>
                    </tr>
                <?php } ?>
                <?php $f++; ?>
            <?php } ?>
        </table>
    <?php endif; ?>
</div>

</div>


<script>
    $(document).ready(function () {
        $("th.th_hidden").click(function () {
            if ($(this).parent().next().is(":hidden"))
                $(this).parent().nextAll().show();
            else
                $(this).parent().nextAll().hide();

        });
    });
</script>