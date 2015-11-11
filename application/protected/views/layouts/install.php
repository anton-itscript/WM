<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" >
    <link href="<?php echo Yii::app()->getBaseUrl(true)?>/css/reset.css" type="text/css" rel="stylesheet" >
    <link href="<?php echo Yii::app()->getBaseUrl(true)?>/css/install.css" type="text/css" rel="stylesheet" >
   
    <title><?php echo $this->getPageTitle()?></title>
</head>

<body>
<?php
    $cur_route = strtolower(Yii::app()->urlManager->parseUrl(Yii::app()->getRequest()));
    $cur_controller = strtolower($this->getId());
    $cur_controller_action = strtolower($this->getAction()->getId());
?>

<div id="headerwrap">
    <div id="header">
        <div id="header_top">
 
            <div id="header_company">
                <div><b>Weather</b> Monitor Setup</div>
            </div>

        </div>
        
        <div id="second_menu" >              
            
            <div class="clear"></div>
        </div>
    </div>
</div><!-- div#headerwrap-->    



<div id="middlewrap"> 
    <?php $this->widget('ThrowStatus');?>
    <?php echo $content?>
</div>

<div id="footerwrap">
    <div id="footer">
        <div id="logo"></div>    
        <div id="copyright">
            &copy; 2007-<?php echo date('Y'); ?>.
            Weather Monitor Software. Copyright Delairco Industries Pty Ltd
        </div>
    </div>
</div><!-- div#footerwrap -->   

</body>
</html>