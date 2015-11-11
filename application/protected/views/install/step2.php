<?php 
	$this->renderPartial('steps', array('available_step' => $conf_form->available_step, 'current_step' => 2), false, true);
?>

<div class="middlenarrow">
    
    <h1>Step 2/4: Setup database</h1>
    
    <?php 
		echo CHtml::beginForm($this->createUrl('install/step2'), 'post'); 
		
		if ($conf_form->available_step > 3) 
		{
			echo 'Database and its tables were created already!<br /><br />';
			echo CHtml::submitButton('Re-create', array('name' => 'create_database'));
	?>
        <input type="button" value="Don't create again, Next" onclick="document.location.href='<?php echo $this->createUrl('install/step3'); ?>'" />
    <?php 
		}
		else 
		{
			echo 'Database and its tables were not created yet!<br /><br />';
			echo CHtml::submitButton('Create DB with tables',  array('name' => 'create_database'));
		} 
	
		echo CHtml::endForm(); 
	?>
</div>