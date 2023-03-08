<?php
	namespace Suphle\Tests\Integration\Production;

	use Suphle\Server\VendorBin;

	use Suphle\Hydration\Container;

	use Suphle\Meta\Commands\ContributorTestsCommand;

	use Suphle\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	use Symfony\Component\Process\Process;

	class ContributorCommandTest extends CommandLineTest {

		protected const TESTS_PATH = "gfc";

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container) ];
		}

		public function test_will_try_running_tests () {

			$this->mockVendorBin();

			$command = $this->consoleRunner->findHandler(

				ContributorTestsCommand::commandSignature()
			);

			// when
			$commandResult = (new CommandTester($command))->execute([

				"--" . ContributorTestsCommand::TESTS_PATH_OPTION => self::TESTS_PATH
			]);

			// then, sanity check
			$this->assertSame($commandResult, Command::SUCCESS );
		}

		protected function mockVendorBin ():void {

			$vendorBin = VendorBin::class;

			$isTestCommand = false;

			$this->massProvide([

				$vendorBin => $this->positiveDouble($vendorBin, [

					"setProcessArguments" => new Process([]) // their new updates prohibits this from any form of doubling or auto-generation (as it was before)
				], [

					"setProcessArguments" => [

						$this->atLeastOnce(), [$this->callback(function($argument) use (&$isTestCommand) {

							$isTestCommand = $argument == "phpunit";

							return true;
						}), $this->callback(function($argument) use ($isTestCommand) {

							if ($isTestCommand)

								return $this->assertSame($argument, [self::TESTS_PATH]);

							return true;
						})]
					]
				])
			]);
		}
	}
?>