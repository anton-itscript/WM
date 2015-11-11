<div id="second_menu" >
    <ul>
        <?php foreach($data as $action => $item ):?>
            <?php  if(Yii::app()->user->ckAct($controller, $action)): ?>
                <?php  if(isset($item['label'])): ?>
                    <li>
                        <a class="<?php if (isset($item['active'])):?>active<?php endif ?>" href="<?php echo Yii::app()->createUrl($controller.'/'.$action) ?>">
                            <?php echo It::t('menu_label', $item['label']); ?>
                        </a>
                    </li>
                    <li class="delimiter">&nbsp;</li>
                <?php endif?>
            <?php endif?>
        <?php endforeach?>
    </ul>
</div>
