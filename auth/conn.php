<?php

try {
	$conn = new PDO("mysql:host=localhost;dbname=;charset=utf8", "root", "", array(PDO::ATTR_PERSISTENT => true));
}
catch (PDOException $e) {
	var_dump("unable to connect to mysql server", $e->getMessage());
}
?>