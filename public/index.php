<?php
	require_once ("../vendor/autoload.php");

	use AppEntry\MyApp;

	$awesomeApp = new MyApp;

	$awesomeApp->bootModules();

	$awesomeApp->extractFromContainer();

	echo $awesomeApp->diffusedRequestResponse(); // this should be wrapped in the loop thingy
?>