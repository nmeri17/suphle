<?php
	use Tilwa\Modules\ModuleWorkerAccessor;

	use Spiral\RoadRunner\Environment;

	use Tilwa\Tests\Mocks\PublishedTestModules;

	require_once "vendor/autoload.php";

	$accessor = new ModuleWorkerAccessor(new PublishedTestModules);

	$accessor->setWorkerMode(Environment::fromGlobals()->getMode());

	$accessor->buildIdentifier()->setActiveWorker()->openEventLoop();
?>