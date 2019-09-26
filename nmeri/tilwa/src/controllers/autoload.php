<?php

	// this is the htaccess of php classes -- it tells the interpreter where/how to find the requested class

	require_once "../vendor/autoload.php"; // bootstrap composer packages
	
	spl_autoload_register(function ($className) {
    
        $sl = DIRECTORY_SEPARATOR; $up = '..'. $sl;

        $upFolders = array_filter( scandir($up), function ($res) use ($up) {

        	return !in_array($res, ['.', '..']) && is_dir($up. $res);
        });
    
        $className = str_replace('\\', $sl, $className).'.php';

        if (is_readable($className)) require_once $className;

        else {

        	$foundInUp = array_filter ($upFolders, function( $dir) use ($up, $className, $sl) {return is_readable($up. $dir.$sl .$className);});

        	if (!empty($foundInUp)) require_once $up. array_values($foundInUp)[0] .$sl. $className;
        }
    });
?>