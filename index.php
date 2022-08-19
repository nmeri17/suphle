<?php
	use Suphle\Modules\ModuleWorkerAccessor;

	use Suphle\Tests\Mocks\PublishedTestModules;

	require_once "vendor/autoload.php";

	$writeHeaders = !isset($_SERVER["REQUEST_TIME"]); // this is set by phpUnit--an env where we don't wanna write headers in

	echo (new ModuleWorkerAccessor(new PublishedTestModules, true))

	->buildIdentifier()->getRequestRenderer(

		$_GET["suphle_path"], $writeHeaders
	)
	->render();
?>