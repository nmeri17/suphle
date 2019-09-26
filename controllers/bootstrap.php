<?php

	define('APP_ROOT', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR); // up one folder

	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false); // to retain int data type

?>