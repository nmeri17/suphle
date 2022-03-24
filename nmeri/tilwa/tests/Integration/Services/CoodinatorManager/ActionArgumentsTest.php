<?php
	namespace Tilwa\Tests\Unit\Services\CoodinatorManager;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\Database\OrmDialect;

	use Tilwa\Services\Structures\{ModelfulPayload, ModellessPayload};

	use Tilwa\Services\CoodinatorManager;

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use stdClass;

	class ActionArgumentsTest extends ModuleLevelTest {

		private $moduleOne, $sut;

		protected function setUp ():void {

			$moduleOne = $this->moduleOne = new ModuleOneDescriptor(new Container);

			$this->sut = $moduleOne->getContainer()->getClass(CoodinatorManager::class);

			parent::setUp();
		}

		protected function getModules ():array {

			return [ $this->moduleOne ];
		}

		public function test_rejects_unwanted_dependencies () {

			// given
			$correctParameters = [

				$this->positiveDouble(ModelfulPayload::class, []),

				$this->positiveDouble(ModellessPayload::class, [])
			];

			$incorrectParameters = [new stdClass];

			$newList = $this->sut->validActionDependencies(array_merge($correctParameters, $incorrectParameters)); // when

			$this->assertSame($newList, $correctParameters); // then
		}

		public function test_injects_dependencies () {

			// given, then
			$parameters = [

				$this->mockModelful(),

				$this->positiveDouble(ModellessPayload::class, []),

				$this->mockModelful()
			];

			$newList = $this->sut->prepareActionModels($parameters); // when
		}

		private function mockModelful ():ModelfulPayload {

			return $this->positiveDouble(ModelfulPayload::class, [], [

				"setDependencies" => [1, [
					$this->callback(function($subject) {

						return $subject instanceof OrmDialect;
					})
				]]
			]);
		}
	}
?>