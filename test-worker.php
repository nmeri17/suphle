<?php
	use Suphle\Modules\ModuleWorkerAccessor;

	use Spiral\RoadRunner\Environment;

	use Suphle\Tests\Mocks\PublishedTestModules;

	use Exception;

	require_once "vendor/autoload.php";

	$accessor = new ModuleWorkerAccessor(new PublishedTestModules);

	$currentMode = Environment::fromGlobals()->getMode();

	$accessor->setWorkerMode($currentMode);

	$accessor->runInSandbox(function ($accessor) {

		$accessor->buildIdentifier()->setActiveWorker();
	});

	if ($accessor->lastOperationSuccessful()) $accessor->openEventLoop();

	$accessor->runInSandbox(function ($accessor) use ($currentMode) {
var_dump(24);
		throw new Exception("Unable to set active worker ". $currentMode);
	});
?>