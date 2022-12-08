<?php
	namespace Suphle\Services\DependencyRules;

	use Suphle\Hydration\Container;

	use Suphle\Exception\Explosives\Generic\UnacceptableDependency;

	class OnlyLoadedByHandler extends BaseDependencyHandler {

		public function evaluateClass (string $className):void {

			foreach ($this->container->getMethodParameters(

				Container::CLASS_CONSTRUCTOR, $className
			) as $dependency) {

				$dependencyType = $dependency::class;
					
				if (
					$this->objectMeta->stringInClassTree(

						$dependencyType, $this->argumentList[0]
					) &&

					!$this->isPermittedParent(

						$this->argumentList[1], $dependency
					)
				)

					throw new UnacceptableDependency (

						$className, $dependencyType
					);
			}
		}
	}
?>