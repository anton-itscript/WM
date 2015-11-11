

<div class="middlenarrow">
    <h1>
        <?php
        echo $access->id ? "Edit " . $access->action : "New Action";
        ?>
    </h1>

    <?php echo CHtml::beginForm($this->createUrl('superadmin/accessedit'), 'post'); ?>
    <input type="hidden" name="id" value="<?php echo $access->id?>" />
    <table class="formtable" style="float: left; width: 550px;" >
        <?php if ($access->id) { ?>
        <tr>
            <th><?php echo CHtml::activeLabel($access, 'id'); ?> <sup>*</sup></th>
            <td colspan="3">
                <b><?php echo $access->id; ?></b>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th style="width: 140px;"><?php echo CHtml::activeLabel($access, 'controller'); ?> <sup>*</sup></th>
            <td colspan="3">
                <?php echo CHtml::activeTextField($access, 'controller', array('style' => 'width: 300px;')); ?>
                <?php echo CHtml::error($access, 'controller'); ?>
            </td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($access, 'action'); ?> <sup>*</sup></th>
            <td colspan="3">
                <?php echo CHtml::activeTextField($access, 'action', array('style' => 'width: 300px;')); ?>
                <?php echo CHtml::error($access, 'action'); ?>
            </td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($access, 'description'); ?></th>
            <td colspan="3">
                <?php echo CHtml::activeTextarea($access, 'description', array('style' => 'width: 300px;resize:none;', 'rows' => '3', 'cols' => '20')); ?>
                <?php echo CHtml::error($access, 'description'); ?>
            </td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($access, 'enable'); ?> <sup>*</sup></th>
            <td colspan="3">
                <?php echo CHtml::activeDropDownList($access, 'enable', Yii::app()->params['enable'], array('style' => 'width: 270px;')); ?>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="3" style="text-align: left;"><?php echo CHtml::submitButton($access->id ? 'Update' : 'Add'); ?></td>
        </tr>
    </table>
    <div style="clear: both;"></div>
    <?php echo CHtml::endForm(); ?>
</div>