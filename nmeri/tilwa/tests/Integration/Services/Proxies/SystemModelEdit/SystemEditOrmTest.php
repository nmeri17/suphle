<?php
	namespace Tilwa\Tests\Integration\Services\Proxies\SystemModelEdit;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Testing\Condiments\{DirectHttpTest, MockFacilitator};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\GoodPutController;

	use Tilwa\Contracts\Database\OrmDialect;

	class SystemEditOrmTest extends IsolatedComponentTest {

		use DirectHttpTest, MockFacilitator;

		private function mockOrm ($numTimes) {

			return $this->negativeStub(OrmDialect::class)->expects($numTimes)

			->method("runTransaction")->with($this->anything());
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