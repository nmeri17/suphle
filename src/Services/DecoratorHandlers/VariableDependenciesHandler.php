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

			foreach ($this->attributesList as $attributeMeta) {
	
				foreach (

					$attributeMeta->newInstance()->dependencyMethods as

					$methodName
				) {

					$this->executeDependencyMethod($methodName, $concrete);
				}
			}

			return $concrete;
		}

		public function executeDependencyMethod (string $methodName, object $concrete):void {

			$concreteName = $concrete::class;

			$parameters = $this->container->getMethodParameters(
					
				$methodName, $concreteName,

				[$concreteName]
			);

			call_user_func_array([$concrete, $methodName], $parameters);
		}
	}
?>