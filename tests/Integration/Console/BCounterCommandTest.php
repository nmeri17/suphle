<?php
	namespace Suphle\Tests\Integration\Console;

	use Suphle\Contracts\Config\Console;

	use Suphle\Testing\Proxies\WriteOnlyContainer;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Concretes\BCounter};

	use Suphle\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	use Suphle\Tests\Integration\Generic\TestsModuleList;

	use Suphle\Tests\Mocks\Interactions\{ModuleThree, ModuleOne};

	use Symfony\Component\Console\Command\Command;

	class BCounterCommandTest extends TestCliRunner {

		use TestsModuleList;

		protected function setUp ():void {

			$this->setAllDescriptors();

			parent::setUp();
		}

		protected function setModuleOne ():void {

			$this->moduleOne = $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

				$consoleConfig = Console::class;

				$container->replaceWithMock($consoleConfig, $consoleConfig, [

					"commandsList" => [$this->sutName]
				])
				->replaceWithConcrete($this->bCounter, $this->mockBCounter(1)); // first match
			});
		}

		protected function setModuleTwo ():void {

			$this->moduleTwo = $this->replicateModule(ModuleTwoDescriptor::class, function (WriteOnlyContainer $container) {

				$consoleConfig = Console::class;

				$container->replaceWithMock($consoleConfig, $consoleConfig, [

					"commandsList" => [$this->sutName]
				])
				->replaceWithConcrete($this->bCounter, $this->mockBCounter(0));
			})
			->sendExpatriates([

				ModuleThree::class => $this->moduleThree
			]);
		}

		protected function getModules ():array {

			return [$this->moduleOne, $this->moduleTwo];
		}

		public function test_command_only_runs_once () {

			$this->assertSame( // then
				$this->runAltersConcrete(),  // when

				Command::SUCCESS
			);
		}
	}
?>