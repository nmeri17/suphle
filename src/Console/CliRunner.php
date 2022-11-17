<?php
	namespace Suphle\Console;

	use Suphle\Contracts\{ConsoleClient, Config\Console};

	use Suphle\Modules\{ModuleHandlerIdentifier, ModuleWorkerAccessor};

	use Suphle\Hydration\Container;

	use Suphle\Hydration\Structures\{BaseInterfaceCollection, ContainerBooter};

	class CliRunner {

		private string $projectRootPath;

		private ?Container $defaultContainer = null;

		private array $allCommands = [];

		public function __construct (

			private readonly ModuleHandlerIdentifier $moduleHandler,

			private readonly ConsoleClient $consoleClient
		) {

			//
		}

		public function setRootPath (string $projectRootPath):self {

			$this->projectRootPath = $projectRootPath;

			return $this;
		}

		public function extractAvailableCommands ():self {

			$allModules = $this->moduleHandler->getModules();

			if (!empty($allModules)) {

				(new ModuleWorkerAccessor($this->moduleHandler, false))

				->buildIdentifier();

				$this->extractCommandsFromModules($allModules);
			}
			else {

				$this->defaultContainer = new Container;

				(new ContainerBooter($this->defaultContainer ))

				->initializeContainer(BaseInterfaceCollection::class);

				$this->extractCommandsFromContainer($this->defaultContainer);
			}

			return $this;
		}

		private function extractCommandsFromModules (array $modules):void {

			foreach ($modules as $module)

				$this->extractCommandsFromContainer($module->getContainer());
		}

		private function extractCommandsFromContainer (Container $container):void {

			$commands = $container->getClass(Console::class)->commandsList();

			$newCommands = array_map(fn($name) => $container->getClass($name), $this->getUniqueCommands($commands));

			$this->allCommands = array_merge($this->allCommands, $newCommands);
		}

		private function getUniqueCommands (array $commands):array {

			return array_diff ($commands, array_map("get_class", $this->allCommands) );
		}

		public function funnelToClient ():void {

			foreach ($this->allCommands as $command) {

				$command->setModules($this->moduleHandler->getModules());

				$command->setExecutionPath($this->projectRootPath);

				if (!is_null($this->defaultContainer))

					$command->setDefaultContainer($this->defaultContainer);

				$this->consoleClient->add($command);
			}
		}

		/**
		 * Not necessary to be called in test env
		*/
		public function awaitCommands ():void {

			$this->consoleClient->run();
		}

		public function findHandler (string $command):BaseCliCommand {

			return $this->consoleClient->findCommand($command);
		}
	}
?>