
<div class="middlenarrow">
    <h1>Check Email Sending</h1>
    
    <?php echo CHtml::beginForm($this->createUrl('admin/checkmailing'), 'post'); ?>
    
    <table class="formtable">
    <tr>
        <th><?php echo CHtml::activeLabel($form, 'email')?></th>
        <td><?php echo CHtml::activeTextField($form, 'email', array('style' => 'width: 400px;'))?></td>
        <td><?php echo CHtml::error($form,'email'); ?></td>
    </tr>
    <tr>
        <th><?php echo CHtml::activeLabel($form, 'subject')?></th>
        <td><?php echo CHtml::activeTextField($form, 'subject', array('style' => 'width: 400px;'))?></td>
        <td><?php echo CHtml::error($form,'subject'); ?></td>
    </tr>
    <tr>
        <th><?php echo CHtml::activeLabel($form, 'message')?></th>
        <td><?php echo CHtml::activeTextArea($form, 'message', array('style' => 'width: 400px; height: 200px;'))?></td>
        <td><?php echo CHtml::error($form,'message'); ?></td>
    </tr>    
    <tr class="bottom">
        <td>&nbsp;</td>
        <td><?php echo CHtml::submitButton('Send')?></td>
        <td>&nbsp;</td>
    </tr>    
    </table>    
    <?php echo CHtml::endForm(); ?> 
    
</div>