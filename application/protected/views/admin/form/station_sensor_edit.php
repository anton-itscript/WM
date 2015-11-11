<?php
/**
 * @var StationSensorEditForm $form
 */
?>
<h2><?php echo $form->handler_name ?></h2>

<?php echo CHtml::beginForm() ?>

    <?php echo CHtml::activeHiddenField($form,'sensor_id')?>
    <?php echo CHtml::activeHiddenField($form,'handler_name')?>

    <?php echo CHtml::activeLabel($form, 'sensor_name') ?>
    <?php echo CHtml::activeTextField($form, 'sensor_name', array('style' => 'width: 290px;')) ?>
    <?php echo CHtml::error($form,'sensor_name') ?>

    <?php if ($form->constant): ?>
        <?php foreach($form->constant as $id => $constant) {?>
            <?php echo CHtml::activeHiddenField($form,"constant[$id][name]") ?>
            <?php echo CHtml::activeHiddenField($form,"constant[$id][metric]") ?>

            <?php echo CHtml::label(
                "{$form->constant[$id]['name']} " . ($form->constant[$id]['metric']?"({$form->constant[$id]['metric']})":''),
                "StationSensorEditForm[constant][$id][value]",
                ['class' => $form->hasErrors("constant[$id]") ? CHtml::$errorCss : '']) ?>
            <?php echo CHtml::activeTextField($form, "constant[$id][value]", array('style' => 'width: 296px;')) ?>
            <?php echo CHtml::error($form,"constant[$id]") ?>
        <?php }?>
    <?php endif ?>

<?php echo CHtml::endForm() ?>
