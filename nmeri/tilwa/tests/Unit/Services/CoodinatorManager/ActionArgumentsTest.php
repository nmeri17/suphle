<?php
	namespace Tilwa\Tests\Unit\Services\CoodinatorManager;

	use Tilwa\Contracts\Database\OrmDialect;

	use Tilwa\Services\Structures\{ModelfulPayload, ModellessPayload};

	use Tilwa\Services\CoodinatorManager;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use stdClass;

	class ActionArgumentsTest extends IsolatedComponentTest {

		public function test_rejects_unwanted_dependencies () {

			// given
			$correctParameters = [

				$this->getMockForAbstractClass(ModelfulPayload::class),

				$this->getMockForAbstractClass(ModellessPayload::class)
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

				$this->getMockForAbstractClass(ModellessPayload::class),

				$this->mockModelful()
			];

			$sut = $this->container->getClass(CoodinatorManager::class);

			$newList = $sut->prepareActionModels($parameters); // when
		}

		private function mockModelful ():ModelfulPayload {

			$modelful = $this->getMockForAbstractClass(ModelfulPayload::class);

			$this->mockCalls([

				"setDependencies" => [1, [
					$this->returnCallback(function($subject) {

						return $subject instanceof OrmDialect;
					})
				]]
			], $modelful);

			return $modelful;
		}
	}
?>