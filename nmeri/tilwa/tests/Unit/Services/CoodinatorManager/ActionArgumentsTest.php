<?php
	namespace Tilwa\Tests\Unit\Services\CoodinatorManager;

	use Tilwa\Contracts\Database\OrmDialect;

	use Tilwa\Services\Structures\{ModelfulPayload, ModellessPayload};

	use Tilwa\Services\CoodinatorManager;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\MockFacilitator};

	class ActionArgumentsTest extends IsolatedComponentTest {

		use MockFacilitator;

		public function test_rejects_unwanted_dependencies () {

			// given
			$correctParameters = [

				$this->negativeDouble(ModelfulPayload::class, []),

				$this->negativeDouble(ModellessPayload::class, [])
			];

			$incorrectParameters = [new stdClass];

			$sut = $this->container->getClass(CoodinatorManager::class);

			$newList = $sut->validActionDependencies(array_merge($correctParameters, $incorrectParameters)); // when

			$this->assertSame($newList, $correctParameters); // then
		}

		public function test_injects_dependencies () {

			// given, then
			$parameters = [

				$this->mockModelful(),

				$this->negativeDouble(ModellessPayload::class, []),

				$this->mockModelful()
			];

			$sut = $this->container->getClass(CoodinatorManager::class);

			$newList = $sut->prepareActionModels($parameters); // when
		}

		private function mockModelful ():ModelfulPayload {

			return $this->negativeDouble(ModelfulPayload::class, [], [

				"setDependencies" => [1, [
					$this->callback(function($subject) {

						return $subject instanceof OrmDialect;
					})
				]]
			]);
		}
	}
?>