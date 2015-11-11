<?php
    $stations   = $form->stations;
    $groups     = $form->groups;
    $data       = $form->data;
?>


<div class="middlenarrow grouplist">
    <h1>Station Group</h1><?php
    echo CHtml::beginForm($this->createUrl('admin/stationgroups'), 'post');
    echo CHtml::activeLabel($group, 'group_name');
    echo CHtml::activeHiddenField($group,'group_id');
    echo CHtml::activeTextField($group,'name', array('style' => 'width: 80px;'));
    echo CHtml::submitButton(isset($group->group_id)?'Update':'Add');
    ?><br /><br /><?php
    echo CHtml::error($group,'name');
    echo CHtml::endForm();

    echo CHtml::beginForm($this->createUrl('admin/stationgroups'), 'post');
    if (count($groups) > 0 AND count($stations) > 0) :?>
        <table class="tablelist">
            <tr>
                <th class="stations" style="border-left: 1px solid #ead6ae !important;">Stations</th>
                <?php foreach ($groups as $group){ ?>
                    <th>
                        <?php echo $group['name']; ?>
                    </th>
                <?php } ?>
            </tr>
            <?php foreach ($stations as $station_id => $station){ ?>
                <tr class="check">
                    <td class="stations"><?php echo $station->station_id_code; ?></td>
                    <?php foreach ($groups as $group_id => $group){?>
                        <td>
                            <?php
                            echo CHtml::activeCheckBox($form,"data[$station_id][$group_id]",array('checked'=>isset($data[$station_id][$group_id])?'checked':''))
                            ?>
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
            <tr class="edit">
                <td></td><?php
                foreach ($groups as $group_id => $group){?>
                    <td><?php
                        echo CHtml::link(
                            'Delete',
                            array('admin/stationgroups', 'group_id' => $group_id,'action' => 'delete'));
                        ?><br><?php
                        echo CHtml::link(
                            'Update',
                            array('admin/stationgroups', 'group_id' => $group_id));?>
                    </td>
                <?php } ?>
            </tr>
            <tr class="save edit">
                <td colspan="<?php echo count($groups)+1; ?>">
                    <input type="submit" name="save" value="Save" />
                </td>
            </tr>
        </table>
    <?php
    endif;
    echo CHtml::endForm();?>
</div>