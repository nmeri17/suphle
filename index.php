<?php

/**
 * Using this for testing the traditional setup without creating modules and other protocols on the starter project
*/
use Suphle\Server\ModuleWorkerAccessor;

use Suphle\Tests\Mocks\PublishedTestModules;

use GuzzleHttp\Psr7\ServerRequest;

require_once "vendor/autoload.php";

$writeHeaders = php_sapi_name() !== "cli";

echo (new ModuleWorkerAccessor(new PublishedTestModules(), true))

->buildIdentifier()->getRequestRenderer(
    $_GET["suphle_path"],
    
    $writeHeaders, ServerRequest::fromGlobals()
)
->render();
