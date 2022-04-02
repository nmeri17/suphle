<?php
	namespace Tilwa\Tests\Integration\Console;

	use Tilwa\Contracts\Config\Console;

	use Tilwa\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Commands\AltersConcreteCommand, Concretes\BCounter};

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	use Symfony\Component\Console\Tester\CommandTester;

	class CliRunnerTest extends CommandLineTest {

		protected function getModules ():array {

			$bindsCommands = function (WriteOnlyContainer $container) {

				$consoleConfig = Console::class;

				$container->replaceWithMock($consoleConfig, $consoleConfig, [

					"commandsList" => [AltersConcreteCommand::class]
				]);
			};

			return [

				$this->replicateModule(ModuleOneDescriptor::class, $bindsCommands),

				$this->replicateModule(ModuleTwoDescriptor::class, $bindsCommands)
			];
		}

		public function test_only_unique_commands_are_hydrated () {

			$sut = AltersConcreteCommand::class;

			$this->massProvide([

				$sut => $this->positiveDouble($sut, [], [

					"execute" => [1, [$this->anything(), $this->anything()]]
				])
			]);

			$this->runAltersConcrete();
		}

		public function test_command_only_runs_once () {

			$bCounter = BCounter::class;

			$this->massProvide([

				$bCounter => $this->positiveDouble($bCounter, [], [

					"setCount" => [1, [$this->anything()]]
				])
			]);

			$this->runAltersConcrete();
		}

		private function runAltersConcrete ():void {

			$command = $this->consoleRunner->findHandler("test:alters_concrete");

			$commandTester = new CommandTester($command);

			$commandTester->execute([ "new_value" => 8 ]); // when
		}
	}
?>