<div class="middlewide third_menu">
    <div class="middlenarrow">
        <div id="third_menu">
            <ul>
                <?php foreach($data['items'] as $action => $item ):?>
                    <?php if (isset($item['label'])):?>
                        <li>
                            <a class="<?php if (isset($item['active'])):?>active<?php endif ?>" href="<?php echo Yii::app()->createUrl($controller.'/'.$action) ?>">
                                <?php echo It::t('menu_label', $item['label']); ?>
                            </a>
                        </li>
                        <li class="delimiter">&nbsp;</li>
                    <?php endif?>
                <?php endforeach?>
            </ul>
            <div class="clear"></div>
        </div>
    </div>
</div>