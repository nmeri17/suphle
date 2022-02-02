<?php
	namespace Tilwa\Tests\Integration\Services\CoodinatorManager;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	class ActionArgumentsTest extends IsolatedComponentTest {

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