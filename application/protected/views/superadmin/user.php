
<div class="middlenarrow">
    <h1>
        <?php echo $user->user_id ? "Edit " . $user->username : "New User"; ?>
    </h1>

    <?php echo CHtml::beginForm($this->createUrl('superadmin/user'), 'post'); ?>
    <input type="hidden" name="user_id" value="<?php echo $user->user_id?>" />
    <table class="formtable" style="width: 550px;" >
        <?php if ($user->user_id) { ?>
            <tr>
                <th><?php echo CHtml::activeLabel($user, 'user_id'); ?> <sup>*</sup></th>
                <td colspan="3">
                    <b><?php echo $user->user_id; ?></b>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <th style="width: 140px;"><?php echo CHtml::activeLabel($user, 'username'); ?> <sup>*</sup></th>
            <td colspan="3">
                <?php echo CHtml::activeTextField($user, 'username', array('style' => 'width: 300px;')); ?>
            </td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($user, 'email'); ?> <sup>*</sup></th>
            <td colspan="3">
                <?php echo CHtml::activeTextField($user, 'email', array('style' => 'width: 300px;')); ?>
            </td>
        </tr>
        <tr>
            <th><?php echo CHtml::activeLabel($user, 'pass'); ?> <sup>*</sup></th>
            <td colspan="3">
                <?php echo CHtml::activePasswordField($user, 'pass', array('style' => 'width: 300px;')); ?>
                <?php echo CHtml::error($user, 'pass'); ?>
            </td>
        </tr>
        <th></th>
        <td colspan="3">
            <?php echo CHtml::activePasswordField($user, 'pass2', array('style' => 'width: 300px;')); ?>
            <?php echo CHtml::error($user, 'pass2'); ?>
        </td>
        <?php if (!$user->isSuperAdmin()){?>
        <tr>
            <th><?php echo CHtml::activeLabel($user, 'role'); ?> <sup>*</sup></th>
            <td colspan="3">
                <?php echo CHtml::activeDropDownList($user, 'role', array_slice(Yii::app()->params['user_role'],0), array('style' => 'width: 270px;')); ?>
            </td>
        </tr>

        <tr>
            <th><?php echo CHtml::activeLabel($user, 'access'); ?></th>
            <td>
                <div>
                    <input type="checkbox" id="check_all_features" onclick="$('div.checkBoxList input').attr('checked', ($(this).attr('checked') == 'checked' ? true : false));"/>
                    <?php echo CHtml::activeLabel($user, 'allAccess'); ?>
                </div>
                <?php
                if (is_array($actions)){
                    foreach ($actions as $value){
                        ?>
                        <div class="checkBoxList">
                            <input type="checkbox" name="access[]" value="<?php echo $value['id']; ?>" <?php echo (in_array($value['id'],$access) ? 'checked' : ''); ?>/>
                            <?php echo $value['action']; ?>
                        </div>
                    <?php
                    }
                }
                ?>
            </td>
        </tr>
        <?php }?>
        <tr>
            <td>&nbsp;</td>
            <td colspan="3" style="text-align: left;"><?php echo CHtml::submitButton($user->user_id ? 'Update' : 'Add'); ?></td>
        </tr>
    </table>
    <?php echo CHtml::endForm(); ?>
</div>


<script>
    $(document).ready(function(){
        $('#check_all_features').click(function(){

            if ($(this).hasClass('checked')) {

                var inputs =  $('#check_all_features').closest('td').find('input')
                for (var i=0;i<inputs.length;i++) {
                    $(inputs[i]).removeAttr('checked');
                    inputs[i].checked = false;
                }
                $(this).removeClass('checked')
                $(this).attr('checked','')
            } else {

                var inputs =  $('#check_all_features').closest('td').find('input')
                for (var i=0;i<inputs.length;i++) {
                    $(inputs[i]).attr('checked','checked');
                    inputs[i].checked = true;
                }
                $(this).attr('checked','checked')
                $(this).addClass('checked')

            }
        })
    });
</script>