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
        <table class="tablelist headers-centered">
            <tr>

                <th rowspan="2" ></th>
                <th rowspan="2" >ID Prefix</th>
                <th rowspan='2' >Handler</th>
                <th rowspan='2' >Unit</th>
                <th rowspan='2' >Description</th>
                <th colspan="3" >Filters & Alerts</th>
                <th rowspan='2' >Height<br>Depth<br>Ref</th>
                <th rowspan='2' >Report <br> Start <br> Time</th>
                <th rowspan='2' style="width: 50px">Panel Limit</th>
                <th rowspan='2' style="width: 30px">Tools</th>
            </tr>
            <tr>

                <th>T1></th>
                <th>T1< </th>
                <th>|T0-T1| ></th>
            </tr>

            <?php $ji=1; ?>
            <?php foreach ($form->handlers as $key => $value) {?>
                <tr>
                    <td  style="text-align: center" rowspan="<?=count($value['features'])?>" ><?php echo $ji++; ?>.</td>
                    <td  style="text-align: center" rowspan="<?=count($value['features'])?>" ><?php echo $value->default_prefix?></td>
                    <td  style="text-align: left" rowspan="<?=count($value['features'])?>" ><?php echo $value->display_name?></td>

                    <?php $i=0;foreach ($value['features'] as $feature) {

                        $additional_features = $form->getFeatureByFeatureCodeAndHandlerIdCode($value->handler_id_code,$feature->feature_code); ?>

                        <td><?=$feature['metric']->html_code ?></td>
                        <td style="text-align: left" ><?php  echo $additional_features['feature_name']?></td>
                        <?php if ($additional_features['exstra_features']==true) { ?>
                            <td colspan="3"></td>
                            <td style="text-align: right" ><?=It::getIntIfIsDecemal($feature->feature_constant_value) ?></td>
                        <?php } else { ?>
                            <td style="text-align: right" ><?=It::getIntIfIsDecemal($feature->filter_max) ?></td>
                            <td style="text-align: right" ><?=It::getIntIfIsDecemal($feature->filter_min)?></td>
                            <td style="text-align: right" ><?=It::getIntIfIsDecemal($feature->filter_diff) ?></td>
                            <td></td>
                        <?php }?>
                    <?php
                       break;
                    } ?>
                    <td rowspan="<?=count($value['features'])?>" style="text-align:center; vertical-align:middle">
                        <?php //echo CHtml::activeDropDownList($value, 'start_time', $form->arrh, array('style' => 'width: 25px;','disabled'=>'disabled')); ?>
                        <?= $value->start_time==-1 ? '' : $form->arrh[$value->start_time]?>
                    </td>

                    <td rowspan="<?=count($value['features'])?>" style="padding: 1px; text-align:center; vertical-align:middle"><?php
                        $checkAws = $value->aws_station_uses;
                        echo CHtml::activeDropDownList($form, "handlers[$value->handler_id][aws_panel_show]",
                            $checkAws?$form->defListBox:array($form->defListBox[0]),
                            array('style' => 'width: 50px;',"disabled"=>$checkAws?'':"disabled"));?>
                    </td>


                    <td rowspan="<?=count($value['features'])?>"  style="text-align:center; vertical-align:middle">
                        <a href="<?php echo $this->createUrl('admin/setupsensor', array('handler_id' => $value->handler_id))?>">Edit</a>
                    </td>
                </tr>
                <?php $i=0;foreach ($value['features'] as $feature) {
                    if($i==0) {
                        $i++;
                        continue;
                    }
                    $additional_features = $form->getFeatureByFeatureCodeAndHandlerIdCode($value->handler_id_code,$feature->feature_code);

                    ?>
                <tr>
                    <td><?=$feature['metric']->html_code ?></td>
                    <td style="text-align: left" ><?php  echo $additional_features['feature_name']?></td>
                    <?php if ($additional_features['exstra_features']==true) { ?>
                        <td colspan="3"></td>
                        <td style="text-align: right" ><?=It::getIntIfIsDecemal($feature->feature_constant_value) ?></td>
                    <?php } else { ?>
                        <td style="text-align: right" ><?=It::getIntIfIsDecemal($feature->filter_max) ?></td>
                        <td style="text-align: right" ><?=It::getIntIfIsDecemal($feature->filter_min) ?></td>
                        <td style="text-align: right" ><?=It::getIntIfIsDecemal($feature->filter_diff) ?></td>
                        <td></td>
                    <?php }?>
                </tr>

                <?php } ?>
            <?php } ?>
            <?php foreach ($form->calculations as $key => $value) {?>
                <tr>
                    <td style="text-align: center" ><?php echo $i++; ?>.</td>
                    <td style="text-align: center" ><?php echo $value->default_prefix?></td>
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
        <div style="text-align: right; padding: 10px 0 0 0"><?php
            echo CHtml::submitButton(It::t('site_label', 'do_save'));?>
        </div><?php
    echo CHtml::endForm();
} ?>

</div>