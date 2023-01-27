<?php
	namespace Suphle\Tests\Integration\Services\Proxies\SystemModelEdit;

	use Suphle\Contracts\Database\OrmDialect;

	use Suphle\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\SystemModelController;

	class SystemEditOrmTest extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds;

		protected string $ormDialect = OrmDialect::class;

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