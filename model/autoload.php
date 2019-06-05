<?php

	// this is like the htaccess of php classes, tells the interpreter where/how to find the requested class
	spl_autoload_register(function ($className) {
    
        $ds = DIRECTORY_SEPARATOR;
        
        $dir = __DIR__; // look for classes within this folder
    
        $className = str_replace('\\', $ds, $className);    
        
        $file = "{$dir}{$ds}{$className}.php"; // get full name of file containing the required class

        if (is_readable($file)) require_once $file; // get file if it is readable
    });
?>