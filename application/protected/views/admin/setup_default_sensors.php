<?php
/** @var $form DefaultSensorsForm */
?>


<div class="middlenarrow">
<h1>Default Sensors Parameters</h1><?php

if (!$form->handlers) {
    echo 'There are no any handlers registered in database.';
} else {
    echo CHtml::beginForm($this->createUrl('admin/SetupSensors'), 'post');?>

        <?php echo CHtml::errorSummary($form); ?>
        <table class="tablelist">
            <tr>
                <th style="width: 20px">&nbsp;</th>
                <th style="width: 50px">ID Prefix</th>
                <th>Handler</th>
                <th style="width: 50px">AWS Panel Limit</th>
                <th style="width: 30px">Tools</th>
            </tr>
            <?php $i=1; ?>
            <?php foreach ($form->handlers as $key => $value) {?>
                <tr>
                    <td><?php echo $i++; ?>.</td>
                    <td><?php echo $value->default_prefix?></td>
                    <td><?php echo $value->display_name?></td>
                    <td style="padding: 1px"><?php
                        $checkAws = $value->aws_station_uses;
                        echo CHtml::activeDropDownList($form, "handlers[$value->handler_id][aws_panel_show]",
                            $checkAws?$form->defListBox:array($form->defListBox[0]),
                            array('style' => 'width: 50px;',"disabled"=>$checkAws?'':"disabled"));?>
                    </td>

                    <td>
                        <a href="<?php echo $this->createUrl('admin/setupsensor', array('handler_id' => $value->handler_id))?>">Edit</a>
                    </td>
                </tr>
            <?php } ?>
            <?php foreach ($form->calculations as $key => $value) {?>
                <tr>
                    <td><?php echo $i++; ?>.</td>
                    <td><?php echo $value->default_prefix?></td>
                    <td>Calculation: <?php echo $value->display_name?></td>
                    <td style="padding: 1px"><?php
                        echo CHtml::activeDropDownList($form, "calculations[$value->handler_id][aws_panel_show]",
                                                       array($form->defListBox[0], $form->defListBox[1]),
                                                       array('style' => 'width: 50px;'));?>
                    </td>
                    <td></td>
                </tr>
            <?php } ?>
            </table>
        <div style="text-align: right;padding: 10px"><?php
            echo CHtml::submitButton(It::t('site_label', 'do_save'));?>
        </div><?php
    echo CHtml::endForm();
} ?>

</div>