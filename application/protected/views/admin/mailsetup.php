
<div class="middlenarrow">
    <h1>Mail Settings</h1>

    <?php echo  CHtml::beginForm($this->createUrl('admin/mailsetup'), 'post', array('id' => 'formMailSettings')); ?>

    <table class="formtable">
        <tr>
            <td><?php echo CHtml::activeRadioButtonList($settings, 'mail__use_fake_sendmail', array(0 => 'This PC has Sendmail Software', 1 => 'PC doesn\'t have a sendmail program. Please use an alternative mail program.')) ?></td>
            <td><?php echo CHtml::error($settings,'mail__use_fake_sendmail'); ?></td>
            <td>&nbsp;</td>
        </tr>
    </table>


    <table class="formtable">
        <tr>
            <th style="width: 150px;"><?php echo CHtml::activeLabel($settings, 'mail__sender_address'); ?></th>
            <td>
                <?php echo CHtml::activeTextField($settings, 'mail__sender_address', array('style' => 'width: 350px;')); ?>
                <?php echo CHtml::error($settings,'mail__sender_address'); ?>
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($settings, 'mail__sender_name'); ?></th>
            <td>
                <?php echo CHtml::activeTextField($settings, 'mail__sender_name', array('style' => 'width: 350px;')); ?>
                <?php echo CHtml::error($settings,'mail__sender_name'); ?>
            </td>
            <td>&nbsp;</td>
        </tr>

        <tr class="use_fake" style="<?php echo ($settings->mail__use_fake_sendmail ? '' : 'display:none;'); ?>">
            <th><?php echo CHtml::activeLabel($settings, 'mail__sender_password'); ?></th>
            <td>
                <?php echo CHtml::activePasswordField($settings, 'mail__sender_password', array('style' => 'width: 350px;')); ?>
                <?php echo CHtml::error($settings,'mail__sender_password'); ?>
            </td>
            <td rowspan="4">
                <blockquote class="tip">
                    <p>
                        SMTP port usually is 25<br/>
                        <br/>
                        Default settings for <b>Gmail</b> accounts:<br/><br/>
                        SMTP server: <b>smtp.gmail.com</b><br/>
                        SMTP port: <b>587</b><br/>
                        SMTP SSL support: <b>tls</b>
                    </p>
                </blockquote>

            </td>
        </tr>

        <tr class="use_fake" style="<?php echo ($settings->mail__use_fake_sendmail ? '' : 'display:none;'); ?>">
            <th><?php echo CHtml::activeLabel($settings, 'mail__smtp_server'); ?></th>
            <td>
                <?php echo CHtml::activeTextField($settings, 'mail__smtp_server', array('style' => 'width: 350px;')); ?>
                <?php echo CHtml::error($settings,'mail__smtp_server'); ?>
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr class="use_fake" style="<?php echo ($settings->mail__use_fake_sendmail ? '' : 'display:none;'); ?>">
            <th><?php echo CHtml::activeLabel($settings, 'mail__smtp_port'); ?></th>
            <td>
                <?php echo CHtml::activeTextField($settings, 'mail__smtp_port', array('style' => 'width: 350px;')); ?>
                <?php echo CHtml::error($settings,'mail__smtp_port'); ?>
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr class="use_fake" style="<?php echo ($settings->mail__use_fake_sendmail ? '' : 'display:none;'); ?>">
            <th><?php echo CHtml::activeLabel($settings, 'mail__smtps_support'); ?></th>
            <td>
                <?php echo CHtml::activeDropDownList($settings, 'mail__smtps_support', Yii::app()->params['smtps_support_options'], array('style' => 'width: 320px;')); ?>
                <?php echo CHtml::error($settings,'mail__smtps_support'); ?>
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
    <?php echo CHtml::submitButton('Save'); ?>

    <?php echo  CHtml::endForm(); ?>


</div>

<div class="middlenarrow">
    <h1>Check Email Sending</h1>

    <?php echo CHtml::beginForm($this->createUrl('admin/mailsetup'), 'post'); ?>

    <table class="formtable">
        <tr>
            <th><?php echo CHtml::activeLabel($form, 'email')?></th>
            <td><?php echo CHtml::activeTextField($form, 'email', array('style' => 'width: 400px;'))?></td>
            <td><?php echo CHtml::error($form,'email'); ?></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($form, 'subject')?></th>
            <td><?php echo CHtml::activeTextField($form, 'subject', array('style' => 'width: 400px;'))?></td>
            <td><?php echo CHtml::error($form,'subject'); ?></td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($form, 'message')?></th>
            <td><?php echo CHtml::activeTextArea($form, 'message', array('style' => 'width: 400px; height: 200px;'))?></td>
            <td><?php echo CHtml::error($form,'message'); ?></td>
        </tr>
        <tr class="bottom">
            <td>&nbsp;</td>
            <td><?php echo CHtml::submitButton('Send')?></td>
            <td>&nbsp;</td>
        </tr>
    </table>
    <?php echo CHtml::endForm(); ?>

</div>

<script type="text/javascript">

    $(document).ready(function(){
        $('#formMailSettings input[name="Settings[mail__use_fake_sendmail]"]').change(function(){
            if ($(this).val() == 1) {
                $('tr.use_fake').show();
            } else {
                $('tr.use_fake').hide();
            }
        });

    });

</script>