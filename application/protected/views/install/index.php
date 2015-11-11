<?php 
	$this->renderPartial('steps', array('available_step' => $conf_form->available_step, 'current_step' => 1), false, true); 
?>

<div class="middlenarrow">   
    <h1>Step 1/4: Database Connection</h1>
    

	<blockquote class="tip">
		<p>Attention! Be sure your DB Name - is name of new or not existed database. Script will create it again during installation.</p>    
    </blockquote>

    <div style="float: left; width: 50%">
        <?php echo CHtml::beginForm($this->createUrl('install/index'), 'post'); ?>
        <?php echo CHtml::errorSummary($conf_form); ?>
        <table class="formtable">
            <tr>
                <th></th>
                <td><b><?php echo CHtml::activeLabel($conf_form, 'dbshort')?></b></td>
            </tr>
            <tr>
                <th><?php echo CHtml::activeLabel($conf_form, 'host')?></th>
                <td><?php echo CHtml::activeTextField($conf_form, 'host')?></td>
                <td><?php echo CHtml::error($conf_form,'host'); ?></td>
            </tr>
            <tr>
                <th><?php echo CHtml::activeLabel($conf_form, 'port')?></th>
                <td><?php echo CHtml::activeTextField($conf_form, 'port')?></td>
                <td><?php echo CHtml::error($conf_form,'port'); ?></td>
            </tr>
            <tr>
                <th><?php echo CHtml::activeLabel($conf_form, 'user')?></th>
                <td><?php echo CHtml::activeTextField($conf_form, 'user')?></td>
                <td><?php echo CHtml::error($conf_form,'user'); ?></td>
            </tr>
            <tr>
                <th><?php echo CHtml::activeLabel($conf_form, 'password')?></th>
                <td><?php echo CHtml::activePasswordField($conf_form, 'password')?></td>
                <td><?php echo CHtml::error($conf_form,'password'); ?></td>
            </tr>
            <tr>
                <th><?php echo CHtml::activeLabel($conf_form, 'dbname')?></th>
                <td><?php echo CHtml::activeTextField($conf_form, 'dbname')?></td>
                <td><?php echo CHtml::error($conf_form,'dbname'); ?></td>
            </tr>
        </table>
        <br/><br/>
        <?php echo CHtml::submitButton('Save', array('name' => 'save_db_config'))?>
        <?php echo CHtml::endForm(); ?>
    </div>
    <div style="float: right; width: 50%">
        <?php echo CHtml::beginForm($this->createUrl('install/index'), 'post'); ?>
        <?php echo CHtml::errorSummary($conf_form_long); ?>
        <table class="formtable">
            <tr>
                <th></th>
                <td><b><?php echo CHtml::activeLabel($conf_form_long, 'dblong')?></b></td>
            </tr>            <tr>
                <th><?php echo CHtml::activeLabel($conf_form_long, 'host')?></th>
                <td><?php echo CHtml::activeTextField($conf_form_long, 'host')?></td>
                <td><?php echo CHtml::error($conf_form_long,'host'); ?></td>
            </tr>
            <tr>
                <th><?php echo CHtml::activeLabel($conf_form_long, 'port')?></th>
                <td><?php echo CHtml::activeTextField($conf_form_long, 'port')?></td>
                <td><?php echo CHtml::error($conf_form_long,'port'); ?></td>
            </tr>
            <tr>
                <th><?php echo CHtml::activeLabel($conf_form_long, 'user')?></th>
                <td><?php echo CHtml::activeTextField($conf_form_long, 'user')?></td>
                <td><?php echo CHtml::error($conf_form_long,'user'); ?></td>
            </tr>
            <tr>
                <th><?php echo CHtml::activeLabel($conf_form_long, 'password')?></th>
                <td><?php echo CHtml::activePasswordField($conf_form_long, 'password')?></td>
                <td><?php echo CHtml::error($conf_form_long,'password'); ?></td>
            </tr>
            <tr>
                <th><?php echo CHtml::activeLabel($conf_form_long, 'dbname')?></th>
                <td><?php echo CHtml::activeTextField($conf_form_long, 'dbname')?></td>
                <td><?php echo CHtml::error($conf_form_long,'dbname'); ?></td>
            </tr>
        </table>
        <br/><br/>
        <?php echo CHtml::submitButton('Save', array('name' => 'save_db_config_long'))?>
        <?php echo CHtml::endForm(); ?>
    </div>

	<?php if ($conf_form->available_step > 2) : ?>
        <input type="button" value="Do not change, Next" onclick="document.location.href='<?php echo $this->createUrl('install/step3'); ?>'" />
    <?php endif; ?>
    

</div>