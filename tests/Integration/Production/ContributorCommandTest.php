<?php
	namespace Suphle\Tests\Integration\Production;

	use Suphle\Server\VendorBin;

	use Suphle\Hydration\Container;

	use Suphle\Meta\{ProjectInitializer, Commands\ContributorTestsCommand};

	use Suphle\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	use Symfony\Component\Process\Process;

	class ContributorCommandTest extends CommandLineTest {

		protected const TESTS_PATH = "gfc";

		protected bool $activateArgumentReading;

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container) ];
		}

		public function test_will_try_running_tests () {

			$this->mockTestRunnerProcess(ProjectInitializer::SYNC_TESTER, self::TESTS_PATH);

			$command = $this->consoleRunner->findHandler(

				ContributorTestsCommand::commandSignature()
			);

			// when
			$commandResult = (new CommandTester($command))->execute([

				"--" . ContributorTestsCommand::TESTS_PATH_OPTION => self::TESTS_PATH
			]);

			// then, sanity check
			$this->assertSame($commandResult, Command::SUCCESS );

			if (!$this->activateArgumentReading)

				$this->fail("Didn't execute phpunit process");
		}

		protected function mockTestRunnerProcess (string $runnerName, string $expectedContent):void {

			$this->activateArgumentReading = false;

			$vendorBin = VendorBin::class;

			$this->massProvide([

				$vendorBin => $this->positiveDouble($vendorBin, [

					"setProcessArguments" => new Process([]) // their new updates prohibits this from any form of doubling or auto-generation (as it was before)
				], [

					"setProcessArguments" => [

						$this->atLeastOnce(), [$this->callback(function($processName) use ($runnerName) {

							$this->activateArgumentReading = $processName == $runnerName;

							return true;
						}), $this->callback(function($processArguments) use ($expectedContent) {

							if ($this->activateArgumentReading)

								return in_array($expectedContent, $processArguments);

							return true;
						})]
					]
				])
			]);
		}

		public function test_can_optionally_switch_to_parallel_mode () {

			$parallelOptions = "--processes=5";

			$this->mockTestRunnerProcess(ProjectInitializer::ASYNC_TESTER, $parallelOptions);

			$command = $this->consoleRunner->findHandler(

				ContributorTestsCommand::commandSignature()
			);

			// when
			$commandResult = (new CommandTester($command))->execute([

				"--" . ContributorTestsCommand::TESTS_PATH_OPTION => self::TESTS_PATH,

				"--" . ContributorTestsCommand::PARALLEL_OPTION => $parallelOptions
			]);

			// then, sanity check
			$this->assertSame($commandResult, Command::SUCCESS );

			if (!$this->activateArgumentReading)

				$this->fail("Didn't execute ". ProjectInitializer::ASYNC_TESTER ." process");
		}
	}
?>