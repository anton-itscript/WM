

<?php
	Yii::app()->clientScript->registerScriptFile('http://maps.googleapis.com/maps/api/js?sensor=false');
?>

<div class="middlewide">
    <div class="middlenarrow">
        <div class="breadcrumbs">
            <a href="<?php echo $this->createUrl('admin/stations'); ?>">Stations</a> &gt; 
            
			<?php if ($form->station_id) {?>
                Edit <?php echo $form->station_id_code?> - <?php echo $form->display_name?> 
            <?php } else {?>
                New 
            <?php }?>
        </div>
    </div>
</div>

<div class="middlenarrow">
<?php if ($form->station_id) { ?>
    <h1>Change Station Settings</h1>
<?php } else { ?>
    <h1>Create New Station</h1>
<?php } ?>

    <?php echo CHtml::beginForm($this->createUrl('admin/stationSave'), 'post'); ?>
    <input type="hidden" name="station_id" value="<?php echo $form->station_id?>" />

    <table class="formtable" style="float: left; width: 550px;" >

    <?php if ($form->station_id) { ?>
		<tr>
			<th>Station ID *</th>
			<td colspan="3"><b><?php echo $form->station_id_code?></b></td>
		</tr>
		<tr>
			<th>Type *</th>
			<td colspan="3"><b><?php echo Yii::app()->params['station_type'][$form->station_type]; ?></b></td>
		</tr>
		<?php } else {?>
		<tr>
			<th><?php echo CHtml::activeLabel($form, 'station_id_code'); ?> <sup>*</sup></th>
			<td colspan="3">
				<?php echo CHtml::activeTextField($form, 'station_id_code', array('style' => 'width: 300px;')); ?>
				<?php echo CHtml::error($form, 'station_id_code'); ?>
			</td>
		</tr>
		<tr>
			<th><?php echo CHtml::activeLabel($form, 'station_type'); ?> <sup>*</sup></th>
			<td colspan="3">
				<?php echo CHtml::activeDropDownList($form, 'station_type', Yii::app()->params['station_type'], array('style' => 'width: 270px;')); ?>
				<?php echo CHtml::error($form, 'station_type'); ?>
			</td>
		</tr>
		<?php }?>
		<tr>
			<th><?php echo CHtml::activeLabel($form, 'color'); ?> <sup>*</sup></th>
			<td colspan="3">
				<?php echo CHtml::activeTextField($form, 'color', array('style' => 'width: 270px;','id'=>'Station_color')); ?>
				<?php echo CHtml::error($form, 'color'); ?>
				<script>
					$('#Station_color').colorpicker({
						showOn:'button',
						color:'<?=$form->color?>',
						displayIndicator: false,
						history: false,
					});
				</script>
			</td>
		</tr>
		<tr>
			<th style="width: 140px;"><?php echo CHtml::activeLabel($form, 'display_name'); ?> <sup>*</sup></th>
			<td colspan="3">
				<?php echo CHtml::activeTextField($form, 'display_name', array('style' => 'width: 300px;')); ?>
				<?php echo CHtml::error($form, 'display_name'); ?>
			</td>
		</tr>
		<tr>
			<th style="width: 140px;"><?php echo CHtml::activeLabel($form, 'icao_code'); ?></th>
			<td colspan="3">
				<?php echo CHtml::activeTextField($form, 'icao_code', array('style' => 'width: 300px;')); ?>
				<?php echo CHtml::error($form, 'icao_code'); ?>
			</td>
		</tr>
        <tr>
			<th style="vertical-align:middle;"><?php echo CHtml::activeLabel($form, 'details'); ?></th>
			<td colspan="3">
				<?php echo CHtml::activeTextarea($form, 'details', array('style' => 'width: 300px;resize:none;', 'rows' => '3', 'cols' => '20')); ?>
				<?php echo CHtml::error($form, 'details'); ?>
			</td>
		</tr>

		<?php /*tr>
			<th><?php echo CHtml::activeLabel($form, 'country_id'); ?>*</th>
			<td>
				<?php echo CHtml::activeTextField($form, 'country_id', array('style' => 'width: 30px;')); ?>
				(2 char max) <?php echo CHtml::error($form, 'country_id'); ?>
			</td>

			<th style="text-align: right;"><?php echo CHtml::activeLabel($form, 'city_id'); ?> <sup>*</sup></th>
			<td><?php echo CHtml::activeTextField($form, 'city_id', array('style' => 'width: 30px;')); ?>
			(2 char max) <?php echo CHtml::error($form, 'city_id'); ?>
			</td>
		</tr */?>

		<tr class="aws_data">
			<th><?php echo CHtml::activeLabel($form, 'wmo_block_number'); ?>*</th>
			<td>
				<?php echo CHtml::activeTextField($form, 'wmo_block_number', array('style' => 'width: 50px;')); ?>
				<?php echo CHtml::error($form, 'wmo_block_number'); ?>
			</td>

			<th style="text-align: right;"><?php echo CHtml::activeLabel($form, 'station_number'); ?> <sup>*</sup></th>
			<td><?php echo CHtml::activeTextField($form, 'station_number', array('style' => 'width: 50px;')); ?>
			(3 char) <?php echo CHtml::error($form, 'station_number'); ?>
			</td>
		</tr>
		<tr class="aws_data">
			<th><?php echo CHtml::activeLabel($form, 'wmo_member_state_id'); ?></th>
			<td>
				<?php echo CHtml::activeTextField($form, 'wmo_member_state_id', array('style' => 'width: 50px;')); ?>
				<?php echo CHtml::error($form, 'wmo_member_state_id'); ?>
			</td>
			<th style="text-align: right;" nowrap><?php echo CHtml::activeLabel($form, 'magnetic_north_offset'); ?> <sup>*</sup></th>
			<td><?php echo CHtml::activeTextField($form, 'magnetic_north_offset', array('style' => 'width: 50px;')); ?> degrees
				<?php echo CHtml::error($form, 'magnetic_north_offset'); ?>
			</td>        
		</tr>
		<?php /*tr class="awos_data">
			<th><?php echo CHtml::activeLabel($form, 'awos_msg_source_folder'); ?>*</th>
			<td colspan="3">
				<?php echo CHtml::activeTextField($form, 'awos_msg_source_folder', array('style' => 'width: 300px;')); ?>
				<div class="comment">
					Put path to folder here (AWOS transfers XML files into), 
					<br/>e.g.: <i>C:\Windows FTP\awos\station1</i></div>
				<?php echo CHtml::error($form, 'awos_msg_source_folder'); ?>
			</td>
		</tr*/?>
		<tr>
			<th><?php echo CHtml::activeLabel($form, 'altitude'); ?></th>
			<td colspan="3"><?php echo CHtml::activeTextField($form, 'altitude', array('style' => 'width: 50px;')); ?> m
				<?php echo CHtml::error($form, 'altitude'); ?>
			</td>
		</tr>

        <tr>
            <th><?php echo CHtml::activeLabel($form, 'station_gravity'); ?></th>
            <td colspan="3">
                <?php echo CHtml::activeHiddenField($form, 'station_gravity'); ?>
                <?php echo CHtml::activeNumberField($form, 'station_gravity', array('style' => 'width: 100px;', 'step'=> '0.00001')); ?>
                <?php echo CHtml::dropDownList(
                    'station_gravity_list',
                    array_key_exists($form->station_gravity,Yii::app()->params['station_gravity']) ?
                        $form->station_gravity : 0
                    , Yii::app()->params['station_gravity'], array('style' => 'width: 170px;')); ?>
                <?php echo CHtml::error($form, 'station_gravity'); ?>
            </td>
        </tr>

		<tr>
			<th><?php echo CHtml::activeLabel($form, 'wmo_originating_centre'); ?></th>
			<td colspan="3"><?php echo CHtml::activeTextField($form, 'wmo_originating_centre', array('style' => 'width: 250px;')); ?>
			(max 3 char) <?php echo CHtml::error($form, 'wmo_originating_centre'); ?>
			</td>
		</tr>  
		<tr>
			<th><?php echo CHtml::activeLabel($form, 'national_aws_number'); ?></th>
			<td colspan="3"><?php echo CHtml::activeTextField($form, 'national_aws_number', array('style' => 'width: 250px;')); ?>
			(max 9 char) <?php echo CHtml::error($form, 'national_aws_number'); ?>
			</td>
		</tr>    
		<?php /*if ($form->station_id) {*/?>
		<tr>
			<th><?php echo CHtml::activeLabel($form, 'status_message_period'); ?> <sup>*</sup></th>
			<td colspan="3">
				<?php echo CHtml::activeDropDownList($form, 'status_message_period', Yii::app()->params['status_message_period'], array('style' => 'width: 270px;')); ?>
				<?php echo CHtml::error($form, 'status_message_period'); ?>
			</td>
		</tr>

		<tr>
			<th><?php echo CHtml::activeLabel($form, 'event_message_period'); ?> <sup>*</sup></th>
			<td colspan="3">
				<?php echo CHtml::activeDropDownList($form, 'event_message_period', Yii::app()->params['event_message_period'], array('style' => 'width: 270px;')); ?>
				<?php echo CHtml::error($form, 'event_message_period'); ?>
			</td>
		</tr>
		<?php /*}*/?>
		<tr style="max-height: 200px">
			<th><?php echo CHtml::activeLabel($form, 'timezone_id'); ?> <sup>*</sup></th>
			<td colspan="3">
				<?php echo CHtml::activeDropDownList($form, 'timezone_id', TimezoneWork::prepareList(), array('style' => 'width: 270px; height: 250px')); ?>
				<?php echo CHtml::error($form, 'timezone_id'); ?>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td colspan="3" style="text-align: left;"><?php echo CHtml::submitButton($form->station_id ? 'Update' : 'Add'); ?></td>
		</tr>
		</table>

		<table class="formtable"  style="float: right;">
		<tr>
			<th><?php echo CHtml::activeLabel($form, 'logger_type'); ?> <sup>*</sup></th>
			<td>
				<?php echo CHtml::activeDropDownList($form, 'logger_type', array('DLM11' => 'DLM11', 'DLM13M' => 'DLM13M'), array('style' => 'width: 200px;')); ?>
				<?php echo CHtml::error($form, 'logger_type'); ?>
			</td>
		</tr>
		<tr>
			<th><?php echo CHtml::activeLabel($form, 'communication_type'); ?> <sup>*</sup></th>
			<td>
				<?php echo CHtml::activeDropDownList($form, 'communication_type', Yii::app()->params['com_type'], array('style' => 'width: 200px;')); ?>
				<?php echo CHtml::error($form, 'communication_type'); ?>
			</td>
		</tr>
		<tr class="communication_direct">
			<th><?php echo CHtml::activeLabel($form, 'communication_port'); ?> <sup>*</sup></th>
			<td>
				<?php if (count($comports_list) > 0) {?>
					<?php echo CHtml::activeDropDownList($form, 'communication_port',$comports_list, array('style' => 'width: 200px;')); ?>
				<?php } else {?>
					Server doesn't have COM ports.
				<?php }?>
				<?php echo CHtml::error($form, 'communication_port'); ?>
			</td>
		</tr>
		<tr class="communication_tcpip">
			<th><?php echo CHtml::activeLabel($form, 'communication_esp_ip'); ?> <sup>*</sup></th>
			<th><?php echo CHtml::activeLabel($form, 'communication_esp_port'); ?> <sup>*</sup></th>
		</tr>   
		<tr class="communication_tcpip">
			<td>
				<?php echo CHtml::activeTextField($form, 'communication_esp_ip', array('style' => 'width: 130px;')); ?>
				<?php echo CHtml::error($form, 'communication_esp_ip'); ?>
			</td>
			<td>
				<?php echo CHtml::activeTextField($form, 'communication_esp_port', array('style' => 'width: 130px;')); ?>
				<?php echo CHtml::error($form, 'communication_esp_port'); ?>
			</td>

		</tr>
		<tr class="communication_reset_info">
			<th><?php echo CHtml::activeLabel($form, 'phone_number'); ?> </th>
			<th><?php echo CHtml::activeLabel($form, 'sms_message'); ?> </th>
		</tr>   
		<tr class="communication_reset_info">
			<td>
				<?php echo CHtml::activeTextField($form, 'phone_number', array('style' => 'width: 130px;')); ?>
				<?php echo CHtml::error($form, 'phone_number'); ?>
			</td>
			<td>
				<?php echo CHtml::activeTextField($form, 'sms_message', array('style' => 'width: 130px;')); ?>
				<?php echo CHtml::error($form, 'sms_message'); ?>
			</td>

		</tr>
		<tr>
			<th><?php echo CHtml::activeLabel($form, 'lat'); ?>:</th>
			<th><?php echo CHtml::activeLabel($form, 'lng'); ?>:</th>
		</tr>    
		<tr>
			<td><?php echo CHtml::activeTextField($form, 'lat', array('style' => 'width: 130px;')); ?><?php echo CHtml::error($form, 'lat'); ?></td>
			<td><?php echo CHtml::activeTextField($form, 'lng', array('style' => 'width: 130px;')); ?><?php echo CHtml::error($form, 'lng'); ?></td>
		</tr>    
		<tr>
			<td colspan="2">
                You change the coordinates by moving the pin.
				<div id="map_canvas" style="width: 386px; height: 300px; border: 1px solid #c0c0c0;"></div>
			</td>
		</tr>
    </table>

    <div style="clear: both;"></div>
    <?php echo CHtml::endForm(); ?>
    
<?php 
	if ($form->station_id) 
	{
?>
    <a href="<?php echo $this->createUrl('admin/sensors', array('station_id' => $form->station_id)); ?>">Setup Sensors for this Station</a>
<?php 
	}
?>
</div>

<script type="text/javascript">
    $( document ).ready(function() {
        $('.selectBox-options').css({ maxHeight: "200px"});
    });
    
    var stationPoint = null;
    var stationMapOptions = null;
    var stationMap = null;
    var stationMarker = null;
	
    $(function()
		{
            initialize_map();

			$('#Station_lat').change(function()
				{
					updatePosition();
				});
			$('#Station_lng').change(function()
				{
					updatePosition();
				});
			$('#Station_station_type').change(function()
				{
					changeStationType(this.value);
				});

			$('#Station_communication_type').change(function()
				{
					changeStationCommunicationType(this.value);
				});        

			changeStationType('<?php echo $form->station_type?>');
			changeStationCommunicationType('<?php echo $form->communication_type?>');
		});

    
    function changeStationType(type)
	{
        if (type == 'rain')
		{
            $('.aws_data').hide();
            $('.rain_data').show();
            $('.awos_data').hide();
        }
		else if (type == 'awos')
		{
            $('.awos_data').show();
            $('.aws_data').show();
            $('.rain_data').hide();
        }
		else {
            $('.rain_data').hide();
            $('.awos_data').hide();
            $('.aws_data').show();      
        }
    }

    function changeStationCommunicationType(type) 
	{
        if (type == 'direct' || type == 'sms')
		{
            $('.communication_tcpip').hide();
            $('.communication_direct').show();
        } 
		else 
		{
            $('.communication_direct').hide();
            $('.communication_tcpip').show();
        }
		
		if (type == 'server' || type == 'gprs')
		{
            //$('.communication_reset_info').show();
        } 
		else 
		{
			//$('.communication_reset_info').hide();
        }
		
        return true;
    }

    function updatePosition()
	{
        var new_place = new google.maps.LatLng($('#Station_lat').val(), $('#Station_lng').val());
        
		stationMarker.setPosition(new_place);
        stationMap.setCenter(new_place);
    }

    function initialize_map() 
	{
        stationPoint = new google.maps.LatLng(<?php echo $form->lat?>, <?php echo $form->lng?>);
        stationMapOptions = { zoom: 4, center: stationPoint, mapTypeId: google.maps.MapTypeId.ROADMAP };
        stationMap = new google.maps.Map(document.getElementById("map_canvas"), stationMapOptions);

        stationMarker = new google.maps.Marker({
            position: stationPoint,
            map: stationMap,
            title:"Place the Pin where your station is located!", 
            draggable: true
        });   

        google.maps.event.addListener(stationMarker, 'dragend', function(data)
			{
				$('#Station_lat').val(data.latLng.lat()); 
				$('#Station_lng').val(data.latLng.lng()); 
			});
    }

    var gravity_list = $('[name *= station_gravity_list]'),
        gravity_number = $('[name *= station_gravity][type = number]'),
        gravity_hidden = $('[name *= station_gravity][type = hidden]');


    gravity_list.on('change',function(){
        gravity_number
            .prop( "disabled", gravity_list.find('option:selected').text() != 'Custom' )
            .val(gravity_list.val());
        gravity_hidden.val(gravity_number.val());
    });

    gravity_number.on('change',function(){
        gravity_hidden.val(gravity_number.val());
        console.log( gravity_hidden.val(), gravity_number.val())
    });

    gravity_number
        .prop( "disabled", gravity_list.find('option:selected').text() != 'Custom' );


</script>

