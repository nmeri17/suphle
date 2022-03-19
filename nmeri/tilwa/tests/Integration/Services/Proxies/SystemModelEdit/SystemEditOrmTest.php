<?php
	namespace Tilwa\Tests\Integration\Services\Proxies\SystemModelEdit;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Testing\Condiments\DirectHttpTest;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\GoodPutController;

	use Tilwa\Contracts\Database\OrmDialect;

	class SystemEditOrmTest extends IsolatedComponentTest {

		use DirectHttpTest;

		private function mockOrm ($numTimes) {

			$ormDialect = $this->createPartialMock(OrmDialect::class);

			$this->mockCalls([
				
				"runTransaction" => [$numTimes, [$this->anything()]]
			]);

			return $ormDialect;
		}

		public function test_update_method_runs_in_transaction () {

			// given
			$this->setHttpParams("/dummy", "put");

			$this->container->whenTypeAny()->needsArguments([

				OrmDialect::class => $this->mockOrm($this->once()) // then
			])

			->getClass(GoodPutController::class)

			->handlePutRequest(); // when
		}

		public function test_other_methods_dont_run_in_transaction () {

			// given
			$this->setHttpParams("/dummy", "put");

			$this->container->whenTypeAny()->needsArguments([

				OrmDialect::class => $this->mockOrm($this->never()) // then
			])

			->getClass(GoodPutController::class)

			->putOtherServiceMethod(); // when
		}
	}
?>