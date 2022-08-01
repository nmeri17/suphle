<?php

	/**
	* The only thing difference between this file and the worker in the project starter is vendor location and published modules given
	*/
	use Suphle\Modules\ModuleWorkerAccessor;

	use Spiral\RoadRunner\{Environment, Environment\Mode};

	use Suphle\Tests\Mocks\PublishedTestModules;

	require_once "vendor/autoload.php";

	$publishedModules = new PublishedTestModules;

	$isHttpMode = Environment::fromGlobals()->getMode() === Mode::MODE_HTTP;

	(new ModuleWorkerAccessor($publishedModules, $isHttpMode))

	->safeSetupWorker();
?>