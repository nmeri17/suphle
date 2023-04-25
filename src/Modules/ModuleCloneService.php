<?php

namespace Suphle\Modules;

use Suphle\Contracts\{ConsoleClient, Modules\DescriptorInterface};

use Suphle\Hydration\Container;

use Suphle\Modules\Structures\ActiveDescriptors;

use Suphle\File\{FolderCloner, FileSystemReader};

use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

use Symfony\Component\Console\Input\ArrayInput;

class ModuleCloneService
{
    protected string $executionPath;

    protected array $moduleList;

    public function __construct(
        protected readonly FileSystemReader $fileSystemReader,
        protected readonly ConsoleClient $consoleClient,
        protected readonly FolderCloner $folderCloner,
        protected readonly ModulesBooter $modulesBooter
    ) {

        //
    }

    public function setConsoleDetails(string $executionPath, array $moduleList): self
    {

        $this->executionPath = $executionPath;

        $this->moduleList = $moduleList;

        return $this;
    }

    public function setCommandDetails(string $sourceName, bool $isAbsoluteSource, string $writeDestination = null): self
    {

        $this->sourceName = $sourceName;

        $this->isAbsoluteSource = $isAbsoluteSource;

        $this->writeDestination = $writeDestination;

        return $this;
    }

    public function createModuleFolder(string $moduleName): bool
    {

        $contentsReplacement = $this->getContentReplacements($moduleName);

        return $this->folderCloner->setEntryReplacements(
            $contentsReplacement,
            $contentsReplacement,
            $contentsReplacement
        )
        ->transferFolder(
            $this->getSource(),
            $this->getDestination($moduleName)
        );
    }

    public function installModuleTemplates(
        string $moduleName,
        OutputInterface $output,
        ?string $descriptorName,
        ?string $componentArguments
    ): int {

        if (empty($descriptorName)) {
            return Command::SUCCESS;
        }

        $descriptor = $this->bootNewlyCreatedContainer($descriptorName);

        $command = $this->consoleClient->findCommand( // using the client without binding cliRunner itself (and using that as we do in the tests) because commands shouldn't have access to that high level object and have no need for all the functionality it provides

            InstallComponentCommand::commandSignature()
        );

        $command->setModules([

            ...$this->moduleList, $descriptor
        ]);

        $commandOptions = [

            InstallComponentCommand::HYDRATOR_MODULE_OPTION => $descriptor->exportsImplements()
        ];

        if (!is_null($componentArguments)) {

            $commandOptions["--" . InstallComponentCommand::COMPONENT_ARGS_OPTION] = $componentArguments;
        }

        $commandInput = new ArrayInput($commandOptions);

        return $command->run($commandInput, $output);
    }

    public function bootNewlyCreatedContainer(string $descriptorName): DescriptorInterface
    {

        $descriptor = new $descriptorName(new Container()); // this is why descriptor fqcn is necessary

        $this->modulesBooter->recursivelyBootModuleSet(
            new ActiveDescriptors([$descriptor])
        );

        return $descriptor;
    }

    protected function getSource(): string
    {

        if ($this->isAbsoluteSource) {

            return $this->sourceName;
        }

        return $this->fileSystemReader->noTrailingSlash(
            $this->executionPath
        ) . DIRECTORY_SEPARATOR. $this->sourceName;
    }

    protected function getDestination(string $target): string
    {

        $destination = $this->fileSystemReader->noTrailingSlash(
            $this->writeDestination ?? $this->executionPath
        ). DIRECTORY_SEPARATOR . $target;

        return $this->fileSystemReader->pathFromLevels($destination, "", 1); // since we expect to modify even the root folder itself, not only the children
    }

    /**
     * Cannot dynamically replace root namespace since at least one module must exist, which isn't true for fresh projects
    */
    protected function getContentReplacements(string $moduleName): array
    {

        return ["_module_name" => $moduleName];
    }
}
