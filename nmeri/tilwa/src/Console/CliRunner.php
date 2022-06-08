<?php
	namespace Tilwa\Console;

	use Tilwa\Contracts\{ConsoleClient, Config\Console};

	use Tilwa\Modules\ModuleHandlerIdentifier;

	class CliRunner {

		private $moduleHandler, $consoleClient, $allCommands = [];

		public function __construct (ModuleHandlerIdentifier $moduleHandler, ConsoleClient $consoleClient) {

			$this->moduleHandler = $moduleHandler;

			$this->consoleClient = $consoleClient;
		}

		public function loadCommands ():void {

			$this->moduleHandler->bootModules();

			$this->moduleHandler->extractFromContainer();

			$this->extractCommands();

			$this->funnelToClient();
		}

		private function extractCommands ():void {

			foreach ($this->moduleHandler->getModules() as $module) {

				$container = $module->getContainer();

				$commands = $container->getClass(Console::class)->commandsList();

				$newCommands = array_map(function ($name) use ($container) {

					return $container->getClass($name);
				}, $this->getUniqueCommands($commands));

				$this->allCommands = array_merge($this->allCommands, $newCommands);
			}
		}

		private function getUniqueCommands (array $commands):array {

			return array_diff ($commands, array_map("get_class", $this->allCommands) );
		}

		private function funnelToClient ():void {

			foreach ($this->allCommands as $command) {

				$command->setModules($this->moduleHandler->getModules());

				$this->consoleClient->add($command);
			}
		}

		public function awaitCommands ():void {

			$this->consoleClient->run();
		}

		public function findHandler (string $command):BaseCliCommand {

			return $this->consoleClient->find($command);
		}
	}
?>