
<div class="middlenarrow">
<h1>Metrics</h1>

<?php if (!$meas_types) {?>
    List of measured things is empty.
<?php } else {?>
    <blockquote class="tip">
        <p>Here you can choose the general metric to store data with.</p>
    </blockquote>    
    
    <?php  $middle = floor(count($meas_types)/2);?>
    <?php echo  CHtml::beginForm($this->createUrl('admin/setup'), 'post'); ?>
    
    <div class="left">
    <table class="formtable">
    
    <?php foreach ($meas_types as $key => $value) {?>
        <tr>    
            <th><?php echo $value->display_name?></th>
            <td>
                <select name="main_metric[<?php echo $key?>]">
                <?php if ($meas_types[$key]->metrics_list) {?>
                    <?php foreach ($meas_types[$key]->metrics_list as $k1 => $v1) {?>
                    <option value="<?php echo $v1['metric_id']?>" <?php if ($v1['is_main']){?>selected<?php }?>><?php echo $v1['name']?></option>
                    <?php }?>
                <?php } else {?>
                    <option>-</option>    
                <?php }?>    
                </select>
            </td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
        </tr>
        <?php  if ($key == $middle) {?>
        </table></div>
        <div class="right">
        <table class="formtable">
        <?php }?>
    <?php }?>
    
    </table>
    </div>
    <div class="clear"></div>
    <?php echo CHtml::submitButton('Save', array('onclick' => 'return confirm("You cannot change the metrics of the database without DELETING ALL DATA. Are you 100% sure you wish to continue? ")'))?>
    <?php echo CHtml::endForm(); ?>
    
<?php }?>
    <div class="spacer"></div>
</div>
