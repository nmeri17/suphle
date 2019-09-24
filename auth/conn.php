<?php

$dotenv = Dotenv\Dotenv::create( APP_ROOT ); // up one level

$dotenv->load();

try {
	$conn = new PDO("mysql:host=localhost;dbname=". getenv('DBNAME') . ";charset=utf8", getenv('DBUSER'), getenv('DBPASS'), array(PDO::ATTR_PERSISTENT => true));
}
catch (PDOException $e) {
	var_dump("unable to connect to mysql server", $e->getMessage());
}
?>