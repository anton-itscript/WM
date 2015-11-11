<?php
/** @var array|StationGroup[] $stationGroup */
?>
<div class="middlenarrow awspanel">
    <?php echo CHtml::beginForm($this->createUrl('site/awspanel'), 'get');?>
        <table class="info">
            <tr>
                <th class="blue">Station Group: &nbsp;&nbsp;</th>
                <td>
                    <select id="select1" name="group_id" onchange="this.form.submit();">
                        <option value="-1">All Stations</option>
                        <?php foreach ($stationGroup as $group_id => $group) { ?>
                            <option value="<?php echo $group_id; ?>" <?php echo ($group_id == $_GET['group_id'] ? 'selected' : ''); ?>><?php echo $group->name; ?></option>
                        <?php } ?>
                    </select>
                </td>
                <th>&nbsp;&nbsp;Stations per Table: &nbsp;&nbsp;</th>
                <td>
                    <select id="select" name="tableSize" onchange="this.form.submit();">
                        <?php $tableSizeArray = array('12','10','8','6'); ?>
                        <?php foreach ($tableSizeArray as $size){ ?>
                            <option value="<?php echo $size; ?>" <?php echo ($size == $_GET['tableSize'] || (!isset($_GET['tableSize']) AND $size == 10)? 'selected' : ''); ?>><?php echo $size; ?></option>
                        <?php } ?>
                    </select>
                </td>
                <th>&nbsp;&nbsp;Tables per Page: &nbsp;&nbsp;</th>
                <td>
                    <select id="select" name="tableCount" onchange="this.form.submit();">
                        <?php $tableSizeArray = array('4','3','2','1'); ?>
                        <?php foreach ($tableSizeArray as $size) { ?>
                            <option value="<?php echo $size; ?>" <?php echo ($size == $_GET['tableCount'] || (!isset($_GET['tableCount']) AND $size == 2) ? 'selected' : ''); ?>><?php echo $size; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
        </table>
    <?php CHtml::endForm(); ?>

</div>