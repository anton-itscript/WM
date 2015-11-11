


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