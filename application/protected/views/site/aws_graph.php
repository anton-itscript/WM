<?php
/**
 * @var AWSGraphForm $form
 * @var array $res
 */
?>

<script language="javascript" type="text/javascript">
    var feature_code = '<?php echo array_shift(array_keys($form->getSelectedGroupSensorFeatureCode())); ?>',
        config = JSON.parse('<?php echo json_encode(Config::get('SITE_AWSGRAPH')) ?>'),
        dataJs = JSON.parse('<?php echo json_encode($res) ?>');
</script>

<?php $this->renderPartial('__aws_graph_form', array('form' => $form)); ?>

<div class="middlenarrow">

    <?php if (!$res): ?>

        <?php if ($form->isPrepare()): ?>
            <h1><?php echo It::t('home_aws', 'graph__no_data')?></h1>
        <?php endif; ?>

    <?php else: ?>

        <div id="graph" style="height: 600px;"></div>

    <?php endif; ?>

</div>
