<?php  Yii::app()->clientScript->registerCssFile(It::baseUrl().'/css/datePicker.css') ?>
<?php  Yii::app()->clientScript->registerScriptFile(It::baseUrl().'/js/date.js');?>
<?php  Yii::app()->clientScript->registerScriptFile(It::baseUrl().'/js/jquery.datePicker.js');?>

<div class="middlenarrow">
<h1>Message Generation</h1>

Choose a station, set a time period, an interval and generate random messages for all the sensors you have selected.<br/><br/>
<?php $current_url = $this->createUrl('admin/msggeneration'); ?>
<form action="<?php echo $current_url?>" method="post">
<div class="form_box" id="filterparams" style="margin-bottom: 10px;">
    <table class="formtable">
        <tr>
            <td style="width: 60px;"><?php echo CHtml::activeLabel($form, 'station_id'); ?></td>
            <td style="width: 250px;">
                <?php if ($form->stations) {?>
                    <?php echo CHtml::activeDropDownList($form, 'station_id', $form->stations, array()); ?>
                <?php } else {?>
                    No any stations registered at database.
                <?php }?>
                <?php echo CHtml::error($form,'station_id'); ?>
            </td>
            <td style="width: 50px;">&nbsp;</td>
            <td rowspan="5" style="vertical-align: top; width: 500px; padding-left: 30px;">
                Sensors:
                <div id="msg_generate_station_sensors">
                    <?php if (is_array($station_sensors)) : ?>
                        <?php foreach ($station_sensors as $key => $value): ?>
                            <div>
                                <input type="checkbox" name="GenerateMessageForm[sensor_id][]" value="<?php echo $value['station_sensor_id']; ?>" <?php echo ($value['checked'] ? 'checked' : ''); ?> />
                                <?php echo $value['sensor_id_code']; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php echo 'No any sensor found.'; ?>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php echo CHtml::activeLabel($form, 'date_from'); ?></td>
            <td>
                <?php echo CHtml::activeTextField($form, 'date_from', array('class' => 'date-pick input-calendar')); ?>
                <div style="clear: both;"></div>
                <?php echo CHtml::error($form,'date_from'); ?>

            </td>
            <td><?php echo CHtml::activeLabel($form, 'time_from'); ?>
                <?php echo CHtml::activeTextField($form, 'time_from', array('style' => 'width: 50px;')); ?>
                <?php echo CHtml::error($form,'time_from'); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo CHtml::activeLabel($form, 'date_to'); ?></td>
            <td>
                <?php echo CHtml::activeTextField($form, 'date_to', array('class' => 'date-pick input-calendar')); ?>
                <div style="clear: both;"></div>
                <?php echo CHtml::error($form,'date_to'); ?>
            </td>
            <td><?php echo CHtml::activeLabel($form, 'time_to'); ?>
                <?php echo CHtml::activeTextField($form, 'time_to', array('style' => 'width: 50px;')); ?>
                <?php echo CHtml::error($form,'time_to'); ?>
            </td>

        </tr>
        <tr>
            <td><?php echo CHtml::activeLabel($form, 'interval'); ?></td>
            <td>
                <?php echo CHtml::activeTextField($form, 'interval', array('style' => 'width: 100px;')); ?>
                Minutes
                <?php echo CHtml::error($form,'interval'); ?>
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="submit" name="generate" value="<?php echo It::t('site_label', 'do_generate'); ?>" />
                <input type="reset" name="clear" value="<?php echo It::t('site_label', 'do_reset'); ?>" />
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
</div>


<?php 
	if (is_array($messages_display)) 
	{
?>
    <h1>Result:</h1>
    <?php echo count($messages_display); ?> messages have been generated.<br/><br/>
    VIEW: 
    <input type="button" value="Smart" onclick="$('#generated_origin_view').hide(); $('#generated_smart_view').show();" />
    <input type="button" value="Original" onclick="$('#generated_smart_view').hide(); $('#generated_origin_view').show();" />
    
    &nbsp;&nbsp;&nbsp;&nbsp;
    WITH DATA:
    <input type="button" name="import" value="Import" onclick="importGeneratedMessages()" />
    
    <div style="border: 1px dashed blue; padding: 10px; background-color: lemonchiffon; font-size: 10px; white-space: nowrap;overflow-x: auto; width:100%;">
       
        <div id="generated_sending_status" style="display:none;font-size:12px;"></div>
        <div id="generated_smart_view">
            <?php echo implode('<br/>', $messages_display); ?>
        </div>
        <div id="generated_origin_view" style="display:none;">
            <?php
				foreach($messages_copy as $key) 
				{
			?>
			<div><?php echo $key; ?></div>
            <?php
				}
			?>
        </div>
    </div>

<?php }?>
</form>
<br/><br/>
<script type="text/javascript">

    $(document).ready(function(){

        $('#GenerateMessageForm_station_id').change(function(){loadSensors($(this).val())});

        $('#filterparams .date-pick').datePicker({startDate:'01/01/1996', clickInput:true, imgCreateButton: true});
        $('#filterparams input[name=date_from]').bind(
            'dpClosed',
            function(e, selectedDates) {
                var d = selectedDates[0];
                if (d) {
                    d = new Date(d);
                    $('#filterparams input[name=date_to]').dpSetStartDate(d.addDays(1).asString());
                }
            }
        );
            
        $('#filterparams input[name=date_to]').bind(
            'dpClosed',
            function(e, selectedDates) {
                var d = selectedDates[0];
                if (d) {
                    d = new Date(d);
                    $('#filterparams input[name=date_from]').dpSetEndDate(d.addDays(-1).asString());
                }
            }
        );
    });
    
    
    function loadSensors(id) {
        $('#msg_generate_station_sensors').html('');
        $.post(
            '<?php echo $this->createUrl('ajax/LoadSensors'); ?>',
            {id:id},
            function(data){
                if(data) {
                    for (var i=0; i<data.length; i++) {
                        var html = '<div>'+
                                   '<input type="checkbox" name="GenerateMessageForm[sensor_id][]" value="'+data[i].station_sensor_id+'" checked />  '+
                                   data[i].sensor_id_code+
                                   '<\/div>';
                        $('#msg_generate_station_sensors').append(html);
                    }
                }
                $('#msg_generate_station_sensors').append('<div style="clear:both;"><\/div>');
            },
            'json'
        );
    }
    
    <?php if ($messages_copy) {?>
   
     
    var imported_count = 0;
    var total_count    = <?php echo count($messages_copy); ?>;    
    var messages = [];
    
        
    function importGeneratedMessages()
	{
        $('#generated_smart_view').hide();
        $('#generated_origin_view div').css('color', '#454545');
        $('#generated_origin_view').show();
        $('#generated_sending_status').html('Import is in process... Wait please.').show();
        
        <?php foreach ($messages_copy as $key => $value) {?>
            messages[<?php echo $key?>] = '<?php echo $value?>';
        <?php }?>  
            
		imported_count = 0;
        importGeneratedMessage(messages[0], 0);
    } 
    

    function importGeneratedMessage(msg, i)
	{
            $.post(
                BaseUrl+'/ajax/ImportMessage',
                {message: msg},

                function(data) {
                     if (data.ok == 1) {
                         $('#generated_origin_view div:eq('+i+')').css('color','green');
                     } else {
                         $('#generated_origin_view div:eq('+i+')').css('color','red');
                     }
                     imported_count++;
                     if (imported_count == total_count) {
                         $('#generated_sending_status').html('All messages were imported into database. <b>Please, concider<\/b> that a few milliseconds are required to parse each message and put detected sensors values into database.');
                     } else {
                         i++;
                         importGeneratedMessage(messages[i], i);
                         $('#generated_sending_status').html('Import is in process: <b>'+imported_count+'<\/b> of <b>'+total_count+ '<\/b> were imported into database. Please wait while all messages are imported.');
                     }
                },
                'json'
            );         
    }
    
    <?php }?>
</script>

</div>