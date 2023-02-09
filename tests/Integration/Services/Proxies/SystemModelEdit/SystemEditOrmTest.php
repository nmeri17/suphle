<?php
	namespace Suphle\Tests\Integration\Services\Proxies\SystemModelEdit;

	use Suphle\Contracts\Database\OrmDialect;

	use Suphle\Testing\TestTypes\ModuleLevelTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\SystemModelController;

	use Suphle\Tests\Integration\Services\ReplacesRequestPayload;

	class SystemEditOrmTest extends ModuleLevelTest {

		use ReplacesRequestPayload;

		private function mockOrm ($numTimes):void {

			$this->massProvide([

				OrmDialect::class => $this->positiveDouble(OrmDialect::class, [], [
				
					"runTransaction" => [$numTimes, [$this->anything()]]
				])
			]);
		}

		public function test_update_method_runs_in_transaction () {

			// given
			$this->stubRequestObjects(1);

			$this->mockOrm(1); // then

			$this->getContainer()->getClass(SystemModelController::class)

			->handlePutRequest(); // when
		}

		public function test_other_methods_dont_run_in_transaction () {

			// given
			$this->stubRequestObjects(1);

			$this->mockOrm(0); // then
			
			$this->getContainer()->getClass(SystemModelController::class)

			->putOtherServiceMethod(); // when
		}
	}
?>