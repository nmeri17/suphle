<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Contracts\Hydration\ScopeHandlers\ModifyInjected;

	use Suphle\Hydration\Container;

	class VariableDependenciesHandler implements ModifyInjected {

		private $container;

		public function __construct (Container $container) {

			$this->container = $container;
		}

		/**
		 * @param {concrete} VariableDependencies
		*/
		public function examineInstance (object $concrete, string $caller):object {

			$concreteName = get_class($concrete);

			foreach ($concrete->dependencyMethods() as $methodName) {

				$parameters = $this->container->getMethodParameters(
				
					$methodName, get_class($concrete),

					[$concreteName]
				);

				call_user_func_array([$concrete, $methodName], $parameters);
			}

			return $concrete;
		}
	}
?>