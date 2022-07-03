<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\Hydration\ScopeHandlers\ModifyInjected;

	use Tilwa\Hydration\Container;

	class VariableDependenciesHandler implements ModifyInjected {

		private $container;

		public function __construct (Container $container) {

			$this->container = $container;
		}

		/**
		 * @param {concrete} VariableDependencies
		*/
		public function examineInstance (object $concrete, string $caller):object {

			foreach ($concrete->dependencyMethods() as $methodName) {

				$parameters = $this->container->getMethodParameters(
				
					$methodName, get_class($concrete)
				);

				call_user_func_array([$concrete, $methodName], $parameters);
			}

			return $concrete;
		}
	}
?>