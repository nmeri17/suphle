<?php
	namespace Suphle\Tests\Integration\Production;

	use Suphle\Hydration\Container;

	use Suphle\Modules\Commands\CloneModuleCommand;

	use Suphle\Server\{VendorBin, Commands\HttpServerCommand};

	use Suphle\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	class HttpServerCommandTest extends CommandLineTest {

		protected const RR_CONFIG = "some/path";

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container)];
		}

		public function test_will_try_start_server_with_given_config () {

			$this->mockInitializerServicers();

			$command = $this->consoleRunner->findHandler(

				HttpServerCommand::commandSignature()
			);

			// when
			$commandResult = (new CommandTester($command))->execute([

				"--" . HttpServerCommand::RR_CONFIG_OPTION => self::RR_CONFIG,

				"--" . HttpServerCommand::DISABLE_SANITIZATION_OPTION => null
			]);

			$this->assertSame($commandResult, Command::SUCCESS );
		}

		protected function mockInitializerServicers ():void {

			$vendorBin = VendorBin::class;

			$this->massProvide([

				$vendorBin => $this->replaceConstructorArguments($vendorBin, [], [], [

					"setProcessArguments" => [$this->atLeastOnce(), [

						"rr", ["serve", "-c", self::RR_CONFIG]
					]]
				])
			]);
		}
	}
?>