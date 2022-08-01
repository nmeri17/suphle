<?php
	use Suphle\Modules\ModuleWorkerAccessor;

	use Suphle\Tests\Mocks\PublishedTestModules;

	require_once "vendor/autoload.php";

	/**
	 * This is for use when handling traditional requests i.e. without RR
	*/
	$handlerIdentifier = new PublishedTestModules;

	echo("Booting Modules...\n");

	$accessor = (new ModuleWorkerAccessor($handlerIdentifier, false))

	->buildIdentifier();

	echo("Listening for requests...\n");

	$accessor->getQueueWorker()->processTasks();
?>