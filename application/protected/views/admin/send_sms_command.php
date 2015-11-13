<?php
/**
 * @var SMSCommandSendForm $form
 * @var CActiveDataProvider $dataProvider
 */
?>


<div class="middlenarrow">
    <h1> Send SMS command </h1>
    <blockquote class="note">
        <b>ATTENTION</b>:
        <ul>
            <li>• This feature should only be used by experienced operators.</li>
            <li>• Consider the GSM network condition at the local and remote location and possible time delays in sending messages.</li>
            <li>• The commands bleow will affect the operation of remote AWS and if an error is made it may be necessary visit the site to rectify the problem.</li>
        </ul>
    </blockquote>
    <div style="width: 50%; float: left;">
        <?php echo $this->renderPartial('form/send_sms_command', ['form' => $form]) ?>
    </div>
    <div style="width: 50%; float: left;">
        <?php echo $this->renderPartial('__sms_command_status', ['sms' => $form->getSMS()]) ?>
    </div>
    <div style="width: 100%; float: left;" id="sms_list">
        <?php echo $this->renderPartial('__sms_command_list', ['dataProvider' => $dataProvider,'dateFrom'=>$dateFrom, 'dateTo'=>$dateTo]) ?>
    </div>

</div>
