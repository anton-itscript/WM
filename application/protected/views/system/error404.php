<!DOCTYPE html PUBLIC
	"-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="<?php echo Yii::app()->getBaseUrl(true)?>/css/reset.css" type="text/css" rel="stylesheet" >
    <link href="<?php echo Yii::app()->getBaseUrl(true)?>/css/styles.css" type="text/css" rel="stylesheet" >
        <link href="<?php echo Yii::app()->getBaseUrl(true)?>/css/error.css" type="text/css" rel="stylesheet" >
    <title>Bad Request</title>

</head>
<body>
    
<div id="headerwrap">
    <div id="header">
        <div id="header_top">
            <div id="first_menu">

                <br/><br/>
                <div id="header_time">

                </div>                 
            </div>  

            <div id="header_company">
                <div id="header_soft_name"><a href="<?php print ($_SERVER['SCRIPT_NAME']);?>"><b>Weather</b> Monitor</a></div>
                <div id="header_company_name"><?php echo Yii::app()->user->getSetting('current_company_name');?></div>
            </div>
            <div class="clear"></div>
        </div>

    </div>
</div><!-- div#headerwrap-->   

<div id="middlewrap"> 
    <div class="middlenarrow">
    <h1>Bad Request</h1>
    <h2><?php echo nl2br(CHtml::encode($data['message'])); ?></h2>
    <p>
    The request could not be understood by the server due to malformed syntax.
    Please do not repeat the request without modifications.
    </p>
    <p>
    If you think this is a server error, please contact <?php echo $data['admin']; ?>.
    </p>
    <div class="version">
    <?php echo date('Y-m-d H:i:s',$data['time']) .' '. $data['version']; ?>

    </div>
    </div>
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