<?php
	namespace Suphle\Tests\Integration\Console;

	use Suphle\Contracts\Config\Console;

	use Suphle\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Commands\AltersConcreteCommand, Concretes\BCounter};

	use Suphle\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	use Symfony\Component\Console\Tester\CommandTester;

	abstract class TestCliRunner extends CommandLineTest {

		protected $sutName = AltersConcreteCommand::class;
  protected $bCounter = BCounter::class;

		protected function runAltersConcrete ():int {

			$command = $this->consoleRunner->findHandler(

				AltersConcreteCommand::commandSignature()
			);

			$commandTester = new CommandTester($command);

			return $commandTester->execute([

				AltersConcreteCommand::NEW_VALUE_ARGUMENT => 8
			]);
		}

		protected function mockBCounter (int $numTimes):BCounter {
		
			return $this->positiveDouble($this->bCounter, [], [

				"setCount" => [$numTimes, [$this->anything()]]
			]);
		}
	}
?>