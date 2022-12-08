<?php
	namespace Suphle\Services\DependencyRules;

	use InvalidArgumentException;

	class ActionDependenciesValidator extends BaseDependencyHandler {

		public function evaluateClass (string $className):void {

			foreach ($this->objectMeta->getPublicMethods($className) as $methodName) {

				foreach (
					$this->container->getMethodParameters(

						$methodName, $className
					) as $dependency
				) {

					if (!$this->isPermittedParent($this->argumentList, $dependency))

						throw new InvalidArgumentException(

							$this->getErrorMessage(

								$className, $dependency::class, $methodName
							)
						);
				}
			}
		}

		protected function getErrorMessage (string $consumer, string $dependency, string $methodName):string {

			return $consumer . "::". $methodName .

			" is forbidden from depending on $dependency";
		}
	}
?>