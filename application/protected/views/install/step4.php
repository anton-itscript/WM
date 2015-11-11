<?php $this->renderPartial('steps', array('available_step' => $conf_form->available_step, 'current_step' => 4),false, true); ?>

<div class="middlenarrow">
    
    <h1>Step 4/4: Schedule</h1>
    
	<?php echo CHtml::beginForm($this->createUrl('install/step4'), 'post'); ?>
    <?php echo CHtml::errorSummary($conf_form); ?>   
    <table class="formtable">
        <tr>
            <th>DB Backup JobId</th>
            <td><?php echo $conf_form->db_backup_id; ?> </td>
            <td>
				(<?php 
					if ($conf_form->checkTask($conf_form->db_backup_id)) 
					{
						?><font color="green">Added to Schedule successfully</font><?php
					}
					else 
					{
						?><font color="red">Not scheduled yet</font><?php
					}
				?>)
			</td>
        </tr>
        <tr>
            <th>Backup Old Data JobId</th>
            <td><?php echo $conf_form->backup_process_id; ?> </td>
            <td>
				(<?php 
					if ($conf_form->checkTask($conf_form->backup_process_id)) 
					{
						?><font color="green">Added to Schedule successfully</font><?php
					}
					else 
					{
						?><font color="red">Not scheduled yet</font><?php
					}
				?>)
			</td>
        </tr>        
        <tr>
            <th>Check Listening Processes JobId</th>
            <td><?php echo $conf_form->check_processes_process_id; ?> </td>
            <td>
				(<?php 
					if ($conf_form->checkTask($conf_form->check_processes_process_id)) 
					{
						?><font color="green">Added to Schedule successfully</font><?php
					}
					else 
					{
						?><font color="red">Not scheduled yet</font><?php
					}
				?>)
			</td>
        </tr>
        <tr>
            <th>Process Received Messages JobId</th>
            <td><?php echo $conf_form->each_minute_prepare_process_id; ?> </td>
            <td>
				(<?php 
					if ($conf_form->checkTask($conf_form->each_minute_prepare_process_id)) 
					{
						?><font color="green">Added to Schedule successfully</font><?php
					}
					else 
					{
						?><font color="red">Not scheduled yet</font><?php
					}
				?>)
			</td>
        </tr>         
        <tr>
            <th>Prepare Report generation JobId</th>
            <td><?php echo $conf_form->each_minute_process_id; ?> </td>
            <td>
				(<?php 
					if ($conf_form->checkTask($conf_form->each_minute_process_id)) 
					{
						?><font color="green">Added to Schedule successfully</font><?php
					}
					else 
					{
						?><font color="red">Not scheduled yet</font><?php
					}
				?>)
			</td>
        </tr>
        <tr>
            <th>Grab XMLs messages JobId</th>
            <td><?php echo $conf_form->get_xml_process_id; ?> </td>
            <td>
				(<?php 
					if ($conf_form->checkTask($conf_form->get_xml_process_id)) 
					{
						?><font color="green">Added to Schedule successfully</font><?php
					}
					else 
					{
						?><font color="red">Not scheduled yet</font><?php
					}
				?>)
			</td>
        </tr>
        <tr>
            <th>Sync short and long DB</th>
            <td><?php echo $conf_form_long->sync_id; ?> </td>
            <td>
				(<?php
					if ($conf_form->checkTask($conf_form_long->sync_id))
					{
						?><font color="green">Added to Schedule successfully</font><?php
					}
					else
					{
						?><font color="red">Not scheduled yet</font><?php
					}
				?>)
			</td>
        </tr>
        <tr>
            <th>DB Backup Long DB</th>
            <td><?php echo $conf_form_long->db_backup_id; ?> </td>
            <td>
				(<?php
					if ($conf_form->checkTask($conf_form_long->db_backup_id))
					{
						?><font color="green">Added to Schedule successfully</font><?php
					}
					else
					{
						?><font color="red">Not scheduled yet</font><?php
					}
				?>)
			</td>
        </tr>
        <tr>
            <th>Heartbeat Report</th>
            <td><?php echo $conf_form_long->heartbeat_id; ?> </td>
            <td>
				(<?php
					if ($conf_form->checkTask($conf_form_long->heartbeat_id))
					{
						?><font color="green">Added to Schedule successfully</font><?php
					}
					else
					{
						?><font color="red">Not scheduled yet</font><?php
					}
				?>)
			</td>
        </tr>

        <?php if ($conf_form->available_step > 4) {?>
        <tr>
            <td colspan="3">
                <h1>Installation was completed! </h1>
                <input type="button" value="Enjoy service" onclick="document.location.href='<?php echo It::baseUrl()?>'" />
            </td>
        </tr>        
        <?php } else {?>
        <tr>
            <td colspan="3"><?php echo CHtml::submitButton('Add to Schedule',  array('name' => 'schedule'))?></td>
        </tr>
        <?php }?>
    </tr>    
    </table>    
    <?php echo CHtml::endForm(); ?>
    
</div>