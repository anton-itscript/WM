<div class="data_box pressure" style="height:auto">
    <div class="header">Water Level</div>
    <div class="content"  style="height:auto">
        <table>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td>Level</td>
            </tr>
        </table>

        <?php foreach($report['WaterLevel'] as $key => $value) { ?>
            <table>
                <tr>
                    <th>Last: </th>
                    <td <?php if (isset($value['last_filter_errors'])){?> title="<?php echo implode("; ", $value['last_filter_errors'])?>" <?php }?>>
                        <div class="cover <?php echo (isset($value['last_filter_errors']) ? 'error' : '')?>">
                            <div class="<?php echo $value['change']?>">
                               <?php echo $value['last']?>
                            </div>
                        </div>
                    </td>
                    <td><?php echo $value['metric_html_code']?></td>
                </tr>

            </table>
        <?php }?>
    </div>
</div>


