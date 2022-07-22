<?php
	namespace Suphle\Tests\Integration\Console;

	use Suphle\Testing\Proxies\WriteOnlyContainer;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Commands\AltersConcreteCommand, Concretes\BCounter};

	use Symfony\Component\Console\Command\Command;

	class BCounterCommandTest extends TestCliRunner {

		private $singleInstance, $bCounter = BCounter::class;

		protected function setUp ():void {

			$this->singleInstance = $this->mockBCounter(1);

			parent::setUp();
		}

		protected function configureWriteOnly (WriteOnlyContainer $container):void {

			$container->replaceWithConcrete($this->bCounter, $this->singleInstance);
		}

		private function mockBCounter (int $numTimes):BCounter {
		
			return $this->positiveDouble($this->bCounter, [], [

				"setCount" => [$numTimes, [$this->anything()]]
			]);
		}

		public function test_command_only_runs_once () {

			$this->assertSame( // then
				$this->runAltersConcrete(),  // when

				Command::SUCCESS
			);
		}
	}
?>