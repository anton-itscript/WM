<?php Yii::app()->clientScript->registerScriptFile(It::baseUrl() . '/js/schedule_work.js'); ?>
<div class="middlewide">
    <div class="middlenarrow">
        <h1><a href="<?php echo $this->createUrl('site/schedule') ?>">Schedule Report Generation</a> / Report <?=$schedule['report']['schedule_id']?> </h1>

        <table class="formtable">
            <tr>
                <th style="text-align: right;">Report:</th>
                <td><?php echo strtoupper($schedule['report']['report_type']) ?></td>

                <td>&nbsp;&nbsp;</td>

                <th style="text-align: right;">Period:</th>
                <td><?php echo Yii::app()->params['schedule_generation_period'][$schedule['report']['period']] ?></td>
            </tr>
            <tr>

                <th style="text-align: right;">Format:</th>
                <td><?php echo $schedule['report']['report_format'] ?></td>

                <td>&nbsp;&nbsp;</td>

                <th style="text-align: right;">Destinations:</th>
                <td>
                    <?php
                    if (count($schedule['report']['destinations']) === 0) {
                        echo 'Just in History';
                    } else {
                        foreach ($schedule['report']['destinations'] as $key => $destination) {
                            echo ($key + 1) . '.';

                            if ($destination['method'] === 'mail') {
                                echo $destination['destination_email'];
                            } else if ($destination['method'] === 'ftp') {
                                echo $destination['destination_ip'] . ':' . $destination['destination_ip_port'];
                            } else {
                                echo Yii::app()->user->getSetting('scheduled_reports_path') . DIRECTORY_SEPARATOR . $destination['destination_local_folder'];
                            }

                            echo '<br />';
                        }
                    }
                    ?>
                </td>
            </tr>
        </table>

    </div>

</div>
<div class="middlenarrow">
    <br/>
    <h2>Stations</h2>
    <table class="tablelist ">
        <tbody>
        <tr>
            <th>Station</th>
            <th>Station Timezone</th>
            <th>View history</th>
        </tr>
        <?php foreach ($schedule['report']['realStation'] as $key => $station): ?>
            <tr>
                <td><?= $station['station_id_code'] ?></td>
                <td><?= $station['timezone_id'] ?> (GMT <?= $station['timezone_offset'] ?>)</td>
                <td>
                    <a href="<?php echo $this->createUrl('site/schedulestationhistory', array('station_to_report_id' => $schedule['report']['stations'][$key]['id'])); ?>"><?php echo It::t('site_label', 'do_history'); ?></a>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
