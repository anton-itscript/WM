<?php
	$this->renderPartial('steps', array('available_step' => $conf_form->available_step, 'current_step' => 3), false, true);
?>

<div class="middlenarrow">
	<h1>Step 3/4: Check Following Paths</h1> 
    
    <?php echo CHtml::beginForm($this->createUrl('install/step3'), 'post'); ?>
    <?php echo CHtml::errorSummary($conf_form); ?>   
	
    <table class="formtable">
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'php_exe_path')?></th>
            <td><?php echo CHtml::activeTextField($conf_form, 'php_exe_path', array('style' => 'width: 400px;'))?></td>
            <td><?php echo CHtml::error($conf_form,'php_exe_path'); ?></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'mysqldump_exe_path')?></th>
            <td><?php echo CHtml::activeTextField($conf_form, 'mysqldump_exe_path', array('style' => 'width: 400px;'))?></td>
            <td><?php echo CHtml::error($conf_form,'mysqldump_exe_path'); ?></td>
        </tr>  
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'mysqld_exe_path')?></th>
            <td><?php echo CHtml::activeTextField($conf_form, 'mysql_exe_path', array('style' => 'width: 400px;'))?></td>
            <td><?php echo CHtml::error($conf_form,'mysql_exe_path'); ?></td>
        </tr>          
        <tr>
            <th><?php echo CHtml::activeLabel($conf_form, 'site_url_for_console')?></th>
            <td><?php echo CHtml::activeTextField($conf_form, 'site_url_for_console', array('style' => 'width: 400px;'))?></td>
            <td><?php echo CHtml::error($conf_form,'site_url_for_console'); ?> (will be used in letter's body)</td>
        </tr>        
        
        <tr>
            <td colspan="3">
                <?php echo CHtml::submitButton('Change',  array('name' => 'save_path'))?>
                &nbsp;&nbsp;
                <input type="button" value="Do not change, Next" onclick="document.location.href='<?php echo $this->createUrl('install/step4')?>'" />
            </td>
        </tr>
    </tr>    
    </table>    
	
    <?php echo CHtml::endForm(); ?>  
</div>