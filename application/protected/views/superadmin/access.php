

<div class="middlenarrow">
    <h1>Access</h1>
    <?php echo CHtml::link('Add new Action', array('superadmin/accessedit')); ?><br /><br />
    <?php if (count($access) > 0) :?>
        <table class="tablelist">
            <tr>
                <th>Controller</th>
                <th>Action</th>
                <th style="width: 40px">Enable</th>
                <th style="width: 50px">Tools</th>
                <th style="width: 90%">Description</th>
            </tr>
            <?php foreach ($access as $key => $action){ ?>
                <tr>
                    <td><?php echo $action->controller; ?></td>
                    <td><?php echo $action->action; ?></td>
                    <td class ="<?php echo $action->enable? 'EnableTD':'DisableTD'; ?>"><?php echo CHtml::link($action->enable?'Enable':'Disable', array('superadmin/accesschange', 'id' => $action->id)) ?></td>
                    <td>
                        <?php echo CHtml::link('Edit', array('superadmin/accessedit', 'id' => $action->id)); ?>&nbsp;<?php echo CHtml::link('Del', array('superadmin/accessdelete', 'id' => $action->id), array('title' => 'Delete action', 'onclick' => "return confirm('Do you really want to delete this action?')")); ?>
                    </td>
                    <td><?php echo $action->description; ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php endif; ?>
</div>