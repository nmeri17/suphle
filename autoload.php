<?php

	// this is the htaccess of php classes -- it tells the interpreter where/how to find our local classes

	require_once "vendor/autoload.php"; // for composer packages
	
	/*spl_autoload_register(function ($className) {
    
        $slash = DIRECTORY_SEPARATOR; $currDir = __DIR__;

        $currDirFolders = array_filter( scandir($currDir), function ($res) use ($currDir, $slash) {

        	return !in_array($res, ['.', '..']) && is_dir($currDir. $slash. $res);
        });
    
        $className = str_replace('\\', $slash, $className).'.php';

        if (is_readable($currDir. $slash.$className)) require_once $currDir. $slash.$className;

        else {

        	$foundInSub = array_filter ($currDirFolders, function( $dir) use ($currDir, $className, $slash) {return is_readable($currDir. $dir.$slash .$className);});

        	if (!empty($foundInSub)) require_once $currDir. array_values($foundInSub)[0] .$slash. $className;
        }
    });*/
?>