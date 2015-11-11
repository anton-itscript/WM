<?php
/** @var AWSTableForm $form */
$this->widget('TwoDatesFilter', array('block_path' => '#filterparams', 'date_from_name' => 'AWSTableForm[date_from]', 'date_to_name' => 'AWSTableForm[date_to]'));

?>

<div class="middlewide">
    <div id = 'blockLargeInfoStation'></div>
    <div class="middlenarrow">
        <?php echo CHtml::errorSummary($form)?>
        <?php echo CHtml::beginForm($this->createUrl('site/awstable'), 'post'); ?>
        <table class="formtable awsstable" id="filterparams" >
            <tr>
                <th><?php echo CHtml::activeLabel($form, 'station_id')?></th>
                <th><?php echo CHtml::activeLabel($form, 'sensor_feature_code')?></th>
                <th><?php echo CHtml::activeLabel($form, 'accumulation_period', ['style' => 'display: none;'])?></th>
                <th><?php echo CHtml::activeLabel($form, 'date_from')?></th>
                <th><?php echo CHtml::activeLabel($form, 'time_from')?></th>
                <th><?php echo CHtml::activeLabel($form, 'date_to')?></th>
                <th><?php echo CHtml::activeLabel($form, 'time_to')?></th>
                <th></th>
            </tr>
            <tr>
                <td>
                    <?php if ($form->getStationsList()) {?>
                        <div class="select-list">
                            <?php echo CHtml::button(It::t('site_label', 'do_select'), array('id' => 'station_select', 'style' => 'width: 140px'))?>
                            <div class="select-option" id="station-select-list" style="display: none;">
                                <?php echo CHtml::activeCheckBoxList(
                                    $form, 'station_id', $form->getStationsList(),
                                    array('separator'=>'','template'=>'<li>{input} {label}</li>', 'container' => 'ul class="select-ul column-4"', 'checkAll' => 'Select All')
                                ) ?>
                            </div>
                        </div>
                    <?php } else {?>
                        <?php echo It::t('site_label', 'no_aws_stations')?>
                    <?php }?>
                </td>
                <td>
                    <div class="select-list">
                        <?php echo CHtml::button(It::t('site_label', 'do_select'), array('id' => 'feature_select', 'style' => 'width: 140px'))?>
                        <div class="select-option" id="handler-select-list"  style="display: none;">
                            <ul class="select-ul  column-4">
                                <?php ?>
                                <?php foreach ($form->getGroupSensorsFeaturesList() as $group_key => $group):  ?>

                                    <li  class="<?=$group['class']?> hide"  style="display:none;">
                                        <ul>

                                            <?php if (!in_array($group_key,['TemperatureWater', 'TemperatureSoil'])): ?>
                                                <li class="head"><?php echo $group['name'] ?></li>
                                            <?php endif; ?>

                                            <li  class="head-colors">
                                                <?php
                                                if (count($group['stations']) )
                                                    foreach ($group['stations'] as $station ) {
                                                        ?>
                                                        <div   class="<?=$station['station_id']?>-station"  style="display: none; background-color:<?=$station['color']?>; width:5px; height:5px; float:left; margin-left:2px; "></div>
                                                        <?php
                                                    }
                                                ?>
                                            </li>
                                            <?php /* if (!in_array($group_key,['TemperatureWater', 'TemperatureSoil'])): ?>
                                                <li class="head"><?php echo $group['name'] ?></li>
                                            <?php endif;*/?>

                                            <?php echo CHtml::activeCheckBoxList(
                                                $form, 'sensor_feature_code[' . $group_key . ']', $group['sensor_features'],
                                                array('separator'=>'','template'=>'<li>{input} {label}</li>', 'container' => '', 'checkAll' => count($group['sensor_features']) > 1 ? 'Select All' : null)
                                            ) ?>
                                        </ul>
                                    </li>
                                <?php endforeach; ?>
                                <li id="station-feature-attention" class="hide">Please select station(s) first</li>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="select-list">
                        <?php echo CHtml::button(It::t('site_label', 'do_select'), array('id' => 'accumulation_select', 'style' => 'width: 100px; display: none;'))?>
                        <div class="select-option" style="display: none;">
                            <h3>This only applies to Rain, Sun Radiation or Sun Duration.</h3>
                            <?php echo CHtml::activeRadioButtonList(
                                $form, 'accumulation_period', $form->getAccumulationList(),
                                array('separator'=>'','template'=>'<li>{input} {label}</li>', 'container' => 'ul class="select-ul column-8"')
                            ) ?>
                        </div>
                    </div>
                </td>
                <td>
                    <?php echo CHtml::activeTextField($form, 'date_from', array('class' => 'date-pick input-calendar', 'style' => 'width: 80px'))?>
                    <div class="clear"></div>
                    <?php echo CHtml::error($form,'date_from'); ?>

                </td>
                <td>
                    <?php echo CHtml::activeTextField($form, 'time_from', array('style' => 'width: 50px;'))?>
                    <?php echo CHtml::error($form,'time_from'); ?>

                </td>
                <td>
                    <?php echo CHtml::activeTextField($form, 'date_to', array('class' => 'date-pick input-calendar', 'style' => 'width: 80px'))?>
                    <div class="clear"></div>
                    <?php echo CHtml::error($form,'date_to'); ?>
                </td>
                <td>
                    <?php echo CHtml::activeTextField($form, 'time_to', array('style' => 'width: 50px;'))?>
                    <?php echo CHtml::error($form,'time_to'); ?>
                </td>
                <td>
                    <?php echo CHtml::submitButton(It::t('site_label', 'do_filter'), array('name' => 'filter'))?>
                    <?php echo CHtml::submitButton(It::t('site_label', 'do_reset'), array('name' => 'clear'))?>
                    <?php echo CHtml::submitButton(It::t('site_label', 'do_export'), array('name' => 'export', 'onclick' => 'return confirm("Exporting data takes time. Please be patient.")'))?>
                </td>
            </tr>
        </table>
        <?php echo CHtml::endForm(); ?>
        <div class="spacer"></div>
    </div>
</div>