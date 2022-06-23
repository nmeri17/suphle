<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use InvalidArgumentException;

	class ActionDependenciesValidator extends BaseArgumentModifier {

		public function transformMethods (object $concreteInstance, array $arguments):array {

			$actionInjectables = $concreteInstance->permittedArguments();

			foreach ($arguments as $argumentName => $dependency) {

				$foundMatch = false;

				$dependencyType = get_class($dependency);

				foreach ($actionInjectables as $validType) {

					if ($this->objectMeta->stringInClassTree(

						$dependencyType, $validType
					)) {

						$foundMatch = true;

						break;
					}
				}

				if (!$foundMatch)

					throw new InvalidArgumentException(

						$this->getErrorMessage($concreteInstance, $dependency)
					);
			}

			return $arguments;
		}

		protected function getErrorMessage (object $concrete, string $dependency):string {

			return get_class($concrete) . "::". $this->calledMethod .

			" is forbidden from depending on $dependency";
		}
	}
?>