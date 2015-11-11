
<div class="middlenarrow">
<h1>Connections</h1>

<?php if (!$connections) {?>
    There are no any stations registered at the system.

<?php }else{?>

    <blockquote class="tip">
        <p>Click the "Start" button to start listening to a COM port or IP address. Click "Stop" to stop listening.</p>
    </blockquote>
    <?php  $i=0; ?>
    
    <table class="tablelist" width="100%">
    <tr>
        <th>Listen to</th>
        <th>Communication Type</th>
        <th>Stations sending messages<br/>via this connection</th>
        <th>Last Status</th>
        <th>Tools</th>
    </tr>
    <?php 
		foreach ($connections as $key => $params) 
		{
			$connection_type = str_replace(array(':', '.'), array('__','_'), $params['connection_type']);
            $communication_type = $connections[$key]['communication_type'];
		?>
    <tr class="<?php echo fmod($i,2) == 0 ? 'c' : ''?>" >
       <td><b><?php echo (CHtml::encode($key)); ?></b></td>
       <td>
            <?php foreach ($params['stations'] as $station => $value) {?>
               <?php echo Yii::app()->params['com_type'][$value->communication_type]; ?><br/><br/>
            <?php }?>                 
       </td>
       <td>
            <?php foreach ($params['stations'] as $station => $value) {?>
               <?php echo $value->station_id_code?>  - <?php echo $value->display_name?><br/><br/>
            <?php }?>
       </td>
       <td id="connection_status_<?php echo $connection_type.$communication_type; ?>" style="width: 300px;">
            <?php if (!$params['last_connection']) {?>
                Has never been read.
            <?php } else if (!$params['last_connection']['stopped_show']) {?>
                Is connected since: <?php echo  $params['last_connection']['started_show']?> (<?php echo  ($params['last_connection']['duration_formatted']) ?>)
            <?php } else {?>
                Last connection: <?php echo  $params['last_connection']['started_show']?> - <?php echo  $params['last_connection']['stopped_show']?> (<?php echo  ($params['last_connection']['duration_formatted']) ?>)
            <?php }?>
       </td>
       <td id="connection_tools_<?php echo $connection_type.$communication_type; ?>" class="connection_tools <?=$connection_type?>">
            <input type="button" value="Start" <?php if ($params['blocked']) { ?>disabled="disabled"<?php } ?>  onclick='startListening("<?php echo $connection_type; ?>","<?=$communication_type;?>")' />
            <input type="button" value="Check" onclick='getStatus("<?php echo $connection_type; ?>","<?=$communication_type;?>")' style="display:none;" />
            <input type="button" value="Stop" <?php if ($params['blocked']) { ?>disabled="disabled"<?php } ?>   onclick="stopListening('<?php echo $connection_type; ?>','<?=$communication_type;?>')" />
            <input type="button" value="Log"   onclick="document.location.href='<?php echo $this->createUrl('admin/connectionslog', array('source' => $connection_type)); ?>'" />
       </td>
    </tr>
    <?php $i++;?>
    <?php }?>
    </table>


<script type="text/javascript">


    function startListening(source, communicationType)
    {
        var rsource = source.replace(/__/g, ":");
        rsource = rsource.replace(/_/g, ".");

        $.post(BaseUrl+'/admin/startlistening/?source='+rsource+'&communication_type='+communicationType, function(data){
            if (data.ok == '1' || data.ok_still == '1') {
                setTimeout(function(){getStatus(source,communicationType)}, 600);
            } else if (data.errors) {
                var str = '';
                for (var i in data.errors) {
                    str+= data.errors[i];
                }
                $('#connection_status_'+source+communicationType).html(str);
            }

            delete communicationType;
            delete rsource;
            delete data;
        }, 'json');

        blockedButtons(source,communicationType);
    }

    function stopListening(source,communicationType)
    {

        var rsource = source.replace(/__/g, ":");
        rsource = rsource.replace(/_/g, ".");
        $.post(BaseUrl+'/admin/stoplistening/?source='+rsource, function(data){
            if (data.ok == '1') {
                setTimeout(function(){getStatus(source,communicationType)}, 500);
            }
            delete rsource;
            delete data;
        }, 'json');


        unblockedButtons(source,communicationType);
    }

    function getStatus(source,communicationType)
    {
        console.log (communicationType) ;
        var rsource = source.replace(/__/g, ":");
        rsource = rsource.replace(/_/g, ".");
        $.getJSON(BaseUrl+'/admin/getstatus/?source='+rsource, function(data){
            if (data.errors) {
                alert('some errors were occured');
            } else {
                if (data.started_show == '') {
                    $('#connection_status_'+source).html('Has never been listened.');
                } else {
                    if (data.stopped_show == '') {
                        $('#connection_status_'+source+communicationType).html('Is connected since: '+data.started_show+' ('+data.duration_formatted+')');
                        setTimeout(function(){getStatus(source,communicationType)}, 1000);
                    } else {
                        $('#connection_status_'+source+communicationType).html('Last connection:  '+data.started_show+' ('+data.duration_formatted+')');
                    }
                }
            }
            delete data;
            delete rsource;
        });
    }

    function blockedButtons(source,communicationType)
    {

        var items = $('.'+source);
        var arrayCount = items.length
        for(var i=0 ;i<arrayCount;i++) {
            if (($(items[i]).attr('id')=='connection_tools_'+source+communicationType)) {
                items.splice(i,1)
            }
        }
        console.log(items);
        var inputs;
        for(i=0 ;i<items.length;i++) {

            inputs = $(items[i]).find('input');

            for(var j=0 ;j<inputs.length;j++) {
                if ($(inputs[j]).attr('value')=='Start' || $(inputs[j]).attr('value')=='Stop' ) {
                    $(inputs[j]).attr('disabled','disabled');
                }
            }
        }
    }

    function unblockedButtons(source,communicationType)
    {

        var items = $('.'+source);
        var arrayCount = items.length
        for(var i=0 ;i<arrayCount;i++) {
            if (($(items[i]).attr('id')=='connection_tools_'+source+communicationType)) {
                items.splice(i,1)
            }
        }
        console.log(items);
        var inputs;
        for(i=0 ;i<items.length;i++) {

            inputs = $(items[i]).find('input');

            for(var j=0 ;j<inputs.length;j++) {
                if ($(inputs[j]).attr('value')=='Start' || $(inputs[j]).attr('value')=='Stop' ) {
                    $(inputs[j]).removeAttr('disabled');
                }
            }
        }
    }


    $(function(){
        <?php foreach ($connections as $key => $params) {?>
                <?php if ($params['last_connection']['started_show'] && !$params['last_connection']['stopped_show'] ) {?>
                    getStatus('<?php echo str_replace(array(':', '.'), array('__','_'), $params['connection_type']); ?>','<?=$connections[$key]['communication_type']?>');
                <?php }?>
        <?php }?>
    });

</script>

<?php }?>

<br/>
</div><!-- /div.middlenarrow -->