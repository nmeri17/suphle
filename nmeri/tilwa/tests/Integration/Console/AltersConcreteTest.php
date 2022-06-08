<?php
	namespace Tilwa\Tests\Integration\Console;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Commands\AltersConcreteCommand;

	use Symfony\Component\Console\Command\Command;

	class AltersConcreteTest extends TestCliRunner {

		private $singleInstance;

		protected function setUp ():void {

			$this->singleInstance = $this->mockCommand(1);

			parent::setUp();
		}

		protected function configureWriteOnly (WriteOnlyContainer $container):void {

			$container->replaceWithConcrete($this->sutName, $this->singleInstance);
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