<?php
	namespace Tilwa\Tests\Integration\Services\Proxies\SystemModelEdit;

	use Tilwa\Contracts\Database\OrmDialect;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\SystemModelController;

	class SystemEditOrmTest extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds;

		private $ormDialect = OrmDialect::class;

		protected $usesRealDecorator = true;

		private function mockOrm ($numTimes) {

			return $this->positiveDouble($this->ormDialect, [], [
				
				"runTransaction" => [$numTimes, [$this->anything()]]
			]);
		}

		public function test_update_method_runs_in_transaction () {

			// given
			$this->setHttpParams("/dummy", "put");

			$this->container->whenTypeAny()->needsArguments([

				$this->ormDialect => $this->mockOrm($this->once()) // then
			])

			->getClass(SystemModelController::class)

			->handlePutRequest(); // when
		}

		public function test_other_methods_dont_run_in_transaction () {

			// given
			$this->setHttpParams("/dummy", "put");

			$this->container->whenTypeAny()->needsArguments([

				$this->ormDialect => $this->mockOrm($this->never()) // then
			])
			->getClass(SystemModelController::class)

			->putOtherServiceMethod(); // when
		}
	}
?>