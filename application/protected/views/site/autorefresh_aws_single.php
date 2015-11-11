
<div id="autorefreshedPageError"></div>

<?php 
	if (!$render_data['stations']) { ?>
        <div class="middlenarrow">
            <div class="spacer"></div>
            <?php echo It::t('home_aws', 'single__no_aws_stations'); ?>
        </div>
    <?php } else { ?>
        <div id="autorefreshedPpage_5min">
            <?php $this->renderPartial('aws_single', array('render_data' => $render_data)); ?>
        </div>
<?php }?>