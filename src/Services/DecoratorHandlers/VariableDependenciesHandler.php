<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Contracts\Hydration\ScopeHandlers\ModifyInjected;

	use Suphle\Hydration\Container;

	use Suphle\Services\Structures\SetsReflectionAttributes;

	class VariableDependenciesHandler implements ModifyInjected {

		use SetsReflectionAttributes;

		public function __construct(private readonly Container $container) {

			//
		}

		public function examineInstance (object $concrete, string $caller):object {

			$concreteName = $concrete::class;

			foreach ($this->attributesList as $attributeMeta) {
	
				foreach ($attributeMeta->newInstance()->dependencyMethods as $methodName) {

					$parameters = $this->container->getMethodParameters(
					
						$methodName, $concrete::class,

						[$concreteName]
					);

					call_user_func_array([$concrete, $methodName], $parameters);
				}
			}

			return $concrete;
		}
	}
?>