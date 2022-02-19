<?php
	namespace Tilwa\Tests\Integration\Console;

	use Tilwa\Testing\TestTypes\CommandLineTest;

	// custom command

	use Symfony\Component\Console\Tester\CommandTester;

	class CliRunnerTest extends CommandLineTest {

		protected function getModules():array {

			return [];
		}

		public function test_only_unique_commands_are_run () {

			//
		}
	}
?>