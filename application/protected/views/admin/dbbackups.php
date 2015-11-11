

<div class="middlenarrow">
	<h1>Last DB Backups</h1>

	<?php 
		if (!$backups) 
		{
	?>		
			There are no any backups yet.
	<?php  
		}
		else
		{
	?>	
		<table class="tablelist">
			<tr>
				<th>Backup Name</th>
				<th>Created</th>
				<th>Tools</th>        
			</tr>
			
			<?php 
				foreach ($backups as $backup) 
				{	
			?>
			<tr>
				<td><?php echo $backup['filename']; ?></td>
				<td><?php echo date('Y-m-d H:i:s', $backup['created'])?></td>
				<td>
					<a href="<?php echo $this->createUrl('admin/dbbackup', array('apply' => $backup['filename'])); ?>" onclick="return confirm('Are you sure you want to apply <?php echo $backup['filename']; ?>?')">Apply</a>
					&nbsp;&nbsp;
					<a href="<?php echo $this->createUrl('admin/dbbackup', array('delete' => $backup['filename'])); ?>" onclick="return confirm('Are you sure you want to delete <?php echo $backup['filename']; ?>?')">Delete</a>
				</td>
			</tr>    
			<?php 
				}
			?>    
		</table>
	<?php  
		}
	?>

	<br />
	<input type="button" name="create_backup" value="Create Fresh Backup Now" onclick="document.location.href='<?php echo $this->createUrl('admin/dbbackup', array('create' => 1)) ?>'" />
</div>