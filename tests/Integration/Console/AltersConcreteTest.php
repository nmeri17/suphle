<?php
	namespace Suphle\Tests\Integration\Console;

	use Suphle\Contracts\Config\Console;

	use Suphle\Testing\Proxies\WriteOnlyContainer;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Commands\AltersConcreteCommand};

	use Suphle\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	use Symfony\Component\Console\Command\Command;

	class AltersConcreteTest extends TestCliRunner {

		protected function getModules ():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$consoleConfig = Console::class;

					$container->replaceWithMock($consoleConfig, $consoleConfig, [

						"commandsList" => [$this->sutName]
					]);

					$container->replaceWithConcrete($this->sutName, $this->mockCommand(1));
				}),

				$this->replicateModule(ModuleTwoDescriptor::class, function (WriteOnlyContainer $container) {

					$consoleConfig = Console::class;

					$container->replaceWithMock($consoleConfig, $consoleConfig, [

						"commandsList" => [$this->sutName]
					]);

					$container->replaceWithConcrete($this->sutName, $this->mockCommand(0));
				})
			];
		}

		private function mockCommand (int $numTimes):AltersConcreteCommand {
		
			return $this->replaceConstructorArguments($this->sutName, [], [], [ // massProvide doesn't work in the actual test, since double is created there, thus container is unable to identify runner as a consumer and flush it

				"execute" => [$numTimes, [

					$this->anything(), $this->anything()
				]]
			]);
		}

		public function test_only_unique_commands_are_hydrated () {

			$this->assertSame( // then
				$this->runAltersConcrete(),  // when

				Command::SUCCESS
			);
		}
	}
?>