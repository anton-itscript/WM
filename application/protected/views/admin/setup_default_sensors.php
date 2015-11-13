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

                <th rowspan="2" ></th>
                <th rowspan="2" >ID Prefix</th>
                <th rowspan='2' >Handler</th>
                <th rowspan='2' >Unit</th>
                <th rowspan='2' >Description</th>
                <th colspan="3" >Filters & Alerts</th>
                <th rowspan='2' >Height/Depth/Ref</th>
                <th rowspan='2' >Report Start Time</th>
                <th rowspan='2' style="width: 50px">Panel Limit</th>
                <th rowspan='2' style="width: 30px">Tools</th>
            </tr>
            <tr>

                <th>T1< </th>
                <th>T1></th>
                <th>|T0-T1| ></th>
            </tr>

            <?php $i=1; ?>
            <?php foreach ($form->handlers as $key => $value) {?>
                <tr>
                    <td rowspan="<?=count($value['features'])?>" ><?php echo $i++; ?>.</td>
                    <td rowspan="<?=count($value['features'])?>" ><?php echo $value->default_prefix?></td>
                    <td rowspan="<?=count($value['features'])?>" ><?php echo $value->display_name?></td>

                    <?php $i=0;foreach ($value['features'] as $feature) {

                    ?>

                            <td><?=$feature['metric']->html_code ?></td>
                            <td><?=$feature->feature_code?></td>
                            <td><?=$feature->filter_max ?></td>
                            <td><?=$feature->filter_min ?></td>
                            <td><?=$feature->filter_diff ?></td>
                            <td><?=$feature->feature_constant_value ?></td>
                    <?php
                       break;
                    } ?>
                    <td rowspan="<?=count($value['features'])?>" >
                        <?php echo CHtml::activeDropDownList($value, 'start_time', $form->arrh, array('style' => 'width: 25px;')); ?>
                    </td>
                    <td rowspan="<?=count($value['features'])?>" style="padding: 1px"><?php
                        $checkAws = $value->aws_station_uses;
                        echo CHtml::activeDropDownList($form, "handlers[$value->handler_id][aws_panel_show]",
                            $checkAws?$form->defListBox:array($form->defListBox[0]),
                            array('style' => 'width: 50px;',"disabled"=>$checkAws?'':"disabled"));?>
                    </td>
                    <td rowspan="<?=count($value['features'])?>">
                        <a href="<?php echo $this->createUrl('admin/setupsensor', array('handler_id' => $value->handler_id))?>">Edit</a>
                    </td>
                </tr>
                <?php $i=0;foreach ($value['features'] as $feature) {
                    if($i==0) {
                        $i++;
                        continue;
                    }
                    ?>
                <tr>
                    <td><?=$feature['metric']->html_code ?></td>
                    <td><?=$feature->feature_code?></td>
                    <td><?=$feature->filter_max ?></td>
                    <td><?=$feature->filter_min ?></td>
                    <td><?=$feature->filter_diff ?></td>
                    <td><?=$feature->feature_constant_value ?></td>
                </tr>

                <?php } ?>
            <?php } ?>
            <?php foreach ($form->calculations as $key => $value) {?>
                <tr>
                    <td><?php echo $i++; ?>.</td>
                    <td><?php echo $value->default_prefix?></td>
                    <td>Calculation: <?php echo $value->display_name?></td>
                    <td><?=$value['metric']->html_code?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
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