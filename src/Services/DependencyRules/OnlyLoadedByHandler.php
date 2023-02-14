<?php
	namespace Suphle\Services\DependencyRules;

	use Suphle\Hydration\Container;

	use Suphle\Exception\Explosives\Generic\UnacceptableDependency;

	class OnlyLoadedByHandler extends BaseDependencyHandler { // talk about this guy

		public function evaluateClass (string $className):void {

			foreach ($this->constructorDependencyTypes($className) as $dependencyType) {
					
				if (
					$this->objectMeta->stringInClassTree(

						$dependencyType, $this->argumentList[0]
					) &&

					!$this->isPermittedParent(

						$this->argumentList[1], $dependencyType
					)
				)

					throw new UnacceptableDependency (

						$className, $dependencyType
					);
			}
		}
	}
?>