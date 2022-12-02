#!/usr/bin/env php
<?php
	require __DIR__.'/vendor/autoload.php';

	use Suphle\Console\CliRunnerAccessor;

	use Suphle\Tests\Mocks\PublishedTestModules;

	(new CliRunnerAccessor(new PublishedTestModules))

	->forwardCommandsToRunner(__DIR__);
?>