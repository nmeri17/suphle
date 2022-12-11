<?php
	namespace Suphle\Tests\Integration\Production;

	use Suphle\Hydration\Container;

	use Suphle\Modules\Commands\CloneModuleCommand;

	use Suphle\Server\{VendorBin, Commands\InitializeProjectCommand};

	use Suphle\Meta\ProjectInitializer;

	use Suphle\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	class InitializeProjectCommandTest extends CommandLineTest {

		protected const MODULE_NAME = "Uba",

		DESCRIPTOR_NAME = "Ramin";

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container)];
		}

		public function test_will_start_server_after_module_is_cloned () {

			$this->mockInitializerServicers();

			$command = $this->consoleRunner->findHandler(

				InitializeProjectCommand::commandSignature()
			);

			// when
			$commandResult = (new CommandTester($command))->execute([

				CloneModuleCommand::MODULE_NAME_ARGUMENT => self::MODULE_NAME,

				"--" . CloneModuleCommand::DESCRIPTOR_OPTION => self::DESCRIPTOR_NAME
			]);

			$this->assertSame($commandResult, Command::SUCCESS );
		}

		protected function mockInitializerServicers ():void {

			$initializerName = ProjectInitializer::class;

			$vendorBin = VendorBin::class;

			$this->massProvide([

				$initializerName => $this->replaceConstructorArguments($initializerName, [

					$vendorBin => $this->replaceConstructorArguments($vendorBin, [], [], [

						"setProcessArguments" => [$this->atLeastOnce(), []],
						"getServerLauncher" => [$this->atLeastOnce(), []]
					])
				], [

					"createModule" => Command::SUCCESS
				], [

					"createModule" => [1, [

						self::MODULE_NAME, self::DESCRIPTOR_NAME, $this->anything()
					]]
				])
			]);
		}
	}
?>