<?php
	namespace Tilwa\Tests\Integration\Services\CoodinatorManager;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Services\CoodinatorManager;

	use Tilwa\Exception\Explosives\Generic\NoCompatibleValidator;

	class ValidatorTest extends IsolatedComponentTest {

		use DirectHttpTest;

		public function setUp () {

			parent::setUp();

			$this->manager = $this->container->getClass(CoodinatorManager::class);
		}

		public function test_get_needs_no_validation () {

			// given
			$this->setHttpParams("/dummy");

			$error = $this->manager->setDependencies($string, $actionMethod)

			->updateValidatorMethod(); // when

			$this->assertNull($error); // then
		}

		public function test_other_methods_requires_validation () {
// NoCompatibleValidator
			//
		}

		public function test_sets_validation_rules () {

			//
		}


		public function test_failed_validation_throws_error () {

			//sut => isValidatedRequest
		}
		private function setHandlerParameters (string $actionMethod):void {

			$parameters = $this->container->getMethodParameters($actionMethod, get_class($this->controller));

			$correctParameters = $this->validActionDependencies($parameters);

			$this->prepareActionModels($correctParameters);

			$this->handlerParameters = $correctParameters;
		}

		private function validActionDependencies (array $argumentList):array {

			$newList = [];

			foreach ($argumentList as $argument => $dependency) { // silently fail

				foreach ($this->actionInjectables as $validType)

					if ($dependency instanceof $validType) {

						$newList[$argument] = $dependency;

						break;
					}
			}

			return $newList;
		}

		private function prepareActionModels (array $argumentList):void {

			$orm = null;

			foreach ($argumentList as $dependency) {

				if (!($dependency instanceof ModelfulPayload))

					continue;

				if (is_null($orm))

					$orm = $this->container->getClass(Orm::class);

				$dependency->setDependencies($orm);
			}
		}
	}
?>