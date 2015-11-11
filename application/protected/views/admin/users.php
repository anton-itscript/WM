

<div class="middlenarrow">
    <h1>Existing Users</h1>
    <?php echo CHtml::link('Add new User', array('admin/user')); ?><br /><br />
    <?php if (count($users) > 0) :?>
        <table class="tablelist">
            <tr>
                <th>User Name</th>
                <th>Role</th>
                <?php foreach ($actions as $action){ ?>
                    <th>
                        <?php
                        $res = array();
                        preg_match_all('/[A-Z][^A-Z]*?/Us',$action['action'],$res,PREG_SET_ORDER);


                        foreach ($res as $act) {
                            echo $act[0].'<br>';
                        }
                        ?>
                    </th>
                <?php } ?>
                <th>Tools</th>
            </tr>
            <?php foreach ($users as $user){ ?>
                <tr>
                    <td><?php echo $user->username; ?></td>
                    <td class ="<?php echo $user->role=='admin'? 'EnableTD':'DisableTD'; ?>"><?php
                        echo ucfirst($user->role);
                        ?></td>
                    <?php foreach ($actions as $action){
                        $check = AccessUser::checkActionAtUser($user->user_id,$action->id); ?>
                        <td class ="<?php echo $check? 'EnableTD':'DisableTD'; ?>">
                            <?php echo CHtml::link(
                                $check?'On':'Off',
                                array('admin/useraccesschange', 'user_id' => $user->user_id, 'action_id' => $action->id))
                            ?>
                        </td>

                    <?php } ?>
                    <td>
                        <?php echo CHtml::link('Edit', array('admin/user', 'user_id' => $user->user_id)); ?>
                        &nbsp;
                        <?php echo CHtml::link('Del', array('admin/userdelete', 'user_id' => $user->user_id), array('title' => 'Delete user', 'onclick' => "return confirm('Do you really want to delete this user?')")); ?>
                    </td>


                </tr>
            <?php } ?>
        </table>
    <?php endif; ?>
</div>