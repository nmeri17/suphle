<?php
	namespace Suphle\Tests\Integration\Production;

	use Suphle\Hydration\Container;

	use Suphle\Server\{VendorBin, HttpServerOperations, PsalmWrapper, Commands\HttpServerCommand};

	use Suphle\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	use Symfony\Component\Process\Process;

	class HttpServerCommandTest extends CommandLineTest {

		protected const RR_CONFIG = "some/path";

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container)];
		}

		public function test_will_try_start_server_with_given_config () {

			$this->stubStaticFailure(""); // given

			$this->mockServerStart(self::RR_CONFIG);

			$command = $this->consoleRunner->findHandler(

				HttpServerCommand::commandSignature()
			);

			// when
			$commandResult = (new CommandTester($command))->execute([

				"--" . HttpServerCommand::RR_CONFIG_OPTION => self::RR_CONFIG,

				"--" . HttpServerCommand::DISABLE_SANITIZATION_OPTION => null,

				"--" . HttpServerCommand::IGNORE_STATIC_FAILURE_OPTION => null // given 2
			]);

			$this->assertSame($commandResult, Command::SUCCESS ); // then
		}

		protected function mockServerStart (string $configPath):void {

			$operationsService = HttpServerOperations::class;

			$arguments = $this->getContainer()->getMethodParameters(

				Container::CLASS_CONSTRUCTOR, $operationsService
			);

			$this->massProvide([

				$operationsService => $this->replaceConstructorArguments($operationsService, $arguments, [], [

					"startRRServer" => [1, [$configPath]]
				])
			]);
		}

		public function test_server_start_runs_command () {

			$this->mockServerStartProcess(self::RR_CONFIG); // then

			$this->getContainer()->getClass(HttpServerOperations::class)

			->startRRServer(

				self::RR_CONFIG // given
			); // when
		}

		protected function mockServerStartProcess (string $configPath):void {

			$vendorBin = VendorBin::class;

			$this->massProvide([

				$vendorBin => $this->replaceConstructorArguments($vendorBin, [], [

					/** @see ContributorCommandTest::mockVendorBin */
					"setProcessArguments" => new Process([])
				], [

					"setProcessArguments" => [1, [

						VendorBin::RR_BINARY, ["serve", "-c", $configPath]
					]]
				])
			]);
		}

		public function test_will_fail_on_static_check_error () {

			$exceptionMessage = "pammy_maduekwe";

			$this->stubStaticFailure($exceptionMessage); // given

			$this->expectOutputRegex("/$exceptionMessage/");

			$command = $this->consoleRunner->findHandler(

				HttpServerCommand::commandSignature()
			);

			// when
			$commandResult = (new CommandTester($command))->execute([

				"--" . HttpServerCommand::RR_CONFIG_OPTION => self::RR_CONFIG,

				"--" . HttpServerCommand::DISABLE_SANITIZATION_OPTION => null
			]);

			$this->assertSame($commandResult, Command::FAILURE );
		}

		protected function stubStaticFailure (string $exceptionMessage):void {

			$wrapperName = PsalmWrapper::class;

			$arguments = $this->getContainer()->getMethodParameters(

				Container::CLASS_CONSTRUCTOR, $wrapperName
			);

			$this->massProvide([

				$wrapperName => $this->replaceConstructorArguments($wrapperName, $arguments, [

					"analyzeErrorStatus" => false, // given

					"getLastProcess" => $this->positiveDouble(Process::class, [

						"getOutput" => $exceptionMessage
					]),

					"scanConfigLevel" => $this->returnSelf()
				])
			]);
		}
	}
?>