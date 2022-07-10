<?php
	require_once "vendor/autoload.php";

	use Tilwa\Modules\ModuleWorkerAccessor;

	use AppEntry\PublishedModules;

	(new ModuleWorkerAccessor(new PublishedModules))

	->onStart()->acceptRequests();
?>