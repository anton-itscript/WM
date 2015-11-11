<?php

function getApplicationsParam($paramName)
{

    $params = require(dirname(__FILE__) . DIRECTORY_SEPARATOR .'params.php');
    return $params['applications'][$paramName];
}

function requireIfFileExist($filepath)
{

    if (is_file($filepath)) {
        return require($filepath);
    }

    return array();
}

function requireMorefiles($filepathArray)
{
    $array = array();
    foreach ($filepathArray as $filepath) {
        $array = array_merge($array,requireIfFileExist($filepath));
    }
    return $array;
}