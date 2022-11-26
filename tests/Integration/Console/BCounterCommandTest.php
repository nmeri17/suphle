<?php
	namespace Suphle\Tests\Integration\Console;

	use Suphle\Contracts\Config\Console;

	use Suphle\Testing\Proxies\WriteOnlyContainer;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Concretes\BCounter};

	use Suphle\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	use Symfony\Component\Console\Command\Command;

	class BCounterCommandTest extends TestCliRunner {

		protected function getModules ():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$consoleConfig = Console::class;

					$container->replaceWithMock($consoleConfig, $consoleConfig, [

						"commandsList" => [$this->sutName]
					])
					->replaceWithConcrete($this->bCounter, $this->mockBCounter(1)); // first match
				}),

				$this->replicateModule(ModuleTwoDescriptor::class, function (WriteOnlyContainer $container) {

					$consoleConfig = Console::class;

					$container->replaceWithMock($consoleConfig, $consoleConfig, [

						"commandsList" => [$this->sutName]
					])
					->replaceWithConcrete($this->bCounter, $this->mockBCounter(0));
				})
			];
		}

		public function test_command_only_runs_once () {

			$this->assertSame( // then
				$this->runAltersConcrete(),  // when

				Command::SUCCESS
			);
		}
	}
?>