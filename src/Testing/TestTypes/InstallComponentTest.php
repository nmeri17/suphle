<?php

namespace Suphle\Testing\TestTypes;

use Suphle\ComponentTemplates\BaseComponentEntry;

use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

use Suphle\Testing\{Condiments\FilesystemCleaner, TestTypes\CommandLineTest};

use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

/**
 * A class rather than a trait since this is a specific test that can't be combined with something else
*/
abstract class InstallComponentTest extends CommandLineTest
{
    use FilesystemCleaner;

    protected ?BaseComponentEntry $componentInstance = null;

    abstract protected function componentEntry(): string;

    protected function assertInstalledComponent(array $commandOptions, bool $doubledInstaller = false): void
    {

        $componentPath = $this->getComponentPath();

        if ($this->componentIsInstalled()) {

            $this->getFilesystemReader()->emptyDirectory($componentPath);
        }

        // then
        $this->assertSame(
            $this->runInstallComponent($commandOptions), // when

            Command::SUCCESS
        );

        if (!$doubledInstaller) {

            $this->assertNotEmptyDirectory($componentPath);
        }
    }

    protected function componentIsInstalled(): bool
    {

        return file_exists($this->getComponentPath());
    }

    /**
     * @return int: Command result
    */
    protected function runInstallComponent(array $commandOptions): int
    {

        $command = $this->consoleRunner->findHandler(
            InstallComponentCommand::commandSignature()
        );

        return (new CommandTester($command))

        ->execute($commandOptions);
    }

    protected function getComponentPath(): string
    {

        return $this->getComponentInstance()->userLandMirror();
    }

    protected function getComponentInstance(): BaseComponentEntry
    {

        if (!is_null($this->componentInstance)) {

            return $this->componentInstance;
        }

        return $this->componentInstance = $this->getContainer()->getClass($this->componentEntry());
    }

    /**
     * For use as dataProvider
     *
     * @return Each installation state along with argument expected to be received by the ejector, ComponentEjector, not the individual entries
    */
    public function overrideOptions(): array
    {

        $entryName = $this->componentEntry();

        return [
            [[], null],
            [

                ["--" .InstallComponentCommand::OVERWRITE_OPTION], null
            ],
            [

                [
                    "--" .InstallComponentCommand::OVERWRITE_OPTION => [$entryName]
                ], [$entryName]
            ]
        ];
    }
}
