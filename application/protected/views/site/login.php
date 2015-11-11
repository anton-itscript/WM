<div class="middlenarrow">
    <h1>Login</h1>


    <?php echo CHtml::beginForm($this->createUrl('site/login'), 'post'); ?>

    <table class="formtable">
    <tr>
        <th><?php echo CHtml::activeLabel($form, 'username')?></th>
        <td><?php echo CHtml::activeTextField($form, 'username')?></td>
        <td><?php echo CHtml::error($form,'username'); ?></td>
    </tr>
    <tr>
        <th><?php echo CHtml::activeLabel($form, 'password')?></th>
        <td><?php echo CHtml::activePasswordField($form, 'password')?></td>
        <td><?php echo CHtml::error($form,'password'); ?></td>
    </tr>
    <tr class="bottom">
        <td><?php echo CHtml::activeCheckBox($form, 'rememberMe')?> <?php echo CHtml::activeLabel($form, 'rememberMe')?></td>
        <td><?php echo CHtml::submitButton('Login')?></td>
        <td>&nbsp;</td>
    </tr>
    </table>

    <?php echo CHtml::endForm(); ?>

</div><!-- div.middlenarrow-->