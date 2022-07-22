<?php
	namespace Suphle\Tests\Integration\Console;

	use Suphle\Contracts\Config\Console;

	use Suphle\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Commands\AltersConcreteCommand};

	use Suphle\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	use Symfony\Component\Console\Tester\CommandTester;

	abstract class TestCliRunner extends CommandLineTest {

		protected $sutName = AltersConcreteCommand::class;

		protected function getModules ():array {

			$bindsCommands = function (WriteOnlyContainer $container) {

				$consoleConfig = Console::class;

				$container->replaceWithMock($consoleConfig, $consoleConfig, [

					"commandsList" => [$this->sutName]
				]);

				$this->configureWriteOnly($container);
			};

			return [

				$this->replicateModule(ModuleOneDescriptor::class, $bindsCommands),

				$this->replicateModule(ModuleTwoDescriptor::class, $bindsCommands)
			];
		}

		/* using a single instance with one call verification is equal to injecting into both modules but with different call expectations; module one called once, module two zero times
		*/
		abstract protected function	configureWriteOnly (WriteOnlyContainer $container):void;

		protected function runAltersConcrete ():int {

			$command = $this->consoleRunner->findHandler("test:alters_concrete");

			$commandTester = new CommandTester($command);

			return $commandTester->execute([ "new_value" => 8 ]);
		}
	}
?>