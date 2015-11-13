<div class="middlenarrow">
    <h1> Setup SMS Port </h1>


    <p><b>Selected Port:</b> <?=$SMSCOMPort->COM?></p> <br/>
    <form action="<?=Yii::app()->urlManager->createUrl('admin/smscommandsetup')?>" method="POST">
        <?php echo CHtml::DropDownList('setup_com', $SMSCOMPort->COM, SysFunc::getAvailableComPortsList())?> <br> <br>
        <input type="submit" value="Apply"/>
    </form>

</div>
