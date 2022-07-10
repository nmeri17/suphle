<?php
	use Tilwa\Modules\ModuleWorkerAccessor;

	use Tilwa\Tests\Mocks\PublishedTestModules;

	require_once "../../vendor/autoload.php";

	(new ModuleWorkerAccessor(new PublishedTestModules))

	->onStart()->acceptRequests();
?>