<?php
$countReport = count($reports);
$countColumn = $countReport > 6 ? 6 : $countReport;
$countColumn = $countReport == 0 ? 1 : $countColumn;
$flag = 0;
?>

<div class="middlenarrow heartbeat">
    <div class="now_report">
        <?php echo CHtml::link('NOW(for 1 day)', array('HeartbeatReport')); ?>
    </div>

    <h1>Heartbeat Reports</h1>

    <table class="tablelist">
        <tr>
            <?php for ($i = 0; $i < $countColumn; $i++) { ?>
                <th>Created</th>
            <?php } ?>

        </tr>
        <tr>
            <?php foreach ($reports as $report_id => $report) {
                if ($flag % $countColumn == 0) echo '<tr>';?>
                <td>
                <?php echo CHtml::link(substr($report->created, 0, 16), array(
                    'HeartbeatReport', 'report_id' => $report_id
                ));?>
                </td><?php

                if ($flag % $countColumn + 1 == $countColumn) {
                    echo '</tr>';
                }
                $flag++;
            }
            ?>
    </table>
</div>

<?php //if(isset($pages)){?>

    <div class="paginator" style="margin:10px; text-align: center">
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

<?php //}?>