<?php
	namespace Suphle\Tests\Integration\Console;

	use Suphle\Contracts\Config\Console;

	use Suphle\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Commands\AltersConcreteCommand};

	use Suphle\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	use Symfony\Component\Console\Tester\CommandTester;

	abstract class TestCliRunner extends CommandLineTest {

		protected $sutName = AltersConcreteCommand::class;

		protected function runAltersConcrete ():int {

			$command = $this->consoleRunner->findHandler("test:alters_concrete");

			$commandTester = new CommandTester($command);

			return $commandTester->execute([ "new_value" => 8 ]);
		}
	}
?>