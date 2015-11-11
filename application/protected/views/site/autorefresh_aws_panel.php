<div class="middlewide">

    <div class="middlenarrow" style="padding: 2px 0">
        <div class="small" style="float:right; padding-top: 13px"><?php echo It::t('site_label', 'page_is_autorefreshing')?></div>
        <div>
            <?php $this->renderPartial('aws_panel_top', array('stationGroup' => $stationGroup), false, true); ?>
        </div>
    </div>
</div>

<div class="middlenarrow">
    <div id="autorefreshedPageError"></div>
</div>

<div class="spacer"></div>

<div id="autorefreshedPpage_5min">
   <?php $this->renderPartial($template, array('render_data' => $render_data)); ?>
</div>

<?php if (isset($pages) AND $pages->getPageCount() > 1){?>

    <div class="paginator" style="margin:10px; text-align: center">
        <?php $this->widget('CLinkPager',
            array(
                'pages' => $pages,
                'header' => '',
                'firstPageLabel' => '&nbsp;',
                'lastPageLabel' => '&nbsp;',
                'nextPageLabel' => '&rarr;',
                'prevPageLabel' => '&larr;'
            ));
        ?>
        <div class="clear"></div>
    </div>

<?php }?>