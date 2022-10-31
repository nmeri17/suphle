<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Contracts\Hydration\ScopeHandlers\ModifyInjected;

	use Suphle\Hydration\Container;

	class VariableDependenciesHandler implements ModifyInjected {

		public function __construct(private readonly Container $container)
  {
  }

		/**
		 * @param {concrete} VariableDependencies
		*/
		public function examineInstance (object $concrete, string $caller):object {

			$concreteName = $concrete::class;

			foreach ($concrete->dependencyMethods() as $methodName) {

				$parameters = $this->container->getMethodParameters(
				
					$methodName, $concrete::class,

					[$concreteName]
				);

				call_user_func_array([$concrete, $methodName], $parameters);
			}

			return $concrete;
		}
	}
?>