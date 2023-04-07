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

		public function test_server_will_start_despite_static_falure_with_option () {

			$this->stubStaticFailure(); // given

			$this->mockServerStart(self::RR_CONFIG);

			$command = $this->consoleRunner->findHandler(

				HttpServerCommand::commandSignature()
			);

			// when
			$commandResult = (new CommandTester($command))->execute(

				$this->getServerOptions([

					"--" . HttpServerCommand::IGNORE_STATIC_FAILURE_OPTION => null // given 2
				])
			);

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

		protected function getServerOptions (array $additionalArguments = []):array {

			return array_merge([

				HttpServerCommand::MODULES_FOLDER_ARGUMENT => "Modules",

				HttpServerCommand::RR_CONFIG_ARGUMENT => self::RR_CONFIG,

				"--" . HttpServerCommand::DISABLE_SANITIZATION_OPTION => null
			], $additionalArguments);
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

			$this->stubStaticFailure($exceptionMessage, [

				"getErrorOutput" => [1, []]
			]); // given

			$this->expectOutputRegex("/$exceptionMessage/");

			$command = $this->consoleRunner->findHandler(

				HttpServerCommand::commandSignature()
			);

			// when
			$commandResult = (new CommandTester($command))

			->execute($this->getServerOptions());

			$this->assertSame($commandResult, Command::FAILURE );
		}

		protected function stubStaticFailure (string $exceptionMessage = "", array $psalmProcessMocks = []):void {

			$wrapperName = PsalmWrapper::class;

			$arguments = $this->getContainer()->getMethodParameters(

				Container::CLASS_CONSTRUCTOR, $wrapperName
			);

			$dummyProcess = $this->positiveDouble(Process::class, [

				"getOutput" => $exceptionMessage
			], $psalmProcessMocks);

			$this->massProvide([

				$wrapperName => $this->replaceConstructorArguments($wrapperName, $arguments, [

					"analyzeErrorStatus" => false, // given

					"getLastProcess" => $dummyProcess
				])
			]);
		}
	}
?>