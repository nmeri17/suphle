<?php
	namespace Suphle\Tests\Integration\Console;

	use Suphle\Contracts\Config\Console;

	use Suphle\Testing\Proxies\WriteOnlyContainer;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\BCounter;

	use Suphle\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	use Symfony\Component\Console\Command\Command;

	use Suphle\Tests\Integration\Generic\TestsModuleList;

	class CommandNotInFirstModule extends TestCliRunner {

		use TestsModuleList;

		protected function setUp ():void {

			$this->setAllDescriptors();

			parent::setUp();
		}

		protected function getModules():array {

			return $this->getAllDescriptors();
		}

		protected function setModuleTwo ():array {

			return [

				$this->replicateModule(ModuleTwoDescriptor::class, function (WriteOnlyContainer $container) {

					$consoleConfig = Console::class;

					$container->replaceWithMock($consoleConfig, $consoleConfig, [

						"commandsList" => [$this->sutName]
					]);

					$container->replaceWithConcrete($this->bCounter, $this->mockBCounter(1));
				})
			];
		}

		// to verify all modules are properly booted
		public function test_command_can_run_when_not_in_1st_module () {

			$this->assertSame( // then
				$this->runAltersConcrete(),  // when

				Command::SUCCESS
			);
		}
	}
?>