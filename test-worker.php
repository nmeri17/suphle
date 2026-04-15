<?php

/**
* The only difference between this file and the worker in the project starter is vendor location and published modules given
*/
use Suphle\Server\ModuleWorkerAccessor;

use Spiral\RoadRunner\{Environment, Environment\Mode};

use Suphle\Tests\Mocks\PublishedTestModules;

require_once "vendor/autoload.php";

$publishedModules = new PublishedTestModules();

(new ModuleWorkerAccessor($publishedModules, Mode::MODE_HTTP))

->safeSetupWorker();
