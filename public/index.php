<?php
	require_once ("../vendor/autoload.php");

	use AppEntry\MyApp;

	$awesomeApp = new MyApp;

	// subject to how those guys make url available
	// set as early as possible for the below calls
	// set again inside the loop
	$awesomeApp->setRequestPath($_GET["suphle_url"]); // this depends on stdInputReader, so it's assumed that headers are equally set, possibly from here

	$awesomeApp->bootModules();

	$awesomeApp->extractFromContainer();

	echo $awesomeApp->diffusedRequestResponse(); // this should be wrapped in the loop thingy
?>