
<?php 
	if ($status_code) 
	{
		print "<div class='middlenarrow'>";
		
		foreach ($status_code as $key => $value) 
		{
			print "<div class='status_success'>".Yii::t('statuscode', $value, array(), null, 'en')."</div>";
		}
		
		print "</div>";
	}
?>