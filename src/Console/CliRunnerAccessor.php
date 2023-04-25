<?php

namespace Suphle\Console;

use Suphle\Adapters\Console\SymfonyCli;

use Suphle\Modules\ModuleHandlerIdentifier;

/**
 * Wrapper class for reusing invocation to the underlying runner
*/
class CliRunnerAccessor
{
    protected readonly CliRunner $runner;

    public function __construct(
        ModuleHandlerIdentifier $handlerIdentifier,
        string $runnerName = "Suphle",
        protected readonly bool $isTestEnv = false
    ) {
        // without running this in the constructor, we'll need an additional method to boot modules before they can provide paths
        $this->runner = new CliRunner(
            $handlerIdentifier,
            new SymfonyCli($runnerName, "v2") // it's important that one instance is hard-coded for this client. If they're dynamically hydrated for each container, those instances will miss commands bound to this instance. Alternatively, we'd have to cycle through each module, no module container (both live and test mode), to bind the copy commands were bound to. It's not worth it
        );

        $this->runner->extractAvailableCommands();
    }

    /**
     * Sending path as argument instead of the constructor since we can't have access to any dynamic paths until containers have booted. In test environments, path-y objects can only be read after container booting
    */
    public function forwardCommandsToRunner(string $rootPath): void
    {

        $this->runner->setRootPath($rootPath)->funnelToClient();

        if (!$this->isTestEnv) {

            $this->runner->awaitCommands();
        }
    }

    public function getRunner(): CliRunner
    {

        return $this->runner;
    }
}
