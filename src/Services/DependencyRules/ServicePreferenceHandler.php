<?php
	namespace Suphle\Services\DependencyRules;

	use Suphle\Hydration\Container;

	use Suphle\Exception\Explosives\Generic\UnacceptableDependency;

	class ServicePreferenceHandler extends BaseDependencyHandler {

		public function evaluateClass (string $className):void {

			foreach ($this->container->getMethodParameters(

				Container::CLASS_CONSTRUCTOR, $className
			) as $dependency) {
					
				if (!$this->isPermittedParent(

					$this->argumentList, $dependency
				))

					throw new UnacceptableDependency (

						$className, $dependency::class
					);
			}
		}
	}
?>