<?php
/** @var AWSTableForm $form */
/** @var array $res */
/** @var int $show_station */

$this->widget('TwoDatesFilter', array('block_path' => '#filterparams', 'date_from_name' => 'AWSTableForm[date_from]', 'date_to_name' => 'AWSTableForm[date_to]'));

?>

<?php $this->renderPartial('__aws_table_form', array('form' => $form)); ?>


<br/><br/>
<div class="middlenarrow">

    <?php if (!$res['prepared_data']): ?>

        <?php if ($form->isPrepare()): ?>
            <h1><?php echo It::t('home_aws', 'table__no_data')?></h1>
        <?php endif; ?>

    <?php else: ?>
        <script type="text/javascript">
            var tabs_loaded = [];
            <?php for ($i = 0; $i < count($form->station_id); $i++) { ?>
                tabs_loaded[<?php echo $i?>] = {station_id: <?php echo $form->station_id[$i]?>, loaded: <?php echo ($i ? 'false' : 'true')?>};
            <?php } ?>
            tabs_loaded[<?php echo count($form->station_id); ?>] = {station_id: -1, loaded: false};
        </script>
        <div>
            <!-- Tabs -->
            <div class="awstable_tabs">
                <?php $first = true;?>
                <?php foreach ($form->station_id as $station_id) {?>
                    <div class="<?php echo $first ? 'active' . $first=false : ''; ?>"><?php echo $form->getStationsList()[$station_id]; ?></div>
                <?php }?>
                <div>All stations</div>
            </div>

            <div class="clear"></div>
            <?php $first = true;?>
            <?php foreach ($form->station_id as $station_id) {?>
                <div class="awstable_block" style="<?php echo $first ? $first=false : 'display:none;'; ?>">
                    <?php if ($station_id == $show_station) {?>
                        <?php $this->renderPartial('__aws_table_part', ['form' => $form, 'res' => $res, 'show_station' => $show_station]); ?>
                    <?php }?>
                </div>
            <?php }?>

            <div class="awstable_block" style="display:none;"></div>
        </div>
    <?php endif; ?>
</div>