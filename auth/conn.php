<?php

$dotenv = Dotenv\Dotenv::create(dirname(__DIR__, 1)); // up one level

$dotenv->load();

try {
	$conn = new PDO("mysql:host=localhost;dbname=". getenv('DBNAME') . ";charset=utf8", getenv('DBUSER'), getenv('DBPASS'), array(PDO::ATTR_PERSISTENT => true));
}
catch (PDOException $e) {
	var_dump("unable to connect to mysql server", $e->getMessage());
}
?>