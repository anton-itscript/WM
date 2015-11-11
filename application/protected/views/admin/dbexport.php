


<div class="middlenarrow" style="padding-top: -100px">
<h1>DB Export Routine</h1>

    <?php echo CHtml::errorSummary($settings); ?>

    <?php echo CHtml::beginForm($this->createUrl('admin/dbexport'), 'post', array('id' => 'formDBExportSettings')); ?>

        
        <?php echo CHtml::activeCheckBox($settings, 'db_exp_enabled')?>
        <?php echo CHtml::activeLabel($settings, 'db_exp_enabled')?>
        
        <table class="formtable enabled_export">
        <tr>
            <th><?php echo CHtml::activeLabel($settings, 'db_exp_period')?>:</th>
            <td><?php echo CHtml::activeDropDownList($settings, 'db_exp_period', array('1' => '1', '10' => '10', '30' => '30', '60' => '60', '90' => '90', '365' => '365'), array('style' => 'width: 50px;'))?></td>
            <td>days (life of data in database)</td>
        </tr>    
        <tr>
            <th><?php echo CHtml::activeLabel($settings, 'db_exp_frequency')?>:</th>
            <td><?php echo CHtml::activeDropDownList($settings, 'db_exp_frequency', array('1' => '1', '5' => '5', '10' => '10', '30' => '30'), array('style' => 'width: 50px;'))?></td>
            <td>days (how often data will be moved to backup database)</td>
        </tr>    
        </table>    
        <br/><br/>
        
        <h1 class="enabled_export">Backup database configuration:</h1>
        <blockquote class="note enabled_export">
            <b>Attention!</b><br/>
            Script tries to create database if it does not exist. Host should have configurred MySQL v.5.3 and above.
        </blockquote>    
        <table class="formtable enabled_export">
        <tr>
            <th><?php echo CHtml::activeLabel($settings, 'db_exp_sql_host')?>:</th>
            <td><?php echo CHtml::activeTextField($settings, 'db_exp_sql_host')?><br/><div class="small">localhost OR ip-address</div></td>
            <td>&nbsp;</td>
            <th><?php echo CHtml::activeLabel($settings, 'db_exp_sql_port')?>:</th>
            <td><?php echo CHtml::activeTextField($settings, 'db_exp_sql_port', array('style' => 'width: 50px;'))?></td>            
        </tr>    
        <tr>
            <th><?php echo CHtml::activeLabel($settings, 'db_exp_sql_dbname')?>:</th>
            <td><?php echo CHtml::activeTextField($settings, 'db_exp_sql_dbname')?></td>
            <td>&nbsp;</td>
            <th>&nbsp;</th>
            <td>&nbsp;</td> 
        </tr>    
        <tr>
            <th><?php echo CHtml::activeLabel($settings, 'db_exp_sql_login')?>:</th>
            <td><?php echo CHtml::activeTextField($settings, 'db_exp_sql_login')?></td>
            <td>&nbsp;</td>
            <th><?php echo CHtml::activeLabel($settings, 'db_exp_sql_password')?>:</th>
            <td><?php echo CHtml::activePasswordField($settings, 'db_exp_sql_password')?></td> 
        </tr>          
        </table> 
        <br/><br/>
        <?php echo CHtml::submitButton('Save')?>   
    <?php echo CHtml::endForm(); ?>

</div>

<script type="text/javascript">
    $(function(){
        $('#Settings_db_exp_enabled').change(function(){
            
            console.log($(this).attr('checked'));
            if (!$(this).is(':checked')) {
                //if (confirm('Are you sure you want disable Database Export? Next Activation will create new database and all previously exported data will be lost')) {
                    showDBExportSettings();
                //}
            } else {
                showDBExportSettings();
            }
        });
        

        showDBExportSettings();
    });
    function showDBExportSettings() {
        if ($('#Settings_db_exp_enabled').is(':checked')) {
            $('.enabled_export').show();
        } else {
            $('.enabled_export').hide();
        }
    }
</script>