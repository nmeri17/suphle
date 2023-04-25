<?php

namespace Suphle\Tests\Integration\Modules\Cloning;

use Suphle\Contracts\ConsoleClient;

use Suphle\Adapters\Console\SymfonyCli;

use Suphle\Console\CliRunnerAccessor;

use Suphle\Testing\{Proxies\Extensions\FrontDoor, TestTypes\IsolatedComponentTest};

use Suphle\Tests\Integration\Generic\CommonBinds;

class EmptyModulesTest extends IsolatedComponentTest
{
    use CommonBinds, SimpleCloneAssertions {

        CommonBinds::simpleBinds as commonSimples;
    }

    protected function setUp(): void
    {

        parent::setUp();

        $this->file = __DIR__ . "/test_file_" . sha1(uniqid(__METHOD__));
    }

    protected function simpleBinds(): array
    {

        return array_merge($this->commonSimples(), [

            ConsoleClient::class => SymfonyCli::class // we don't bother with this on CommandLineTest because their setUp boots the runner and binds that to all containers
        ]);
    }

    public function test_can_clone_without_modules()
    {

        $this->simpleCloneDependencies()->setConsoleRunner()

        ->assertSimpleCloneModule();
    }

    /**
     * Initialize cliRunner with no module
    */
    protected function setConsoleRunner(): self
    {

        $runnerAccessor = new CliRunnerAccessor(
            new FrontDoor([]),
            "SuphleTest",
            true
        );

        $runnerAccessor->forwardCommandsToRunner(
            $this->fileConfig->getRootPath()
        );

        $this->consoleRunner = $runnerAccessor->getRunner();

        $this->container = $this->consoleRunner->getDefaultContainer(); // without this, the doubling bind done to replace ModuleClonerService will not bind to the correct container

        return $this;
    }
}
