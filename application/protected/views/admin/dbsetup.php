

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
                        <a href="<?php echo $this->createUrl('admin/dbsetup', array('apply' => $backup['filename'])); ?>" onclick="return confirm('Are you sure you want to apply <?php echo $backup['filename']; ?>?')">Apply</a>
                        &nbsp;&nbsp;
                        <a href="<?php echo $this->createUrl('admin/dbsetup', array('delete' => $backup['filename'])); ?>" onclick="return confirm('Are you sure you want to delete <?php echo $backup['filename']; ?>?')">Delete</a>
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
    <input type="button" name="create_backup" value="Create Fresh Backup Now" onclick="document.location.href='<?php echo $this->createUrl('admin/dbsetup', array('create' => 1)) ?>'" />
    <input type="button" name="create_backup" value="Create Fresh Long Backup Now" onclick="document.location.href='<?php echo $this->createUrl('admin/dbsetup', array('create_long' => 1)) ?>'" />
</div>

<div class="middlenarrow" style="padding-top: -100px">
    <h1>DB Export Routine</h1>

    <?php echo CHtml::errorSummary($settings); ?>

    <?php echo CHtml::beginForm($this->createUrl('admin/dbsetup'), 'post', array('id' => 'formDBExportSettings')); ?>


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

<div class="middlenarrow" style="padding: 0">
    <h1>DB Export History</h1>

    <?php if (!$list) {?>
        Log is empty.
    <?php } else {?>

        <?php foreach ($list as $key => $value) {?>
            <b>Backup date: <?php echo $value['backup_date']?>.
                <br/>Data older than <?php echo $value['data_timestamp_limit']?> has been moved to a backup database.</b>

            <?php if ($value['logs']) {?>
                <table class="tablelist">
                    <?php foreach ($value['logs'] as $k => $v) {?>
                        <tr>
                            <td><?php echo $v['created']?></td>
                            <td><?php echo $v['comment']?></td>
                        </tr>
                    <?php }?>
                </table>
            <?php }?>
            <br/><br/>
        <?php }?>


        <?php if ($pages->getPageCount() > 1){?>

            <div class="paginator" style="margin-top: 10px;">
                <?php  $this->widget(
                    'CLinkPager',
                    array(
                        'pages' => $pages,
                        'header' => '',
                        'firstPageLabel' => '&nbsp;',
                        'lastPageLabel' => '&nbsp;',
                        'nextPageLabel' => '&rarr;',
                        'prevPageLabel' => '&larr;'
                    ));
                ?>
                <div class="clear"></div>
            </div>

        <?php }?>

    <?php }?>

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