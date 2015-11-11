<div class="middlewide">
    <div class="middlenarrow">
        <div class="breadcrumbs">
            <a href="<?php echo $this->createUrl('admin/stations')?>">Stations</a> &gt; 
            <a href="<?php echo $this->createUrl('admin/StationSave', array('station_id' => $station->station_id))?>"><?php echo $station->station_id_code?> - <?php echo $station->display_name?></a> &gt; 
            <a href="<?php echo $this->createUrl('admin/sensors', array('station_id' => $station->station_id))?>">Sensors</a> &gt; 
            Calculation
        </div>
    </div>
</div>

<div class="middlenarrow">
<h1>Calculation <?php echo $handler_db->display_name ?></h1>

<?php echo CHtml::beginForm($this->createUrl('admin/calculationSave'), 'post'); ?>
<input type="hidden" name="handler_id" value="<?php echo $handler_db->handler_id?>" />
<input type="hidden" name="station_id" value="<?php echo $station->station_id?>" />

<div style="float: left;">
    <b>Choose sensors:</b><br/>

    <table class="formtable" >
    <?php foreach ($measurements as $key => $value) {?>
        <tr>
            <td><?php echo CHtml::activeLabel($value['object'], '[]sensor_feature_id', array('label' => $value['display_name']))?> <?php echo ($value['required']?'*':'')?>:</td> 
            <td>
                <?php echo CHtml::activeDropDownList($value['object'], '[]sensor_feature_id', CHtml::listData($value['sensors'], 'sensor_feature_id', 'sensor_id_code'), array('empty' => array(0 => 'Select...')))?>
                <?php echo CHtml::error($value['object'], '[]sensor_feature_id', $htmlOptions)?>
            </td>    
        </tr>    

    <?php  } ?>
    </table>
</div>
<?php if ($formulas) {?>
    <div style="float: left; margin-left:20px;">
        <b>Choose formula:</b><br/>
        <?php echo  CHtml::activeRadioButtonList($calculation_db, 'formula', $formulas, array('separator' => '&nbsp;'))?>
    </div>
<?php }?>
<div class="clear"></div>

<br/><br/>
<?php echo CHtml::submitButton($calculation_db->calculation_id ? 'Update' : 'Add')?>
<?php if ($calculation_db->calculation_id) {?>
    <?php echo CHtml::button('Delete', array('onclick' => 'deleteCalculation()'))?>
<?php }?>
<?php echo CHtml::endForm(); ?>


<?php if ($calculation_db->calculation_id) {?>
<script type="text/javascript">
 function deleteCalculation() {
     if (confirm('Are you sure you want to delete Calculation?')) {
         document.location.href = "<?php echo $this->createUrl('admin/calculationDelete', array('id' => $calculation_db->calculation_id))?>";
     }
     return false;
 }    
</script>
<?php  } ?>


</div>