<?php
	namespace Suphle\Services\DependencyRules;

	use Suphle\Exception\Explosives\DevError\UnacceptableDependency;

	class ServicePreferenceHandler extends BaseDependencyHandler {

		public function evaluateClass (string $className):void {

			foreach ($this->constructorDependencyTypes($className) as $dependencyType) {

				if (!$this->isPermittedParent($this->argumentList, $dependencyType ))

					throw new UnacceptableDependency (

						$className, $dependencyType
					);
			}
		}
	}
?>