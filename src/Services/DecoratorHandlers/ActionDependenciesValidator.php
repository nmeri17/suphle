<?php
	namespace Suphle\Services\DecoratorHandlers;

	use InvalidArgumentException;

	class ActionDependenciesValidator extends BaseArgumentModifier {

		public function transformMethods (object $concreteInstance, array $arguments, string $methodName):array {

			$actionInjectables = $concreteInstance->permittedArguments();

			foreach ($arguments as $argumentName => $dependency) {

				$foundMatch = false;

				$dependencyType = $dependency::class;

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

						$this->getErrorMessage(

							$concreteInstance, $dependencyType, $methodName
						)
					);
			}

			return $arguments;
		}

		protected function getErrorMessage (object $concrete, string $dependency, string $methodName):string {

			return $concrete::class . "::". $methodName .

			" is forbidden from depending on $dependency";
		}
	}
?>