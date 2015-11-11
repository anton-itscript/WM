<div class="middlewide">
    <div class="middlenarrow">
        
        <div class="navigation">
            
            <a href="<?php echo ($available_step >= 1 ? $this->createUrl('install/index') : '#'); ?>" class="<?php echo ($available_step >= 1 ? 'available ' : ''); echo ($current_step == 1? 'active' : ''); ?>" >
                1: Database Connection
            </a>

            <a href="<?php echo ($available_step >= 2 ? $this->createUrl('install/step2') : '#'); ?>" class="<?php echo ($available_step >= 2 ? 'available ' : ''); echo ($current_step == 2? 'active' : ''); ?>">
                2: Setup database
            </a>

            <a href="<?php echo ($available_step >= 3 ? $this->createUrl('install/step3') : '#'); ?>" class="<?php echo ($available_step >= 3 ? 'available ' : ''); echo ($current_step == 3? 'active' : ''); ?>">
                3: Check Following Paths
            </a>

            <a href="<?php echo ($available_step >= 4 ? $this->createUrl('install/step4') : '#'); ?>" class="<?php echo ($available_step >= 4 ? 'available ' : ''); echo ($current_step == 4? 'active' : ''); ?>">
                4: Schedule
            </a>
        
            <div class="clear"></div>
        </div>
    </div>
</div>